<?php

namespace App\Transformers;

use App\Models\CadastralUnit;
use Illuminate\Support\Collection;

class CadastralUnitMapTransformer
{
    public function transformCollection(Collection $units): Collection
    {
        return $units->map(fn($unit) => $this->transform($unit));
    }

    public function transform(CadastralUnit $unit): array
    {
        return [
            'id' => $unit->id,
            'sheet' => $unit->sheet,
            'parcel' => $unit->parcel,
            'cadastral_area' => $unit->cadastral_area,
            'city' => [
                'name' => $unit->city->name,
                'province' => $unit->city->province->name,
                'province_code' => $unit->city->province->code,
                'region' => $unit->city->region->name,
            ],
            'geometry_json' => $unit->geometry_json,
            'centroid_json' => $unit->centroid_json,
        ];
    }
}
