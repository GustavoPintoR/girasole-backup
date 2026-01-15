<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;
use App\Models\Company;
use Illuminate\Http\Request;
use App\Models\CadastralUnit;
use App\Models\CadastralGroup;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\RedirectResponse;

class CadastralUnitController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $cadastralUnits = CadastralUnit::with('cadastralGroup')->get();
        return response()->json(['cadastralUnits' => $cadastralUnits]);
    }

    /**
     * Get available units for a specific user
     */
    public function getAvailableUnits(Request $request): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'current_user_id' => 'required|exists:users,id',
            'user_id' => 'nullable|exists:users,id',
            'company_id' => 'nullable|exists:companies,id',
            'exclude_group_id' => 'nullable|exists:cadastral_groups,id',
        ]);

        $currentUser = User::find($validated['current_user_id']);

        $query = CadastralUnit::query()
            ->whereNotNull('geometry')
            ->with(['city.province.region']);

        // If company is specified
        if (!empty($validated['company_id'])) {
            // Verify user has access to this company
            if (
                !$currentUser->isSuperAdmin() &&
                !$currentUser->companies()->where('companies.id', $validated['company_id'])->exists()
            ) {
                return response()->json(['error' => 'Unauthorized company access'], 403);
            }

            $company = Company::with('users')->find($validated['company_id']);
            $companyUserIds = $company->users->pluck('id')->toArray();
            $query->whereIn('user_id', $companyUserIds);
        }
        // If user_id is specified
        elseif (!empty($validated['user_id'])) {
            $query->where('user_id', $validated['user_id']);
        }
        // Default to current user
        else {
            $query->where('user_id', $currentUser->id);
        }

        // Handle group exclusion
        if (!empty($validated['exclude_group_id'])) {
            $query->where(function ($q) use ($validated) {
                $q->whereNull('cadastral_group_id')
                    ->orWhere('cadastral_group_id', $validated['exclude_group_id']);
            });
        } else {
            $query->whereNull('cadastral_group_id');
        }

        $units = $query->get()->map(function ($unit) use ($validated) {
            return [
                'id' => $unit->id,
                'sheet' => $unit->sheet,
                'parcel' => $unit->parcel,
                'cadastral_area' => $unit->cadastral_area,
                'selected' => !empty($validated['exclude_group_id']) &&
                    $unit->cadastral_group_id == $validated['exclude_group_id'],
                'city' => [
                    'name' => $unit->city->name,
                    'province' => $unit->city->province->name,
                    'province_code' => $unit->city->province->code ?? null,
                    'region' => $unit->city->region->name,
                ],
                'geometry_json' => $unit->geometry_json,
                'centroid_json' => $unit->centroid_json,
            ];
        });

        return response()->json([
            'units' => $units,
            'count' => $units->count(),
        ]);
    }
}
