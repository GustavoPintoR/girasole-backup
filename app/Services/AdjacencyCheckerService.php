<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AdjacencyCheckerService
{
    /**
     * Check if cadastral units are adjacent
     */
    public function areUnitsAdjacent(Collection $units): bool
    {
        if ($units->count() === 1) {
            return true;
        }

        if ($units->count() < 2) {
            return false;
        }

        $unitsWithGeometry = $units->filter(fn($unit) => $unit->geometry !== null);

        if ($unitsWithGeometry->count() === 1) {
            return true;
        }

        if ($unitsWithGeometry->count() < 2) {
            return false;
        }

        $unitIds = $unitsWithGeometry->pluck('id')->toArray();
        $adjacencyPairs = $this->getAdjacencyPairs($unitIds);
        $connections = $this->buildConnectionGraph($unitIds, $adjacencyPairs);

        return $this->isConnectedGraph($connections, $unitIds);
    }

    /**
     * Get adjacency pairs for the given unit IDs
     */
    protected function getAdjacencyPairs(array $unitIds): Collection
    {
        return DB::table('cadastral_units as a')
            ->join('cadastral_units as b', function ($join) {
                $join->on('a.id', '<>', 'b.id')
                    ->whereRaw('(ST_Touches(a.geometry, b.geometry) OR ST_Intersects(a.geometry, b.geometry))');
            })
            ->whereIn('a.id', $unitIds)
            ->whereIn('b.id', $unitIds)
            ->select('a.id as unit1_id', 'b.id as unit2_id')
            ->get();
    }

    /**
     * Build a connection graph from adjacency pairs
     */
    protected function buildConnectionGraph(array $unitIds, Collection $adjacencyPairs): array
    {
        $connections = [];
        foreach ($unitIds as $id) {
            $connections[$id] = [];
        }

        foreach ($adjacencyPairs as $row) {
            $connections[$row->unit1_id][] = $row->unit2_id;
            $connections[$row->unit2_id][] = $row->unit1_id;
        }

        return $connections;
    }

    /**
     * Check if a graph is fully connected using BFS
     */
    protected function isConnectedGraph(array $connections, array $nodes): bool
    {
        if (empty($nodes)) {
            return true;
        }

        $visited = [];
        $queue = [$nodes[0]];
        $visited[$nodes[0]] = true;

        while (!empty($queue)) {
            $current = array_shift($queue);

            foreach ($connections[$current] ?? [] as $neighbor) {
                if (!isset($visited[$neighbor])) {
                    $visited[$neighbor] = true;
                    $queue[] = $neighbor;
                }
            }
        }

        return count($visited) === count($nodes);
    }
}
