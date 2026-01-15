<?php

namespace App\Services;

use App\Models\ForecastLog;
use App\Models\ForecastSetup;
use App\Repositories\ForecastRepository;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;

class ForecastService
{
    public function __construct(public ForecastRepository $repository) {}

    public function paginatedSetups(Request $request, int $perPage, string $sortBy, string $sortOrder, ?string $search): array
    {
        $paginator = $this->repository->paginateSetups($request, $perPage, $sortBy, $sortOrder, $search);

        $data = $paginator->getCollection()->map(function ($group) {
            return $this->mapGroup($group);
        })->values();

        return [
            'data' => $data,
            'pagination' => $this->paginationMeta($paginator),
        ];
    }

    public function weeklyStats(): array
    {
        $week = (int) now()->isoWeek();
        $year = (int) now()->year;

        $startOfWeek = Carbon::now()->setISODate($year, $week)->startOfWeek();
        $endOfWeek = (clone $startOfWeek)->endOfWeek();

        $logs = ForecastLog::query()
            ->whereBetween('created_at', [$startOfWeek->startOfDay(), $endOfWeek->endOfDay()])
            ->get(['status', 'created_at']);

        $daysOfWeek = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        $stats = [
            'days' => $daysOfWeek,
            'rows' => [
                'done' => array_fill_keys($daysOfWeek, 0),
                'errors' => array_fill_keys($daysOfWeek, 0),
                'total' => array_fill_keys($daysOfWeek, 0),
            ],
        ];

        $dowMap = [0 => 'sunday', 1 => 'monday', 2 => 'tuesday', 3 => 'wednesday', 4 => 'thursday', 5 => 'friday', 6 => 'saturday'];

        foreach ($logs as $log) {
            $dayIndex = $log->created_at?->dayOfWeek;
            $dayKey = $dayIndex !== null ? ($dowMap[$dayIndex] ?? null) : null;
            if (! $dayKey) {
                continue;
            }
            $stats['rows']['total'][$dayKey]++;
            if ($log->status === 'success' || $log->status === 'ok') {
                $stats['rows']['done'][$dayKey]++;
            } elseif ($log->status === 'failed') {
                $stats['rows']['errors'][$dayKey]++;
            }
        }

        return [
            'meta' => [
                'week' => $week,
                'year' => $year,
                'start' => $startOfWeek->toDateString(),
                'end' => $endOfWeek->toDateString(),
            ],
            'stats' => $stats,
        ];
    }

    public function remainingCalls(): array
    {
        $calls = config('girasole.weather_forecast.calls');
        $setups = ForecastSetup::all();

        return [
            'monday' => max(0, $calls['monday'] - $setups->where('monday', true)->count()),
            'tuesday' => max(0, $calls['tuesday'] - $setups->where('tuesday', true)->count()),
            'wednesday' => max(0, $calls['wednesday'] - $setups->where('wednesday', true)->count()),
            'thursday' => max(0, $calls['thursday'] - $setups->where('thursday', true)->count()),
            'friday' => max(0, $calls['friday'] - $setups->where('friday', true)->count()),
            'saturday' => max(0, $calls['saturday'] - $setups->where('saturday', true)->count()),
            'sunday' => max(0, $calls['sunday'] - $setups->where('sunday', true)->count()),
        ];
    }

    public function mapGroup(object $group): array
    {
        $ownerName = null;
        if (! empty($group->user_first_name) || ! empty($group->user_last_name)) {
            $ownerName = trim(($group->user_first_name ?? '').' '.($group->user_last_name ?? '')) ?: null;
        }

        return [
            'id' => $group->id,
            'setup_id' => $group->setup_id ?? null,
            'field' => [
                'id' => $group->id,
                'name' => $group->name,
                'owner_name' => $ownerName,
                'company_name' => $group->company_name ?? null,
            ],
            'days' => [
                'monday' => (bool) $group->monday,
                'tuesday' => (bool) $group->tuesday,
                'wednesday' => (bool) $group->wednesday,
                'thursday' => (bool) $group->thursday,
                'friday' => (bool) $group->friday,
                'saturday' => (bool) $group->saturday,
                'sunday' => (bool) $group->sunday,
            ],
            'retention' => $group->retention,
        ];
    }

    /**
     * Build pagination meta array.
     */
    protected function paginationMeta(LengthAwarePaginator $paginator): array
    {
        return [
            'pageIndex' => $paginator->currentPage() - 1,
            'pageSize' => $paginator->perPage(),
            'pageCount' => $paginator->lastPage(),
            'total' => $paginator->total(),
            'from' => $paginator->firstItem() ?? 0,
            'to' => $paginator->lastItem() ?? 0,
        ];
    }
}
