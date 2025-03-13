<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RestaurantRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'title' => 'nullable|string|max:255',
            'file' => $this->isMethod('post') ? 'required|file|image|max:41943040' : 'nullable|file|image|max:41943040',
            'link' => 'required|string|max:255|url',
            'status' => 'nullable',
        ];
    }

    public function messages()
    {
        return [
            'file.required' => 'The "File" field is required.',
            'file.image' => 'The file must be an image.',
            'file.max' => 'The image size must not exceed 40MB.',
            'link.required' => 'The "Link" field is required.',
            'link.url' => 'The link must be a valid URL.',
            'status.boolean' => 'The status must be true or false.',
        ];
    }
}
