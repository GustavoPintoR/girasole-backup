<?php

namespace App\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class ForecastDataParser
{
    public function parse(array $rawData): array
    {
        $series = $this->flattenData($rawData);

        if ($series->isEmpty()) {
            return $this->emptyDashboard();
        }

        $waterBalance = $this->buildWaterBalance($series);
        $growthRisks = $this->buildGrowthRisks($series);
        $evolutionTimeline = $this->buildEvolutionTimeline($series);
        $weatherList = $this->buildWeatherList($series);
        $soilList = $this->buildSoilList($series);

        return [
            'waterBalance' => $waterBalance,
            'growthRisks' => $growthRisks,
            'evolutionTimeline' => $evolutionTimeline,
            'weatherList' => $weatherList,
            'soilList' => $soilList,
            'hasWaterBalanceData' => $this->hasWaterBalanceData($waterBalance),
            'hasGrowthRisksData' => $this->hasGrowthRisksData($growthRisks),
            'hasEvolutionTimelineData' => ! empty($evolutionTimeline),
            'hasWeatherListData' => ! empty($weatherList),
            'hasSoilListData' => ! empty($soilList),
            'hasAnyData' => $this->hasAnyValidData($waterBalance, $growthRisks, $evolutionTimeline, $weatherList, $soilList),
            'periodStart' => $series->first()['timestamp'] ?? null,
            'periodEnd' => $series->last()['timestamp'] ?? null,
        ];
    }

    private function flattenData(array $data): Collection
    {
        $grouped = [];

        foreach ($data as $metric) {
            $param = $metric['parameter'];
            // first coordinate set
            $dates = $metric['coordinates'][0]['dates'] ?? [];

            foreach ($dates as $point) {
                $date = $point['date'];
                if (! isset($grouped[$date])) {
                    $grouped[$date] = ['date' => $date, 'timestamp' => Carbon::parse($date)];
                }
                $grouped[$date][$param] = $point['value'];
            }
        }

        return collect($grouped)->sortBy('date')->values();
    }

    private function buildWaterBalance(Collection $series): array
    {
        // Sum over the available period
        $rain = $series->sum('precip_1h:mm');
        $evapo = $series->sum('evapotranspiration_1h:mm');

        // Expected Deficit: Last hour value
        $lastPoint = $series->last();
        $deficit = $lastPoint['soil_moisture_deficit:mm'] ?? 0;

        return [
            'rainTotal' => round($rain, 1),
            'evapotranspiration' => round($evapo, 1),
            'deficit' => round($deficit, 1),
            'deficitColor' => $this->getDeficitColor($deficit),
        ];
    }

    private function getDeficitColor(float $deficit): string
    {
        if ($deficit <= 0) {
            return 'text-blue-600';
        }
        if ($deficit <= 30) {
            return 'text-green-500';
        }
        if ($deficit <= 80) {
            return 'text-amber-500';
        }

        return 'text-red-500';
    }

    private function buildGrowthRisks(Collection $series): array
    {
        // GDD: Last - First
        $firstGdd = $series->first()['growing_degree_days_accumulated:gdd'] ?? 0;
        $lastGdd = $series->last()['growing_degree_days_accumulated:gdd'] ?? 0;
        $gdd = max(0, $lastGdd - $firstGdd);

        // Wetness: Sum of hours where leaf_wetness:idx = 1
        $wetnessHours = $series->filter(fn($item) => ($item['leaf_wetness:idx'] ?? 0) == 1)->count();

        // Wind: 95th percentile
        $windSpeeds = $series->pluck('wind_speed_2m:kmh')->sort()->values();
        $count = $windSpeeds->count();
        // k = ceil(0.95 * N). For 0-based index: k - 1.
        $index = $count > 0 ? (int) ceil(0.95 * $count) - 1 : 0;
        $maxWind = $windSpeeds[$index] ?? 0;

        return [
            'gdd' => round($gdd),
            'gddColor' => $this->getGddColor($gdd),
            'wetnessRisk' => $this->getWetnessLabel($wetnessHours),
            'wetnessColor' => $this->getWetnessColor($wetnessHours),
            'maxGusts' => round($maxWind),
            'maxGustsColor' => $this->getWindColor($maxWind),
        ];
    }

    private function getGddColor(float $gdd): string
    {
        if ($gdd <= 50) {
            return 'text-slate-500';
        }
        if ($gdd <= 120) {
            return 'text-green-500';
        }

        return 'text-amber-500';
    }

    private function getWetnessLabel(int $hours): string
    {
        if ($hours <= 1) {
            return 'low';
        }
        if ($hours <= 9) {
            return 'medium';
        }

        return 'high';
    }

    private function getWetnessColor(int $hours): string
    {
        if ($hours <= 1) {
            return 'text-green-500';
        }
        if ($hours <= 9) {
            return 'text-yellow-500';
        }

        return 'text-red-500';
    }

    private function getWindColor(float $speed): string
    {
        if ($speed <= 15) {
            return 'text-green-500';
        }
        if ($speed <= 30) {
            return 'text-amber-500';
        }

        return 'text-red-500';
    }

    private function buildEvolutionTimeline(Collection $series): array
    {
        $today = Carbon::today();

        // Filter for specific times, e.g., 06:00 and 12:00
        return $series->filter(function ($item) use ($today) {
            $ts = $item['timestamp'];

            // Filter out past days
            if ($ts->lt($today)) {
                return false;
            }

            return in_array($ts->hour, [6, 12]);
        })->map(function ($item) {
            $ts = $item['timestamp'];
            $isMorning = $ts->hour === 6;

            // Zone 1: Label
            $hourString = str_pad($ts->hour, 2, '0', STR_PAD_LEFT) . ':00';
            $label = $ts->isToday()
                ? strtoupper(__('ui.today') . ' ') . $hourString
                : strtoupper($ts->translatedFormat('D') . ' ' . $hourString);

            // Add formatted date
            $dateString = $ts->format('d/m/y');

            // Zone 2: Icon, Rad, Rain
            $precip = $item['precip_1h:mm'] ?? 0;
            $rad = $item['global_rad:W'] ?? 0;
            $prob = $item['prob_precip_1h:p'] ?? 0;

            // Icon Logic
            if ($precip > 0.2) {
                $conditionIcon = '🌧️';
                $conditionLabel = 'Pioggia';
                $conditionColor = 'text-blue-500';
            } else {
                if ($rad <= 150) {
                    $conditionIcon = '☁️';
                    $conditionLabel = 'Coperto';
                    $conditionColor = 'text-gray-500';
                } elseif ($rad <= 400) {
                    $conditionIcon = '⛅';
                    $conditionLabel = 'Parz. Nuvoloso';
                    $conditionColor = 'text-yellow-600';
                } else {
                    $conditionIcon = '☀️';
                    $conditionLabel = 'Sereno';
                    $conditionColor = 'text-amber-500';
                }
            }

            // Radiation String
            $radString = $rad < 50 ? '-' : round($rad) . 'W';

            // Rain/Prob String
            $probPct = $prob;

            if ($precip < 0.1 && $probPct < 20) {
                $rainString = '-';
            } else {
                $rainString = round($precip, 1) . 'mm/' . $probPct . '%';
            }

            // Zone 3: Temp, RH, DP
            $temp = round($item['t_mean_2m_1h:C'] ?? 0);

            $rhVal = $item['relative_humidity_mean_2m_1h:p'] ?? 0;
            $rh = ($rhVal <= 1 && $rhVal > 0) ? round($rhVal * 100) : round($rhVal);

            $dp = round($item['dew_point_2m:C'] ?? 0);

            return [
                'label' => $label,
                'dateString' => $dateString,
                'isMorning' => $isMorning,
                'conditionIcon' => $conditionIcon,
                'conditionLabel' => $conditionLabel,
                'conditionColor' => $conditionColor,
                'radString' => $radString,
                'rainString' => $rainString,
                'temp' => $temp,
                'rh' => $rh,
                'dp' => $dp,
            ];
        })->values()->toArray();
    }

    private function buildWeatherList(Collection $series): array
    {
        $today = Carbon::today();

        // Group by day
        return $series->groupBy(function ($item) {
            return $item['timestamp']->format('Y-m-d');
        })->filter(function ($dayItems, $dateStr) use ($today) {
            return Carbon::parse($dateStr)->gte($today);
        })->map(function ($dayItems, $dateStr) {
            $date = Carbon::parse($dateStr);
            $avgVpd = $dayItems->avg('vapor_pressure_deficit_mean_2m_1h:hPa');
            $minTemp = $dayItems->min('t_mean_2m_1h:C');
            $maxTemp = $dayItems->max('t_mean_2m_1h:C');

            // Rain
            // precip_24h:mm -> Value at 23:00 (last of the day)
            $rainMm = $dayItems->last()['precip_24h:mm'] ?? 0;
            // prob_precip_24h:p -> Max of 24 values
            $rainProb = $dayItems->max('prob_precip_24h:p') ?? 0;

            return [
                'day' => strtoupper($date->translatedFormat('D')),
                'isToday' => $date->isToday(),
                'minTemp' => round($minTemp ?? 0),
                'maxTemp' => round($maxTemp ?? 0),
                'rainMm' => round($rainMm, 1),
                'rainProb' => round($rainProb),
                'vpd' => round($avgVpd ?? 0, 1),
                'vpdColor' => $this->getVpdColor($avgVpd ? round($avgVpd, 1) : 0),
            ];
        })->values()->toArray();
    }

    private function getVpdColor(float $vpd): string
    {
        if ($vpd <= 0.4) {
            return '#4DA3FF';
        }
        if ($vpd <= 1.6) {
            return '#2ECC71';
        }

        return '#E74C3C';
    }

    private function buildSoilList(Collection $series): array
    {
        $today = Carbon::today();

        return $series->groupBy(function ($item) {
            return $item['timestamp']->format('Y-m-d');
        })->filter(function ($dayItems, $dateStr) use ($today) {
            return Carbon::parse($dateStr)->gte($today);
        })->map(function ($dayItems, $dateStr) {
            $date = Carbon::parse($dateStr);

            $avgTemp = $dayItems->avg('t_-15cm:C');
            $surfaceMoisture = $dayItems->avg('volumetric_soil_water_-15cm:m3m3');
            $deepMoisture = $dayItems->avg('volumetric_soil_water_-50cm:m3m3');

            $smi15 = $dayItems->avg('soil_moisture_index_-15cm:idx') ?? 0;
            $smi50 = $dayItems->avg('soil_moisture_index_-50cm:idx') ?? 0;

            // Quality Index Bar (SMI 15cm)
            // clamp(SMI15_D, 0, 1.2) / 1.2
            $qualityIndexPct = (min(max($smi15, 0), 1.2) / 1.2) * 100;

            $qualityColor = '#E53935'; // red
            if ($smi15 > 1.0) {
                $qualityColor = '#1E88E5'; // blue
            } elseif ($smi15 > 0.3) {
                $qualityColor = '#43A047'; // green
            }

            // Black Line (SMI 50cm)
            $deepMarkerPos = (min(max($smi50, 0), 1.2) / 1.2) * 100;

            return [
                'day' => strtoupper($date->translatedFormat('D')),
                'isToday' => $date->isToday(),
                'surfaceMoisture' => round($surfaceMoisture ?? 0, 2),
                'deepMoisture' => round($deepMoisture ?? 0, 2),
                'qualityIndex' => round($qualityIndexPct),
                'qualityColor' => $qualityColor,
                'deepMarkerPos' => round($deepMarkerPos),
                'temp' => round($avgTemp ?? 0),
            ];
        })->values()->toArray();
    }

    private function hasWaterBalanceData(array $waterBalance): bool
    {
        return $waterBalance['rainTotal'] > 0 ||
            $waterBalance['evapotranspiration'] > 0 ||
            abs($waterBalance['deficit']) > 0;
    }

    private function hasGrowthRisksData(array $growthRisks): bool
    {
        return $growthRisks['gdd'] > 0 ||
            $growthRisks['maxGusts'] > 0 ||
            ($growthRisks['wetnessRisk'] !== '-' && ! empty($growthRisks['wetnessRisk']));
    }

    private function hasAnyValidData(
        array $waterBalance,
        array $growthRisks,
        array $evolutionTimeline,
        array $weatherList,
        array $soilList
    ): bool {
        return $this->hasWaterBalanceData($waterBalance) ||
            $this->hasGrowthRisksData($growthRisks) ||
            ! empty($evolutionTimeline) ||
            ! empty($weatherList) ||
            ! empty($soilList);
    }

    private function emptyDashboard(): array
    {
        return [
            'header' => ['title' => 'No Data', 'meta' => '', 'date' => ''],
            'waterBalance' => ['rainTotal' => 0, 'evapotranspiration' => 0, 'deficit' => 0, 'deficitColor' => 'text-blue-600'],
            'growthRisks' => [
                'gdd' => 0,
                'gddColor' => 'text-slate-500',
                'wetnessRisk' => '-',
                'wetnessColor' => 'text-slate-500',
                'maxGusts' => 0,
                'maxGustsColor' => 'text-green-500',
            ],
            'evolutionTimeline' => [],
            'weatherList' => [],
            'soilList' => [],
            'hasWaterBalanceData' => false,
            'hasGrowthRisksData' => false,
            'hasEvolutionTimelineData' => false,
            'hasWeatherListData' => false,
            'hasSoilListData' => false,
            'hasAnyData' => false,
            'periodStart' => null,
            'periodEnd' => null,
        ];
    }
}
