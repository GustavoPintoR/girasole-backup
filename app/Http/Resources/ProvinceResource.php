<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProvinceResource extends JsonResource
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
            'name' => $this->name,
            'code' => $this->code,
            'region_id' => $this->region_id,
            'region' => new RegionResource($this->whenLoaded('region')),
            'cities' => CityResource::collection($this->whenLoaded('cities')),
        ];
    }
}
