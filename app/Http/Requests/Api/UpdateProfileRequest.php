<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->user()->id;

        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', 'max:255', Rule::unique('users')->ignore($userId)],
            'avatar' => ['nullable', 'string', 'url', 'max:255'],
            'birth_date' => ['nullable', 'date', 'before:today'],
            'country_id' => ['nullable', 'exists:countries,id'],
            'region_id' => ['nullable', 'exists:regions,id'],
            'district_id' => ['nullable', 'exists:districts,id'],
            'city_id' => ['nullable', 'exists:cities,id'],
            'school_id' => ['nullable', 'exists:schools,id'],
            'school_class_id' => ['nullable', 'exists:school_classes,id'],
            'preferences' => ['nullable', 'array'],
            'settings' => ['nullable', 'array'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.unique' => 'Пользователь с таким email уже существует',
            'birth_date.before' => 'Дата рождения должна быть в прошлом',
            '*.exists' => 'Выбранное значение не существует',
        ];
    }
}
