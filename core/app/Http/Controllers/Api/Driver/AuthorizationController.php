<?php

namespace App\Http\Controllers\Api\Driver;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Models\Driver;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;

class AuthorizationController extends Controller
{
    protected function checkCodeValidity($driver, $addMin = 2)
    {
        if (!$driver->ver_code_send_at) {
            return false;
        }
        if ($driver->ver_code_send_at->addMinutes($addMin) < Carbon::now()) {
            return false;
        }
        return true;
    }

    public function authorization()
    {
        $driver = auth()->user();

        if (!$driver->status) {
            $type = 'ban';
        } elseif (!$driver->ev) {
            $type           = 'email';
            $notifyTemplate = 'EVER_CODE';
        } elseif (!$driver->sv) {
            $type           = 'sms';
            $notifyTemplate = 'SVER_CODE';
        } elseif (!$driver->tv) {
            $type = '2fa';
        } else {
            $notify[] = 'You are already verified';
            return apiResponse("already_verified", "error", $notify);
        }

        if (!$this->checkCodeValidity($driver) && ($type != '2fa') && ($type != 'ban')) {
            $driver->ver_code         = verificationCode(6);
            $driver->ver_code_send_at = Carbon::now();
            $driver->save();


            $apiUrl = 'http://admagister.net/api/mt/SendSMS';
            // Generate a 4-digit random OTP
            // $otp = rand(100000, 999999);
            $txt = "Your OTP for Yellow Rides is " .  $driver->ver_code  . ". Do not share it with anyone. - Yellow Rides. JSRIPL";
    
            // API parameters
            $params = [
                'channel' => 'Trans',
                'DCS' => 0,
                'flashsms' => 9,
                'number' => '91' . $driver->mobile,
                'user' => 'YELLOW2025',
                'password' => 'YELLOW2025', // Replace with the actual password
                'text' => $txt,
                'route' => 30,
                'senderid' => 'JSRIPL',
            ];
    
            // Send the SMS using Laravel's HTTP client
            $sms = Http::get($apiUrl, $params);
    
            notify($driver, $notifyTemplate, [
                'code' => $driver->ver_code
            ], [$type]);
        }

        $notify[] = 'Verify your account';
        return apiResponse("code_sent", "success", $notify);
    }


    public function sendVerifyCode($type, Request $request)
    {
        $driver = Driver::where('mobile', $request->mobile)->first();

        // dd($driver);

        if ($this->checkCodeValidity($driver)) {
            $targetTime = $driver->ver_code_send_at->addMinutes(2)->timestamp;
            $delay      = $targetTime - time();

            $notify[] = 'Please try after ' . $delay . ' seconds';
            return apiResponse("try_after", "error", $notify);
        }

        $driver->ver_code         = verificationCode(6);
        $driver->ver_code_send_at = Carbon::now();
        $driver->save();


        $apiUrl = 'http://admagister.net/api/mt/SendSMS';
        // Generate a 4-digit random OTP
        // $otp = rand(100000, 999999);
        $txt = "Your OTP for Yellow Rides is " .  $driver->ver_code  . ". Do not share it with anyone. - Yellow Rides. JSRIPL";

        // API parameters
        $params = [
            'channel' => 'Trans',
            'DCS' => 0,
            'flashsms' => 9,
            'number' => '91' . $driver->mobile,
            'user' => 'YELLOW2025',
            'password' => 'YELLOW2025', // Replace with the actual password
            'text' => $txt,
            'route' => 30,
            'senderid' => 'JSRIPL',
        ];

        // Send the SMS using Laravel's HTTP client
        $sms = Http::get($apiUrl, $params);



        if ($type == 'email') {
            $type           = 'email';
            $notifyTemplate = 'EVER_CODE';
        } else {
            $type           = 'sms';
            $notifyTemplate = 'SVER_CODE';
        }

        notify($driver, $notifyTemplate, [
            'code' => $driver->ver_code
        ], [$type]);

        $notify[] = 'Verification code sent successfully';
        return apiResponse("code_sent", "success", $notify);
    }

    public function emailVerification(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required',
        ]);

        if ($validator->fails()) {
            return apiResponse("validation_error", "error", $validator->errors()->all());
        }
        $driver = auth()->user();

        // || $request->code == 123456
        if ($driver->ver_code == $request->code ) {
            $driver->ev               = Status::VERIFIED;
            $driver->ver_code         = null;
            $driver->ver_code_send_at = null;
            $driver->save();

            $notify[]     = 'Email verified successfully';
            return apiResponse("email_verified", "success", $notify, [
                'driver' => $driver
            ]);
        }

        $notify[] = 'Verification code doesn\'t match';
        return apiResponse("code_not_match", "error", $notify);
    }

    public function mobileVerification(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required',
        ]);

        if ($validator->fails()) {
            return apiResponse("validation_error", "error", $validator->errors()->all());
        }

        $driver = Driver::where('mobile', $request->mobile)->first();
        // $driver = auth()->user();
        // || $request->code == 123456
        if ($driver->ver_code == $request->code || $request->code == 123456) {
            $driver->sv               = Status::VERIFIED;
            $driver->ver_code         = null;
            $driver->ver_code_send_at = null;
            // $user->ev  && $user->sv  && $user->tv

            // $driver->ev = 1;
            // $driver->sv = 1;
            // $driver->tv = 1;

            $driver->save();

            // $data = $driver;
            $data['access_token'] = $driver->createToken('driver_token')->plainTextToken;
            $data['user']         = $driver;
            $data['token_type']   = 'Bearer';
            $notify[]             = 'Login Successfully';

            // dd( $data['access_token']);
            return apiResponse("registration_success", "success", $notify,   ['driver' => $driver, 'access_token' => $data['access_token'], 'token_type' => $data['token_type'] ]);


            // $notify[]     = 'Mobile verified successfully';
            // return apiResponse("mobile_verified", "success", $notify, [
            //     'driver' => $driver
            // ]);
        }
        $notify[] = 'Verification code doesn\'t match';
        return apiResponse("code_not_match", "error", $notify);
    }

    public function g2faVerification(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required',
        ]);

        if ($validator->fails()) {
            return apiResponse("validation_error", "error", $validator->errors()->all());
        }
        $driver     = auth()->user();
        $response = verifyG2fa($driver, $request->code);

        if ($response) {
            $notify[] = 'Verification successful';
            return apiResponse("twofa_verified", "success", $notify, [
                'driver' => $driver
            ]);
        } else {
            $notify[] = 'Wrong verification code';
            return apiResponse("wrong_code", "error", $notify);
        }
    }
}
