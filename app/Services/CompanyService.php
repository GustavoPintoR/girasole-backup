<?php

namespace App\Services;

use App\Interfaces\CompanyInterface;
use App\Enums\UserRole;
use App\Exceptions\CompanyNotFoundException;
use App\Helpers\CompanyHelper;
use App\Models\Company;
use App\Models\User;
use Illuminate\Contracts\Database\Eloquent\Builder;

class CompanyService implements CompanyInterface
{
    public ?User $user;

    public function __construct()
    {
        $this->user = CompanyHelper::getCurrentUser();
    }

    /**
     * @throws CompanyNotFoundException
     */
    public function addGlobalCompanyScope(): void
    {
        $classes = CompanyHelper::getCompanyableClasses();
        $user = $this->user;

        if (!$user) {
            return;
        }

        $companyIds = $user->companies()->pluck('id')->toArray();

        $classes->each(function ($modelClass) use ($user, $companyIds) {
            $modelClass::addGlobalScope('companyScope', function (Builder $builder) use ($user, $companyIds) {
                if ($user->hasAnyRole([UserRole::ADMINISTRATOR->value, UserRole::INTEGRATION->value])) {
                    return;
                }

                if (!empty($companyIds)) {
                    $builder->whereIn('company_id', $companyIds);
                } else {
                    $builder->whereNull('company_id');
                }
            });
        });
    }

    /**
     * @return void
     */
    public function addGlobalCompanyIndexScope(): void
    {
        $this?->user?->loadMissing('companies');

        Company::addGlobalScope('companyIndex', function (Builder $builder) {
            $user = $this->user;

            if (!$user) {
                return;
            }

            $userRoles = $user->getRoleNames()->toArray();
            if (array_intersect([UserRole::ADMINISTRATOR->value, UserRole::INTEGRATION->value], $userRoles)) {
                return;
            }

            $companyIds = $user->companies->pluck('id');

            $builder->whereIn('id', $companyIds);
        });
    }
}
