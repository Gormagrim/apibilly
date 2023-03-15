<?php

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/
use App\Models\PDFController;

$router->get('/', function () use ($router) {
    return $router->app->version();
});
$router->post('register', 'AuthController@register');
$router->post('login', 'AuthController@login');
$router->put('refresh', 'AuthController@extendToken');
$router->get('user', 'UserController@getUsersList');
$router->get('thisuser', 'UserController@getUser');
$router->put('password', 'UserController@passwordModify');
$router->post('checkPassword', 'AuthController@checkPassword');
$router->post('checkMail', 'AuthController@checkMail');
$router->post('checkUserName', 'AuthController@checkUserName');
$router->post('reloadDualAuth', 'AuthController@reloadDualAuth');
$router->get('getUsersinfos', 'UserController@getUsersinfos');
$router->post('addLogo', 'UserController@addLogo');
$router->put('updateUsersInfos', 'UserController@updateUsersInfos');
$router->get('customers', 'CustomerController@getCustomersList');
$router->get('customersbyname', 'CustomerController@getCustomersListByName');
$router->get('customersbytype', 'CustomerController@getCustomersListByType');
$router->get('customersbypriority', 'CustomerController@getCustomersListByPriority');
$router->get('customersbyactivity', 'CustomerController@getCustomersListByActivity');
$router->get('customersoutactivity', 'CustomerController@getCustomersListWithOutActivity');
$router->get('customer/{id}', 'CustomerController@getCustomers');
$router->get('alltype', 'CustomerController@getAllType');
$router->put('updatetype', 'CustomerController@updateCustomerType');
$router->put('updatepriority', 'CustomerController@updateCustomerPriority');
$router->put('updatenote', 'CustomerController@updateCustomerNote');
$router->get('allcompany', 'CustomerController@getAllCompany');
$router->post('addcustomer', 'CustomerController@addCustomer');
$router->delete('delcustomer/{id}', 'CustomerController@deleteCustomer');
$router->post('addcompany', 'CustomerController@addCompany');
$router->get('todo/{id}', 'CustomerController@getToDo');
$router->get('mytodo', 'CustomerController@getMyToDo');
$router->get('myIntodo', 'CustomerController@getMyCourantToDo');
$router->put('isdo', 'CustomerController@isDo');
$router->put('isnotdo', 'CustomerController@isNotDo');
$router->put('removeTodo', 'CustomerController@removeTodo');
$router->post('addTodo', 'CustomerController@addTodo');
$router->put('nextactivity', 'CustomerController@updateNextActivityDate');
$router->get('nexttodo/{id}', 'CustomerController@getNextToDo');
$router->get('estimate', 'CustomerController@getAllEstimate');
$router->post('estimatebydate', 'CustomerController@getAllEstimateByDate');
$router->get('estimateSeven', 'CustomerController@getEstimateLseven');
$router->get('myestimate/{id}', 'CustomerController@getEstimate');
$router->get('estimateStatus', 'CustomerController@getEstimateStatus');
$router->put('estimateStatusUpdate', 'CustomerController@updateEstimateStatus');
$router->get('generate-pdf', 'PDFController@generatePDF');
$router->post('estimate', 'CustomerController@addEstimate');
$router->get('countestimate', 'CustomerController@countEstimate');
$router->get('myitems', 'CustomerController@getMyItems');
$router->get('myitems/{id}', 'CustomerController@getThisItems');
$router->post('additem', 'CustomerController@addItemToEstimate');
$router->put('updateitem', 'CustomerController@updateItemToEstimate');
$router->put('updateitemprice', 'CustomerController@updateItemPrice');
$router->put('estimateUpdate', 'CustomerController@updateEstimate');
$router->put('estimatefinish', 'CustomerController@EstimateIsFinish');
$router->post('addnewitem', 'CustomerController@addNewItem');
$router->delete('delitem', 'CustomerController@deleteItemToEstimate');
$router->delete('estimate', 'CustomerController@deleteEstimate');
$router->post('invoice', 'CustomerController@getThisInvoice');
$router->get('invoice', 'CustomerController@getInvoice');
$router->put('ispay', 'CustomerController@invoiceIsPay');
$router->post('newInvoice', 'CustomerController@addNewInvoice');
$router->post('monthInvoice', 'CustomerController@getInvoiceByMonth');
$router->post('monthInvoicePaid', 'CustomerController@getInvoicePaidByMonth');
$router->post('estipdf', 'CustomerController@addPdfEstimate');
$router->post('invoicepdf', 'CustomerController@addPdfInvoice');
$router->get('devis/{fileName}', 'DownloadsController@downloadEstiamte');
$router->get('factures/{fileName}', 'DownloadsController@downloadInvoice');
$router->post('invoicebydate', 'CustomerController@getInvoiceByDate');
$router->get('options', 'CustomerController@getOptions');
$router->put('isae', 'CustomerController@updateIsAe');
$router->put('infos', 'CustomerController@updateInfos');
$router->put('validity', 'CustomerController@updateValidity');
$router->put('color', 'CustomerController@updateColor');
$router->put('tva', 'CustomerController@updateTva');
$router->put('charges', 'CustomerController@updateCharges');
$router->get('users', [
    'middleware' => 'role:100',
    'uses' => 'CustomerController@getUsersList'
]);