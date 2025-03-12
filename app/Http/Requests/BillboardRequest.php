<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BillboardRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'file' => $this->isMethod('post') ? 'required|file|image|max:2048' : 'nullable|file|image|max:2048',
            'link' => 'required|string|max:255|url',
            'status' => 'nullable',
        ];
    }

    public function messages()
    {
        return [
            'file.required' => 'The "File" field is required.',
            'file.image' => 'The file must be an image.',
            'file.max' => 'The image size must not exceed 2MB.',
            'link.required' => 'The "Link" field is required.',
            'link.url' => 'The link must be a valid URL.',
            'status.boolean' => 'The status must be true or false.',
        ];
    }
}
