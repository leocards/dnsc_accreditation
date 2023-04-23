<?php

namespace App\Http\Controllers;

use App\Http\Traits\InstrumentsTrait;
use App\Models\Accreditation;
use App\Models\Institute;
use App\Models\Instrument;
use App\Models\Program;
use App\Models\AreaAssign;
use App\Models\AreaSelfAccreditor;
use App\Models\DocumentCurrentVersion;
use App\Models\TaskAssign;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ProgramController extends Controller
{
    use InstrumentsTrait;

    public function index()
    {
        $isntitutes = Institute::all('id', 'abbreviation');
        
        if(Auth::user()->auth == 4)
            $programs = Program::where('id', Auth::user()->programId)->get(['id', 'abbreviation', 'program_name', 'instituteId', 'bot', 'established']);
        else if(Auth::user()->auth == 3)
            $programs = Program::where('instituteId', Auth::user()->instituteId)->get(['id', 'abbreviation', 'program_name', 'instituteId', 'bot', 'established']);
        else
            $programs = Program::all('id', 'abbreviation', 'program_name', 'instituteId', 'bot', 'established');

        return Inertia::render('Program', ['institutes'=>$isntitutes, 'programs'=>$programs]);
    }

    public function programLevel($id, $lvl = null, $current = null)
    {
        $currentLevel = Accreditation::leftjoin('instruments as i', 'i.id', '=', 'accreditations.instrumentId')
        ->where('accreditations.programId', $id)->where('accreditations.instrumentId', $lvl)
        ->first([
            'i.id',
            'i.title',
            'accreditations.id as accredId',
        ]);

        return Inertia::render('ProgramLevel', [
            'crumbs'=> $current ? $this->getCrumbs($current) : [],
            'currentlvl'=> $currentLevel,
            'instruments'=> !$lvl ? $this->getProgramLevel($id) : $this->getProgramInstruments($current, $currentLevel),
            'program'=>Program::find($id)->makeHidden(['created_at', 'updated_at', 'bot', 'established']),
            'current'=> $current? Instrument::find($current)->makeHidden(['created_at', 'updated_at']): null,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'abbreviation'=>['required', 'unique:programs,abbreviation'],
            'program_name'=>['required', 'unique:programs,program_name'],
            'institute'=>['required'],
        ]);

        try{

            DB::transaction(function () use ($request) {
                Program::create([
                    'abbreviation'=>$request->abbreviation, 
                    'program_name'=>$request->program_name, 
                    'instituteId'=>$request->institute, 
                    'bot'=>$request->bot_resolution, 
                    'established'=>$request->established
                ]);
            });

            return back()->with('success', 'Created successfully');

        }catch(\Throwable $th){
            return back()->withErrors('Failed to create');
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'abbreviation'=>['required', 'unique:programs,abbreviation,'.$id],
            'program_name'=>['required', 'unique:programs,program_name,'.$id],
            'institute'=>['required'],
        ]);

        $exist = Program::where('id', '!=', $id)->where('abbreviation', $request->abbreviation)->first();
        if($exist)
            $request->validate([
                'abbreviation' => ['unique:programs,abbreviation']
            ]);
        else
            $exist = Program::where('id', '!=', $id)->where('program_name', $request->abbreviation)->first();
            if($exist)
                $request->validate([
                    'program_name' => ['unique:programs,program_name']
                ]);

        try{

            DB::transaction(function () use ($request, $id) {
                Program::where('id', $id)->update([
                    'abbreviation'=>$request->abbreviation, 
                    'program_name'=>$request->program_name, 
                    'instituteId'=>$request->institute, 
                    'bot'=>$request->bot_resolution, 
                    'established'=>$request->established
                ]);
            });

            return back()->with('success', 'Updated successfully');

        }catch(\Throwable $th){
            return back()->withErrors('Failed to update');
        }
    }

    public function searchProgram($search = null)
    {
        $result = Program::where('abbreviation', 'LIKE', "%{$search}%")->orWhere('program_name', 'LIKE', "%{$search}%")->get();

        $result ? $result->makeHidden(['created_at', 'updated_at']) : $result;

        return response()->json(['programs'=>$result]);
    }

    public function getPrograms()
    {
        return response()->json(['programs' => Program::all('id', 'abbreviation', 'instituteId')]);
    }

    public function assignTaskForce(Request $request, $accredlvl, $area)
    {
        try {

            DB::transaction(function () use ($request, $accredlvl, $area) {
                $chairperson = AreaAssign::create([
                    'userId' => $request->tfcId, 
                    'areaId' => $area, 
                    'parent' => null, 
                    'role' => 'tfc', 
                    'levelId' => $accredlvl
                ]);

                foreach ($request->members as $member) {
                    AreaAssign::create([
                        'userId' => $member['id'], 
                        'areaId' => $area, 
                        'parent' => $chairperson['id'], 
                        'role' => 'member', 
                        'levelId' => $accredlvl
                    ]);
                }
            });

            return back()->with('success', 'Assigned successfully');
        }catch(\Throwable $th) {
            return back()->with('error', 'Failed to assign');
        }
    }

    public function addMemberTaskForce(Request $request, $accredlvl, $area)
    {
        try {

            DB::transaction(function () use ($request, $accredlvl, $area) {
                foreach ($request->members as $member) {
                    AreaAssign::create([
                        'userId' => $member['id'], 
                        'areaId' => $area, 
                        'parent' => $request->tfcId, 
                        'role' => 'member', 
                        'levelId' => $accredlvl
                    ]);
                }
            });

            return back()->with('success', 'New member added');
        }catch(\Throwable $th) {
            return back()->with('error', 'Failed to add member');
        }
    }

    public function changeMemberTaskForce(Request $request, $accredlvl, $area)
    {
        try {

            DB::transaction(function () use ($request, $accredlvl, $area) {
                TaskAssign::where('userId', $request['change']['old']['userId'])
                    ->where('accredId', $accredlvl)
                    ->where('areaId', $area)
                    ->update(['userId' => $request['change']['new']['id']]);

                $change = AreaAssign::find($request['change']['old']['id']);
                $change->userId = $request['change']['new']['id'];
                $change->save();
            });

            return back()->with('success', 'Updated successfully');
        }catch(\Throwable $th) {
            return back()->with('error', $th->getMessage());
        }
    }

    public function changeTaskForceChair(Request $request, $accredlvl, $area)
    {
        try {

            DB::transaction(function () use ($request, $accredlvl, $area) {
                $change = AreaAssign::find($request['change']['old']['id']);
                $change->userId = $request['change']['new']['id'];
                $change->save();
            });

            return back()->with('success', 'Updated successfully');
        }catch(\Throwable $th) {
            return back()->with('error', $th->getMessage());
        }
    }

    public function getAreaAssigned($accredlvl, $area)
    {
        try {

            $assigned = AreaAssign::where('levelId', $accredlvl)->where('areaId', $area)
            ->get(['id', 'userId', 'parent']);

            $mapped = $assigned->map(function ($user) {
                $collection = collect([]);
                $assignUser = $user->userAssigned()->first(['first_name', 'last_name', 'id']);
                $collection['id'] = $user->id;
                $collection['name'] = $assignUser->first_name.' '.$assignUser->last_name;
                $collection['userId'] = $assignUser->id;
                $collection['parent'] = $user->parent;
                return $collection;
            });

            return response()->json(['assigned' => $mapped]);

        }catch(\Throwable $th){
            return response()->json(['assigned' => 'Something went wrong']);
        }
    }

    public function assignSelfAccreditor(Request $request)
    {
        DB::beginTransaction();
        try {

            foreach ($request->accreditors as $user) {
                AreaSelfAccreditor::create([
                    'userId'=>$user['id'],
                    'instrumentId'=>$request->area,
                    'accredlvl'=>$request->accredlvl,
                ]);
            }

            DB::commit();
            return back()->with('success', 'Assigned successfully');

        } catch (\Throwable $th) {
            DB::rollBack();
            return back()->with('error', $th->getMessage());
        }
    }

    public function getAssignSelfAccreditor(Request $request)
    {
        return response()->json([
            'assigned' => AreaSelfAccreditor::where('instrumentId', $request->area)
                ->where('accredlvl', $request->accredlvl)
                ->get(['userId', 'id'])
                ->map(function ($assign) {
                    $user = $assign;
                    $user->user = $assign->selfAccreditor->getFullName();
                    return $user->only('userId', 'id', 'user');
                })
        ]);
    }

    public function removeMember(Request $request)
    {
        try {

            DB::transaction(function () use ($request) {
                AreaAssign::find($request->id)->delete();
                
                TaskAssign::where('areaId', $request->area)
                    ->where('accredId', $request->accredlvl)
                    ->where('userId', $request->user)
                    ->delete();
            });

            return response()->json(200);
        } catch (\Throwable $th) {
            return response()->json(400);
        }
    }

    function getProgramLevel($program)
    {
        return Accreditation::leftjoin('instruments as i', 'i.id', '=', 'accreditations.instrumentId')
                ->where('accreditations.programId', $program)
                ->get([
                    'i.id',
                    'i.title',
                    'i.category',
                    'accreditations.id as accredId',
                ]);
    }
    function getProgramInstruments($inst, $accred)
    {

        $isArea = Instrument::find($inst);
        
        if(!$isArea)
            abort(404);

        if($isArea->category == 'area')
        {
            return $this->getInstruments($inst, $accred->accredId)->map(function ($value) use ($accred) {
                $value->documents = $this->getDocuments($value->id, $accred->accredId);
                return $value;
            });
        }
        
        $instrument = Instrument::where('parent', $inst)->whereNull('action')
                ->orderBy('title', 'asc')
                ->get([
                    'id',
                    'title',
                    'category',
                    'description'
                ]);
                

        return $instrument;
    }
    
    function getCrumbs($parent)
    {
        Global $crumbs;
        $crumbs = collect([]);

        function getParents($id)
        {
            global $crumbs;

            $instrument = Instrument::where('id', $id)->whereNotNull('parent')->first(['id', 'parent', 'title']);

            $instrument ? $crumbs->push($instrument) : '';
            $instrument ? getParents($instrument->parent) : '';
            
        }
        getParents($parent);

        return $crumbs;
    }

}
