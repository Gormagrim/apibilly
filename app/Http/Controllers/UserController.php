<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Company;
use App\Models\UserStatus;
use App\Models\UsersInfos;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use GuzzleHttp\Client;


class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['getUsersList']]);
    }

    public function getUsersList()
    {
        try {
            $allUsers = User::get();
            return response()->json($allUsers);
        } catch (ModelNotFoundException $e) {
            return response()->json('Liste non trouvée', 404);
        }
    }
    public function getUser()
    {
        try {
            $allUsers = User::with('UserStatus')->where('id', auth()->user()->id)->get();
            return response()->json($allUsers);
        } catch (ModelNotFoundException $e) {
            return response()->json('Liste non trouvée', 404);
        }
    }

    public function getUsersinfos()
    {
        try {
            $user = User::where(['id' => auth()->user()->id])->first();
            $userInfos = UsersInfos::where(['id_users' => auth()->user()->id])->first();
            $userStatus = UserStatus::where(['id' => auth()->user()->id_userStatus])->first();
            $company = Company::where(['id' => auth()->user()->id_company])->first();

            return response()->json([
                'utilisateur' => $user,
                'infos' => $userInfos,
                'status' => $userStatus,
                'entreprise' => $company
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json('Liste non trouvée', 404);
        }
    }

    public function updateUsersInfos(Request $request)
    {
        try {
            $userInfos = new UsersInfos;
            $user = new User;
            $company = new Company;
            $user->where('id', auth()->user()->id)->update([
                'userName' => $request['userName'],
                'mail' => $request['mail']
            ]);
            $userInfos->where('id_users', auth()->user()->id)->update([
                'firstname' => htmlspecialchars($request['firstname']),
                'lastname' => htmlspecialchars($request['lastname'])
            ]);
            $company->where('id', auth()->user()->id_company)->update([
                'companyName' => htmlspecialchars($request['companyName']),
                'phoneNumber' => htmlspecialchars($request['phoneNumber']),
                'companyMail' => htmlspecialchars($request['companyMail']),
                'postalCode' => htmlspecialchars($request['postalCode']),
                'city' => htmlspecialchars($request['city']),
                'street' => htmlspecialchars($request['street']),
            ]);
            return response()->json([
                'message' => 'Vos informations ont bien été mise à jour',
                'userDescription' => $user
            ], 202);
        } catch (ModelNotFoundException $e) {
            return response()->json('Une erreur est survenue durant la création de votre contenu photo', 500);
        }
    }

    public function addLogo(Request $request)
    {
        $this->validate($request, [
            'logoFileName' => 'required',
            'file' => 'required|image:jpeg,png,jpg,gif,svg|max:1024'
        ], [
            'logoFileName.required' => 'Le titre d\'une photo est obligatoire.',
            'file.required' => 'Merci de selectionner un fichier photo.',
            'file.image' => 'Le fichier photo doit obligatoirement être en .jpeg, .png, .jpg, .gif ou .svg.',
            'file.max' => 'La taille du fichier ne doit pas dépasser 1024 ko.'
        ]);
        $input = $request->only('logoFileName', 'file');

        try {
            if ($request->hasFile('file')) {
                $image = $request->file('file');
                $destination_path = './resources/logo/';
                $image = 'Logo-' . time() . '.' . $image->extension();
                $request->file('file')->move($destination_path, $image);
                $logo = new Company;
                $logo->where('id', auth()->user()->id_company)->update([
                    'logoFileName' => $input['logoFileName'],
                    'logoLink' => '/resources/logo/' . $logo->photoFileName . $image
                ]);


                return response()->json([
                    'message' => 'Votre contenu photo a bien été créé',
                    'user' => $logo
                ], 201);
            } else {
                return response()->json('Merci de sélectionner un fichier.', 403);
            }
        } catch (ModelNotFoundException $e) {
            return response()->json('Une erreur est survenue durant la création de votre contenu photo', 500);
        }
    }

    public function passwordModify(request $request)

    {

        $this->validate($request, [
            'current_password' => 'required',
            'password' => 'required|min:8',
            'password_confirmation' => 'required|same:password'
        ], [
            'password.required' => 'Merci de saisir votre nouveau mot de passe',
            'current_password.required' => 'Merci de saisir votre mot de passe actuel.',
            'password_confirmation.required' => 'Merci de confirmer votre nouveau mot de passe.',
            'password_confirmation.same' => 'Il y a une erreur dans la confirmation de votre mot de passe.',
            'password.min' => 'Votre mot de passe doit contenir minimum 8 caractères.'
        ]);
        $user = new user;
        $checkUser = $user->findOrFail(auth()->user()->id);
        $current = $request->current_password;
        try {
            if (Hash::check($current, $checkUser->password)) {
                if ($request->password == $request->password_confirmation) {
                    if ($request->password != $request->current_password) {
                        $user->where('id', auth()->user()->id)->update([
                            'password' => Hash::make($request->password),
                            'password_modification_date' => Carbon::now()
                        ]);
                        return response()->json([
                            'message' => 'Votre mot de passe à bien été modifié.'
                        ], 202);
                    } else {
                        return response()->json([
                            'message' => 'Votre nouveau mot de passe doit être différent de l\'ancien.'
                        ], 403);
                    }
                } else {
                    return response()->json([
                        'message' => 'Il y a une erreur dans la confirmation de votre mot de passe.'
                    ], 403);
                }
            } else {
                return response()->json([
                    'message' => 'Votre mot de passe actuel n\'est pas correct.'
                ], 403);
            }
        } catch (ModelNotFoundException $e) {
            return response()->json('Une erreur est survenue durant la modification de votre mot de passe.', 500);
        }
    }
}
