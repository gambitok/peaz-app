<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FilterRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $rules = [
            'name' => 'required|string|max:255',
            'tag_ids' => 'required|array',
            'tag_ids.*' => 'integer|exists:tags,id',
            'dietary_ids' => 'required|array',
            'dietary_ids.*' => 'integer|exists:dietaries,id',
            'cuisine_ids' => 'required|array',
            'cuisine_ids.*' => 'integer|exists:cuisines,id',
        ];

        if ($this->isMethod('put') || $this->isMethod('patch')) {
            $rules['name'] = 'sometimes|string|max:255';
            $rules['tag_ids'] = 'sometimes|array';
        }

        return $rules;
    }

    public function messages()
    {
        return [
            'name.required' => 'The "Filter Name" field is required.',
            'name.string' => 'The name must be a string.',
            'name.max' => 'The name must not exceed 255 characters.',
            'tag_ids.required' => 'At least one tag must be selected.',
            'tag_ids.array' => 'Tags must be in the form of an array.',
            'tag_ids.*.integer' => 'Tag IDs must be integers.',
            'tag_ids.*.exists' => 'One or more of the selected tags are not countries.',
            'dietary_ids.required' => 'At least one dietary option must be selected.',
            'dietary_ids.array' => 'Dietary options must be in the form of an array.',
            'dietary_ids.*.integer' => 'Dietary option IDs must be integers.',
            'dietary_ids.*.exists' => 'One or more of the selected dietary options are invalid.',
            'cuisine_ids.required' => 'At least one cuisine must be selected.',
            'cuisine_ids.array' => 'Cuisines must be in the form of an array.',
            'cuisine_ids.*.integer' => 'Cuisine IDs must be integers.',
            'cuisine_ids.*.exists' => 'One or more of the selected cuisines are invalid.',
        ];
    }
}
