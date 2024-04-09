<?php

namespace App\Http\Controllers\Api\User;

use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Stripe\PaymentIntent;
use Stripe\Stripe;
use Stripe\Token;
use Stripe\Charge;
use Session;
use Illuminate\Support\Facades\Http;

class Payment extends Controller
{
    public function charge(Request $request)
    {
        Stripe::setApiKey(env('STRIPE_TEST_SECRET'));

        // Create a payment intent
        $paymentIntent = PaymentIntent::create([
            'amount' => $request->amount,
            'currency' => $request->currency,
            'payment_method' => $request->payment_method,
            'confirm' => true,
            'automatic_payment_methods' => [
                'enabled' => true,
                'allow_redirects' => 'never',
            ],
        ]);

        // Handle successful payment
        if ($paymentIntent->status === 'succeeded') {
            return response()->json(['success' => true, 'message' => 'Payment successful']);
        } else {
            return response()->json(['success' => false, 'message' => 'Payment failed']);
        }
    }

    public function charge2(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'amount'            => 'required',
            'currency'          => 'required',
            'type'              => 'required',
            'card_number'       => 'required',
            'exp_month'         => 'required',
            'exp_year'          => 'required',
            'cvc'               => 'required',
        ]);

        $data = $request->all();
        $token = 'pm_card_visa_debit';

        Stripe::setApiKey(env('STRIPE_SECRET'));

        try {
            // Create a charge
            Charge::create([
                'amount' => 1000, // Amount in cents
                'currency' => 'usd',
                'source' => [
                    'object' => 'card',
                    'number' => '4242424242424242',
                    'exp_month' => $data['exp_month'],
                    'exp_year' => $data['exp_year'],
                    'cvc' => $data['cvc'],
                ],
                'description' => 'Example Charge',
            ]);

            // Payment successful
            return response()->json(['message' => 'Payment successful']);
        } catch (\Exception $e) {
            // Payment failed
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
    public function charge1(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'amount'            => 'required',
            'currency'          => 'required',
            'type'              => 'required',
            'card_number'       => 'required',
            'exp_month'         => 'required',
            'exp_year'          => 'required',
            'cvc'               => 'required',
        ]);
        $data = $request->all();
        Stripe::setApiKey(env('STRIPE_SECRET'));
    
        Charge::create ([
                "amount" => 100 * 100,
                "currency" => "usd",
                "source" => $request->stripeToken,
                "description" => "Test payment from itsolutionstuff.com." 
        ]);
      
        Session::flash('success', 'Payment successful!');

        // $formData = http_build_query([
        //     'card[number]' => $data['card_number'],
        //     'card[exp_month]' => $data['exp_month'],
        //     'card[exp_year]' => $data['exp_year'],
        //     'card[cvc]' => $data['cvc'],
        // ]);
        $formData = [
            'card[number]' => $data['card_number'],
            'card[exp_month]' => $data['exp_month'],
            'card[exp_year]' => $data['exp_year'],
            'card[cvc]' => $data['cvc'],
        ];
        // $response = Http::withBasicAuth(env('STRIPE_SECRET'), '')
        //                     ->post('https://api.stripe.com/v1/tokens', [
        //                         'card' => [
        //                             'number' => $data['card_number'],
        //                             'exp_month' => $data['exp_month'],
        //                             'exp_year' => $data['exp_year'],
        //                             'cvc' => $data['cvc'],
        //                         ],
        //                     ]);
        $response = Http::withBasicAuth(env('STRIPE_SECRET'), '')
                            ->asForm()
                            ->post('https://api.stripe.com/v1/tokens', $formData);
                            dd($response->body());

            // Check if request was successful
            if ($response->successful()) {
                $token = $response['id'];

                // Now you can use the token to charge the customer
                // Example: Charge::create([...]);

                // Payment successful
                return response()->json(['message' => 'Token created successfully', 'token' => $token]);
            } else {
                // Handle unsuccessful request
                return response()->json(['error' => 'Failed to create token'], $response->status());
            }
        if ($validator->fails()) {
            return $this->sendError($validator->errors(),[],401);
        }
        $data = $request->all();
        // Set your secret key
        Stripe::setApiKey(env('STRIPE_SECRET'));

        try {

            $stripe = new \Stripe\StripeClient(env('STRIPE_SECRET'));
            $stripe->tokens->create([
                'card' => [
                    'number' => $data['card_number'],
                    'exp_month' => $data['exp_month'],
                    'exp_year' => $data['exp_year'],
                    'cvc' => $data['cvc'],
                ],  
            ]);
            dd($stripe);

            $token = Token::create([
                'card' => [
                    'number' => $data['card_number'],
                    'exp_month' => $data['exp_month'],
                    'exp_year' => $data['exp_year'],
                    'cvc' => $data['cvc'],
                ],
            ]);
            dd($token);
            // Create a charge
            $payment =  Charge::create([
                            'amount' => 1000, // Amount in cents
                            'currency' => 'usd',
                            'source' => $token->id,
                            'description' => $request->description,
                        ]);

            // Payment successful
            return response()->json(['message' => 'Payment successful']);
        } catch (\Exception $e) {
            // Payment failed
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
}