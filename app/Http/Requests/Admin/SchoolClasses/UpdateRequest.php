<?php

namespace App\Http\Requests\Admin\SchoolClasses;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string',
            'teacher_id' => 'required|exists:users,id',
            'school_id' => 'required|exists:schools,id',
            'fon' => 'nullable|string',
            'complexity' => 'nullable|string',
        ];
    }

    public function messages(): array {
        return [
            'name.required' => 'Название обязательно для заполения',
            'teacher_id.required' => 'Необходимо выбрать учителя для класса',
            'school_id.required' => 'Необходимо выбрать школу для класса',
        ];
    }
}
