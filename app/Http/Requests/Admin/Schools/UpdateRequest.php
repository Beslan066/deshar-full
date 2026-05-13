<?php

namespace App\Http\Requests\Admin\Schools;

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
            'country_id' => 'required',
            'district_id' => 'nullable',
            'locality_id' => 'required',
            'city_id' => 'nullable',  // Поле городов для иностранных ученников
            'region_id' => 'nullable',
            'manager_id' => 'nullable',
            'director_id' => 'nullable',
        ];
    }

    public function messages(): array {
        return [
            'name.required' => 'Поле обязательно для заполнения',
            'locality_id.required' => 'Необходимо выбрать населенный пункт'
        ];
    }
}
