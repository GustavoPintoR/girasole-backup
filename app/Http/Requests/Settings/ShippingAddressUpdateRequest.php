<?php

namespace App\Http\Requests\Settings;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class ShippingAddressUpdateRequest extends FormRequest
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
            'same_as_billing' => ['required', 'boolean'],
            'shipping_region_id' => ['required_if:same_as_billing,false'],
            'shipping_province_id' => ['required_if:same_as_billing,false'],
            'shipping_postal_code' => ['required_if:same_as_billing,false', 'max:10'],
            'shipping_city_id' => ['required_if:same_as_billing,false'],
            'name' => ['required_if:same_as_billing,false'],
            'address' => ['required_if:same_as_billing,false'],
            'cellular' => ['required_if:same_as_billing,false'],
        ];
    }

    /**
     * Handle a failed validation attempt.
     *
     * @param  \Illuminate\Contracts\Validation\Validator  $validator
     * @return void
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function failedValidation(Validator $validator)
    {
        if ($this->expectsJson()) {
            throw new ValidationException($validator, new JsonResponse([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422));
        }

        throw new ValidationException($validator);
    }
}
