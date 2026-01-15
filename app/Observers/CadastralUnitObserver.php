<?php

namespace App\Observers;

use App\Models\CadastralUnit;
use App\Models\CadastralGroup;

class CadastralUnitObserver
{
    /**
     * Handle the CadastralUnit "updated" event.
     */
    public function updated(CadastralUnit $cadastralUnit): void
    {
        if ($cadastralUnit->isDirty('cadastral_group_id')) {
            $oldGroupId = $cadastralUnit->getOriginal('cadastral_group_id');
            $newGroupId = $cadastralUnit->cadastral_group_id;

            if ($oldGroupId) {
                $oldGroup = CadastralGroup::find($oldGroupId);
                if ($oldGroup) {
                    $oldGroup->updateGeometryFromUnits();
                }
            }

            if ($newGroupId) {
                $newGroup = CadastralGroup::find($newGroupId);
                if ($newGroup) {
                    $newGroup->updateGeometryFromUnits();
                }
            }
        }

        if ($cadastralUnit->isDirty('geometry') && $cadastralUnit->cadastral_group_id) {
            $cadastralUnit->cadastralGroup->updateGeometryFromUnits();
        }
    }

    /**
     * Handle the CadastralUnit "deleted" event.
     */
    public function deleted(CadastralUnit $cadastralUnit): void
    {
        if ($cadastralUnit->cadastral_group_id) {
            $group = CadastralGroup::find($cadastralUnit->cadastral_group_id);
            if ($group) {
                $group->updateGeometryFromUnits();
            }
        }
    }
}
