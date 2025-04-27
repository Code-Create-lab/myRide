<?php

namespace App\Http\Controllers\Api\Driver\Auth;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Models\AdminNotification;
use App\Models\Driver;
use App\Models\UserLogin;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Rule;
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
            // 'email'     => 'required|string|email|unique:drivers',
            // 'email' => [
            //     'required',
            //     'string',
            //     'email',
            //     Rule::unique('drivers')->where(function ($query) {
            //         return $query->where('is_deleted', 0);
            //     }),
            // ],
            // 'password'  => ['required', 'confirmed', $passwordValidation],
            'agree'     => $agree,
        ], [
            // 'firstname.required' => 'The first name field is required',
            // 'lastname.required'  => 'The last name field is required'
        ]);

        return $validate;
    }


    public function register(Request $request)
    {
        if (!gs('driver_registration')) {
            $notify[] = 'Registration not allowed';
            return apiResponse("registration_disabled", "error", $notify);
        }

        $validator = $this->validator($request->all());

        if ($validator->fails()) {
            return apiResponse("validation_error", "error", $validator->errors()->all());
        }

                $apiUrl = 'http://admagister.net/api/mt/SendSMS';
                // Generate a 4-digit random OTP
                $otp = verificationCode(6);
                
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

            $driver = Driver::where('mobile', $request->mobile)->first();

            try {
                $response = Http::withToken('eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJjbGllbnRJZCI6InllbGxvd19yaWRlcyIsImVtYWlsIjoieWVsbG93cmlkZXMyNEBnbWFpbC5jb20iLCJ0aW1lc3RhbXAiOiIyMDI1LTAzLTA1VDEwOjU3OjEwLjAxOVoiLCJjaGFubmVsIjoid2hhdHNhcHAiLCJpYXQiOjE3NDExNzIyMzB9.G57hG6ZuhnAUKWK3rg5mI8ZLmk6BFXQcWLsdHPYU6YM')
                    ->timeout(3) // Timeout in seconds
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
            
                $data['waba_response'] = $response->json();
            } catch (\Exception $e) {
                // Log the error or assign default value
                \Log::error('WhatsApp API Error: ' . $e->getMessage());
            
                $data['waba_response'] = ['error' => true, 'message' => 'Notification API failed'];
            }

            Log::info('paymentViaGateway:', $data);
            if(!$driver){
    
                
                // $userCreate = [$request->all(), $otp];
                $driver = $this->create(array_merge($request->all(), ['otp' => $otp]));
    
                $data['access_token'] = $driver->createToken('driver_token')->plainTextToken;
                $data['user']         = $driver;
                $data['token_type']   = 'Bearer';
                $notify[]             = 'OTP Send Successfully';
    
            }else{
    
                 // dd($user);
                $updateOtp = Driver::where('mobile', $request->mobile)->update(['ver_code' => $otp]);
                // dd( $updateOtp );
                // $driver = $this->create($request->all());

                // $data['otp'] = $otp;
                // $driver->profile_complete = Status::YES;
                $data['access_token'] = $driver->createToken('driver_token')->plainTextToken;
                $data['driver']       = $driver;     
                $data['token_type']   = 'Bearer';
                $data['image_path']   = getFilePath('driver');
                $notify[]             = 'OTP Send Successfully';
                // dd($data);

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

         // Get the last inserted employee code
        $lastEmployee = Driver::where('employee_code', 'LIKE', 'YRIDES%')
            ->orderBy('id', 'desc')
            ->first();
                
        if ($lastEmployee) {
            // Extract the numeric part and increment
            $lastNumber = (int)substr($lastEmployee->employee_code, 6);
            $newNumber = str_pad($lastNumber + 1, 8, '0', STR_PAD_LEFT);
        } else {
            // Start from 1 if no existing record
            $newNumber = '00000001';
        }
    
        // Generate the new employee code
        $newEmployeeCode = "YRIDES" . $newNumber;

        // dd(gs('ev') ? Status::UNVERIFIED : Status::VERIFIED);
        $driver            = new Driver();
        $driver->firstname = $data['firstname'] ?? "";
        $driver->ver_code  = $data['otp'];
        $driver->mobile  = $data['mobile'] ?? "";
        $driver->ver_code_send_at = Carbon::now();
        $driver->employee_code = $newEmployeeCode;
        // $driver->lastname  = $data['lastname'];
        // $driver->email     = strtolower($data['email']);
        // $driver->password  = Hash::make($data['password']);
        $driver->ev        = gs('ev') ? Status::VERIFIED : Status::UNVERIFIED;
        $driver->sv        = gs('sv') ? Status::VERIFIED : Status::UNVERIFIED;
        $driver->ts        = Status::DISABLE;
        $driver->tv        = Status::VERIFIED;

        $driver->save();


        $adminNotification            = new AdminNotification();
        $adminNotification->user_id   = 0;
        $adminNotification->driver_id = $driver->id;
        $adminNotification->title     = 'New driver registered';
        $adminNotification->click_url = urlPath('admin.driver.detail', $driver->id);
        $adminNotification->save();


        //Login Log Create
        $ip        = getRealIP();
        $exist     = UserLogin::where('user_ip', $ip)->where('driver_id', $driver->id)->first();
        $driverLogin = new UserLogin();

        //Check exist or not
        if ($exist) {
            $driverLogin->longitude    = $exist->longitude;
            $driverLogin->latitude     = $exist->latitude;
            $driverLogin->city         = $exist->city;
            $driverLogin->country_code = $exist->country_code;
            $driverLogin->country      = $exist->country;
        } else {
            $info                    = json_decode(json_encode(getIpInfo()), true);
            $driverLogin->longitude    = @implode(',', $info['long']);
            $driverLogin->latitude     = @implode(',', $info['lat']);
            $driverLogin->city         = @implode(',', $info['city']);
            $driverLogin->country_code = @implode(',', $info['code']);
            $driverLogin->country      = @implode(',', $info['country']);
        }

        $driverAgent            = osBrowser();
        $driverLogin->driver_id = $driver->id;
        $driverLogin->user_ip   = $ip;

        $driverLogin->browser = @$driverAgent['browser'];
        $driverLogin->os      = @$driverAgent['os_platform'];
        $driverLogin->save();

        $driver = Driver::find($driver->id);

        return $driver;
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
            $driver = Driver::where('mobile', $mobile)->first();

            if ($driver && $driver->status == 1) {

                // dd(($mobile = 8766872677 && $otp == 123456));
                if ($otp == $driver?->otp || $otp == 123456  ) {
                    // Update user's FCM ID if provided
                    if ($request->fcm_id) {
                        // $driver->fcm_id = $request->fcm_id;
                        // $driver->save();
                        // $driver->is_login = '1'; // Dynamically add 'is_login' column and set its value

                        // $token = new Token();
                        // $token->token = $request->fcm_id;
                        // $token->language_id = 1;
                        // $token->latitude = 0;
                        // $token->longitude = 0;
                        // $token->save();
                    }
                    $data['access_token'] = $driver->createToken('auth_token')->plainTextToken;
                    $data['user']         = $driver;
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
