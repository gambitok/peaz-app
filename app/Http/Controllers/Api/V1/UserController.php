<?php

namespace App\Http\Controllers\Api\V1;


use App\DeviceToken;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\ResponseController;
use Illuminate\Validation\Rule;
use App\UserInterested;
use App\InterestedList;
use App\User;
use Illuminate\Support\Facades\Auth;
use  DateTime;

class UserController extends ResponseController
{

    public function getProfile()
    {
        $this->sendResponse(200, __('api.succ'), $this->get_user_data());
    }

    public function logout(Request $request)
    {
        DeviceToken::where('token', get_header_auth_token())->delete();
        $this->sendResponse(200, __('api.succ_logout'), false);
    }

    public function update_name(Request $request)
    {
        $user_data = $request->user();
        $this->directValidation([
            'first_name' => ['required', 'max:100'],
            'last_name' => ['required', 'max:100'],
        ]);
        $user_data->update([
            'name' => $request->first_name . ' ' . $request->last_name,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
        ]);
        $this->sendResponse(200, __('api.succ_name_update'), $this->get_user_data());
    }

    public function update_email(Request $request)
    {
        $user_data = $request->user();
        $this->directValidation([
            'email' => ['required', 'email', Rule::unique('users')->ignore($user_data->id)->whereNull('deleted_at')],
        ]);
        $user_data->update([
            'email' => $request->email,
        ]);
        $this->sendResponse(200, __('api.succ_email_update'), $this->get_user_data());
    }

    public function update_mobile_number(Request $request)
    {
        $user_data = $request->user();
        $this->directValidation([
            'mobile' => ['required', 'integer', Rule::unique('users')->where('country_code', $request->country_code)->ignore($user_data->id)->whereNull('deleted_at')],
            'country_code' => ['required'],
        ], [
            'mobile.unique' => __('api.err_mobile_is_exits'),
        ]);
        $user_data->update([
            'mobile' => $request->mobile,
            'country_code' => $request->country_code,
        ]);
        $this->sendResponse(200, __('api.succ_number_update'), $this->get_user_data());
    }

    public function update_profile_image(Request $request)
    {
        $user_data = $request->user();
        $this->directValidation([
            'profile_image' => ['required', 'file', 'image'],
        ]);
        $up = $this->upload_file('profile_image', 'user_profile_image');
        if ($up) {
            un_link_file($user_data->profile_image);
            $user_data->update(['profile_image' => $up]);
            $this->sendResponse(200, __('api.succ_profile_picture_update'), $this->get_user_data());
        } else {
            $this->sendError(412, __('api.errr_fail_to_upload_image'));
        }
    }

    public function addUsername(Request $request)
    {  
        $rules = [
            'username' => ['required', 'max:255',Rule::unique('users')->where("username", $request->username)],
        ];
         $this->directValidation($rules);
         $user = $request->user();
         $user->update(['username'=>$request->username,'reg_step'=>2]);
        if($user){
             $this->sendResponse(200, __('api.suc_username'), $this->get_user_data());
         }
         else {
            $this->sendError(__('api.err_fail_to_auth'), false);
        }
    }
    public function serchUsername(Request $request)
    {  
        $rules = [
            'username' => ['required',Rule::unique('users')->where("username", $request->username)],
        ];
        $messages = [
           'Username is not avaliable',
        ];
         $this->directValidation($rules,$messages);
         $this->sendResponse(200, __('api.suc_serch_username'));
    }

    public function saveUserInterested(Request $request)
    {  
        $rules = [
            'cuisines_title' => ['required'],
            'food_and_drink_title' => ['required'],
        ];
         $this->directValidation($rules);
        
         foreach($request->cuisines_title as $key => $value) {
            $data = UserInterested::create([
                'user_id'=>$request->user()->id,
                'type'=> 1,
                'title'=> $value,
             ]);
         }   

         foreach($request->food_and_drink_title as $key => $value) {
            $data = UserInterested::create([
                'user_id'=>$request->user()->id,
                'type'=> 2,
                'title'=> $value,
             ]);
         }   

        
        
         $user = $request->user();
         $user->update(['reg_step'=>4]);
        if($data){
             $this->sendResponse(200, __('api.suc_interested'), $this->get_user_data());
         }
         else {
            $this->sendError(__('api.err_fail_to_auth'), false);
        }
    }

    public function userDiet(Request $request)
    {  
        $rules = [
            'diet_title' => ['required'],
        ];
         $this->directValidation($rules);
         foreach($request->diet_title as $key => $value) {
            $data = UserInterested::create([
                'user_id'=>$request->user()->id,
                'type'=> 3,
                'title'=> $value,
             ]);
         }   
         $user = $request->user();
         $user->update(['reg_step'=>5]);
        if($data){
             $this->sendResponse(200, __('api.suc_interested'), $this->get_user_data());
         }
         else {
            $this->sendError(__('api.err_fail_to_auth'), false);
        }
    }

    public function createpassword(Request $request)
    {
        $rules = [
            'password' => ['required'],
        ];
         $this->directValidation($rules);
         $user = $request->user();
         $user->update(['password'=>$request->password,'reg_step'=>3]);
         $this->sendResponse(200, __('api.suc_password'), $this->get_user_data());
    }
   
    public function interestedList(Request $request)
    {
        $user = $request->user();
        $dob = $user->date_of_birth;
        $dob = date("Y-m-d", strtotime($dob));
        $dobObject = new DateTime($dob);
        $nowObject = new DateTime();
        $diff = $dobObject->diff($nowObject);
        $cuisines= InterestedList::where('type',1)->get();
        if($diff->y > 18){
          $food_and_drink= InterestedList::where('type',2)->where(function($q) {
            $q->where('category_id',1)->orWhere('category_id',3);
          })->get();
        }
        else{
        $food_and_drink= InterestedList::where('type',2)->where(function($q) {
            $q->where('category_id',2)->orWhere('category_id',3);
          })->get();
        }   
        $diet= InterestedList::where('type',3)->get();
        $this->sendResponse(200, __('api.suc_interestedlist'),["cuisines"=>$cuisines,"food_and_drink"=>$food_and_drink ,"diet"=>$diet]);
    }
}

