<?php

namespace App\Http\Requests\Admin\Schools;

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

    protected function defaults()
    {
        return [
            'country_id'   => '1',
            'region_id'  => '1',
        ];
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
            'country_id' => 'nullable',
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
          'name.required' => 'Название обязательно для заполения',
          'locality_id.required' => 'Необходимо выбрать населенный пункт',
        ];
    }
}
