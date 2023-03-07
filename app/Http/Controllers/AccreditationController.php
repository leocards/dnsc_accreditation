<?php

namespace App\Http\Controllers;

use App\Models\Accreditation;
use App\Models\Instrument;
use App\Models\Progress;
use App\Models\SelfSurvey;
use App\Models\SelfSurveyRate;
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
                                ->get(['id', 'instrumentId', 'programId', 'restrict', 'survey', 'status'])
                                ->map(function ($accred) {
                                    $program = $accred->taggedPrograms->only('abbreviation');
                                    $accred->program = $program['abbreviation'];
                                    $accred->title = $accred->getLevelInstrument->only('title')['title'];
                                    return $accred->only('id', 'instrumentId', 'programId', 'restrict', 'survey', 'program', 'title', 'programId', 'selfSurvey', 'status');
                                }),
            'self_survey' => Accreditation::where('survey', 2)
                                ->get(['id', 'instrumentId', 'programId', 'restrict', 'survey', 'status'])
                                ->map(function ($accred) {
                                    $program = $accred->taggedPrograms->only('abbreviation');
                                    $accred->program = $program['abbreviation'];
                                    $accred->title = $accred->getLevelInstrument->only('title')['title'];
                                    return $accred->only('id', 'instrumentId', 'programId', 'restrict', 'survey', 'program', 'title', 'programId', 'selfSurvey', 'status');
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
            'date_of_self_survey' => ['required', 'date'],
            'date_of_actual_survey' => ['required', 'date']
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

            $this->saveRates($objAccred->id);

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

    public function unverified_accred()
    {
        return Accreditation::whereNull('verified')
            ->get()
            ->map(function ($val) {
                $accred = '';

                return collect([
                    'title' => $val->taggedPrograms->abbreviation.' '.$val->getLevelInstrument->title,
                    'id' => $val->id
                ]);
            });
    }

    public function confirmVerified(Request $request)
    {
        try {

            $confirm = Accreditation::find($request->id);
            $confirm->verified = true;
            $confirm->save();

            return back()->with('success', 'Verified');
        } catch (\Throwable $th) {
            return back()->with('error', 'Failed to verify');
        }
    }

    public function getAccredAreas(Request $request)
    {
        return response()->json(
            Progress::where('accredlvlId', $request->id)
            ->whereNull('parent')
            ->get(['instrumentId', 'rate'])
            ->map(function ($inst) {
                return collect([
                    'id' => $inst->instrumentId,
                    'area' => $inst->instrument->only('title', 'description'),
                    'rate' => $inst->rate
                ]);
            })
        );
    }

    public function setAccredStatus(REquest $request)
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
            'accreditations.status'
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
