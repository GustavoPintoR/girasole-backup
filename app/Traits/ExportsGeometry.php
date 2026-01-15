<?php

namespace App\Traits;

trait ExportsGeometry
{
    /**
     * Export group data as GeoJSON
     */
    public function exportAsGeojson(): string
    {
        if ($this->creation_method === 'units' && $this->cadastralUnits->count() > 0) {
            $geometries = [];
            foreach ($this->cadastralUnits as $unit) {
                if ($unit->geometry) {
                    $geometries[] = json_decode($unit->geometry_json, true);
                }
            }

            return json_encode([
                'type' => 'GeometryCollection',
                'geometries' => $geometries
            ], JSON_PRETTY_PRINT);
        }

        if ($this->boundary_geometry) {
            return $this->boundary_geometry_json;
        }

        return json_encode(['type' => 'GeometryCollection', 'geometries' => []]);
    }

    /**
     * Export group data as a JSON feature collection
     */
    public function exportAsFeatureCollection(): string
    {
        $features = [];

        if ($this->boundary_geometry) {
            $features[] = [
                'type' => 'Feature',
                'properties' => [
                    'name' => $this->name,
                    'description' => $this->description,
                    'total_area' => $this->total_area,
                    'units_count' => $this->units_count,
                    'creation_method' => $this->creation_method,
                    'type' => 'group_boundary'
                ],
                'geometry' => json_decode($this->boundary_geometry_json, true)
            ];
        }

        if ($this->creation_method === 'units') {
            foreach ($this->cadastralUnits as $unit) {
                if ($unit->geometry) {
                    $features[] = [
                        'type' => 'Feature',
                        'properties' => [
                            'sheet' => $unit->sheet,
                            'parcel' => $unit->parcel,
                            'cadastral_area' => $unit->cadastral_area,
                            'city' => $unit->city->name,
                            'type' => 'cadastral_unit'
                        ],
                        'geometry' => json_decode($unit->geometry_json, true)
                    ];
                }
            }
        }

        return json_encode([
            'type' => 'FeatureCollection',
            'features' => $features
        ], JSON_PRETTY_PRINT);
    }

    /**
     * Export group as MultiPolygon
     */
    public function exportAsMultiPolygon(): string
    {
        if ($this->boundary_geometry) {
            return $this->boundary_geometry_json;
        }
        return json_encode(['type' => 'MultiPolygon', 'coordinates' => []]);
    }
}
