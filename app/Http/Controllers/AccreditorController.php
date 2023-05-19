<?php

namespace App\Http\Controllers;

use App\Http\Traits\InstrumentsTrait;
use App\Models\Accreditation;
use App\Models\AreaSelfAccreditor;
use App\Models\Instrument;
use App\Models\Program;
use App\Models\Progress;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class AccreditorController extends Controller
{
    use InstrumentsTrait;
    
    public function verifyAccreditor()
    {
        if(Auth::user()->designation == 7)
            return redirect()->route('self_accreditor');
        else
            return redirect()->route('accreditor');
    }

    public function selfAccreditor(Request $request)
    {
        try {
            throw_if(Auth::user()->designation != 7, AuthorizationException::class, '401');
            
            if($request->survey)
            {
                $checkIfOnSurvey = Accreditation::find($request->survey);
                if(!$checkIfOnSurvey->survey)
                    return redirect()->route('accreditor_verify');
            }

            return Inertia::render('Accreditor', [
                'accreditor'=>false,

                'programs'=>$this->accreditationProgram(2),

                'currentProgram'=>$request->program?Program::where('id', $request->program)
                                    ->get(['id', 'abbreviation', 'program_name']):null,

                'areas'=>$request->level?$this->getAreas($request->survey):null,

                'currentArea'=>$request->area?Instrument::find($request->area)
                                ->only('id', 'title', 'description', 'category'):null,

                'instruments'=>$request->area?$this->buildTreeInstruments($request->area, $request->survey, true):null,

                'accred'=>Accreditation::where('instrumentId', $request->level)
                            ->where('programId', $request->program)
                            ->first(['id', 'instrumentId', 'programId']),
                'rate' => true
            ]);
            
        } catch (\Throwable $th) {
            if($th->getMessage() == '401')
                abort(401);

            abort(404);
        }
    }

    public function accreditor(Request $request)
    {
        try {
            throw_if(Auth::user()->designation != 8, AuthorizationException::class, '401');

            return Inertia::render('Accreditor', [
                'accreditor'=>true,
                'programs'=>$this->accreditationProgram(),
                'currentProgram'=>$request->program?Program::where('id', $request->program)
                                        ->get(['id', 'abbreviation', 'program_name']):null,
                'areas'=>$request->level?
                        Instrument::where('parent', $request->level)->get(['id', 'title', 'description'])
                    :null,

                'currentArea'=>$request->area?Instrument::find($request->area)
                                ->only('id', 'title', 'description', 'category'):null,

                'instruments'=>$request->area?$this->buildTreeInstruments($request->area, $request->survey, true):null,

                'accred'=>Accreditation::where('instrumentId', $request->level)
                            ->where('programId', $request->program)
                            ->first(['id', 'instrumentId', 'programId']),
                'rate' => false
            ]);
            
        } catch (\Throwable $th) {
            if($th->getMessage() == '401')
                abort(401);

            abort(404);
        }
        
    }

    public function rateInstrument(Request $request)
    {
        $request->validate([
            'rate'=>'required'
        ]);

        if(is_numeric($request->rate))
            $request->validate([
                'rate'=>'numeric|max:5|min:0'
            ]);

        if(Auth::user()->designation != 7)
            return back()->with('Unauthorized request');

        DB::beginTransaction();
        try {
            $rateInst = null;
            $rateInst = DB::transaction(function () use ($request) {
                $rate = Progress::find($request->id);
                $rate->rate = trim($request->rate);
                $rate->save();
                return $rate;
            });

            $this->getParents($rateInst->parent, false, function ($parent) use ($rateInst) {
                $childreRates = Progress::where('parent', $parent->id)
                    ->where('accredlvlId', $rateInst->accredlvlId)
                    ->whereNull('exclude_rate')
                    ->get(['id', 'rate'])
                    ->map(function ($val) {
                        $rate = is_numeric($val->rate)?$val->rate:0;

                        return collect([
                            'id' => $val->id,
                            'rate' => $rate
                        ]);
                    });

                $rate = floor(($childreRates->sum('rate') / $childreRates->count()) * 100)/100;

                $pr = Progress::where('instrumentId', $parent->id)
                    ->where('accredlvlId', $rateInst->accredlvlId)
                    ->first();
                $pr->rate =$rate;
                $pr->save();
            });

            DB::commit();
            return back()->with('success', 'Rate successful');

        } catch (\Throwable $th) {

            DB::rollBack();
            return back()->with('error', $th->getMessage());
        }
    }

    public function reCalculate(Request $request)
    {
        try{
            function getParentsCalc($inst, $res=false, $callBack=null)
            {
                $instruments = collect([]);

                $parent = Instrument::find($inst);

                $parent?$parent->makeHidden(['created_at', 'updated_at']):'';

                if($parent)
                    while ($parent->category != 'lvl') {
                        $instruments->push($parent);
                        $callBack?$callBack($parent):'';
                        $parent = Instrument::find($parent->parent);//category
                        $parent?$parent->makeHidden(['created_at', 'updated_at']):'';
                    }

                if($res)
                    return $instruments;
            }

            getParentsCalc($request->id, false, function ($parent) use ($request) {
                $childreRates = Progress::where('parent', $parent->id)
                    ->where('accredlvlId', $request->accredlvlId)
                    ->whereNull('exclude_rate')
                    ->get(['id', 'rate'])
                    ->map(function ($val) {
                        $rate = is_numeric($val->rate)?trim($val->rate):0;
    
                        return collect([
                            'id' => $val->id,
                            'rate' => $rate
                        ]);
                    });
    
                $rate = floor(($childreRates->sum('rate') / $childreRates->count()) * 100)/100;
                
    
                $pr = Progress::where('instrumentId', $parent->id)
                    ->where('accredlvlId', $request->accredlvlId)
                    ->first();
                $pr->rate = $rate;
                $pr->save();
            });

            return response()->json('success', 200);
        } catch (\Throwable $th) {  
            return response()->json('error', 400);
        }
    }

    function accreditationProgram($isAccreditor = 1)
    {
        return Accreditation::where('survey', $isAccreditor)->get(['id', 'instrumentId', 'programId'])
            ->map(function ($accred) {
                $accred->program = $accred->taggedPrograms->only('abbreviation', 'program_name');
                $accred->level = $accred->getLevelInstrument->only('title');
                return $accred->only(
                    'id', 'instrumentId', 'programId', 'program', 'level'
                );
            });
    }

    function getAreas($level) 
    {
        return AreaSelfAccreditor::where('userId', Auth::id())
                ->where('accredlvl', $level)
                ->get(['id', 'userId', 'instrumentId'])
                ->map(function ($value) {
                    return $value->area->only(['id', 'title', 'description']);
                });
    }

}
