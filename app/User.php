<?php

namespace App;

use App\Mail\General\User_Password_Reset_Mail;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Storage;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;
    use SoftDeletes;

    protected $guarded = [];
    protected $hidden = ['password', 'remember_token'];
    protected $casts = [];

    public function comments()
    {
        return $this->hasMany(Comment::class, 'user_id');
    }

    public function posts()
    {
        return $this->hasMany(Post::class, 'user_id');
    }

    public function userInterested()
    {
        return $this->hasMany(UserInterested::class, 'user_id');
    }

    public function socialAccounts()
    {
        return $this->hasMany(SocialAccounts::class, 'user_id');
    }

    public static function AddTokenToUser()
    {
        $user = Auth::user();
        $token = token_generator();
        $device_id = request('device_id');
        DeviceToken::where('device_id', $device_id)->delete();
        $user->login_tokens()->create([
            'token' => $token,
            'type' => request('device_type'),
            'device_id' => $device_id,
            'push_token' => request('push_token'),
        ]);
        return $token;
    }

    public function login_tokens()
    {
        return $this->hasMany(DeviceToken::class);
    }

    public function scopeGenerateResetToken($query, Request $request): string
    {
        $reset_token =  genUniqueStr('', 50, 'users', 'reset_token', true);
        $query->whereNull("deleted_at")->where('email', $request->email)
            ->where('otp', $request->otp)->update([
                "reset_token" => $reset_token
            ]);
        return $reset_token;
    }

    public static function password_reset($email = "", $flash = true)
    {
        $user = User::where('type','admin')->where('email', $email)->first();
        if ($user) {
            if ($user->status == "active") {
                $user->update([
                    'reset_token' => genUniqueStr('', 30, 'users', 'reset_token', true)
                ]);
                Mail::to($user->email)->send(new User_Password_Reset_Mail($user));
                if ($flash) {
                    success_session('Email send Successfully');
                } else {
                    return ['status' => true, 'message' => 'Email send Successfully'];
                }
            } else {
                if ($flash) {
                    error_session('User account disabled by administrator');
                } else {
                    return ['status' => false, 'message' => 'Email send Successfully'];
                }

            }
        } else {
            if ($flash) {
                error_session(__('api.err_email_not_exits'));
            } else {
                return ['status' => false, 'message' => __('api.err_email_not_exits')];
            }
        }
    }

    public function scopeSimpleDetails($query)
    {
        return $query->select(['id', 'name', 'first_name', 'last_name', 'profile_image']);
    }

    public function setPasswordAttribute($password)
    {
        $this->attributes['password'] = bcrypt($password);
    }

    public function getProfileImageAttribute($val)
    {
        if (!empty($val)) {
            if (filter_var($val, FILTER_VALIDATE_URL)) {
                return $val;
            }

            return Storage::disk('s3')->url($val);
        }

        return get_asset($val, false, get_constants('default.user_image'));
    }

    public function scopeAdminSearch($query, $search)
    {
        $query->Where('email', 'like', "%$search%")
            ->orWhere('username', 'like', "%$search%")
            ->orWhere('country_code', 'like', "%$search%")
            ->orWhere('mobile', 'like', "%$search%")
            ->orWhereRaw("concat(country_code,'',mobile) like '%$search%'")
            ->orWhereRaw("concat(country_code,' ',mobile) like '%$search%'");
    }

    public function scopeUpdatePassword($query, Request $request): bool
    {
        $query->where('reset_token', $request->reset_token)->update([
            "password" => bcrypt($request->password),
            "reset_token" => NULL
        ]);
        return TRUE;
    }

    public function social_logins(): HasOne
    {
        return $this->hasOne(SocialAccounts::class, "user_id", "id");
    }

}
