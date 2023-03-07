<?php

namespace App\Http\Controllers;

use App\Http\Traits\AreaAssignTrait;
use App\Http\Traits\InstrumentsTrait;
use App\Models\Accreditation;
use App\Models\AreaAssign;
use App\Models\Instrument;
use App\Models\Program;
use App\Models\Progress;
use App\Models\TaskAssign;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class TaskHomeController extends Controller
{
    use AreaAssignTrait;
    use InstrumentsTrait;

    public function index(Request $request)
    {
        if($this->areaAssigned()->count() > 0)
        {
            if(!$request->program)
            {
                return redirect()->route('task_home_indx', [
                    'program'=> $this->getAssignedPrograms()[0]->id, 
                    'level'=> [...$this->programLevels($this->getAssignedPrograms()[0]->id)][0]->accredId
                ]);
            }else{
                if(!$request->level)
                {
                    return redirect()->route('task_home_indx', [
                        'program'=> $request->program, 
                        'level'=> [...$this->programLevels($request->program)][0]->accredId
                    ]);
                }
                return Inertia::render('TaskHome', [
                    'program'=> $this->getAssignedPrograms()->filter(function ($prog) use ($request) {
                            return $prog->id != $request->program;
                        }),
                    'currentProgram'=> $this->getAssignedPrograms()
                        ->where('id', $request->program)
                        ->first(),
                    'programLevels'=> $this->programLevels($request->program),
                    'currentLevel'=> $this->currentLevel($request->level),
                    'areas'=> $this->areaAssigned()
                        ->where('levelId', $request->level)
                        ->map(function ($area) {
                            $inst = $area->instruments
                                ->where('id', $area->areaId)
                                ->first([
                                    'id',
                                    'title',
                                    'description'
                                ]);
                            $inst->role = $area->role == 'tfc' ? true : false ;
                            $inst->assigned = $this->getAssigned($area->areaId, $area->levelId);
                            $inst->progress = Progress::where('instrumentId', $area->areaId)
                                ->where('accredlvlId', $area->levelId)
                                ->first('progress');
                            return $inst;
                        })
                ]);
            }
        }else{
            return Inertia::render('TaskHome');
        }
    }

    public function getInstrumentLocation($inst, $area, $accredlvl)
    {
        return redirect()
                ->route('task_page_indx', ['accredId'=>$accredlvl, 'areaId'=>$area, 'inst' => $inst]);
    }

    public function getParameterProgress(Request $request)
    {
        try {

            return response()->json(
                Progress::where('parent', $request->area)
                    ->where('accredlvlId', $request->accredlvl)
                    ->get(['progress', 'instrumentId'])
                    ->map(function ($progress) {
                        $dd = $progress->instrument->only('title');
                        $dd['progress'] = $progress->progress;
                        $dd['instrumentId'] = $progress->instrumentId;
                        
                        return $dd;
                    })
            );

        } catch (\Throwable $th) {
            return response()->json('Error');
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
                return $assigned->assignedAccreditation;
            })->map(function ($program) {
                    return $program->taggedPrograms;
            })
            ->unique('id')
            ->map(function ($value) {
                $prog = new Program();
                $prog->id = $value->id;
                $prog->program = $value->abbreviation;
                return $prog;
            });
    }

    function programLevels($program)
    {
        $accred = $this->areaAssigned()
            ->unique('levelId')
            ->map(function ($assigned) use ($program) {
        //return 
                $ass = $assigned->assignedAccreditation
                    ->where('programId', $program)
                    ->where('id', $assigned->levelId)
                    ->first();
                //dd($ass);
                return $ass?$ass->makeHidden(['created_at', 'updated_at', 'survey', 'status', 'restrict']):null;
            })
            ->filter(function ($val) {
                return $val;
            })
            ->unique('id');
        //dd($accred);
        return $accred->map(function ($lvl) {
            $level = $lvl->getLevelInstrument->where('id', $lvl->instrumentId)
                ->first(['id', 'title']);

            $level->accredId = $lvl->id;
            //dd($level);
            return $level;
        });
    }

    function currentLevel($accredLvl)
    {
        return Accreditation::where('id', $accredLvl)->first(['id']);
    }

    function programLevelAreas($program)
    {
        $accred = $this->areaAssigned()
            ->map(function ($assigned) use ($program) {
                return $assigned->assignedAccreditation
                    ->where('programId', $program)
                    ->first()
                    ->makeHidden(['created_at', 'updated_at', 'survey', 'status', 'restrict']);
            });

        return $accred->map(function ($lvl) {
            $level = $lvl->getLevelInstrument->where('id', $lvl->instrumentId)->first();
            $level->accredId = $lvl->id;
            return $level;
        });
    }

    function getAssigned($area, $accredlvl)
    {
        return TaskAssign::where('areaId', $area)
            ->where('accredId', $accredlvl)
            ->get()
            ->map(function ($inst) use ($accredlvl) {
                $assigned = $inst->getInstrument->only(['id', 'title', 'description', 'attachment', 'category']);
                $assigned['children'] = $assigned;
                $assigned['due'] = $inst->due;
                $assigned['status'] = $inst->complete;

                return $assigned;
            });
    }

    function getParent($id)
    {
        Global $root;
        $root = collect([]);

        function get_parent($id)
        {
            global $root;

            $parent = Instrument::find($id);
            if($parent->parent)
            {
                $root->push($parent->id);
                get_parent($parent->parent);
            }

            return $root;
        }
        $inst = Instrument::find($id);

        if($inst){
            get_parent($inst->parent);
            $root->push($inst->id);
            return $root;
        }
        else 
            return back()->with('error', 'Invalid request');
    }
}
