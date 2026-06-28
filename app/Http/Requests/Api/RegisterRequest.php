<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'user_type' => ['sometimes', 'string', Rule::in(['student', 'teacher'])],
            'birth_date' => ['nullable', 'date', 'before:today'],
            'country_id' => ['nullable', 'exists:countries,id'],
            'region_id' => ['nullable', 'exists:regions,id'],
            'district_id' => ['nullable', 'exists:districts,id'],
            'city_id' => ['nullable', 'exists:cities,id'],
            'school_id' => ['nullable', 'exists:schools,id'],
            'school_class_id' => ['nullable', 'exists:school_classes,id'],
            'avatar' => ['nullable', 'string', 'url', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Имя обязательно для заполнения',
            'name.max' => 'Имя не должно превышать 255 символов',
            'email.required' => 'Email обязателен для заполнения',
            'email.email' => 'Введите корректный email адрес',
            'email.unique' => 'Пользователь с таким email уже существует',
            'password.required' => 'Пароль обязателен для заполнения',
            'password.min' => 'Пароль должен содержать минимум 8 символов',
            'password.confirmed' => 'Пароли не совпадают',
            'user_type.in' => 'Некорректный тип пользователя',
            'birth_date.before' => 'Дата рождения должна быть в прошлом',
            '*.exists' => 'Выбранное значение не существует',
        ];
    }
}
