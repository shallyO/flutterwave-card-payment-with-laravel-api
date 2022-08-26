<?php

namespace App\Http\Controllers\Api\CustomerPage;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Helpers\PrepareResponseController;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class CustomerController extends Controller
{

    public function __construct(PrepareResponseController $prepareResponse)
    {

        $this->prepareResponse = $prepareResponse;
    }
    #
    public function createCustomer(Request $request){

        #validate data
        $validator = Validator::make($request->all(), [
            'fullname' => 'string|required',
            'email' => 'required|email|unique:customers',
            'phonenumber' => 'required|unique:customers|min:11',
        ]);


        if ($validator->stopOnFirstFailure()->fails()) {
            return $this->prepareResponse->simple_response(
                false,
                $validator->errors()->first(),
                "Please Ensure all values are entered correctly",
                array('errors' => $validator->errors()->all()),
                400);
        }


        try{

            Customer::create($request->all());

        }catch(\Exception $ex){

            Log::info($ex);

            return $this->prepareResponse->simple_response(
                false,
                'An error occured',
                "Please Ensure all values are entered correctly",
                array(),
                400);

        }


        return $this->prepareResponse->simple_response(
            true,
            "Successful",
            "Customer Created successfully",
            $request->all()
            ,200);





    }

    public function fetchAllCustomers(){


           $data =  Customer::paginate(10);

        return $this->prepareResponse->simple_response(
            true,
            "Successful",
            "Customer Created successfully",
            $data
            ,200);



    }


}
