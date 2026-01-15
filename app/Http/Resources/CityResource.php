<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CityResource extends JsonResource
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
            'cadastral_code' => $this->cadastral_code,
            'province_id' => $this->province_id,
            'region_id' => $this->region_id,
            'province' => new ProvinceResource($this->whenLoaded('province')),
            'region' => new RegionResource($this->whenLoaded('region')),
            'postalCodes' => PostalCodeResource::collection($this->whenLoaded('postalCodes')),
        ];
    }
}
