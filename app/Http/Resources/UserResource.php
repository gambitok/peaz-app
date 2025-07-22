<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'username' => $this->username,
            'profile_image' => $this->getProfileImageAttribute(),
            'bio' => $this->bio,
            'website' => $this->website,
            'email' => $this->email,
            'mobile' => $this->mobile,
            'type' => $this->type,
            'membership_level' => $this->membership_level,
            'verified' => $this->verified,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'is_following' => $this->is_following ?? 0,
        ];
    }

    /**
     * Get the profile image attribute.
     *
     * @return string
     */
    public function getProfileImageAttribute()
    {
        if (!empty($this->profile_image)) {
            if (filter_var($this->profile_image, FILTER_VALIDATE_URL)) {
                return $this->profile_image;
            }

            return Storage::disk('s3')->url($this->profile_image);
        }

        return get_asset($this->profile_image, false, get_constants('default.user_image'));
    }
}
