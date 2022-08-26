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

        $transactionData = Transaction::where('customer_id', $customerId)->paginate(10);

        return $this->prepareResponse->simple_response(
            true,
            "Successful",
            "Transaction History fetch was successful",
            $transactionData
            ,200);

    }

    public function fetchTransactionByCustomerId($customerId){

        try{

            $customerData = Customer::where('id', $customerId)->first();

        }catch(Exception $ex){

        }

        $transactionData = Transaction::where('customer_id', $customerId)->paginate(10);

        return $this->prepareResponse->simple_response(
            true,
            "Successful",
            "Transaction History fetch was successful",
            array('userDetails'=> $customerData, 'transactions' => $transactionData)
            ,200);

    }
}
