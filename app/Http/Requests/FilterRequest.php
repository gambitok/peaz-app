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
            'name.required' => 'Поле "Назва фільтра" є обовʼязковим.',
            'name.string' => 'Назва повинна бути рядком.',
            'name.max' => 'Назва не повинна перевищувати 255 символів.',
            'tag_ids.required' => 'Потрібно вибрати принаймні один тег.',
            'tag_ids.array' => 'Теги повинні бути у вигляді масиву.',
            'tag_ids.*.integer' => 'ID тегів повинні бути цілими числами.',
            'tag_ids.*.exists' => 'Один або кілька вибраних тегів не існують.',
        ];
    }
}
