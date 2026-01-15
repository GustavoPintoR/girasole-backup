<?php

namespace App\Traits;

use App\Models\CustomField;
use App\Models\CustomFieldValue;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasCustomFields
{
    /**
     * Boot the trait and register model events
     */
    protected static function bootHasCustomFields(): void
    {
        static::deleting(function ($model) {
            $model->customFieldValues()->delete();
        });
    }

    /**
     * Get all CustomFieldValues
     *
     * @return MorphMany
     */
    public function customFieldValues(): MorphMany
    {
        return $this->morphMany(CustomFieldValue::class, 'fieldable');
    }

    /**
     * Get all custom fields
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getCustomFields()
    {
        return CustomField::forModel(static::class);
    }

    /**
     * Get the value of a specific custom field
     *
     * @param string $key
     * @return string|null
     */
    public function getCustomFieldValue(string $key)
    {
        $field = CustomField::where('model_type', static::class)
            ->where('key', $key)
            ->first();

        if (!$field) {
            return null;
        }

        $value = $this->customFieldValues()
            ->where('custom_field_id', $field->id)
            ->first();

        if (!$value) {
            return null;
        }

        return $this->castValueForOutput($field->type, $value->value);
    }

    /**
     * Set (create/update) the value of a custom field
     *
     * @param string $key
     * @param string $value
     */
    public function setCustomFieldValue(string $key, $value): void
    {
        $field = CustomField::where('model_type', static::class)
            ->where('key', $key)
            ->firstOrFail();

        $stored = $this->normalizeValue($field->type, $value);

        $this->customFieldValues()->updateOrCreate(
            ['custom_field_id' => $field->id],
            ['value' => $stored]
        );
    }

    /**
     * Get all custom fields & values as an array
     *
     * @return array
     */
    public function getCustomFieldsArray(): array
    {
        $fields = $this->getCustomFields();
        $values = $this->customFieldValues()->with('customField')->get();

        return $fields->mapWithKeys(function ($field) use ($values) {
            $value = $values->firstWhere('custom_field_id', $field->id);
            return [$field->key => [
                'label' => $field->label,
                'value' => $value ? $this->castValueForOutput($field->type, $value->value) : null,
                'unit' => $field->unit,
                'type' => $field->type,
            ]];
        })->toArray();
    }

    /**
     * Get only the values of all custom fields
     *
     * @return array
     */
    public function getCustomFieldValuesOnly(): array
    {
        $fields = $this->getCustomFields();
        $values = $this->customFieldValues()->with('customField')->get();

        return $fields->mapWithKeys(function ($field) use ($values) {
            $value = $values->firstWhere('custom_field_id', $field->id);
            return [$field->key => $value ? $this->castValueForOutput($field->type, $value->value) : null];
        })->toArray();
    }

    /**
     * Normalize value for storage based on field type
     */
    protected function normalizeValue(string $type, $value)
    {
        if ($type === 'checkbox') {
            // store checkbox as '1' or '0'
            if (is_bool($value)) {
                return $value ? '1' : '0';
            }

            if (is_string($value)) {
                $lower = strtolower($value);
                if ($lower === 'true' || $lower === '1') {
                    return '1';
                }
                return '0';
            }

            return $value ? '1' : '0';
        }

        if ($type === 'number') {
            // store integers as string
            return is_null($value) || $value === '' ? null : (string) intval($value);
        }

        if ($type === 'float') {
            return is_null($value) || $value === '' ? null : (string) floatval($value);
        }

        // default: store as string
        return is_null($value) ? null : (string) $value;
    }

    /**
     * Cast stored value for output based on field type
     */
    protected function castValueForOutput(string $type, $value)
    {
        if ($value === null) {
            return null;
        }

        switch ($type) {
            case 'checkbox':
                if (is_bool($value)) {
                    return $value;
                }

                $val = strtolower((string) $value);
                return in_array($val, ['1', 'true', 't', 'yes', 'y'], true);

            case 'number':
                return is_numeric($value) ? intval($value) : null;

            case 'float':
                return is_numeric($value) ? floatval($value) : null;

            default:
                return $value;
        }
    }

    /**
     * Sync custom fields
     *
     * @param array $customFieldsData
     */
    public function syncCustomFields(array $customFieldsData): void
    {
        foreach ($customFieldsData as $key => $value) {
            $field = CustomField::where('model_type', static::class)
                ->where('key', $key)
                ->first();

            if (!$field) {
                continue;
            }

            if ($value !== null && $value !== '') {
                $this->setCustomFieldValue($key, $value);
            } else {
                $this->customFieldValues()
                    ->where('custom_field_id', $field->id)
                    ->delete();
            }
        }
    }
}
