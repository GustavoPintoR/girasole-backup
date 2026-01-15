<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SensorTypeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'operations' => $this->whenLoaded('sensorOperations', fn() => $this->sensorOperations->map(fn($operation) => [
                'id' => $operation->id,
                'name' => $operation->name,
                'description' => $operation->description,
            ])->toArray()),
        ];
    }
}
