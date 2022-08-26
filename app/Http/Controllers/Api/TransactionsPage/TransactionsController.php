<?php

namespace App\Http\Controllers\Api\TransactionsPage;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Helpers\PrepareResponseController;
use App\Models\Customer;
use App\Models\Transaction;
use Illuminate\Http\Request;
use SebastianBergmann\Diff\Exception;

class TransactionsController extends Controller
{
    public function __construct(PrepareResponseController $prepareResponse)
    {

        $this->prepareResponse = $prepareResponse;
    }

    /*
     * This funtion returns the transactions made by a particular customer
     */
    public function fetchCustomerTransactions($customerId){

        try{

            $customerData = Customer::where('id', $customerId)->first();

        }catch(Exception $ex){

        }

        if($customerData === null){

            return $this->prepareResponse->simple_response(
                false,
                'An error occured',
                "Customer Does not exist",
                array(),
                400);
        }

        #fetch transaction data of the customer by the customer Id
        $transactionData = Transaction::where('customer_id', $customerId)->paginate(10);

        return $this->prepareResponse->simple_response(
            true,
            "Successful",
            "Transaction History fetch was successful",
            $transactionData
            ,200);

    }

    /*
     * This function returns the customer details along side the transaction details of the customer
     */
    public function fetchTransactionByCustomerId($customerId){

        try{

            #ensure that the customer exists
            $customerData = Customer::where('id', $customerId)->first();

        }catch(Exception $ex){

        }

        #fetch transaction data of the customer by the customer Id
        $transactionData = Transaction::where('customer_id', $customerId)->paginate(10);

        return $this->prepareResponse->simple_response(
            true,
            "Successful",
            "Transaction History fetch was successful",
            array('userDetails'=> $customerData, 'transactions' => $transactionData)
            ,200);

    }
}
