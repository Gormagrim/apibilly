<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use  App\Models\User;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class AuthController extends Controller
{


    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['register', 'login', 'checkMail', 'checkPassword', 'checkUserName', 'reloadDualAuth']]);
    }

    public function register(Request $request)
    {
        $errors = [
            'mail.required' => 'Une adresse mail est nécessaire à l\'inscription.',
            'mail.email' => 'Merci de saisir une adresse mail valide.',
            'mail.unique' => 'Cette adresse mail a déjà été utilisée pour la création d\'un compte sur notre site.',
            'password.required' => 'Un mot de passe est obligatoire pour créer votre compte.',
            'userName' => 'Un nom d\'utilisateur est obligatoire.'
        ];
        $this->validate($request, [
            'mail' => 'required|email|unique:users',
            'password' => 'required',
            'isActive' => 'boolean',
            'inscriptionDate' => 'date',
            'id_userGroup' => 'regex:/^[1-7]$/',
            'userName' => 'required|unique:users'
        ], $errors);
        $input = $request->only('userName', 'mail', 'password');

        try {
            $user = new User;
            $user->userName = '@' . $input['userName'];
            $user->mail = $input['mail'];
            $password = $input['password'];
            $user->password = Hash::make($password);
            $user->validationKey = md5(uniqid());
            $user->dualAuth = '33';
            $user->save();

            return response()->json([
                'message' => 'Vous vous êtes correctement inscit',
                'user' => $user
            ], 201);
        } catch (ModelNotFoundException $e) {
            return response()->json('Une erreur est survenue durant la création de votre compte', 500);
        }
    }

    public function checkUserName(Request $request)
    {
        try {
            $checkUserName = new User();
            $userOk = $checkUserName->where('userName', $request->userName)->count();
            return response()->json(['message' => $userOk]);
        } catch (ModelNotFoundException $e) {
            return response()->json('Une erreur est survenue durant la requête.', 500);
        }
    }

  
    
    public function login(Request $request)
    {
        $this->validate($request, [
            'userName' => 'required',
            'password' => 'required'
        ], [
            'userName.required' => 'Votre nom d\'utilisateur est nécessaire à la connection.',
            'password.required' => 'Un mot de passe est nécessaire à la connection.'
        ]);

        $input = $request->only('userName', 'password');

        if (!$authorized = Auth::attempt($input)) {
            $code = 401;
            $output = [
                'code' => $code,
                'message' => 'Votre nom d\'utilisateur ou votre mot de passe comporte une erreur.'
            ];
        } else {
            $token = $this->respondWithToken($authorized);
            $code = 200;
            $output = [
                'code' => $code,
                'message' => 'Vous vous êtes correctement connecté',
                'token' => $token,
                'id' => auth()->user()->id,
                'userStatus' => auth()->user()->id_userStatus,
                'isActive' => auth()->user()->isActive,
                'userName' => auth()->user()->userName,
                'dualAuth' => auth()->user()->dualAuth,
                'mail' => auth()->user()->mail,
                'endDate' => auth()->user()->endDate
            ];
            $user = new User();
            $user->where('id', auth()->user()->id)->update([
                'lastC' => Carbon::now()
            ]);
        }
        return response()->json($output, $code);
    }

    public function reloadDualAuth(Request $request)
    {
        $this->validate($request, [
            'userName' => 'required'
        ], [
            'userName.required' => 'Votre nom d\'utilisateur est nécessaire.'
        ]);

        $input = $request->only('userName');

        try {
            $user = new User;
            $user->where('userName', $input['userName'])->update([
                'dualAuth' => rand(100000, 999999)
            ]);
            return response()->json([
                'message' => 'Vos informations ont bien été mise à jour',
                'userDescription' => $user
            ], 202);
        } catch (ModelNotFoundException $e) {
            return response()->json('Une erreur est survenue durant la modification de vos informations', 500);
        }
    }

    public function checkMail(Request $request)
    {
        try {
            $userMail = new User();
            $mail = $userMail->where('mail', $request->mail)->count();
            return response()->json(['message' => $mail]);
        } catch (ModelNotFoundException $e) {
            return response()->json('Une erreur est survenue durant la requête.', 500);
        }
    }

    public function checkPassword(Request $request)
    {
        try {
            $userPassword = new user;
            $password = $userPassword->where('mail', $request->mail)->first();
            if (Hash::check($request->password, $password->password)) {
                return response()->json([
                    'message' => 1
                ], 200);
            }else{
                return response()->json([
                   'message' => 'Votre adresse mail ou votre mot de passe comporte une erreur.'
                ], 403);
            }
        } catch (ModelNotFoundException $e) {
            return response()->json('Une erreur est survenue durant la requête.', 500);
        }
    }

    public function extendToken()
    {
        return $this->respondWithToken(Auth::refresh());
    }
   
}