<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group([
    'middleware' => 'api',
    'prefix' => 'v1'

], function ($router) {

    Route::post('createCustomer', 'Api\CustomerPage\CustomerController@createCustomer');
    Route::get('fetchAllCustomers', 'Api\CustomerPage\CustomerController@fetchAllCustomers');


    #process card payment
    Route::post('validateCard', 'Api\TransactionsPage\CardPaymentController@chargeCard');
    Route::post('validateCardPin', 'Api\TransactionsPage\CardPaymentController@validateCardPin');
    Route::post('validateOtp', 'Api\TransactionsPage\CardPaymentController@verifyOtpAndPayment');


    #customer transactions
    Route::get('fetchCustomerDetailsWithTransactions/{customerId}', 'Api\TransactionsPage\TransactionsController@fetchTransactionByCustomerId');
    Route::get('fetchTransactionByCustomerId/{customerId}', 'Api\TransactionsPage\TransactionsController@fetchCustomerTransactions');

});





Route::fallback(function(){
    return response()->json([
        'status' => false,'message' => 'Page Not Found. If error persists, contact admin'], 404);
});
