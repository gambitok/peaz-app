<?php

namespace App\Http\Controllers\Api\V1;

use App\Content;
use App\Http\Controllers\Api\ResponseController;
use App\User;
use App\Otpverify;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Mail;
use App\Mail\General\SendOTPmail; 
use App\Mail\General\User_Rest_Password;
use App\OtpRequestLog;
use App\SocialAccounts;
use Carbon\Carbon;

class GuestController extends ResponseController
{

    public function login(Request $request)
    {
        $rules = [
           // 'mobile' => ['required','integer'],
            'email' => ['required'],
            'country_code' => ['required_if:type,mobile'],
            'password' => ['required'],
            'push_token' => ['nullable'],
            'device_type' => ['required', 'in:android,ios'],
            'device_id' => ['required','max:255'], 
        ];
        $messages = [
            'email.exists' => __('api.err_email_not_register'),
            'country_code.required_if'=> __('api.err_country_code'),
        ];
        $this->directValidation($rules, $messages);
        $find_field = filter_var($request->email, FILTER_VALIDATE_EMAIL) ? "email" : "username";
        $attempt = [$find_field => $request->email, 'password' => $request->password,'type' => 'user', 'status' => 'active'];
        if (Auth::attempt($attempt)) {
            $token = User::AddTokenToUser();
            $this->sendResponse(200, __('api.suc_user_login'), $this->get_user_data($token));
        } else {
            $this->sendError(__('api.err_fail_to_auth'), false);
        }
    }


    public function signup(Request $request)
    {
        $valid = $this->directValidation([
            'type' => ["required","in:mobile,email,social"],
            'push_token' => ['nullable'],
            'email' => ['required_if:type,email','required_if:type,social',Rule::unique('users') ->whereNull('deleted_at')],
            'date_of_birth' => ['required', 'max:100'],
            'country_code' => ['required_if:type,mobile','required_if:type,social','max:4'],
            'device_id' => ['required', 'max:255'],
            'mobile' => ['required_if:type,mobile','numeric', Rule::unique('users')->where('country_code', $request->country_code)->whereNull('deleted_at')],
            'device_type' => ['required', 'in:android,ios'],
            'provider' => ['required_if:type,social','in:apple,google,facebook'],
            'provider_id'=>['required_if:type,social'],

        ],[
            'mobile.unique' => __('api.err_mobile_is_exits'),
            'email.unique' => __('api.err_email_is_exits'),
        ]);
        $user= User::create([
           // 'password' => $request->password,
             'email' => $request->email,
           // 'name' => $request->first_name . ' ' . $request->last_name,
           // 'first_name' => $request->first_name,
           // 'last_name' => $request->last_name,
           //  'username' => $request->username,
            'country_code' => $request->country_code,
            'mobile' => $request->mobile ? $request->mobile : "",
            'date_of_birth' => $request->date_of_birth,
            'reg_step'=>1,
            'profile_image' => config('constants.default.user_image'),
        ]);
        $provider = $request->provider;
        $social_id = $request->provider_id;
        if ($user) {
            Auth::loginUsingId($user->id);
            $token = User::AddTokenToUser();
        if($request->type === "social"){
            Auth::user()->social_logins()->updateOrCreate(
                ['provider' => $provider, 'user_id' => $user->id],
                ['provider' => $provider, 'provider_id' => $social_id]
            ); 
        }
         $this->sendResponse(200, __('api.suc_user_register'), $this->get_user_data($token));
        
        } else {
            $this->sendError(__('api.err_something_went_wrong'), false);
        }
    }

    public function forgot_password(Request $request)
    {
        // $data = User::password_reset($request->email, false);
        // $status = $data['status'] ? 200 : 412;
        // $this->sendResponse($status, $data['message']);
        $rules = [
            'email'=>['required','email',Rule::exists("users")->whereNull("deleted_at")],
        ];
        $messages = ['email.exists' => __('api.err_email_not_register')];
        $this->directValidation($rules, $messages);
        $otp = rand(111111, 999999);
        $user = Otpverify::updateOrCreate(
            ['email' => $request->email],
            ['otp' => $otp]
        );  
         $data = user::where('email',$request->email)->update(['otp' => $otp]);
        $user = user::where('email',$request->email)->first(); 
        Mail::to($request->email)->send(new User_Rest_Password($user));
        return ['status' => 200, 'message' => 'Email send successfully','reset_token'=>$user->reset_token];
    }

    public function content(Request $request, $type)
    {
        $data = Content::where('slug', $type)->first();
        return ($data) ? $data->content : "Invalid Content type passed";
    }

    public function check_ability(Request $request)
    {
        $otp = "";
        $type = $request->type;
        $is_sms_need = $request->is_sms_need;
        $rules = [
            'type' => ['required', 'in:username,email,mobile_number'],
            'value' => ['required'],
            'country_code' => ['required_if:type,mobile']
        ];
        $user_id = $request->user_id;
        if ($type == "email") {
            $rules['value'][] = 'email';
            $rules['value'][] = Rule::unique('users', 'email')->ignore($user_id)->whereNull('deleted_at');
        } elseif ($type == "username") {
            $rules['value'][] = 'regex:/^\S*$/u';
            $rules['value'][] = Rule::unique('users', 'username')->ignore($user_id)->whereNull('deleted_at');
        } else {
            $rules['value'][] = 'integer';
            $rules['value'][] = Rule::unique('users', 'mobile')->ignore($user_id)->where('country_code', $request->country_code)->whereNull('deleted_at');
        }
        $this->directValidation($rules, ['regex' => __('api.err_space_not_allowed'), 'unique' => __('api.err_field_is_taken', ['attribute' => str_replace('_', ' ', $type)])]);
        $this->sendResponse(200, __('api.succ'));
    }


    public function version_checker(Request $request)
    {
        $type = $request->type;
        $version = $request->version;
        $this->directValidation([
            'type' => ['required', 'in:android,ios'],
            'version' => 'required',
            'device_id' => ['required', 'max:255'],
            'push_token' => ['required'],
        ]);
        $data = [
            'is_force_update' => ($type == "ios") ? IOS_Force_Update : Android_Force_Update,
        ];
        DeviceToken::updateOrCreate(
            ['device_id' => $request->device_id, 'type' => $request->device_type],
            ['device_id' => $request->device_id, 'type' => $request->device_type, 'push_token' => $request->push_token, 'badge' => 0]
        );
        $check = ($type == "ios") ? (IOS_Version <= $version) : (Android_Version <= $version);
        if ($check) {
            $this->sendResponse(200, __('api.succ'), $data);
        } else {
            $this->sendResponse(412, __('api.err_new_version_is_available'), $data);
        }
    }
        public function sendOTPMobile(Request $request)
        {
            $this->directValidation([ 
                'country_code' => ['required', 'max:4'],
                'mobile' => ['required','numeric'],
            ]);
            if (register_user_authy($request->country_code . $request->mobile)) {
                OtpRequestLog::create([
                    'country_code' => $request->country_code,
                    'mobile' => $request->mobile,
                    'type' => "signup",
                    'ip' => $request->ip(),
                ]);
                $this->sendResponse(200, __('api.succ_otp_sent'), false);
            }
            $this->sendError(__("api.err_something_went_wrong"));
        }

       

    public function verifyOTPmobile(Request $request)
    { 
        $this->directValidation([
            'otp' => ['required', 'numeric'],
            'country_code' => ['required', 'max:5'],
            'mobile' => ['required', 'numeric'],
            'device_type'=>['required'],
            'device_id'=>['required'],
        ]);
      //  if (verify_token_authy($request->country_code . $request->mobile, $request->otp)) {
            $where = ['mobile' => $request->mobile,'country_code' => $request->country_code,'type' => 'user', 'status' => 'active'];
            $user = User::where($where)->first();
            if($user){
                Auth::loginUsingId($user->id);
                $token = User::AddTokenToUser();
                $this->sendResponse(200, __('api.suc_user_login'), $this->get_user_data($token));
            }
            else{
                $this->sendResponse(201, __('api.otp_verfiy_succ'));
            }
           $this->sendError(__('api.err_fail_to_auth'), false);
     //  }
      //  $this->sendError(__("api.err_invalid_api"));
    }

    

    public function sendOTPEmail(Request $request)
    {
        $this->directValidation([ 
            'email' => ['required'],
        ]);
        $otp = rand(111111, 999999);
        $user = Otpverify::updateOrCreate(
        ['email' => $request->email],
        ['otp' => $otp]
    );  
        if($user){
            $mail = Mail::to($request->email)->send(new SendOTPmail($user));
            $this->sendResponse(200, __('api.succ_sent_mail'), false);
        }
        else{
            $this->sendError(__("api.err_something_went_wrong"));
        }
    }

    public function verifyOTPemail(Request $request)
    { 
        $this->directValidation([
            'email' => ['required','max:50', 'email', Rule::exists("otp_verify")],
            'otp' => ['required', 'numeric', Rule::exists('otp_verify')->where(function ($query) use ($request) {
                $query->where('updated_at', '>=', Carbon::now()->subMinutes(1))
                    ->where('otp', $request->otp);
            }),
        ],
            'device_type'=>['required'],
            'device_id'=>['required'],
        ],
        [
            "email.exists" => "Email not exists",
            "otp" => "otp not match",
        ]);

        $where = ['email' => $request->email,
        'country_code' => $request->country_code,
        'type' => 'user', 'status' => 'active'];
        $user = User::where($where)->first();
        if($user){
                Auth::loginUsingId($user->id);
                $token = User::AddTokenToUser();
                $this->sendResponse(200, __('api.suc_user_login'), $this->get_user_data($token));
            }
            else{
                $this->sendResponse(201, __('api.otp_verfiy_succ'));
            }
          $this->sendError(__('api.err_fail_to_auth'), false);
        
        // $reset_token = User::GenerateResetToken($request);
        // $this->sendResponse(200, __('Email Successfully Verfiy'), ["token" => $reset_token]);    
    }

    public function resetPassword(Request $request)
    {
        $this->directValidation([
            'reset_token' => ['required', Rule::exists('users', "reset_token")->whereNull('deleted_at')],
            'password' => ['required']
        ]);
        User::UpdatePassword($request);
        $this->sendResponse(200, __('Successfully password rest'));
    }
    public function verifyOTP(Request $request)
    {
        $this->directValidation([
            'email' => ['required','max:50', 'email', Rule::exists("users")->whereNull("deleted_at")],
            'otp' => ['required', 'numeric', Rule::exists('otp_verify')->where(function ($query) use ($request) {
                $query->where('updated_at', '>=', Carbon::now()->subMinutes(1))
                    ->where('otp', $request->otp);
            }),
        ],
        ],
        [
            "email.exists" => "Email not exists",
            "otp" => "otp not match",
        ]);
        $reset_token = User::GenerateResetToken($request);
        $this->sendResponse(200, __('Email successfully verfiy'), ["reset_token" => $reset_token]);
    }



    public function check_social_ability(Request $request)
    {
        $this->directValidation([
            'social_id' => ['required'],
            'device_id' => ['required'],
            'device_type' => ['required', 'in:android,ios'],
            'push_token' => ['nullable'],
            'provider' => ['required', 'in:apple,google,facebook'],
        ]);
       
        $provider = $request->provider;
        $user_id = 0;
        $email = $request->email;
        $social_id = $request->social_id;
     
        if (!$user_id) {
            $is_user_exits = SocialAccounts::where(['provider' => $provider, 'provider_id' => $social_id])
                ->has('user')->with('user')->first();
            if ($is_user_exits) {
                if ($is_user_exits->user->status == "active") {
                    $user_id = $is_user_exits->user_id;
                } else {
                    $this->sendResponse(201, __('api.err_account_ban'));
                }
            }
        }
        if (!$user_id) {
            $user = User::create([
                'email' => $email,
                // 'name' => ($request->name) ?? "",
            ]);
            $user_id = $user->id;   
        }
        if ($user_id) {
            Auth::loginUsingId($user_id);
            Auth::user()->social_logins()->updateOrCreate(
                ['provider' => $provider, 'user_id' => $user_id],
                ['provider' => $provider, 'provider_id' => $social_id]
            );
            $token = User::AddTokenToUser();
            $this->sendResponse(200, __('api.suc_user_login'), $this->get_user_data($token));
        } else {
            $this->sendResponse(201, __('api.err_please_register_social'));
        }
    }


  
}
