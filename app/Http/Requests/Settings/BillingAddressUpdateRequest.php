<?php

namespace App\Http\Requests\Settings;

use Illuminate\Foundation\Http\FormRequest;

class BillingAddressUpdateRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'street' => ['required', 'string', 'max:255'],
            'street_number' => ['required', 'string', 'max:255'],
            'postal_code' => ['required', 'string', 'max:10', 'exists:postal_codes,code'],
            'city_id' => ['required', 'integer', 'exists:cities,id'],
            'province_id' => ['required', 'integer', 'exists:provinces,id'],
            'region_id' => ['required', 'integer', 'exists:regions,id'],
            'state' => ['required', 'string', 'max:2'],
        ];
    }
}
