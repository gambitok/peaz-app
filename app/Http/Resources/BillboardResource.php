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
            'tag_id' => $this->tag_id,
            'verified' => $this->verified,
            'status' => $this->status,
        ];
    }
}
