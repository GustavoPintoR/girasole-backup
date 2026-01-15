<?php

namespace App\Services;

use App\Models\CadastralGroup;
use App\Models\Company;
use App\Models\ForecastLog;
use App\Models\User;

class ForecastLogService
{
    public function __construct(public ForecastDataParser $parser) {}

    public function getForecastData(string $cadastralGroupId, string $user): array
    {
        $user = User::findOrFail($user);
        $cadastralGroup = CadastralGroup::findOrFail($cadastralGroupId);

        $rawData = ForecastLog::where('field_id', $cadastralGroup->id)
            ->where('status', 'success')
            ->whereNotNull('ran_at')
            ->orderByDesc('ran_at')
            ->limit(2)
            ->get()
            ->flatMap(fn (ForecastLog $log) => ($log->data ?? [])['data'] ?? [])
            ->all();

        $data = $this->parser->parse($rawData);

        $meta = '';
        if (! empty($data['periodStart']) && ! empty($data['periodEnd'])) {
            $meta = __('ui.analysis_period', ['start' => $data['periodStart']->format('d/m/Y'), 'end' => $data['periodEnd']->format('d/m/Y')]);
        }

        // Determine if company name should be shown
        $shouldShowCompany = false;
        if ($user) {
            if ($user->isSuperAdmin()) {
                $shouldShowCompany = Company::count() > 1;
            } else {
                $userCompanyCount = $user->companies()->count() + $user->companyOwner()->count();
                $shouldShowCompany = $userCompanyCount > 1;
            }
        }

        $title = $cadastralGroup->name;
        if ($shouldShowCompany && $cadastralGroup->company) {
            $title .= " ({$cadastralGroup->company->name})";
        }

        $data['header'] = [
            'title' => $title,
            'meta' => $meta,
            'date' => '',
        ];

        return $data;
    }
}
