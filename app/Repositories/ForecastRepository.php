<?php

namespace App\Repositories;

use App\Models\CadastralGroup;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

class ForecastRepository
{
    /**
     * Paginate forecast setups joined with related entities.
     */
    public function paginateSetups(Request $request, int $perPage, string $sortBy, string $sortOrder, ?string $search): LengthAwarePaginator
    {
        $query = CadastralGroup::query()
            ->leftJoin('forecast_setups', 'cadastral_groups.id', '=', 'forecast_setups.field_id')
            ->leftJoin('users', 'cadastral_groups.user_id', '=', 'users.id')
            ->leftJoin('companies', 'cadastral_groups.company_id', '=', 'companies.id')
            ->select(
                'cadastral_groups.*',
                'forecast_setups.id as setup_id',
                'forecast_setups.monday',
                'forecast_setups.tuesday',
                'forecast_setups.wednesday',
                'forecast_setups.thursday',
                'forecast_setups.friday',
                'forecast_setups.saturday',
                'forecast_setups.sunday',
                'forecast_setups.retention',
                'users.first_name as user_first_name',
                'users.last_name as user_last_name',
                'companies.name as company_name'
            );

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('cadastral_groups.name', 'ilike', "%{$search}%")
                    ->orWhere('users.first_name', 'ilike', "%{$search}%")
                    ->orWhere('users.last_name', 'ilike', "%{$search}%")
                    ->orWhere('companies.name', 'ilike', "%{$search}%");
            });
        }

        $sortMap = [
            'field_name' => 'cadastral_groups.name',
            'field.name' => 'cadastral_groups.name',
            'user_name' => 'users.last_name',
            'field.owner_name' => 'users.last_name',
            'company_name' => 'companies.name',
            'field.company_name' => 'companies.name',
            'monday' => 'forecast_setups.monday',
            'tuesday' => 'forecast_setups.tuesday',
            'wednesday' => 'forecast_setups.wednesday',
            'thursday' => 'forecast_setups.thursday',
            'friday' => 'forecast_setups.friday',
            'saturday' => 'forecast_setups.saturday',
            'sunday' => 'forecast_setups.sunday',
            'retention' => 'forecast_setups.retention',
        ];

        if (in_array($sortBy, ['user_name', 'field.owner_name'], true)) {
            $query->orderBy('users.last_name', $sortOrder)
                ->orderBy('users.first_name', $sortOrder);
        } else {
            $sortColumn = $sortMap[$sortBy] ?? 'companies.name';
            $query->orderBy($sortColumn, $sortOrder);
        }

        return $query->paginate($perPage)->appends($request->query());
    }
}
