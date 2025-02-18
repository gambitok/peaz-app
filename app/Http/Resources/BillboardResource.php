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
            'title' => $this->title,
            'caption' => $this->caption,
            'file' => $this->file,
            'link' => $this->link,
//            'tag' => $this->tag ? $this->tag->toArray() : null,
            'tag_id' => $this->tag_id,
            'user' => $this->user ? $this->user->only(['id', 'name', 'username', 'email', 'verified', 'membership_level', 'status']) : null,
            'verified' => $this->verified,
            'status' => $this->status,
            'created_at' => $this->created_at ? $this->created_at->format('d.m.Y') : null,
        ];
    }
}
