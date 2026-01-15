<?php

namespace App\Http\Requests\CadastralGroup;

use Illuminate\Foundation\Http\FormRequest;

class StoreCadastralGroupRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization handled by Gates in controller
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $rules = [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'color' => 'nullable|string|size:7|regex:/^#[0-9A-Fa-f]{6}$/',
            'creation_method' => 'required|in:units,manual,import',
            'geojson' => 'required_if:creation_method,manual|required_if:creation_method,import|nullable|json',
            'cultivars' => 'nullable|array',
            'cultivars.*.id' => 'required|exists:cultivars,id',
            'planting_schemes' => 'nullable|array',
            'planting_schemes.*.id' => 'required|exists:planting_schemes,id',
            'plant_diseases' => 'nullable|array',
            'plant_diseases.*.id' => 'required|exists:plant_diseases,id',
            'irrigations' => 'nullable|array',
            'irrigations.*.id' => 'required|exists:irrigations,id',
        ];

        // Add user_id validation for admins
        if ($this->user()?->isSuperAdmin()) {
            $rules['user_id'] = 'nullable|exists:users,id';
        }

        // Add unit validation for unit-based creation
        if ($this->input('creation_method') === 'units') {
            $rules['unit_ids'] = 'required|array|min:1';
            $rules['unit_ids.*'] = 'required|integer|exists:cadastral_units,id';
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => __('ui.name_is_required'),
            'creation_method.required' => __('ui.creation_method_is_required'),
            'creation_method.in' => __('ui.invalid_creation_method'),
            'geojson.required_if' => __('ui.geojson_required_for_method'),
            'unit_ids.required' => __('ui.units_required_for_method'),
            'unit_ids.min' => __('ui.at_least_one_unit_required'),
        ];
    }

    /**
     * Get the user ID for the group
     */
    public function getUserId(): int
    {
        if ($this->user()?->isSuperAdmin() && $this->has('user_id')) {
            return $this->input('user_id');
        }

        return $this->user()->id;
    }

    /**
     * Get cultivar IDs from the request
     */
    public function getCultivarIds(): ?array
    {
        if (!$this->has('cultivars')) {
            return null;
        }

        return array_column($this->input('cultivars'), 'id');
    }

    /**
     * Get planting scheme IDs from the request
     */
    public function getPlantingSchemeIds(): ?array
    {
        if (!$this->has('planting_schemes')) {
            return null;
        }

        return array_column($this->input('planting_schemes'), 'id');
    }

    /**
     * Get plant disease IDs from the request
     */
    public function getPlantDiseaseIds(): ?array
    {
        if (!$this->has('plant_diseases')) {
            return null;
        }

        return array_column($this->input('plant_diseases'), 'id');
    }

    /**
     * Get irrigation IDs from the request
     */
    public function getIrrigationIds(): ?array
    {
        if (!$this->has('irrigations')) {
            return null;
        }

        return array_column($this->input('irrigations'), 'id');
    }
}
