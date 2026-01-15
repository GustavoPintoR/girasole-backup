<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CustomField extends Model
{
    use HasFactory;

    protected $fillable = [
        'model_type',
        'key',
        'label',
        'type',
        'unit',
        'is_required',
        'description',
        'options',
        'order',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'options' => 'array',
    ];

    public function values(): HasMany
    {
        return $this->hasMany(CustomFieldValue::class);
    }

    // Get fields for a specific model
    public static function forModel(string $modelType): \Illuminate\Database\Eloquent\Collection
    {
        return static::where('model_type', $modelType)
            ->orderBy('order')
            ->get();
    }

    public static function getValidationRules(string $modelType): array
    {
        $fields = static::forModel($modelType);
        $rules = [];
        foreach ($fields as $field) {
            $fieldRules = $field->is_required ? ['required'] : ['nullable'];
            switch ($field->type) {
                case 'text':
                case 'textarea':
                    $fieldRules[] = 'string';
                    break;
                case 'number':
                    $fieldRules[] = 'integer';
                    break;
                case 'float':
                    $fieldRules[] = 'numeric';
                    break;
                case 'date':
                case 'datetime':
                    $fieldRules[] = 'date';
                    break;
                case 'select':
                    $fieldRules[] = 'in:' . implode(',', $field->options);
                    break;
                case 'checkbox':
                    $fieldRules[] = 'boolean';
                    break;
                default:
                    break;
            }
            $rules["custom_fields.{$field->key}"] = $fieldRules;
        }
        return $rules;
    }
}
