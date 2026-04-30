<?php

namespace App\Http\Requests\Admin\Cities;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
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
            'country_id' => 'required',
        ];
    }

    public function messages(): array {
        return [
          'name.required' => 'Название обязательно для заполения',
          'country_id.required' => 'Необходимо выбрать страну для города',
        ];
    }
}
