<?php

namespace App\Http\Controllers;

use App\Http\Traits\AreaAssignTrait;
use App\Http\Traits\InstrumentsTrait;
use App\Models\Accreditation;
use App\Models\AreaAssign;
use App\Models\DocumentCurrentVersion;
use App\Models\Instrument;
use App\Models\Progress;
use App\Models\TaskAssign;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class TaskPageController extends Controller
{
    use AreaAssignTrait;
    use InstrumentsTrait;

    public function index($accredId, $areaId, Request $request)
    {
        try{

            if($request->inst)
            {
                $query = Instrument::find($request->inst);
            }

            return Inertia::render('TaskPage', [
                'home' => Accreditation::find($accredId)->only('id', 'programId'),
                'instruments' => $request->inst ? 
                    $this->buildTreeInstruments($query->parent, $accredId, false, $query->id):
                    $this->buildTreeInstruments($areaId, $accredId),
                'area' => $this->areaAssigned()
                        ->where('areaId', $areaId)
                        ->first()
                        ->instruments
                        ->where('id', $areaId)
                        ->first(['id', 'title', 'description']),
                'areaRole' => ($this->areaAssigned()
                        ->where('areaId', $areaId)
                        ->where('levelId', $accredId)
                        ->first()->role == 'tfc'),
                'due' => Accreditation::find($accredId)->only('date_self_survey', 'date_actual_survey')
            ]);
        }catch(\Throwable $th){
            return dd($th->getMessage())/* abort(404) */;
        }
    }

    public function getMembers($area, $accredlvl)
    {
        try {
            return response()->json([
                    'members'=>$this->getAssignedTF($area, $accredlvl)
                ]);
        }catch (\Throwable $th){
            return response()->json(['error'=>$th->getMessage()], 400);
        }
    }

    public function assignMember(Request $request)
    {
        $request->validate([
            'due' => 'required|date'
        ]);

        try {

            DB::transaction(function () use ($request) {
                TaskAssign::create([
                    'due' => $request->due,
                    'areaId' => $request->area,
                    'userId' => $request->userId,
                    'instrumentId' => $request->instrument,
                    'accredId' => $request->accredlvl,
                ]);
            });

            return back()->with('success', 'Successfully assigned');

        }catch(\Throwable $th){
            return back()->with('error', $th->getMessage());
        }
    }

    public function getassignedMember($instrument, $accredlvl)
    {
        return response()->json([
            'assigned' => TaskAssign::where('instrumentId', $instrument)
                            ->where('accredId', $accredlvl)
                            ->get()
                            ->map(function ($user) {

                                $assigned = $user->assignedMember->only('id', 'first_name', 'last_name');
                                $assigned['due'] = $user->due;
                                $assigned['assignId'] = $user->id;
                                return $assigned;
                            })
        ]);
    }

    public function setAsComplete(Request $request)
    {
        DB::beginTransaction();
        try {
            $completeInst = null;

            if(!$request->isComplete || $request->isComplete == null){
                $completeInst = DB::transaction(function () use ($request) {
                    $progress = Progress::find($request->id);
                    $progress->isComplete = true;
                    $progress->progress = 100;
                    $progress->save();
                    return $progress;
                });

                foreach($this->getInstruments($completeInst->instrumentId) as $child)
                {
                    DB::transaction(function () use ($request, $child) {
                        $progressChild = Progress::where('instrumentId', $child->id)
                            ->where('accredlvlId', $request->accredlvl)
                            ->first();
        
                        $progressChild->isComplete = true;
                        $progressChild->progress = 100;
                        $progressChild->save();
                    });
                }
            }else{
                $completeInst = DB::transaction(function () use ($request) {
                    $progress = Progress::find($request->id);
                    $progress->isComplete = null;
                    $progress->progress = null;
                    $progress->save();
                    return $progress;
                });

                foreach($this->getInstruments($completeInst->instrumentId) as $child)
                {
                    DB::transaction(function () use ($request, $child) {
                        $progressChild = Progress::where('instrumentId', $child->id)
                            ->where('accredlvlId', $request->accredlvl)
                            ->first();
        
                        $progressChild->isComplete = null;
                        $progressChild->progress = null;
                        $progressChild->save();
                    });
                }
            }

            $this->getParents($completeInst->parent, false, function ($parent) use ($request) {
                $parentProgress = Progress::where('parent', $parent->id)
                    ->where('accredlvlId', $request->accredlvl)
                    ->get(['id', 'isComplete', 'progress', 'instrumentId']);//

                $percentage = (($parentProgress->sum('progress') / ($parentProgress->count() * 100)) * 100);
                
                $progress = $percentage == 0 ? null : $percentage;
                
                Progress::where('instrumentId', $parent->id)
                    ->where('accredlvlId', $request->accredlvl)
                    ->update(['progress'=>$progress, 'isComplete'=>($progress == 100 ? true : null)]);
            });

            DB::commit();
            return back()->with('success', 'success');
        } catch (\Throwable $th) {

            DB::rollBack();
            return back()->with('error', 'error');
        }
    }

    public function updateAssign(Request $request)
    {
        try {

            DB::transaction(function () use ($request) {
                $update = TaskAssign::find($request->id);
                $update->due = $request->due;
                $update->save();
            });

            return back()->with('success', 'Updated successfully');
        } catch (\Throwable $th) {
            return back()->with('error', 'Failed to update');
        }
    }

    function areaAssigned()
    {
        return AreaAssign::where('userId', Auth::id())
            ->get();
    }

    function getAssignedPrograms()
    {
        return $this->areaAssigned()
            ->map(function ($assigned) {
                return $assigned->assignedAccreditation->taggedPrograms
                    ->first(['id', 'abbreviation as program']);
            });
    }
}
