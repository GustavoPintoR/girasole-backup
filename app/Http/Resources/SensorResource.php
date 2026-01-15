<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SensorResource extends JsonResource
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
            'type' => $this->type,
            'serial_number' => $this->serial_number,
            'urn' => $this->urn,
            'iccid' => $this->iccid,
            'transmission_module_identification' => $this->transmission_module_identification,
            'description' => $this->description,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'firmware' => $this->firmware,
            'metadata' => $this->metadata,
            'sensor_type' => new SensorTypeResource($this->whenLoaded('sensorType')),
            'fields' => new CadastralGroupResource($this->whenLoaded('cadastralGroup')),
            'owner' => new UserResource($this->whenLoaded('owner')),
            'created_at' => $this->created_at,
        ];
    }
}
