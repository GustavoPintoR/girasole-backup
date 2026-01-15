<?php

namespace App\Http\Requests\CadastralUnit;

use Illuminate\Foundation\Http\FormRequest;

class CadastralUnitIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'per_page' => 'nullable|integer|min:1',
            'sort_by' => 'nullable|in:sheet,parcel,cadastral_area,city_id,created_at,city,province,region',
            'sort_order' => 'nullable|in:asc,desc',
            'search' => 'nullable|string|max:255',
        ];
    }

    public function validated($key = null, $default = null): array
    {
        $validated = parent::validated();

        // Set defaults
        $validated['per_page'] = $validated['per_page'] ?? 10;
        $validated['sort_order'] = $validated['sort_order'] ?? 'asc';

        return $validated;
    }
}
