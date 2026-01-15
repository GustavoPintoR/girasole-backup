<?php

namespace App\Http\Requests\Settings;

use Illuminate\Foundation\Http\FormRequest;

class BillingInfoUpdateRequest extends FormRequest
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
            'type' => ['required', 'string', 'max:50', 'in:business,sole_business'],
            'business_name' => ['nullable', 'string', 'max:255', 'required_if:type,business'],
            'fiscal_code' => ['required', 'string', 'max:16'],
            'vat_number' => ['nullable', 'string', 'max:20', 'required_if:type,business'],
            'sdi_code' => ['nullable', 'string', 'max:7', 'required_if:type,business'],
        ];
    }
}
