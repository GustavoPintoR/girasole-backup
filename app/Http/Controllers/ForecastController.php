<?php

namespace App\Http\Controllers;

use App\Http\Requests\ForecastSetup\UpdateRequest;
use App\Models\ForecastSetup;
use App\Services\ForecastService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\DB;

// use function Illuminate\Log\log;

class ForecastController extends Controller
{
    public function __construct(
        public ForecastService $forecastService,
    ) {}

    /**
     * Display the forecast setup table.
     */
    public function index(Request $request): Response
    {
        Gate::authorize('read_forecast_setup');

        $validated = $request->validate([
            'per_page' => 'nullable|integer|min:1',
            'sort_by' => ['nullable', 'in:field_name,field.name,user_name,field.owner_name,company_name,field.company_name,monday,tuesday,wednesday,thursday,friday,saturday,sunday,retention'],
            'sort_order' => 'nullable|in:asc,desc',
            'search' => 'nullable|string|max:255',
        ]);

        $perPage = $validated['per_page'] ?? 20;
        $sortBy = $validated['sort_by'] ?? 'field_name';
        $sortOrder = $validated['sort_order'] ?? 'asc';
        $search = $validated['search'] ?? null;

        $weekly = $this->forecastService->weeklyStats();
        $setups = $this->forecastService->paginatedSetups($request, $perPage, $sortBy, $sortOrder, $search);

        return Inertia::render('forecasts/Index', [
            'setups' => [
                'data' => $setups['data'],
                'pagination' => $setups['pagination'],
            ],
            'sorting' => [
                'sortBy' => $sortBy,
                'sortOrder' => $sortOrder,
            ],
            'filtering' => [
                'search' => $search,
                'searchableColumns' => ['field_name', 'user_name', 'company_name'],
            ],
            'remainingCalls' => $this->forecastService->remainingCalls(),
            'logsWeek' => $weekly['meta'],
            'forecastLogsStats' => $weekly['stats'],
        ]);
    }

    /**
     * Update forecast setups.
     */
    public function update(UpdateRequest $request)
    {
        Gate::authorize('update_forecast_setup');

        $changes = $request->validated()['changes'];
        $dayColumns = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];

        $rows = [];

        foreach ($changes as $change) {
            $row = [
                'field_id'  => $change['id'],
                'retention' => array_key_exists('retention', $change)
                    ? (int) $change['retention']
                    : null,
            ];

            foreach ($dayColumns as $col) {
                $row[$col] = $change['days'][$col] ?? false;
            }

            $rows[] = $row;
        }

        DB::transaction(function () use ($rows, $dayColumns) {
            // DB::enableQueryLog();
            ForecastSetup::upsert(
                $rows,
                ['field_id'],
                array_merge($dayColumns, ['retention'])
            );
            // log('ForecastSetup upsert queries: ', DB::getQueryLog());
            // DB::disableQueryLog();
        });

        return back()->with('success', __('ui.updated_successfully', [
            'resource' => __('ui.weather_forecast_setup')
        ]));
    }
}
