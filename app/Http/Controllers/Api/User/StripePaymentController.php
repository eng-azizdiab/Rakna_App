<?php

namespace App\Http\Controllers\Api\User;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Session;
use Stripe;
use App\Http\Controllers\Controller;

class StripePaymentController extends Controller
{
    /**
     * success response method.
     *
     * @return \Illuminate\Http\Response
     */
    public function stripe()
    {
        return view('stripe');
    }

    /**
     * success response method.
     *
     * @return \Illuminate\Http\Response
     */
    public function stripePost(Request $request)
    {
        Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));

        Stripe\Charge::create ([
            "amount" => 1000 * 100,
            "currency" => "usd",
            "source" => $request->stripeToken,
            "description" => "Test payment from itsolutionstuff.com."
        ]);

        Session::flash('success', 'Payment successful!');

        return back();
    }

    public function charge(Request $request)
    {
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
            $payment = new Payment;
            $payment->user_id = $user->id;
            $payment->amount = $amount;
            $payment->save();

            // Payment successful
            return response()->json(['message' => 'Payment successful']);
        } catch (\Stripe\Exception\CardException $e) {
            // Payment failed
            return response()->json(['message' => 'Payment failed: ' . $e->getError()->message]);
        }
    }
}
