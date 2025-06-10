<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BillboardResource extends JsonResource
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
            'file' => $this->file,
            'logo_file' => $this->logo_file,
            'horizontal_file' => $this->horizontal_file,
            'video_file' => $this->video_file,
            'link' => $this->link,
            'user' => $this->user ? $this->user->only(['id', 'name', 'username', 'email', 'verified', 'membership_level', 'status']) : null,
            'status' => $this->status,
            'created_at' => $this->created_at ? $this->created_at->format('d.m.Y') : null,
        ];
    }
}
