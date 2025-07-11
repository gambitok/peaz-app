<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class IngredientResource extends JsonResource
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
            'user_id' => $this->user_id,
            'type' => $this->type ?: null,
            'name' => $this->name,
            'measurement' => $this->measurement,
            'created_at' => $this->created_at ? $this->created_at->format('d.m.Y') : null,
        ];
    }
}
