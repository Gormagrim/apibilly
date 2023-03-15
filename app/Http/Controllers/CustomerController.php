<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Company;
use App\Models\UserStatus;
use App\Models\UsersInfos;
use App\Models\Customers;
use App\Models\CustomersType;
use App\Models\ToDo;
use App\Models\Estimate;
use App\Models\EstimateStatus;
use App\Models\EstimateItems;
use App\Models\AddedItems;
use App\Models\CustomersInvoice;
use App\Models\Options;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Barryvdh\DomPDF\Facade\Pdf;


class CustomerController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['']]);
    }

    public function getCustomersList()
    {
        try {
            $allCustomers = Customers::with('CustomersCompany', 'CustomersType')->where(['id_users' => auth()->user()->id])->get();

            return response()->json($allCustomers, 200);
        } catch (ModelNotFoundException $e) {
            return response()->json('Liste non trouvée', 404);
        }
    }

    public function getCustomersListByName()
    {
        try {
            $allCustomers = Customers::with('CustomersCompany', 'CustomersType')->where(['id_users' => auth()->user()->id])->orderBy('Customers.lastname')->get();

            return response()->json($allCustomers, 200);
        } catch (ModelNotFoundException $e) {
            return response()->json('Liste non trouvée', 404);
        }
    }
    public function getCustomersListByType()
    {
        try {
            $allCustomers = Customers::with('CustomersCompany', 'CustomersType')->where(['id_users' => auth()->user()->id])->orderBy('Customers.id_customersType', 'Desc')->get();

            return response()->json($allCustomers, 200);
        } catch (ModelNotFoundException $e) {
            return response()->json('Liste non trouvée', 404);
        }
    }
    public function getCustomersListByPriority()
    {
        try {
            $allCustomers = Customers::with('CustomersCompany', 'CustomersType')->where(['id_users' => auth()->user()->id])->orderBy('Customers.priority', 'Asc')->get();

            return response()->json($allCustomers, 200);
        } catch (ModelNotFoundException $e) {
            return response()->json('Liste non trouvée', 404);
        }
    }
    public function getCustomersListByActivity()
    {
        try {
            $allCustomers = Customers::with('CustomersCompany', 'CustomersType')->where(['id_users' => auth()->user()->id])->where('Customers.nextActivity', '!=', null)->orderBy('Customers.nextActivity', 'Asc')->get();

            return response()->json($allCustomers, 200);
        } catch (ModelNotFoundException $e) {
            return response()->json('Liste non trouvée', 404);
        }
    }
    public function getCustomersListWithOutActivity()
    {
        try {
            $allCustomers = Customers::with('CustomersCompany', 'CustomersType')->where(['id_users' => auth()->user()->id])->where('Customers.nextActivity', '=', null)->get();

            return response()->json($allCustomers, 200);
        } catch (ModelNotFoundException $e) {
            return response()->json('Liste non trouvée', 404);
        }
    }

    public function getCustomers($id)
    {
        try {
            $Customer = Customers::with('CustomersCompany', 'CustomersType')->where(['id_users' => auth()->user()->id])->findOrFail($id);

            return response()->json($Customer, 200);
        } catch (ModelNotFoundException $e) {
            return response()->json('Liste non trouvée', 404);
        }
    }

    public function getAllType()
    {
        try {
            $CustomerType = CustomersType::get();

            return response()->json($CustomerType, 200);
        } catch (ModelNotFoundException $e) {
            return response()->json('Liste non trouvée', 404);
        }
    }

    public function updateCustomerType(Request $request)
    {
        try {
            $type = new Customers;
            $type->where('id', $request['id'])->update([
                'id_customersType' => htmlspecialchars($request['id_customersType'])
            ]);
            return response()->json([
                'message' => 'Vos informations ont bien été mise à jour',
                'userDescription' => $type
            ], 202);
        } catch (ModelNotFoundException $e) {
            return response()->json('Une erreur est survenue durant la création de votre contenu photo', 500);
        }
    }

    public function updateCustomerPriority(Request $request)
    {
        try {
            $type = new Customers;
            $type->where('id', $request['id'])->update([
                'priority' => htmlspecialchars($request['priority'])
            ]);
            return response()->json([
                'message' => 'Vos informations ont bien été mise à jour',
                'userDescription' => $type
            ], 202);
        } catch (ModelNotFoundException $e) {
            return response()->json('Une erreur est survenue durant la création de votre contenu photo', 500);
        }
    }

    public function updateCustomerNote(Request $request)
    {
        try {
            $type = new Customers;
            $type->where('id', $request['id'])->update([
                'note' => htmlspecialchars($request['note'])
            ]);
            return response()->json([
                'message' => 'Vos informations ont bien été mise à jour',
                'userDescription' => $type
            ], 202);
        } catch (ModelNotFoundException $e) {
            return response()->json('Une erreur est survenue durant la création de votre contenu photo', 500);
        }
    }

    public function getAllCompany()
    {
        try {
            $Company = Company::where('users_id', auth()->user()->id)->get();

            return response()->json($Company, 200);
        } catch (ModelNotFoundException $e) {
            return response()->json('Liste non trouvée', 404);
        }
    }

    public function addCustomer(Request $request)
    {
        $errors = [
            'firstname.required' => 'Une adresse mail est nécessaire à l\'inscription.',
            'lastname.required' => 'Une adresse mail est nécessaire à l\'inscription.',
            'isPro.required' => 'Une adresse mail est nécessaire à l\'inscription.',
            'priority.required' => 'Une adresse mail est nécessaire à l\'inscription.',
            'id_company.required' => 'Une adresse mail est nécessaire à l\'inscription.'
        ];
        $this->validate($request, [
            'firstname' => 'required',
            'lastname' => 'required',
            'isPro' => 'boolean',
            'priority' => 'required',
            'id_company' => 'required'
        ], $errors);
        $input = $request->only('firstname', 'lastname', 'isPro', 'note', 'personnalMail', 'personnalPhone', 'city', 'postalCode', 'address', 'priority', 'id_company', 'id_customersType');

        try {
            $customer = new Customers;
            $customer->firstname = htmlspecialchars($input['firstname']);
            $customer->lastname = htmlspecialchars($input['lastname']);
            $customer->isPro = htmlspecialchars($input['isPro']);
            $customer->priority = htmlspecialchars($input['priority']);
            $customer->note = htmlspecialchars($input['note']);
            $customer->personnalMail = htmlspecialchars($input['personnalMail']);
            $customer->personnalPhone = htmlspecialchars($input['personnalPhone']);
            $customer->city = htmlspecialchars($input['city']);
            $customer->postalCode = htmlspecialchars($input['postalCode']);
            $customer->address = htmlspecialchars($input['address']);
            $customer->id_company = htmlspecialchars($input['id_company']);
            $customer->id_users = auth()->user()->id;
            $customer->id_customersType = htmlspecialchars($input['id_customersType']);
            $customer->save();

            return response()->json([
                'message' => 'Vous vous êtes correctement inscit',
                'user' => $customer
            ], 201);
        } catch (ModelNotFoundException $e) {
            return response()->json('Une erreur est survenue durant la création de votre compte', 500);
        }
    }

    public function deleteCustomer(Request $request)
    {
        try {
            $customer = new Customers;
            $deleteCustomer = Customers::findOrFail($request->id);
            if ($deleteCustomer->id_users == auth()->user()->id || auth()->user()->id_userStatus == 1) {
                $customer->where('id', $request->id)->delete();
                return response()->json([
                    'message' => 'L\'article a bien été supprimé'
                ], 200);
            } else {
                return response()->json([
                    'message' => 'Seul le créateur de l\'article ou un administrateur peut le supprimé.'
                ], 403);
            }
        } catch (ModelNotFoundException $e) {
            return response()->json('Article non trouvé', 404);
        }
    }

    public function addCompany(Request $request)
    {
        $errors = [
            'companyName.required' => 'Une adresse mail est nécessaire à l\'inscription.',
            'postalCode.required' => 'Une adresse mail est nécessaire à l\'inscription.',
            'city.required' => 'Une adresse mail est nécessaire à l\'inscription.',
            'street.required' => 'Une adresse mail est nécessaire à l\'inscription.',
            'phoneNumber.required' => 'Une adresse mail est nécessaire à l\'inscription.'
        ];
        $this->validate($request, [
            'companyName' => 'required',
            'postalCode' => 'required',
            'city' => 'required',
            'street' => 'required',
            'phoneNumber' => 'required'
        ], $errors);
        $input = $request->only('companyName', 'postalCode', 'city', 'street', 'phoneNumber', 'website', 'companyMail', 'logoFileName');

        try {
            $company = new Company;
            $company->users_id = auth()->user()->id;
            $company->companyName = htmlspecialchars($input['companyName']);
            $company->postalCode = htmlspecialchars($input['postalCode']);
            $company->city = htmlspecialchars($input['city']);
            $company->street = htmlspecialchars($input['street']);
            $company->phoneNumber = htmlspecialchars($input['phoneNumber']);
            $company->website = htmlspecialchars($input['website']);
            $company->companyMail = htmlspecialchars($input['companyMail']);
            if ($request->hasFile('file')) {
                $image = $request->file('file');
                $destination_path = './resources/logo/';
                $image = 'Logo-' . $input['logoFileName'] . '-' . time() . '.' . $image->extension();
                $request->file('file')->move($destination_path, $image);

                $company->logoFileName = htmlspecialchars($input['logoFileName']);
                $company->logoLink =  '/resources/logo/' . $image;
            }
            $company->save();

            return response()->json([
                'message' => 'Vous vous êtes correctement inscit',
                'user' => $company
            ], 201);
        } catch (ModelNotFoundException $e) {
            return response()->json('Une erreur est survenue durant la création de votre compte', 500);
        }
    }

    public function getToDo($id)
    {

        try {
            $todo = ToDo::where(['id_users' => auth()->user()->id])->where(['id_customers' => $id])->orderBy('deliveryDate')->get();

            return response()->json($todo, 200);
        } catch (ModelNotFoundException $e) {
            return response()->json('Liste non trouvée', 404);
        }
    }

    public function getMyToDo()
    {

        try {
            $todo = ToDo::with('Customer')->where(['id_users' => auth()->user()->id])->limit(8)->orderBy('deliveryDate')->get();

            return response()->json($todo, 200);
        } catch (ModelNotFoundException $e) {
            return response()->json('Liste non trouvée', 404);
        }
    }

    public function getMyCourantToDo()
    {

        try {
            $todo = ToDo::with('Customer')->where(['id_users' => auth()->user()->id])->where('ToDo.isRemove', '0')->orderBy('deliveryDate')->get();

            return response()->json($todo, 200);
        } catch (ModelNotFoundException $e) {
            return response()->json('Liste non trouvée', 404);
        }
    }

    public function isDo(Request $request)
    {
        try {
            $todo = new ToDo;
            $todo->where('id', $request['id'])->update([
                'isDo' => '1'
            ]);
            return response()->json([
                'message' => 'Vos informations ont bien été mise à jour',
                'userDescription' => $todo
            ], 202);
        } catch (ModelNotFoundException $e) {
            return response()->json('Une erreur est survenue durant la création de votre contenu photo', 500);
        }
    }

    public function isNotDo(Request $request)
    {
        try {
            $todo = new ToDo;
            $todo->where('id', $request['id'])->update([
                'isDo' => '0'
            ]);
            return response()->json([
                'message' => 'Vos informations ont bien été mise à jour',
                'userDescription' => $todo
            ], 202);
        } catch (ModelNotFoundException $e) {
            return response()->json('Une erreur est survenue durant la création de votre contenu photo', 500);
        }
    }

    public function removeTodo(Request $request)
    {
        try {
            $todo = new ToDo;
            $todo->where('id', $request['id'])->update([
                'isRemove' => '1'
            ]);
            return response()->json([
                'message' => 'Vos informations ont bien été mise à jour',
                'userDescription' => $todo
            ], 202);
        } catch (ModelNotFoundException $e) {
            return response()->json('Une erreur est survenue durant la création de votre contenu photo', 500);
        }
    }

    public function addTodo(Request $request)
    {
        $errors = [
            'note.required' => 'Une note est nécessaire.',
            'id_customers.required' => 'Un client est nécessaire.',
            'deliveryDate.required' => 'Une date de fin est nécessaire.'
        ];
        $this->validate($request, [
            'note' => 'required',
            'id_customers' => 'required',
            'deliveryDate' => 'required'
        ], $errors);
        $input = $request->only('note', 'id_customers', 'deliveryDate');

        try {
            $toDo = new ToDo;
            $toDo->note = htmlspecialchars($input['note']);
            $toDo->id_customers = htmlspecialchars($input['id_customers']);
            $toDo->id_users = auth()->user()->id;
            $toDo->deliveryDate = htmlspecialchars($input['deliveryDate']);
            $toDo->save();

            return response()->json($toDo, 201);
        } catch (ModelNotFoundException $e) {
            return response()->json('Une erreur est survenue durant la création de votre compte', 500);
        }
    }

    public function updateNextActivityDate(Request $request)
    {
        try {
            $customer = new Customers;
            $customer->where('id', $request['id'])->update([
                'nextActivity' => $request['nextActivity']
            ]);
            return response()->json([
                'message' => 'Vos informations ont bien été mise à jour',
                'userDescription' => $customer
            ], 202);
        } catch (ModelNotFoundException $e) {
            return response()->json('Une erreur est survenue durant la création de votre contenu photo', 500);
        }
    }

    public function getNextToDo($id)
    {

        try {
            $todo = ToDo::where(['id_users' => auth()->user()->id])->where(['id_customers' => $id])->where('isDo', '0')->orderBy('deliveryDate')->limit(1)->get();

            return response()->json($todo, 200);
        } catch (ModelNotFoundException $e) {
            return response()->json('Liste non trouvée', 404);
        }
    }

    public function getAllEstimate()
    {

        try {
            $estimate = Estimate::with('Customers', 'Company', 'EstimateStatus', 'EstimateItems', 'AddedItems', 'CustomersInvoice')->where(['users_id' => auth()->user()->id])->get();

            return response()->json($estimate, 200);
        } catch (ModelNotFoundException $e) {
            return response()->json('Liste non trouvée', 404);
        }
    }

    public function getAllEstimateByDate(Request $request)
    {

        try {
            $estimate = Estimate::with('Customers', 'Company', 'EstimateStatus', 'EstimateItems', 'AddedItems', 'CustomersInvoice')->where(['users_id' => auth()->user()->id])->where('month', $request['month'])->where('year', $request['year'])->get();

            return response()->json($estimate, 200);
        } catch (ModelNotFoundException $e) {
            return response()->json('Liste non trouvée', 404);
        }
    }

    public function getEstimateLseven()
    {

        try {
            $estimate = Estimate::with('Customers', 'Company', 'EstimateStatus', 'EstimateItems', 'AddedItems', 'CustomersInvoice')->where(['users_id' => auth()->user()->id])->orderBy('created_at', 'Desc')->limit(6)->get();

            return response()->json($estimate, 200);
        } catch (ModelNotFoundException $e) {
            return response()->json('Liste non trouvée', 404);
        }
    }

    public function getEstimateWithInvoice()
    {

        try {
            
            $estimate = Estimate::with('Customers', 'Company', 'EstimateStatus', 'EstimateItems', 'AddedItems')->where(['users_id' => auth()->user()->id])->get();
            $invoice = CustomersInvoice::where('id_estimate', $estimate->id);
            return response()->json($estimate, 200);
        } catch (ModelNotFoundException $e) {
            return response()->json('Liste non trouvée', 404);
        }
    }

    public function getEstimate($id)
    {

        try {
            $estimate = Estimate::with('Customers', 'Company', 'EstimateStatus', 'EstimateItems', 'AddedItems')->where(['users_id' => auth()->user()->id])->findOrFail($id);

            return response()->json($estimate, 200);
        } catch (ModelNotFoundException $e) {
            return response()->json('Liste non trouvée', 404);
        }
    }

    public function getEstimateStatus()
    {

        try {
            $status = EstimateStatus::get();

            return response()->json($status, 200);
        } catch (ModelNotFoundException $e) {
            return response()->json('Liste non trouvée', 404);
        }
    }

    public function updateEstimateStatus(Request $request)
    {
        try {
            $estimate = new Estimate;
            $estimate->where('id', $request['id'])->update([
                'id_estimateStatus' => htmlspecialchars($request['id_estimateStatus'])
            ]);
            return response()->json([
                'message' => 'Vos informations ont bien été mise à jour',
                'userDescription' => $estimate
            ], 202);
        } catch (ModelNotFoundException $e) {
            return response()->json('Une erreur est survenue durant la création de votre contenu photo', 500);
        }
    }

    public function EstimateIsFinish(Request $request)
    {
        try {
            $estimate = new Estimate;
            $date = Carbon::now();
            $estimate->where('id', $request['id'])->update([
                'ended' => 1,
                'endDate' => $date
            ]);
            return response()->json([
                'message' => 'Vos informations ont bien été mise à jour',
                'userDescription' => $estimate
            ], 202);
        } catch (ModelNotFoundException $e) {
            return response()->json('Une erreur est survenue durant la création de votre contenu photo', 500);
        }
    }

    public function countEstimate()
    {
        try {
            $countEstimate = Estimate::where('users_id', auth()->user()->id)->count();

            //  return response()->json($countEstimate, 200);
        } catch (ModelNotFoundException $e) {
            return response()->json('Liste non trouvée', 404);
        }
    }

    public function addEstimate(Request $request)
    {
        $errors = [
            'estimateName.required' => 'Une adresse mail est nécessaire à l\'inscription.',
            'id_customers.required' => 'Une adresse mail est nécessaire à l\'inscription.',
            'created_at.required' => 'Une adresse mail est nécessaire à l\'inscription.'
        ];
        $this->validate($request, [
            'estimateName' => 'required',
            'id_customers' => 'required',
            'created_at' => 'required'
        ], $errors);

        $input = $request->only('estimateName', 'id_customers', 'created_at');

        try {
            $countEstimate = Estimate::where('users_id', auth()->user()->id)->withTrashed()->count();
            $countEstimate++;
            $date = Carbon::now();
            $m = $date->isoFormat('M');
            $y = $date->isoFormat('Y');
            $estimate = new Estimate;
            $estimate->month = $m;
            $estimate->year = $y;
            $estimate->estimateNumber = 'DEV-' . $y . '-' . ($m < 10 ? '0' . $m : $m) . '-' . ($countEstimate < 100 ? '00' . $countEstimate : ($countEstimate > 10 ? '0' . $countEstimate : $countEstimate));
            $estimate->estimateName = htmlspecialchars($input['estimateName']);
            $estimate->users_id = auth()->user()->id;
            $estimate->id_customers = htmlspecialchars($input['id_customers']);
            $estimate->created_at = htmlspecialchars($input['created_at']);

            $estimate->save();

            return response()->json([
                'message' => 'Vous vous êtes correctement inscit',
                'user' => $estimate
            ], 201);
        } catch (ModelNotFoundException $e) {
            return response()->json('Une erreur est survenue durant la création de votre devis', 500);
        }
    }

    public function getMyItems()
    {
        try {
            $myItems = EstimateItems::where('user_id', auth()->user()->id)->get();

            return response()->json($myItems, 200);
        } catch (ModelNotFoundException $e) {
            return response()->json('Liste non trouvée', 404);
        }
    }

    public function getThisItems($id)
    {
        try {
            $myItems = EstimateItems::where('user_id', auth()->user()->id)->findOrFail($id);

            return response()->json($myItems, 200);
        } catch (ModelNotFoundException $e) {
            return response()->json('Liste non trouvée', 404);
        }
    }

    public function addItemToEstimate(Request $request)
    {
        $errors = [
            'id.required' => 'Une note est nécessaire.',
            'id_estimateItems.required' => 'Un client est nécessaire.'
        ];
        $this->validate($request, [
            'id' => 'required',
            'id_estimateItems' => 'required'
        ], $errors);
        $input = $request->only('id', 'id_estimateItems');

        try {
            $item = new AddedItems;
            $item->id_estimateItems = htmlspecialchars($input['id_estimateItems']);
            $item->id = htmlspecialchars($input['id']);
            $item->save();


            return response()->json($item, 201);
        } catch (ModelNotFoundException $e) {
            return response()->json('Une erreur est survenue durant la création de votre compte', 500);
        }
    }

    public function updateItemToEstimate(Request $request)
    {
        try {
            $item = new AddedItems;
            $item->where('id', $request['id'])->where('id_estimateItems', $request['id_estimateItems'])->update([
                'quantity' => htmlspecialchars($request['quantity'])
            ]);
            return response()->json($item, 202);
        } catch (ModelNotFoundException $e) {
            return response()->json('Une erreur est survenue durant la création de votre devis', 500);
        }
    }

    public function updateItemPrice(Request $request)
    {
        try {
            $item = new AddedItems;
            $item->where('id', $request['id'])->where('id_estimateItems', $request['id_estimateItems'])->update([
                'priceHt' => htmlspecialchars($request['priceHt'])
            ]);
            return response()->json($item, 202);
        } catch (ModelNotFoundException $e) {
            return response()->json('Une erreur est survenue durant la création de votre devis', 500);
        }
    }

    public function updateEstimate(Request $request)
    {
        try {
            $estimate = new Estimate;
            $estimate->where('id', $request['id'])->update([
                'totalPriceHT' => htmlspecialchars($request['totalPriceHT']),
                'tva' => htmlspecialchars($request['tva']),
                'totalPriceTTC' => htmlspecialchars($request['totalPriceTTC']),
            ]);
            return response()->json($estimate, 202);
        } catch (ModelNotFoundException $e) {
            return response()->json('Une erreur est survenue durant la création de votre devis', 500);
        }
    }

    public function addNewItem(Request $request)
    {
        $errors = [
            'itemDesignation.required' => 'Un client est nécessaire.'
        ];
        $this->validate($request, [
            'itemDesignation' => 'required'
        ], $errors);
        $input = $request->only('itemReference', 'itemDesignation', 'itemUnity');

        try {
            $item = new EstimateItems;
            $countEstimateItems = EstimateItems::where('user_id', auth()->user()->id)->count();
            $countEstimateItems++;
            $item->itemReference = 'd-0' . $countEstimateItems;
            $item->itemDesignation = $input['itemDesignation'];
            $item->itemUnity = 'U';
            $item->user_id = auth()->user()->id;
            $item->save();

            return response()->json($item, 201);
        } catch (ModelNotFoundException $e) {
            return response()->json('Une erreur est survenue durant la création de votre compte', 500);
        }
    }

    public function deleteItemToEstimate(Request $request)
    {
        try {
            $additem = new AddedItems;
            $additem->where('id_estimateItems', $request->id_estimateItems)->where('id', $request->id)->delete();


            return response()->json([
                'message' => 'L\'article a bien été supprimé'
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json('Article non trouvé', 404);
        }
    }

    public function deleteEstimate(Request $request)
    {
        try {
            $estimate = new Estimate;

            $estimate->where('id', $request->id)->delete();
            return response()->json([
                'message' => 'L\'article a bien été supprimé'
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json('Article non trouvé', 404);
        }
    }

    public function getThisInvoice(Request $request)
    {
        try {
            $myItems = CustomersInvoice::where('user_id', auth()->user()->id)->where('id_estimate', $request->id_estimate)->where('id_customers', $request->id_customers)->get();

            return response()->json($myItems, 200);
        } catch (ModelNotFoundException $e) {
            return response()->json('Liste non trouvée', 404);
        }
    }

    public function getInvoice()
    {
        try {
            $myItems = CustomersInvoice::with('Estimate', 'Customers')->where('user_id', auth()->user()->id)->limit(6)->orderBy('billingDate', 'Desc')->get();

            return response()->json($myItems, 200);
        } catch (ModelNotFoundException $e) {
            return response()->json('Liste non trouvée', 404);
        }
    }

    public function invoiceIsPay(Request $request)
    {
        try {
            $invoice = new CustomersInvoice;
            $invoice->where('id', $request['id'])->update([
                'isPaid' => 1,
                'paymentDate' => htmlspecialchars($request['paymentDate'])
            ]);
            return response()->json($invoice, 202);
        } catch (ModelNotFoundException $e) {
            return response()->json('Une erreur est survenue durant la création de votre devis', 500);
        }
    }

    public function addNewInvoice(Request $request)
    {
        $errors = [
            'billPercentage.required' => 'Un client est nécessaire.',
            'id_estimate.required' => 'Un client est nécessaire.',
            'id_customers.required' => 'Un client est nécessaire.'
        ];
        $this->validate($request, [
            'billPercentage' => 'required',
            'id_estimate' => 'required',
            'id_customers' => 'required'
        ], $errors);
        $input = $request->only('billPercentage', 'id_estimate', 'id_customers');

        try {
            $invoice = new CustomersInvoice;
            $countInvoice = CustomersInvoice::where('user_id', auth()->user()->id)->count();
            $countEstimateItems = CustomersInvoice::where('id_estimate', htmlspecialchars($input['id_estimate']))->count();
            $countInvoice++;
            $date = Carbon::now();
            $m = $date->isoFormat('M');
            $y = $date->isoFormat('Y');
            $invoice->billingDate = $date;
            $invoice->month = ($m < 10 ? '0' . $m : $m);
            $invoice->year = $y;
            $invoice->num = $countEstimateItems;
            $invoice->invoiceNumber = 'FACT-' . $y . '-' . ($m < 10 ? '0' . $m : $m) . '-' . ($countInvoice < 100 ? '00' . $countInvoice : ($countInvoice > 10 ? '0' . $countInvoice : $countInvoice));
            $invoice->billPercentage = htmlspecialchars($input['billPercentage']);
            $invoice->id_customers = htmlspecialchars($input['id_customers']);
            $invoice->id_estimate = htmlspecialchars($input['id_estimate']);
            $invoice->user_id = auth()->user()->id;
            $invoice->save();

            return response()->json($invoice, 201);
        } catch (ModelNotFoundException $e) {
            return response()->json('Une erreur est survenue durant la création de votre compte', 500);
        }
    }

    public function getInvoiceByMonth(Request $request)
    {
        try {
            $date = Carbon::now();
            $y = $date->isoFormat('Y');
            $myItems = CustomersInvoice::with('Estimate')->where('user_id', auth()->user()->id)->where('month', 'like', '%' . $request->to . '%')->where('year', $y)->get();

            return response()->json($myItems, 200);
        } catch (ModelNotFoundException $e) {
            return response()->json('Liste non trouvée', 404);
        }
    }

    public function getInvoicePaidByMonth(Request $request)
    {
        try {
            $date = Carbon::now();
            $y = $date->isoFormat('Y');
            $myItems = CustomersInvoice::with('Estimate')->where('user_id', auth()->user()->id)->where('month', 'like', '%' . $request->to . '%')->where('year', $y)->where('isPaid', 1)->get();

            return response()->json($myItems, 200);
        } catch (ModelNotFoundException $e) {
            return response()->json('Liste non trouvée', 404);
        }
    }

    public function addPdfEstimate(Request $request)
    {
        try {
            $pdf = new Estimate;
            if ($request->hasFile('file')) {
                $image = $request->file('file');
                $destination_path = './resources/devis/';
                $image = 'devis-' . $request['estimateName'] . '-' . time() . '.' . $image->extension();
                $request->file('file')->move($destination_path, $image);

            }
            $pdf->where('id', $request['id'])->update([
                'pdfFileName' => htmlspecialchars($request['pdfFileName']),
                'FileName' => $image,
                'pdfLink' => '/resources/devis/' . $image
            ]);

            return response()->json($pdf, 201);
        } catch (ModelNotFoundException $e) {
            return response()->json('Une erreur est survenue', 500);
        }
    }

    public function addPdfInvoice(Request $request)
    {
        try {
            $pdf = new CustomersInvoice();
            if ($request->hasFile('file')) {
                $image = $request->file('file');
                $destination_path = './resources/factures/';
                $image = 'facture-' . $request['estimateName'] . '-' . time() . '.' . $image->extension();
                $request->file('file')->move($destination_path, $image);

            }
            $pdf->where('id', $request['id'])->update([
                'pdfFileName' => htmlspecialchars($request['pdfFileName']),
                'FileName' => $image,
                'pdfLink' => '/resources/factures/' . $image
            ]);

            return response()->json($pdf, 201);
        } catch (ModelNotFoundException $e) {
            return response()->json('Une erreur est survenue', 500);
        }
    }

    public function getInvoiceByDate(Request $request)
    {
        try {
            $myItems = CustomersInvoice::with('Estimate', 'Customers')->where('user_id', auth()->user()->id)->where('month', $request['month'])->where('year', $request['year'])->get();

            return response()->json($myItems, 200);
        } catch (ModelNotFoundException $e) {
            return response()->json('Liste non trouvée', 404);
        }
    }

    public function getOptions()
    {
        try {
            $options = Options::where('users_id', auth()->user()->id)->get();

            return response()->json($options, 200);
        } catch (ModelNotFoundException $e) {
            return response()->json('Liste non trouvée', 404);
        }
    }

    public function updateIsAe(Request $request)
    {
        try {
            $options = new Options;
            $options->where('users_id', auth()->user()->id)->update([
                'isAe' => $request['isAe']
            ]);
            return response()->json([
                'message' => 'Vos informations ont bien été mise à jour',
                'userDescription' => $options
            ], 202);
        } catch (ModelNotFoundException $e) {
            return response()->json('Une erreur est survenue durant la création de votre contenu photo', 500);
        }
    }
    public function updateInfos(Request $request)
    {
        try {
            $options = new Options;
            $options->where('users_id', auth()->user()->id)->update([
                'estiInfos' => $request['estiInfos']
            ]);
            return response()->json([
                'message' => 'Vos informations ont bien été mise à jour',
                'userDescription' => $options
            ], 202);
        } catch (ModelNotFoundException $e) {
            return response()->json('Une erreur est survenue durant la création de votre contenu photo', 500);
        }
    }
    public function updateValidity(Request $request)
    {
        try {
            $options = new Options;
            $options->where('users_id', auth()->user()->id)->update([
                'estiValidity' => $request['estiValidity']
            ]);
            return response()->json([
                'message' => 'Vos informations ont bien été mise à jour',
                'userDescription' => $options
            ], 202);
        } catch (ModelNotFoundException $e) {
            return response()->json('Une erreur est survenue durant la création de votre contenu photo', 500);
        }
    }

    public function updateColor(Request $request)
    {
        try {
            $options = new Options;
            $options->where('users_id', auth()->user()->id)->update([
                'estiColor' => $request['estiColor']
            ]);
            return response()->json([
                'message' => 'Vos informations ont bien été mise à jour',
                'userDescription' => $options
            ], 202);
        } catch (ModelNotFoundException $e) {
            return response()->json('Une erreur est survenue durant la création de votre contenu photo', 500);
        }
    }
    public function updateTva(Request $request)
    {
        try {
            $options = new Options;
            $options->where('users_id', auth()->user()->id)->update([
                'tva' => $request['tva']
            ]);
            return response()->json([
                'message' => 'Vos informations ont bien été mise à jour',
                'userDescription' => $options
            ], 202);
        } catch (ModelNotFoundException $e) {
            return response()->json('Une erreur est survenue durant la création de votre contenu photo', 500);
        }
    }
    public function updateCharges(Request $request)
    {
        try {
            $options = new Options;
            $options->where('users_id', auth()->user()->id)->update([
                'charges' => $request['charges']
            ]);
            return response()->json([
                'message' => 'Vos informations ont bien été mise à jour',
                'userDescription' => $options
            ], 202);
        } catch (ModelNotFoundException $e) {
            return response()->json('Une erreur est survenue durant la création de votre contenu photo', 500);
        }
    }

    public function getUsersList()
    {
        try {
            if (auth()->user()->id_userStatus = '1') {
                $allUsers = User::with('Company', 'UserStatus', 'UsersInfos')->get();
            }
            

            return response()->json($allUsers, 200);
        } catch (ModelNotFoundException $e) {
            return response()->json('Liste non trouvée', 404);
        }
    }
}
