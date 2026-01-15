<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Enums\UserRole;
use App\Helpers\PlanHelper;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Lab404\Impersonate\Models\Impersonate;
use Laravel\Cashier\Billable;
use Laravel\Cashier\Subscription;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use Billable, CanResetPassword, HasApiTokens, HasFactory, HasRoles, Impersonate, Notifiable, SoftDeletes;

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = ['full_name', 'billing_info'];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'active',
        'terms_and_conditions_id',
        'accepted_at',
        'mobile_number',
        'main_company_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'accepted_at' => 'datetime:Y-m-d'
        ];
    }

    /**
     * Check if the user is an admin.
     */
    public function isAdmin(): bool
    {
        return $this->is_admin;
    }

    /**
     * Scope a query to only include admin users.
     */
    #[Scope]
    protected function admins(Builder $query): void
    {
        $query->whereHas('roles', function ($q) {
            $q->where('name', UserRole::ADMINISTRATOR->value);
        });
    }

    /**
     * Check if user has accepted most recent terms and conditions.
     */
    public function hasAcceptedTerms(): bool
    {
        return $this->isSuperAdmin() || $this->termsAndConditions()->where('is_active', true)->exists();
    }

    public function getBillingInfoAttribute()
    {
        if ($this->mainCompany && $this->mainCompany->billingInfo) {
            return $this->mainCompany->billingInfo;
        }

        // Check owned companies first
        $ownedCompany = $this->companyOwner->first();
        if ($ownedCompany && $ownedCompany->billingInfo) {
            return $ownedCompany->billingInfo;
        }

        // Check member companies
        $memberCompany = $this->companies->first();
        if ($memberCompany && $memberCompany->billingInfo) {
            return $memberCompany->billingInfo;
        }

        return null;
    }

    public function mainCompany(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'main_company_id');
    }

    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }
    

    public function companyOwner(): HasMany
    {
        return $this->hasMany(Company::class, 'owner_id');
    }

    /**
     * @return BelongsToMany
     */
    public function companies(): BelongsToMany
    {
        return $this->belongsToMany(Company::class);
    }

    /**
     * @return HasMany
     */
    public function cadastralGroup(): HasMany
    {
        return $this->hasMany(CadastralGroup::class);
    }

     /**
     * @return HasMany
     */
    public function cadastralUnits(): HasMany
    {
        return $this->hasMany(CadastralUnit::class);
    }

    /**
     * Get the user's full name.
     */
    protected function fullName(): Attribute
    {
        return Attribute::make(
            get: fn ($value, array $attributes) => trim(
                ucfirst($attributes['last_name'] ?? '').' '.ucfirst($attributes['first_name'] ?? '')
            ),
        );
    }

    /**
     * Get
     */
    public function subscriptionType(): Attribute
    {
        $subscription = $this->subscriptions->where('stripe_status', 'active')->first();
        return Attribute::make(
            get: fn () => ucfirst($subscription->type)
        );
    }

    public function termsAndConditions(): BelongsTo
    {
        return $this->belongsTo(TermsAndConditions::class);
    }

    public function company(): BelongsToMany
    {
        return $this->belongsToMany(Company::class);
    }

    public function sensors(): HasMany
    {
        return $this->hasMany(Sensor::class, 'owner_id');
    }

    //  public function subscription(): HasMany
    // {
    //     return $this->hasMany(Subscription::class, 'user_id')->where('stripe_status', 'active');
    // }

    /**
     * By default, all users can impersonate anyone
     * this example limits it so only admins can
     * impersonate other users
     */
    public function canImpersonate(): bool
    {
        return $this->isSuperAdmin();
    }

    /**
     * Asks if the user is a super admin.
     */
    public function isSuperAdmin(): bool
    {
        return $this->hasRole(UserRole::ADMINISTRATOR->value);
    }

    /**
     * Asks if the user is an integration user
     */
    public function isIntegration(): bool
    {
        return $this->hasRole(UserRole::INTEGRATION->value);
    }

    /**
     * Asks if the user is a technician user
     */
    public function isTechnician(): bool
    {
        return $this->hasRole(UserRole::TECHNICIAN->value);
    }

    /**
     * Determine if the user has a valid paid subscription or is exempt.
     */
    public function hasValidPaidSubscription(): bool
    {
        if (PlanHelper::checkSubscription($this)){
            return true;
        }

        return false;
    }

    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification(#[\SensitiveParameter] $token)
    {
        $this->notify(new ResetPasswordNotification($token));
    }
}
