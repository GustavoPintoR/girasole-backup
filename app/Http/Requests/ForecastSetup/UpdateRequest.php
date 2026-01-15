<?php

namespace App\Http\Requests\ForecastSetup;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'changes' => 'required|array',
            'changes.*.id' => 'required|exists:cadastral_groups,id',
            'changes.*.days' => 'required|array',
            'changes.*.days.monday' => 'boolean',
            'changes.*.days.tuesday' => 'boolean',
            'changes.*.days.wednesday' => 'boolean',
            'changes.*.days.thursday' => 'boolean',
            'changes.*.days.friday' => 'boolean',
            'changes.*.days.saturday' => 'boolean',
            'changes.*.days.sunday' => 'boolean',
            'changes.*.retention' => 'nullable|integer|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'changes.required' => __('validation.required', ['attribute' => 'changes']),
        ];
    }
}
