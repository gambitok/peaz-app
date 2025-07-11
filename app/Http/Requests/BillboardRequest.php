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
            'file' => $this->isMethod('post') ? 'required|file|image|max:41943040' : 'nullable|file|image|max:41943040',
            'logo_file' => 'nullable|file|image|max:41943040',
            'horizontal_file' => 'nullable|file|image|max:41943040',
            'video_file' => 'nullable|file|mimes:mp4,mov,avi,mkv|max:51200',
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
            'logo_file.image' => 'The logo file must be an image.',
            'logo_file.max' => 'The image size must not exceed 40MB.',
            'horizontal_file.image' => 'The horizontal file must be an image.',
            'horizontal_file.max' => 'The image size must not exceed 40MB.',
            'video_file.max' => 'The video size must not exceed 50MB.',
            'video_file.video' => 'The video file must be an video.',
            'link.required' => 'The "Link" field is required.',
            'link.url' => 'The link must be a valid URL.',
            'status.boolean' => 'The status must be true or false.',
        ];
    }
}
