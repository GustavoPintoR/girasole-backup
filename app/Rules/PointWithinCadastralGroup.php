<?php

namespace App\Rules;

use Closure;
use App\Models\CadastralGroup;
use Illuminate\Contracts\Validation\ValidationRule;

class PointWithinCadastralGroup implements ValidationRule
{
    /**
     * Indicates whether the rule should be implicit.
     *
     * @var bool
     */
    public $implicit = true;

    protected ?CadastralGroup $cadastralGroup;
    protected string $latitudeField;
    protected string $longitudeField;

    /**
     * Create a new rule instance.
     *
     * @param CadastralGroup|int|null $cadastralGroup The cadastral group or its ID
     * @param string $latitudeField The name of the latitude field in the request
     * @param string $longitudeField The name of the longitude field in the request
     */
    public function __construct($cadastralGroup, string $latitudeField = 'latitude', string $longitudeField = 'longitude')
    {
        if (is_numeric($cadastralGroup)) {
            $this->cadastralGroup = CadastralGroup::find($cadastralGroup);
        } elseif ($cadastralGroup instanceof CadastralGroup) {
            $this->cadastralGroup = $cadastralGroup;
        } else {
            $this->cadastralGroup = null;
        }

        $this->latitudeField = $latitudeField;
        $this->longitudeField = $longitudeField;
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $data = request()->all();

        $latitude = $data[$this->latitudeField] ?? null;
        $longitude = $data[$this->longitudeField] ?? null;

        if (!$this->cadastralGroup) {
            $fail('The cadastral group does not exist.');
            return;
        }

        if (!is_numeric($latitude) || !is_numeric($longitude)) {
            $fail('Invalid coordinates provided.');
            return;
        }

        if (!$this->cadastralGroup->pointIsWithinGroup((float)$latitude, (float)$longitude)) {
            $fail("The location must be within the boundaries of {$this->cadastralGroup->name}.");
        }
    }
}
