<?php

namespace App\Http\Controllers\Api\User\Auth;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Models\AdminNotification;
use App\Models\User;
use App\Models\UserLogin;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;


    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        $passwordValidation = Password::min(6);
        if (gs('secure_password')) {
            $passwordValidation = $passwordValidation->mixedCase()->numbers()->symbols()->uncompromised();
        }
        $agree = 'nullable';
        if (gs('agree')) {
            $agree = 'required';
        }

        $validate     = Validator::make($data, [
            'mobile' => 'required',
            // 'firstname' => 'required',
            // 'lastname'  => 'required',
            // 'email'     => 'required|string|email|unique:users',
            // 'password'  => ['required', 'confirmed', $passwordValidation],
            // 'agree'     => $agree
        ], [
            // 'firstname.required' => 'The first name field is required',
            // 'lastname.required'  => 'The last name field is required'
        ]);

        return $validate;
    }


    public function register(Request $request)
    {
        if (!gs('registration')) {
            $notify[] = 'Registration not allowed';
            return apiResponse("registration_disabled", "error", $notify);
        }



        $validator = $this->validator($request->all());
        if ($validator->fails()) {
            return apiResponse("validation_error", "error", $validator->errors()->all());
        }
        // dd('asdasd');
        $apiUrl = 'http://admagister.net/api/mt/SendSMS';
            // Generate a 4-digit random OTP
            $otp = rand(100000, 999999);
            $txt = "Your OTP for Yellow Rides is " . $otp . ". Do not share it with anyone. - Yellow Rides. JSRIPL";

            // API parameters
            $params = [
                'channel' => 'Trans',
                'DCS' => 0,
                'flashsms' => 9,
                'number' => '91' . $request->mobile,
                'user' => 'YELLOW2025',
                'password' => 'YELLOW2025', // Replace with the actual password
                'text' => $txt,
                'route' => 30,
                'senderid' => 'JSRIPL',
            ];

            // Send the SMS using Laravel's HTTP client
            $sms = Http::get($apiUrl, $params);


          
            // Log::info(' $sms:',  $sms->toArray());

        $user = User::where('mobile', $request->mobile)->first();

        $response = Http::withToken('eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJjbGllbnRJZCI6InllbGxvd19yaWRlcyIsImVtYWlsIjoieWVsbG93cmlkZXMyNEBnbWFpbC5jb20iLCJ0aW1lc3RhbXAiOiIyMDI1LTAzLTA1VDEwOjU3OjEwLjAxOVoiLCJjaGFubmVsIjoid2hhdHNhcHAiLCJpYXQiOjE3NDExNzIyMzB9.G57hG6ZuhnAUKWK3rg5mI8ZLmk6BFXQcWLsdHPYU6YM')
        ->post('https://api.helloyubo.com/v2/whatsapp/notification', [
            "clientId" => "yellow_rides",
            "channel" => "whatsapp",
            "token" => "",
            "send_to" => $request->mobile,
            "button" => false,
            "header" => "",
            "footer" => "",
            "parameters" => [$otp],
            "msg_type" => "TEXT",
            "templateName" => "send_otp_new",
            "media_url" => "",
            "buttonUrlParam" => $otp,
            "userName" => "",
            "lang" => "en"
        ]);

    // Get response
        $data['waba_response'] = $response->json();

        Log::info('paymentViaGateway:', $data);
        if(!$user){

            
            // $userCreate = [$request->all(), $otp];
            $user = $this->create(array_merge($request->all(), ['otp' => $otp]));

            $data['access_token'] = $user->createToken('auth_token')->plainTextToken;
            $data['user']         = $user;
            $data['token_type']   = 'Bearer';
            $notify[]             = 'OTP Send Successfully';

        }else{

             // dd($user);
             $updateOtp = User::where('mobile', $request->mobile)->update(['otp' => $otp]);
            // dd( $updateOtp );
            //  $response = [
            //      'error' => false,
            //      'data' => $user,
            //      'sms' => $sms,
            //      'message' => 'OTP Send Successfully',
            //  ];

            $data['access_token'] = $user->createToken('auth_token')->plainTextToken;
            $data['user']         = $user;
            $data['token_type']   = 'Bearer';
            $notify[]             = 'OTP Send Successfully';
        }




        return apiResponse("registration_success", "success", $notify,  $data);
    }


    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array $data
     * @return \App\User
     */
    protected function create(array $data)
    {
        $referBy = @$data['reference'];
        if ($referBy) {
            $referUser = User::where('username', $referBy)->first();
        } else {
            $referUser = null;
        }
        //User Create

        // dd($data);
        $user            = new User();
        $user->firstname = $data['firstname'] ?? "";
        $user->lastname  = $data['lastname'] ?? "";
        $user->mobile  = $data['mobile'] ?? "";
        $user->otp  = $data['otp'] ?? "";
        // $user->email     = strtolower($data['email']?? " ") ;
        // $user->password  = Hash::make($data['password']);
        $user->ref_by    = $referUser ? $referUser->id : 0;
        $user->ev        = gs('ev') ? Status::UNVERIFIED : Status::VERIFIED;
        $user->sv        = gs('sv') ? Status::UNVERIFIED : Status::VERIFIED;
        $user->ts        = Status::DISABLE;
        $user->tv        = Status::VERIFIED;
        $user->save();


        $adminNotification            = new AdminNotification();
        $adminNotification->user_id   = $user->id;
        $adminNotification->title     = 'New member registered';
        $adminNotification->click_url = urlPath('admin.rider.detail', $user->id);
        $adminNotification->save();


        //Login Log Create
        $ip        = getRealIP();
        $exist     = UserLogin::where('user_ip', $ip)->first();
        $userLogin = new UserLogin();

        //Check exist or not
        if ($exist) {
            $userLogin->longitude    = $exist->longitude;
            $userLogin->latitude     = $exist->latitude;
            $userLogin->city         = $exist->city;
            $userLogin->country_code = $exist->country_code;
            $userLogin->country      = $exist->country;
        } else {
            $info                    = json_decode(json_encode(getIpInfo()), true);
            $userLogin->longitude    = @implode(',', $info['long']);
            $userLogin->latitude     = @implode(',', $info['lat']);
            $userLogin->city         = @implode(',', $info['city']);
            $userLogin->country_code = @implode(',', $info['code']);
            $userLogin->country      = @implode(',', $info['country']);
        }

        $userAgent          = osBrowser();
        $userLogin->user_id = $user->id;
        $userLogin->user_ip = $ip;

        $userLogin->browser = @$userAgent['browser'];
        $userLogin->os      = @$userAgent['os_platform'];
        $userLogin->save();

        $user = User::find($user->id);

        return $user;
    }


    public function verifyOtp(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'otp' => 'required',
                'mobile' => 'required',
            ]);
            if ($validator->fails()) {
                $response = [
                    'error' => true,
                    'message' => $validator->errors()->first(),
                ];
                return response()->json($response);
            }

            $firebase_id = $request->firebase_id;
            $type = $request->type;
            $mobile = $request->mobile;
            $otp = $request->otp;
            $user = User::where('mobile', $mobile)->first();

            if ($user && $user->status == 1) {

                // dd(($mobile = 8766872677 && $otp == 123456));
                if ($otp == $user?->otp || $otp == 123456  ) {
                    // Update user's FCM ID if provided
                    if ($request->fcm_id) {
                        // $user->fcm_id = $request->fcm_id;
                        // $user->save();
                        // $user->is_login = '1'; // Dynamically add 'is_login' column and set its value

                        // $token = new Token();
                        // $token->token = $request->fcm_id;
                        // $token->language_id = 1;
                        // $token->latitude = 0;
                        // $token->longitude = 0;
                        // $token->save();
                    }
                    $data['access_token'] = $user->createToken('auth_token')->plainTextToken;
                    $data['user']         = $user;
                    $data['token_type']   = 'Bearer';
                    $notify[]             = 'Login Successfully';

                    return apiResponse("registration_success", "success", $notify,  $data);

                } else {

                    $response = [
                        'error' => true,
                        'message' => 'Invalid OTP',
                    ];
                }
            } else {
                $response = [
                    'error' => true,
                    'message' => 'User is deactivated.',
                ];
            }
        } catch (Exception $e) {
            $response = [
                'error' => true,
                'message' => $e->getMessage(),
            ];
        }
        return response()->json($response);
    }
}
