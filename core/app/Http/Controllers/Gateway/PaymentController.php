<?php

namespace App\Http\Controllers\Gateway;

use App\Constants\Status;
use App\Events\Ride as EventsRide;
use App\Http\Controllers\Controller;
use App\Lib\FormProcessor;
use App\Lib\RidePaymentManager;
use App\Models\AdminNotification;
use App\Models\Deposit;
use App\Models\Driver;
use App\Models\Ride;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Mail\Invoice;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Http;

class PaymentController extends Controller
{

    public function depositHistory()
    {
        $pageTitle = 'Deposit History';
        $deposits  = auth()->user()->deposits()->searchable(['trx'])->with(['gateway'])->orderBy('id', 'desc')->paginate(getPaginate());
        return view('Template::user.payment.deposit_history', compact('pageTitle', 'deposits'));
    }

    public function depositHistoryDriver()
    {
        $pageTitle = 'Deposit History';
        $deposits  = auth()->guard('driver')->user()->deposits()->searchable(['trx'])->with(['gateway'])->orderBy('id', 'desc')->paginate(getPaginate());
        return view('Template::driver.payment.deposit_history', compact('pageTitle', 'deposits'));
    }

    public function appDepositConfirm($hash)
    {
        
        try {
            $id = decrypt($hash);
        } catch (\Exception $ex) {
            abort(404);
        }

        $data = Deposit::where('id', $id)->where('status', Status::PAYMENT_INITIATE)->orderBy('id', 'DESC')->firstOrFail();
        session()->put('Track', $data->trx);

      
       
        
        if ($data->user_id) {
            $user = User::findOrFail($data->user_id);
            auth()->login($user);
            return to_route('user.deposit.confirm');
        } else {
            $driver = Driver::findOrFail($data->driver_id);
            auth()->guard('driver')->login($driver);
            return to_route('driver.deposit.confirm');
        }
    }


    public function depositConfirm()
    {
        $track   = session()->get('Track');
        $deposit = Deposit::where('trx', $track)->where('status', Status::PAYMENT_INITIATE)->orderBy('id', 'DESC')->with('gateway')->firstOrFail();


       
       
        if ($deposit->method_code >= 1000) {
            return to_route('driver.deposit.manual.confirm');
        }

        $dirName = $deposit->gateway->alias;
        $new     = __NAMESPACE__ . '\\' . $dirName . '\\ProcessController';

        $data = $new::process($deposit);
        $data = json_decode($data);


       
        if (isset($data->error)) {
            $notify[] = ['error', $data->message];
            return back()->withNotify($notify);
        }
        if (isset($data->redirect)) {
            return redirect($data->redirect_url);
        }

        // for Stripe V3
        if (@$data->session) {
            $deposit->btc_wallet = $data->session->id;
            $deposit->save();
        }

        $view      = $deposit->driver_id ? str_replace('user', 'driver', $data->view) : $data->view;
        $pageTitle = 'Payment Confirm';
        // dd($view);

        // Log::info('ProcessPayment Controller', $data);
        return view("Template::$view", compact('data', 'pageTitle', 'deposit'));
    }

    public static function userDataUpdate($deposit, $isManual = null)
    {
        if ($deposit->status == Status::PAYMENT_INITIATE || $deposit->status == Status::PAYMENT_PENDING) {
            $deposit->status = Status::PAYMENT_SUCCESS;
            $deposit->save();

            if ($deposit->ride_id) {
                $user           = User::find($deposit->user_id);
                $user->balance += $deposit->amount;
                $user->save();

                $methodName = $deposit->methodName();

                $transaction               = new Transaction();
                $transaction->user_id      = $deposit->user_id;
                $transaction->amount       = $deposit->amount;
                $transaction->post_balance = $user->balance;
                $transaction->charge       = $deposit->charge;
                $transaction->trx_type     = '+';
                $transaction->details      = 'Payment Via ' . $methodName;
                $transaction->trx          = $deposit->trx;
                $transaction->remark       = 'payment';
                $transaction->save();

                $ride = Ride::findOrFail($deposit->ride_id);
                (new RidePaymentManager())->payment($ride, Status::PAYMENT_TYPE_GATEWAY);
                $ride->load('driver', 'driver.brand', 'service', 'user');
                initializePusher();

                event(new EventsRide($ride, 'online-payment-received'));

                $gstValue = "( ".$ride->service->gst."% ) :";

      
            if(!$ride->is_scheduled){



                $paramWaba = [
                    $ride->uid,
                    $ride->service->name,
                    $ride->pickup_location,
                    $ride->destination,
                    (float) number_format($ride->recommend_amount, 2, '.', ''),
                    (float) ($ride->service->platform_fee ?? 0),
                    $gstValue . (float) (number_format((($ride->service->gst / 100) * ($ride->recommend_amount - $ride->discount_amount)), 2, '.', '') ?? 0),
                    (float) number_format($ride->discount_amount, 2, '.', ''),
                    (float) number_format(
                        round(
                            (($ride->recommend_amount - $ride->discount_amount) +
                            ($ride->service->platform_fee + (($ride->service->gst / 100) * ($ride->recommend_amount - $ride->discount_amount))))
                        ),
                        2,
                        '.',
                        ''
                    )
                    ];
          
        $response = Http::withToken('eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJjbGllbnRJZCI6InllbGxvd19yaWRlcyIsImVtYWlsIjoieWVsbG93cmlkZXMyNEBnbWFpbC5jb20iLCJ0aW1lc3RhbXAiOiIyMDI1LTAzLTA1VDEwOjU3OjEwLjAxOVoiLCJjaGFubmVsIjoid2hhdHNhcHAiLCJpYXQiOjE3NDExNzIyMzB9.G57hG6ZuhnAUKWK3rg5mI8ZLmk6BFXQcWLsdHPYU6YM')
        ->post('https://api.helloyubo.com/v2/whatsapp/notification', [
            "clientId" => "yellow_rides",
            "channel" => "whatsapp",
            "token" => "",
            "send_to" => $ride->user->mobile,
            "button" => false,
            "header" => "",
            "footer" => "",
            "parameters" => $paramWaba,
            "msg_type" => "TEXT",
            "templateName" => "ride_completed1",
            "media_url" =>"",
            "buttonUrlParam" =>$ride->uid,
            "userName" => "",
            "lang" => "en"
        ]);


        // Http::withToken('eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJjbGllbnRJZCI6InllbGxvd19yaWRlcyIsImVtYWlsIjoieWVsbG93cmlkZXMyNEBnbWFpbC5jb20iLCJ0aW1lc3RhbXAiOiIyMDI1LTAzLTA1VDEwOjU3OjEwLjAxOVoiLCJjaGFubmVsIjoid2hhdHNhcHAiLCJpYXQiOjE3NDExNzIyMzB9.G57hG6ZuhnAUKWK3rg5mI8ZLmk6BFXQcWLsdHPYU6YM')
        // ->post('https://api.helloyubo.com/v2/whatsapp/notification', [
        //     "clientId" => "yellow_rides",
        //     "channel" => "whatsapp",
        //     "token" => "",
        //     "send_to" => 7979068408,
        //     "button" => false,
        //     "header" => "",
        //     "footer" => "",
        //     "parameters" => $paramWaba,
        //     "msg_type" => "TEXT",
        //     "templateName" => "ridecompleted",
        //     "media_url" => "",
        //     "buttonUrlParam" =>  $ride->uid,
        //     "userName" => "",
        //     "lang" => "en"
        // ]);

        // Http::withToken('eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJjbGllbnRJZCI6InllbGxvd19yaWRlcyIsImVtYWlsIjoieWVsbG93cmlkZXMyNEBnbWFpbC5jb20iLCJ0aW1lc3RhbXAiOiIyMDI1LTAzLTA1VDEwOjU3OjEwLjAxOVoiLCJjaGFubmVsIjoid2hhdHNhcHAiLCJpYXQiOjE3NDExNzIyMzB9.G57hG6ZuhnAUKWK3rg5mI8ZLmk6BFXQcWLsdHPYU6YM')
        // ->post('https://api.helloyubo.com/v2/whatsapp/notification', [
        //     "clientId" => "yellow_rides",
        //     "channel" => "whatsapp",
        //     "token" => "",
        //     "send_to" => 8766271520,
        //     "button" => false,
        //     "header" => "",
        //     "footer" => "",
        //     "parameters" => $paramWaba,
        //     "msg_type" => "TEXT",
        //     "templateName" => "ridecompleted",
        //     "media_url" => "",
        //     "buttonUrlParam" => $ride->uid,
        //     "userName" => "",
        //     "lang" => "en"
        // ]);

    }else{


        $paramWaba = [
            $ride->uid,
            $ride->service->name,
            $ride->pickup_location,
            $ride->destination,
            $ride->distance,
            (float) number_format($ride->recommend_amount, 2, '.', ''),
            (float) ($ride->service->platform_fee ?? 0),
            $gstValue . (float) (number_format((($ride->service->gst / 100) * ($ride->recommend_amount - $ride->discount_amount)), 2, '.', '') ?? 0),
            (float) number_format($ride->discount_amount, 2, '.', ''),
            (float) number_format(
                round(
                    (($ride->recommend_amount - $ride->discount_amount) +
                    ($ride->service->platform_fee + (($ride->service->gst / 100) * ($ride->recommend_amount - $ride->discount_amount))))
                ),
                2,
                '.',
                ''
            )
            ];

        $response = Http::withToken('eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJjbGllbnRJZCI6InllbGxvd19yaWRlcyIsImVtYWlsIjoieWVsbG93cmlkZXMyNEBnbWFpbC5jb20iLCJ0aW1lc3RhbXAiOiIyMDI1LTAzLTA1VDEwOjU3OjEwLjAxOVoiLCJjaGFubmVsIjoid2hhdHNhcHAiLCJpYXQiOjE3NDExNzIyMzB9.G57hG6ZuhnAUKWK3rg5mI8ZLmk6BFXQcWLsdHPYU6YM')
        ->post('https://api.helloyubo.com/v2/whatsapp/notification', [
            "clientId" => "yellow_rides",
            "channel" => "whatsapp",
            "token" => "",
            "send_to" => $ride->user->mobile,
            "button" => false,
            "header" => "",
            "footer" => "",
            "parameters" => $paramWaba,
            "msg_type" => "TEXT",
            "templateName" => "advance_booking1",
            "media_url" =>"",
            "buttonUrlParam" =>$ride->uid,
            "userName" => "",
            "lang" => "en"
        ]);


        // Http::withToken('eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJjbGllbnRJZCI6InllbGxvd19yaWRlcyIsImVtYWlsIjoieWVsbG93cmlkZXMyNEBnbWFpbC5jb20iLCJ0aW1lc3RhbXAiOiIyMDI1LTAzLTA1VDEwOjU3OjEwLjAxOVoiLCJjaGFubmVsIjoid2hhdHNhcHAiLCJpYXQiOjE3NDExNzIyMzB9.G57hG6ZuhnAUKWK3rg5mI8ZLmk6BFXQcWLsdHPYU6YM')
        // ->post('https://api.helloyubo.com/v2/whatsapp/notification', [
        //     "clientId" => "yellow_rides",
        //     "channel" => "whatsapp",
        //     "token" => "",
        //     "send_to" => 7979068408,
        //     "button" => false,
        //     "header" => "",
        //     "footer" => "",
        //     "parameters" => $paramWaba,
        //     "msg_type" => "TEXT",
        //     "templateName" => "advance_booking",
        //     "media_url" => "",
        //     "buttonUrlParam" =>  $ride->uid,
        //     "userName" => "",
        //     "lang" => "en"
        // ]);

        // Http::withToken('eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJjbGllbnRJZCI6InllbGxvd19yaWRlcyIsImVtYWlsIjoieWVsbG93cmlkZXMyNEBnbWFpbC5jb20iLCJ0aW1lc3RhbXAiOiIyMDI1LTAzLTA1VDEwOjU3OjEwLjAxOVoiLCJjaGFubmVsIjoid2hhdHNhcHAiLCJpYXQiOjE3NDExNzIyMzB9.G57hG6ZuhnAUKWK3rg5mI8ZLmk6BFXQcWLsdHPYU6YM')
        // ->post('https://api.helloyubo.com/v2/whatsapp/notification', [
        //     "clientId" => "yellow_rides",
        //     "channel" => "whatsapp",
        //     "token" => "",
        //     "send_to" => 8766271520,
        //     "button" => false,
        //     "header" => "",
        //     "footer" => "",
        //     "parameters" => $paramWaba,
        //     "msg_type" => "TEXT",
        //     "templateName" => "advance_booking",
        //     "media_url" => "",
        //     "buttonUrlParam" => $ride->uid,
        //     "userName" => "",
        //     "lang" => "en"
        // ]);

    }

        $data['waba_response'] = $response ;
        $data['waba_param'] = $paramWaba ;

        if($ride->user->email){

            Mail::to($ride->user->email)->send(new Invoice($ride));
        }



                // event(new EventsRide($ride, 'cash-payment-request'));

                // event(new EventsRide($ride, 'ride_end'));

                Log::info('User Payemnt form paymentcontroller:', (array)$data);
            } else {

                $driver           = Driver::find($deposit->driver_id);
                $driver->balance += $deposit->amount;
                $driver->save();

                $methodName = $deposit->methodName();

                $transaction               = new Transaction();
                $transaction->user_id      = 0;
                $transaction->driver_id    = $driver->id;
                $transaction->amount       = $deposit->amount;
                $transaction->post_balance = $driver->balance;
                $transaction->charge       = $deposit->charge;
                $transaction->trx_type     = '+';
                $transaction->details      = 'Deposit Via ' . $methodName;
                $transaction->trx          = $deposit->trx;
                $transaction->remark       = 'deposit';
                $transaction->save();


                if (!$isManual) {
                    $adminNotification            = new AdminNotification();
                    $adminNotification->driver_id = $driver->id;
                    $adminNotification->title     = 'Deposit successful via ' . $methodName;
                    $adminNotification->click_url = urlPath('admin.deposit.successful');
                    $adminNotification->save();
                }

                notify($driver, $isManual ? 'DEPOSIT_APPROVE' : 'DEPOSIT_COMPLETE', [
                    'method_name'     => $methodName,
                    'method_currency' => $deposit->method_currency,
                    'method_amount'   => showAmount($deposit->final_amount, currencyFormat: false),
                    'amount'          => showAmount($deposit->amount, currencyFormat: false),
                    'charge'          => showAmount($deposit->charge, currencyFormat: false),
                    'rate'            => showAmount($deposit->rate, currencyFormat: false),
                    'trx'             => $deposit->trx,
                    'post_balance'    => showAmount($driver->balance)
                ]);
            }
        }
    }

    public function manualDepositConfirm()
    {
        $track = session()->get('Track');
        $data = Deposit::with('gateway')->where('status', Status::PAYMENT_INITIATE)->where('trx', $track)->first();
        abort_if(!$data, 404);
        if ($data->method_code > 999) {
            $pageTitle = 'Confirm Deposit';
            $method = $data->gatewayCurrency();
            $gateway = $method->method;
            return view('Template::driver.payment.manual', compact('data', 'pageTitle', 'method', 'gateway'));
        }
        abort(404);
    }

    public function manualDepositUpdate(Request $request)
    {
        $track = session()->get('Track');
        $data = Deposit::with('gateway')->where('status', Status::PAYMENT_INITIATE)->where('trx', $track)->first();
        abort_if(!$data, 404);

        $gatewayCurrency = $data->gatewayCurrency();
        $gateway         = $gatewayCurrency->method;
        $formData        = $gateway->form->form_data;

        $formProcessor  = new FormProcessor();
        $validationRule = $formProcessor->valueValidation($formData);
        $request->validate($validationRule);
        $userData = $formProcessor->processFormData($request, $formData);


        $data->detail = $userData;
        $data->status = Status::PAYMENT_PENDING;
        $data->save();


        $adminNotification            = new AdminNotification();
        $adminNotification->driver_id = $data->driver->id;
        $adminNotification->title     = 'Deposit request from ' . $data->driver->username;
        $adminNotification->click_url = urlPath('admin.deposit.details', $data->id);
        $adminNotification->save();

        notify($data->driver, 'DEPOSIT_REQUEST', [
            'method_name'     => $data->gatewayCurrency()->name,
            'method_currency' => $data->method_currency,
            'method_amount'   => showAmount($data->final_amount, currencyFormat: false),
            'amount'          => showAmount($data->amount, currencyFormat: false),
            'charge'          => showAmount($data->charge, currencyFormat: false),
            'rate'            => showAmount($data->rate, currencyFormat: false),
            'trx'             => $data->trx
        ]);

        $notify[] = ['success', 'You have deposit request has been taken'];
        return to_route('driver.deposit.history')->withNotify($notify);
    }
}
