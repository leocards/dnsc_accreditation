<?php

namespace App\Http\Controllers;

use App\Models\Accreditation;
use App\Models\AreaSelfAccreditor;
use App\Models\Instrument;
use App\Models\Progress;
use App\Models\SelfSurvey;
use App\Models\SelfSurveyRate;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class AccreditationController extends Controller
{
    public function index()
    {
        return Inertia::render('Accreditation', [
            'accreditation' => $this->getAccreditations(),
            'actual_survey' => Accreditation::where('survey', 1)
                                ->get(['id', 'instrumentId', 'programId', 'restrict', 'survey', 'status', 'verified', 'date_self_survey', 'date_actual_survey'])
                                ->map(function ($accred) {
                                    $program = $accred->taggedPrograms->only('abbreviation');
                                    $accred->program = $program['abbreviation'];
                                    $accred->title = $accred->getLevelInstrument->only('title')['title'];
                                    return $accred->only('id', 'instrumentId', 'programId', 'restrict', 'survey', 'program', 'title', 'programId', 'date_self_survey', 'date_actual_survey', 'selfSurvey', 'status', 'verified');
                                }),
            'self_survey' => Accreditation::where('survey', 2)
                                ->get(['id', 'instrumentId', 'programId', 'restrict', 'survey', 'status', 'verified', 'date_self_survey', 'date_actual_survey'])
                                ->map(function ($accred) {
                                    $program = $accred->taggedPrograms->only('abbreviation');
                                    $accred->program = $program['abbreviation'];
                                    $accred->title = $accred->getLevelInstrument->only('title')['title'];
                                    return $accred->only('id', 'instrumentId', 'programId', 'restrict', 'survey', 'program', 'title', 'programId', 'date_self_survey', 'date_actual_survey', 'selfSurvey', 'status', 'verified');
                                }),
        ]);
    }
 
    public function programTagged($id)
    {
        $tagged = Accreditation::leftjoin('programs as p', 'p.id', '=', 'accreditations.programId')
        ->where('accreditations.instrumentId', $id)
        ->get([
            'p.id as program',
            'p.program_name',
            'accreditations.date_self_survey',
            'accreditations.date_actual_survey',
        ]);

        return response()->json([
            'tagged'=> $tagged
        ]);
    } 

    public function tagProgram(Request $request)
    {
        $request->validate([
            'date_of_self_survey' => ['required', 'date', 'before:date_of_actual_survey', 'not_in:'.Carbon::now()->format('Y-m-d')],
            'date_of_actual_survey' => ['required', 'date', 'after:date_of_self_survey', 'not_in:'.Carbon::now()->format('Y-m-d')]
        ],[
            'date_of_self_survey.before' => 'date should not be the same',
            'date_of_actual_survey.after' => 'date should not be the same',
            'date_of_self_survey.not_in' => 'date should not be today',
            'date_of_actual_survey.not_in' => 'date should not be today',
        ]);

        DB::beginTransaction();
        try {

            $accredLvl = Accreditation::create([
                'date_self_survey' => $request->date_of_self_survey, 
                'date_actual_survey' => $request->date_of_actual_survey, 
                'instrumentId' => $request->level, 
                'programId' => $request->program
            ]);

            function createProgress($id, $area, $lvl)
            {
                $instruments = Instrument::where('parent', $id)->whereNull('action')->get(['id', 'parent']);
            
                foreach ($instruments as $inst) {
                    Progress::create([
                        'instrumentId' => $inst->id,
                        'area' => $area,
                        'accredlvlId' => $lvl,
                        'parent' => $inst->parent
                    ]);
                    createProgress($inst->id, $area, $lvl);
                }
            }

            $areas = Instrument::where('parent', $request->level)->whereNull('action')->get(['id', 'parent']);
            
            foreach ($areas as $area) {
                Progress::create([
                    'instrumentId' => $area->id,
                    'area' => $area->id,
                    'accredlvlId' => $accredLvl->id,
                    'parent' => null,
                    'exclude_rate' => $accredLvl->exclude_rate
                ]);
                createProgress($area->id, $area->id, $accredLvl->id);
            }

            DB::commit();
            return back()->with('success', 'Program tagged successfully');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error',  'Failed to tag program');
        }
    }

    public function updateTag(Request $request)
    {
        $request->validate([
            'date_of_self_survey' => ['required', 'date', function ($attribute, $value, $fail) use ($request) {
                if ($request->date_of_actual_survey == $value) {
                    $fail('The self survey date and actual survey date must not be the same.');
                }
            }],
            'date_of_actual_survey' => ['required', 'date', function ($attribute, $value, $fail) use ($request) {
                if ($request->date_of_self_survey == $value) {
                    $fail('The self survey date and actual survey date must not be the same.');
                }
            }],
        ]);

        DB::beginTransaction();
        try {

            Accreditation::where('programId', $request->program)
                ->where('instrumentId', $request->level)
                ->update([
                    'date_self_survey' => $request->date_of_self_survey, 
                    'date_actual_survey' => $request->date_of_actual_survey, 
                ]);

            DB::commit();
            return back()->with('success', 'Updated successfully');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error',  $e->getMessage()/* 'Failed to updated' */);
        }
    }

    public function openSurvey(Request $request)
    {
        DB::beginTransaction();
        try {

            $ss = Accreditation::find($request->id);
            $ss->survey = $request->survey;
            $ss->restrict = $request->survey == 1?true:null;
            $ss->save();

            DB::commit();
            return back()->with('success', 'Program is open for survey');
        } catch (\Throwable $th) {
            DB::rollBack();
            return back()->with('error',  'Can\'t open survey');
        }
    }

    public function restrictUpload(Request $request)
    {
        DB::beginTransaction();
        try {

            $accred = Accreditation::find($request->id);
            $accred->restrict ? $accred->restrict = null : $accred->restrict = true;
            $accred->save();

            DB::commit();
            return back();
        } catch (\Throwable $th) {
            DB::rollBack();
            return back()->with('error',  'Cannot proccess request');
        }
    }

    public function closeCurrentSurvey(Request $request)
    {
        DB::beginTransaction();
        try {
            $objAccred = (object) $request->accred;

            $accred = Accreditation::find($objAccred->id);
            $accred->survey = null;
            $accred->restrict = null;
            $accred->save();

            //$this->saveRates($objAccred->id);

            DB::commit();
            return back()->with('success', $objAccred->program.' '.$objAccred->title.' survey is closed');
        } catch (\Throwable $th) {
            DB::rollBack();
            return back()->with('error',  $th->getMessage());
        }
    }

    public function markAsCompleteSelfSurvey($accred)
    {
        try {

            $completeSurvey = Accreditation::find($accred);
            $completeSurvey->selfsurvey = true;
            $completeSurvey->save();

            return back()->with('success', 'Marked as complete');
        } catch (\Throwable $th) {
            return back()->with('error', 'Failed to mark as complete');
        }
    }

    public function removeMarkAsCompleteSelfSurvey(Request $request)
    {
        try {

            $completeSurvey = Accreditation::find($request->id);
            $completeSurvey->selfsurvey = null;
            $completeSurvey->save();

            return back()->with('success', 'Unmared successfully');
        } catch (\Throwable $th) {
            return back()->with('error', 'Failed to umark as complete');
        }
    }

    public function unverified_accred(Request $req)
    {
        if(!$req->verified)
            return Accreditation::whereNull('verified')
                ->get()
                ->map(function ($val) {
                    $accred = '';

                    return collect([
                        'title' => $val->taggedPrograms->abbreviation.' '.$val->getLevelInstrument->title,
                        'id' => $val->id
                    ]);
                });
        else{
            $results = SelfSurvey::all()->unique('accredlvl');
            $surveys = collect([]);

            foreach($results as $survey)
            {
                //if(Accreditation::where('id', $survey->accredlvl)->whereNotNull('verified')->first())
                    $surveys->push(
                        SelfSurvey::where('accredlvl', $survey->accredlvl)
                        ->latest('created_at')
                        ->first()
                    );
            }

            return $surveys->map(function ($val) {
                $accred = Accreditation::where('id', $val->accredlvl)->first();
                return collect([
                    'surveyId' => $val->id,
                    'title' => $accred->taggedPrograms->abbreviation.' '.$accred->getLevelInstrument->title,
                    'date' => $val->created_at,
                    'rate' => $val->rate
                ]);
            });
        }
            
    }

    public function confirmVerified(Request $request)
    {
        try {

            DB::transaction(function() use ($request) {
                $confirm = Accreditation::find($request->id);
                $confirm->verified = true;
                $confirm->save();

                $this->saveRates($request->id);
            });

            return back()->with('success', 'Verified');
        } catch (\Throwable $th) {
            return back()->with('error', 'Failed to verify');
        }
    }

    public function getAccredAreas(Request $request)
    {
        try {
            
            if(!$request->parent) {
                $areas = Progress::where('accredlvlId', $request->id)
                ->whereNull('parent')
                ->get(['instrumentId', 'rate', 'id'])
                ->map(function ($inst) {
                    $insts = collect([...$inst->instrument->only('title', 'description', 'category', 'indicator')]);
                    return collect([
                        'id' => $inst->instrumentId,
                        'title' => $insts['title'],
                        'description' => $insts['description'],
                        'category' => $insts['category'],
                        'indicator' => $insts['indicator'],
                        'rate' => $inst->rate,
                        'progress' => $inst->id
                    ]);
                });
                
                return response()->json(
                    $areas
                );
            }

            $insts = Instrument::find($request->parent);

            if($insts->category != 'param')
                $areas = Progress::where('progress.accredlvlId', $request->id)->where('instruments.parent', $request->parent)
                ->join('instruments', 'progress.instrumentId', '=', 'instruments.id')
                ->select('progress.instrumentId as id', 'progress.rate', 'progress.id as progress', 'instruments.title', 'instruments.description', 'instruments.category', 'instruments.indicator')
                ->orderBy('instruments.title', 'asc')
                ->get();
            else {
                $areas = Progress::where('progress.accredlvlId', $request->id)->where('instruments.parent', $request->parent)
                ->join('instruments', 'progress.instrumentId', '=', 'instruments.id')
                ->select('progress.instrumentId as id', 'progress.rate', 'progress.id as progress', 'instruments.title', 'instruments.description', 'instruments.category', 'instruments.indicator')
                ->orderBy('instruments.indicator', 'asc')
                ->get();
            }

            return response()->json(
                $areas
            );

        } catch (\Throwable $th) {
            return response()->json('error'.$th->getMessage(), 400);
        }
    }

    public function setAccredStatus(Request $request)
    {
        try {

            DB::transaction(function () use ($request) {
                $status = Accreditation::find($request->id);
                $status->status = $request->status;
                $status->save();
            }); 

            return back()->with('success', 'Set status successfully');

        } catch(\Throwable $th) {
            return back()->with('error', 'Failed to set status');
        }
    }

    public function unverify(Request $request)
    {
        try {
            DB::transaction(function () use ($request) {
                $status = Accreditation::find($request->id);
                $status->verified = null;
                $status->save();
            });
            
            return back()->with('success', 'Unverified successfully');

        } catch (\Throwable $th) {
            return back()->with('error', 'Failed to unverify');
        }
    }

    public function accred_ratings($id = null) {
        $area_survey = SelfSurveyRate::where('surveyId', $id)
            ->get(['instrumentId', 'areaId', 'rate', 'parent'])
            ->filter(function ($value) {
                $inst = $value->getInstrument;
                if($inst->category == 'area')
                    return $value;
            })->map(function ($value) {
                $inst = $value->getInstrument;
                return collect([
                    'instrumentId' => $inst->id,
                    'title' => $inst->title,
                    'category' => $inst->category,
                    'description' => $inst->description,
                    'rate' => trim($value->rate),
                    'parent' => $value->parent,
                ]);
            });
        
        $survey = SelfSurveyRate::where('surveyId', $id)
        ->get(['instrumentId', 'areaId', 'rate', 'parent']);

        function buildTree($data, $parentId = null)
        {
            $tree = collect([]);

            foreach ($data as $item) {
                if ($item->parent == $parentId) {
                    $children = buildTree($data, $item['instrumentId']);
                    $res = null;
                    if ($children) {
                        $instrument = Instrument::where('id', $item['instrumentId'])->first();
                        $res = collect([
                            'instrumentId' => $item['instrumentId'],
                            'title' => $instrument['title'],
                            'category' => $instrument['category'],
                            'description' => $instrument['description'],
                            'rate' => trim($item['rate']),
                            'parent' => $item['parent'],
                            'children' => $children
                        ]);
                    }
                    $tree->push($res);
                }
            }

            return $tree;
        }

        return $area_survey->map(function ($value) use ($survey) {
            return collect([
                'instrumentId' => $value['instrumentId'],
                'title' => $value['title'],
                'category' => $value['category'],
                'description' => $value['description'],
                'rate' => trim($value['rate']),
                'parent' => $value['parent'],
                'children' => buildTree($survey, $value['instrumentId'])
            ]);
        });
    }

    public function print_rates($id, $surveyId) {
        $area_survey = SelfSurveyRate::where('surveyId', $surveyId)
            ->where('instrumentId', $id)
            ->get(['instrumentId', 'areaId', 'rate', 'parent'])
            ->filter(function ($value) {
                $inst = $value->getInstrument;
                if($inst->category == 'area')
                    return $value;
            })->map(function ($value) {
                $inst = $value->getInstrument;
                return collect([
                    'instrumentId' => $inst->id,
                    'title' => $inst->title,
                    'category' => $inst->category,
                    'description' => $inst->description,
                    'rate' => trim($value->rate),
                    'parent' => $value->parent,
                ]);
            });
        
        $survey = SelfSurveyRate::where('surveyId', $surveyId)
        ->get(['instrumentId', 'areaId', 'rate', 'parent']);

        function buildTrees($data, $parentId = null)
        {
            $tree = collect([]);

            foreach ($data as $item) {
                if ($item->parent == $parentId) {
                    $children = buildTrees($data, $item['instrumentId']);
                    $res = null;
                    if ($children) {
                        $instrument = Instrument::where('id', $item['instrumentId'])->first();
                        $res = collect([
                            'instrumentId' => $item['instrumentId'],
                            'title' => $instrument['title'],
                            'category' => $instrument['category'],
                            'description' => $instrument['description'],
                            'rate' => trim($item['rate']),
                            'parent' => $item['parent'],
                            'children' => $children
                        ]);
                    }
                    $tree->push($res);
                }
            }

            return $tree;
        }

        $collects = $area_survey->map(function ($value) use ($survey) {
            return collect([
                'instrumentId' => $value['instrumentId'],
                'title' => $value['title'],
                'category' => $value['category'],
                'description' => $value['description'],
                'rate' => trim($value['rate']),
                'parent' => $value['parent'],
                'children' => buildTrees($survey, $value['instrumentId'])
            ]);
        });

        $accredLvl = SelfSurvey::find($surveyId);
        $self_accreditors = AreaSelfAccreditor::where('instrumentId',$id)
            ->where('accredlvl',$accredLvl->accredlvl)
            ->get(['userId'])
            ->map(function ($value) {
                $user = $value->selfAccreditor;
                return collect([
                    'user' => $user->first_name.' '.$user->last_name
                ]);
            });
            
        return Inertia::render('PrintingPage', ['id'=>intval($id), 'area'=>$collects, 'self_accreditors'=>$self_accreditors]);
    }

    function saveRates($accred)
    {
        $rates = Progress::where('accredlvlId', $accred)->get(['instrumentId', 'area', 'rate', 'parent']);

        $surveyId = SelfSurvey::create([
            'accredlvl'=>$accred,
            'rate' => $this->overAllCalculation($accred)
        ]);

        foreach($rates as $rate)
        {
            SelfSurveyRate::create([
                'surveyId' => $surveyId->id,
                'instrumentId' => $rate->instrumentId,
                'parent' => $rate->parent,
                'areaId' => $rate->area,
                'rate' => $rate->rate,
            ]);
        }

    }

    function getAccreditations()
    {
        $programs = Accreditation::join('programs as p', 'p.id', '=', 'accreditations.programId')
        ->join('instruments as i', 'i.id', '=', 'accreditations.instrumentId')
        ->whereNull('survey')
        ->get([
            'accreditations.id as id',
            'p.abbreviation as program',
            'i.title',
            'accreditations.survey',
            'accreditations.restrict',
            'accreditations.date_self_survey',
            'accreditations.date_actual_survey',
            'accreditations.programId',
            'accreditations.instrumentId',
            'accreditations.selfSurvey',
            'accreditations.status',
            'accreditations.verified',
        ]);
        return $programs;
    }

    function overAllCalculation($accred)
    {
        $areaRates = Progress::where('accredlvlId', $accred)
            ->whereNull('parent')
            ->whereNull('exclude_rate')
            ->get('rate');

        $overAllRate = round($areaRates->sum('rate') / $areaRates->count(), 1);

        $overAllRate = $overAllRate == 0 ? null : $overAllRate;

        return $overAllRate;
    }

}
