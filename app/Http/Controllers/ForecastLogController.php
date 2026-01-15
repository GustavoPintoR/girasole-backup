<?php

namespace App\Http\Controllers;

use App\Models\ForecastLog;
use Illuminate\Http\Request;;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class ForecastLogController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', ForecastLog::class);

        $validated = $request->validate([
            'per_page' => 'nullable|integer|min:1|max:100',
            'sort_by' => 'nullable|string|in:ran_at,status',
            'sort_order' => 'nullable|string|in:asc,desc',
            'search' => 'nullable|string|max:255',
        ]);

        $perPage = $validated['per_page'] ?? config('app.pagination_number');
        $sortBy = $validated['sort_by'] ?? 'ran_at';
        $sortOrder = $validated['sort_order'] ?? 'desc';
        $search = $validated['search'] ?? null;

        $searchableColumns = ['ran_at', 'status', 'field.name'];

        $query = ForecastLog::query()
            ->leftJoin('cadastral_groups', 'forecast_logs.field_id', '=', 'cadastral_groups.id')
            ->with('field')
            ->select('forecast_logs.*');

        if ($search) {
            $like = '%'.strtolower($search).'%';

            $query->where(function ($q) use ($like) {
                $q->where('forecast_logs.ran_at', 'ilike', $like)
                    ->orWhere('forecast_logs.status', 'ilike', $like)
                    ->orWhere('cadastral_groups.name', 'ilike', $like);
            });
        }

        $forecastLogs = $query
            ->orderBy($sortBy, $sortOrder)
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('forecast-logs/Index', [
            'forecastLogs' => $forecastLogs,
            'sorting' => compact('sortBy', 'sortOrder'),
            'filtering' => [
                'search' => $search,
                'searchableColumns' => $searchableColumns,
            ],
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(ForecastLog $forecastLog): Response
    {
        Gate::authorize('view', $forecastLog);

        $forecastLog->load('field');

        return Inertia::render('forecast-logs/Show', ['forecastLog' => $forecastLog]);
    }
}
