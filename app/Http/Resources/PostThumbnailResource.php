<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PostThumbnailResource extends JsonResource
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
            'post_id' => $this->post_id,
            'file' => $this->file ?: null,
            'file_type' => $this->file_type ?: null,
            'thumbnail' => $this->thumbnail ?: null,
            'type' => $this->type ?: null,
            'title' => $this->title,
            'description' => $this->description,
            'created_at' => $this->created_at ? $this->created_at->format('d.m.Y') : null,
        ];
    }
}
