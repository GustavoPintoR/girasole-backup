<?php

namespace App\Rules;

use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class UserBelongsToCompany implements ValidationRule
{
    public function __construct(protected User $user) {}

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! $this->user->companyOwner()->where('id', $value)->exists() &&
            ! $this->user->companies()->where('companies.id', $value)->exists()) {
            $fail('The selected main company is invalid.');
        }
    }
}
