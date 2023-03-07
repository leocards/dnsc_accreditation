<?php
namespace App\Http\Traits;

use App\Models\AreaAssign;
use App\Models\Progress;

trait AreaAssignTrait {

    public function areaProgress($area, $accredlvl)
    {
        return Progress::where('parent', $area)
            ->where('accredlvlId', $accredlvl)
            ->get();
    }

    public function getAssignedTF($area, $accredlvl)
    {
        return AreaAssign::where('areaId', $area)
                ->where('levelId', $accredlvl)
                ->get()
                ->map(function ($assign) {
                    $area_assign = $assign
                                    ->userAssigned
                                    ->only('id', 'first_name', 'last_name');

                    $area_assign['role'] = $assign->role;
                    $area_assign['area'] = $assign->areaId;
                    $area_assign['accredlvl'] = $assign->levelId;

                    return $area_assign;
                });
    }
}
