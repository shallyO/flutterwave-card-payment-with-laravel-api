<?php

namespace App\Http\Controllers\Api\TransactionsPage;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Helpers\PrepareResponseController;
use App\Models\CardPaymentHistory;
use App\Models\Customer;
use App\Models\Transaction;
use Flutterwave\CardPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


class CardPaymentController extends Controller
{
    public function __construct(PrepareResponseController $prepareResponse)
    {

        $this->prepareResponse = $prepareResponse;
    }

    public function chargeCard(Request $request){

        #validate data
        $validator = Validator::make($request->all(), [
            'customer_id' => 'required',
            'card_number' => 'required',
            'cvv' => 'required',
            'expiry_month' => 'required',
            'expiry_year' => 'required',
            'currency' => 'required',
            'amount' => 'required',
        ]);


        if ($validator->stopOnFirstFailure()->fails()) {
            return $this->prepareResponse->simple_response(
                false,
                $validator->errors()->first(),
                "Please Ensure all values are entered correctly",
                array('errors' => $validator->errors()->all()),
                400);
        }

        #comfirm customer ID
        $customerDetails = Customer::where('id', $request->customer_id )->first();

        if ($customerDetails !== null) {

            #if customer exists, add customer details to payload
            $system_ref = rand(10,100);
            $data = $request->all();
            $data['fullname'] = $customerDetails->fullname;
            $data['email'] = $customerDetails->email;
            $data['tx_ref'] = $system_ref;
            $data['redirect_url'] = "https://www.flutterwave.ng";


            #initialize the card
            $payment = new CardPayment();
            $res = $payment->cardCharge($data);//Send request to fetch authorization method
            $data['authorization']['mode'] = $res['meta']['authorization']['mode']; //confirm authentication mode


            if($res['status'] == 'success'){

                #confirm if authorization method is pin, if pin proceed
                if($res['meta']['authorization']['mode'] == 'pin'){

                    #save unique details in db
                    $systemDetails = CardPaymentHistory::create(
                        array('card_number' => $data['card_number'],
                            'system_ref' => $system_ref,
                            'status' => 'awaiting_pin',
                            'customer_id' => $data['customer_id'],
                        ));

                    return $this->prepareResponse->simple_response(
                        true,
                        "Please provide card pin",
                        "Please provide card pin",
                        array('paymentDetails' => $systemDetails)
                        ,200);


                }


            }else{

                return $this->prepareResponse->simple_response(
                    false,
                    'An Error occured',
                    "Unable to charge card",
                    array(),
                    400);

            }




        }else{

            return $this->prepareResponse->simple_response(
                false,
                'An Error occured',
                "User does not exist",
                array(),
                400);
        }
    }

    public function validateCardPin(Request $request){

        $validator = Validator::make($request->all(), [
            'customer_id' => 'required',
            'card_number' => 'required',
            'cvv' => 'required',
            'expiry_month' => 'required',
            'expiry_year' => 'required',
            'currency' => 'required',
            'amount' => 'required',
            'pin' => 'required|min:4',
            'system_ref' => 'required|exists:card_payment_history'
        ]);

        if ($validator->stopOnFirstFailure()->fails()) {
            return $this->prepareResponse->simple_response(
                false,
                $validator->errors()->first(),
                "Please Ensure all values are entered correctly",
                array('errors' => $validator->errors()->all()),
                400);
        }

        #ensure that the transaction has not been completed before
        $cardTransactionStatus = CardPaymentHistory::where(array('system_ref' => $request->system_ref, 'status' => 'awaiting_pin'))->first();

        if($cardTransactionStatus === null){

            return $this->prepareResponse->simple_response(
                false,
                'An error occurred',
                "Transaction with system ref has been closed Or does not exist, please validate card again",
                array(),
                400);
        }


        $customerDetails = Customer::where('id', $request->customer_id )->first();

        //Supply authorization pin here and add user details
        $data = $request->all();
        $data['authorization']['mode'] = 'pin';
        $data['authorization']['pin'] = $request->pin;
        $data['fullname'] = $customerDetails->fullname;
        $data['email'] = $customerDetails->email;
        $data['tx_ref'] = $cardTransactionStatus->system_ref;
        $data['redirect_url'] = "https://www.flutterwave.ng";
        $payment = new CardPayment();

        $result = $payment->cardCharge($data);//charge with new  to validate pin

        //print_r($result);

        if($result['data']['auth_mode'] == 'otp'){
            $id = $result['data']['id'];
            $flw_ref = $result['data']['flw_ref'];
            //$otp = '12345';

            #save transaction details to db
            $saveRef = CardPaymentHistory::where('system_ref', $request->system_ref)->update((
                array(
                    'flw_ref' => $flw_ref,
                    'status' => 'awaiting_otp',
                    'flw_id' => $id,
                    'amount' => $request->amount
                )));


            if($saveRef){

                return $this->prepareResponse->simple_response(
                    true,
                    "Card Verified, Please Enter Otp",
                    "Please Enter Otp",
                    array('system_ref' => $request->system_ref, 'customer_id' => $request->customer_id)
                    ,200);
            }else{

                return $this->prepareResponse->simple_response(
                    false,
                    'An Error occurred',
                    "An error occurred while verifying card",
                    array(),
                    400);

            }
        }else{

            return $this->prepareResponse->simple_response(
                false,
                'An Error occured',
                "Otp verification not accepted",
                array(),
                400);

        }
    }

    public function verifyOtpAndPayment(Request $request){

        $validator = Validator::make($request->all(), [
            'customer_id' => 'required',
            'otp' => 'required|int',
            'system_ref' => 'required',
        ]);

        $customerId = $request->customer_id;
        $ref = $request->system_ref;
        $otp = $request->otp;

        #validate parameters
        if ($validator->stopOnFirstFailure()->fails()) {
            return $this->prepareResponse->simple_response(
                false,
                $validator->errors()->first(),
                "Please Ensure all values are entered correctly",
                array('errors' => $validator->errors()->all()),
                400);
        }



        $refDetails = CardPaymentHistory::where(array('customer_id' => $customerId, 'system_ref' => $ref,'status' => 'awaiting_otp' ))->first();
        $flw_ref = $refDetails->flw_ref;
        $flw_id = $refDetails->flw_id;

        if($refDetails !== null){

            $payment = new CardPayment();
            $validate = json_decode($payment->validateTransaction($otp,$flw_ref));// you can print_r($validate) to see the response
            $verify = $payment->verifyTransaction($flw_id);

            //print_r($validate->status);

            if($validate->status == 'success' && $verify['status'] == 'success'){

                #update status in history
                CardPaymentHistory::where('system_ref', $request->system_ref)->update((
                array(
                    'status' => 'completed',
                )));

                #save transaction details
                Transaction::create(array('amount' => $refDetails->amount,
                                            'details' => 'payment was received',
                                            'status' => 'success',
                                            'customer_id' => $refDetails->customer_id,
                                            'transaction_id' => $refDetails->system_ref));

                return $this->prepareResponse->simple_response(
                    true,
                    "Successful",
                    "Transaction Successful",
                    $verify
                    ,200);

            }else{

                return $this->prepareResponse->simple_response(
                    false,
                    'Unable to verify Otp',
                    "Otp details does not match",
                    array(),
                    400);
            }

        }else{

            return $this->prepareResponse->simple_response(
                false,
                'Unable to make payment',
                "User or Card Data was not found",
                array(),
                400);

        }



    }

}
