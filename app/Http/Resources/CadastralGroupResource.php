<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CadastralGroupResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'name' => $this->name,
            'description' => $this->description,
            'total_area' => $this->total_area,
            'units_count' => $this->units_count,
            'color' => $this->color,
            'creation_method' => $this->creation_method,
            'original_geojson' => $this->original_geojson ? json_decode($this->original_geojson, true) : null,
            'boundary_geometry_json' => $this->boundary_geometry_json ? json_decode($this->boundary_geometry_json, true) : null,
            'centroid_json' => $this->centroid_json ? json_decode($this->centroid_json, true) : null,
            'sensors' => SensorResource::collection($this->whenLoaded('sensors')),
            'cadastral_units' => CadastralUnitResource::collection($this->whenLoaded('cadastralUnits')),
            'recent_forecast_logs' => ForecastLogResource::collection($this->whenLoaded('forecastLogs')),
        ];
    }
}
