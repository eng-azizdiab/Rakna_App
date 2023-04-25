<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Traits\GeneralTrait;
use App\Models\Parking;
use App\Models\Payment;
use App\Models\Recharge;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Stripe;
use App\Http\Controllers\Controller;

class RechargeController extends Controller
{
    use GeneralTrait;
    public function recharge(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0',
            'currency' => 'required|in:usd,eur',
            'description' => 'required|string|max:255',
            'payment_token' => 'required|string',
        ]);
        if($validator->fails()){
            return response()->json($validator->errors(), 400);
        }
        // Retrieve the payment details from the request
        $amount = $request->amount;
        $currency = $request->currency;
        $description = $request->description;
        $payment_token = $request->payment_token;
        $userId = Auth::guard('user-api')->user()->id; // Retrieve the user ID

        // Set your API key
        \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));

        // Create a charge using the payment details
        try {
            $charge = \Stripe\Charge::create([
                'amount' => $amount,
                'currency' => $currency,
                'description' => $description,
                'source' => $payment_token,
            ]);

            // Associate the payment with the corresponding user in your database
            $user = User::findOrFail($userId);
            $recharge = new Recharge();
            $recharge->user_id = $user->id;
            $recharge->amount = $amount;
            $recharge->save();
            $credit=$user->credit;
            $new_credit=$credit+$amount;
            $user->credit=$new_credit;
            $user->save();
            // Payment successful
            return response()->json(['message' => 'Payment successful']);
        } catch (\Stripe\Exception\CardException $e) {
            // Payment failed
            return response()->json(['message' => 'Payment failed: ' . $e->getError()->message]);
        }
    }

    public function get_All_Charges(){
        $user_id=Auth::guard('user-api')->user()->id;
        $recharges=Recharge::where('user_id',$user_id)->get();
        if (count($recharges)>0)
            return  $this->returnData('recharges',$recharges);
        else
            return $this->returnError(404, 'there is no any recharges');
    }
    public function get_Interval_Charges(Request $request){
        $validator = Validator::make($request->all(), [
            'first_date'=>'required|date',
            'second_date' => 'required|date',
        ]);
        if($validator->fails()){
            return response()->json($validator->errors(), 400);
        }
        $user_id=Auth::guard('user-api')->user()->id;
        $recharges=Recharge::where('user_id',$user_id)->whereBetween('created_at', [$request->first_date,$request->second_date])->get();
        if (count($recharges)>0)
            return  $this->returnData('recharges',$recharges);
        else
            return $this->returnError(404, 'there is no any recharges in this interval');
    }

    public function get_Day_Charges(){
        $user_id=Auth::guard('user-api')->user()->id;
        $recharges=Recharge::where('user_id',$user_id)->where('created_at', '>', Carbon::today())->get();
        if (count($recharges)>0)
           return  $this->returnData('recharges',$recharges);
        else
            return $this->returnError(404, 'there is no any recharges today');
    }

    public function day_payments(){
        $user_id=Auth::guard('user-api')->user()->id;
        $payments = Payment::where('user_id', $user_id)->where('created_at', '>', Carbon::today())->get();
        if (count($payments)>0) {
            return $this->returnData('payments', $payments);
        } else {
            return $this->returnError(404, 'there is no any payments today');
        }
    }

    public function all_payments(){
        $user_id=Auth::guard('user-api')->user()->id;

            $payments = Payment::where('user_id', $user_id)->get();
            if (count($payments)>0) {
                return $this->returnData('payments', $payments);
            } else {
                return $this->returnError(404, 'there is no any previous payments');
            }

    }

    public function interval_payments(Request $request){
        $validator = Validator::make($request->all(), [
            'first_date'=>'required|date',
            'second_date' => 'required|date',
        ]);
        if($validator->fails()){
            return response()->json($validator->errors(), 400);
        }


           $user_id=Auth::guard('user-api')->user()->id;
            $payments = Payment::where('user_id', $user_id)->whereBetween('created_at', [$request->first_date,$request->second_date])->get();
            if (count($payments)>0) {
                return $this->returnData('payments', $payments);
            } else {
                return $this->returnError(404, 'there is no any payments in this interval');
            }


    }
}
