<?php

namespace App\Http\Controllers;

use App\Models\Accreditation;
use App\Models\Announcement;
use App\Models\Institute;
use App\Models\Program;
use App\Models\Progress;
use App\Models\SelfSurvey;
use App\Models\SelfSurveyRate;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class DashboardController extends Controller
{

    public function index()
    {
        return Inertia::render('Dashboard', [
            'statistic' => collect([
                'users' => User::count(),
                'institute' => Institute::count(),
                'program' => Program::count(),
                'accred' => Accreditation::where('status', 'accredited')->count()
            ]),
            'surveys' => $this->surveysLvl()
        ]);
    }

    public function announcement()
    {
        try {

            return response()->json(
                Announcement::orderBy('created_at', 'desc')
                    ->get(['id', 'userId', 'title', 'message', 'created_at'])
                    ->map(function ($announce) {
                        $user = $announce->user;
                        return collect([
                            'id' => $announce->id,
                            'name' => $user->id == Auth::id() ? 'You' : $user->name,
                            'title' => $announce->title,
                            'message' => $announce->message,
                            'time' => $announce->created_at,
                            'avatar' => $user->avatar
                        ]);
                    })
            , 200);

        } catch (\Throwable $th) {
            return response()->json($th->getMessage(), 400);
        }
    }

    public function addAnnounce(Request $request)
    {

        $request->validate([
            'title' => ['required' ],
            'message' => ['required' ]
        ]);

        try {

            if($request->id)
            {
                $announce = $this->updateAnounce($request);
            }else{
                $announce = DB::transaction(function () use ($request) {
                    return Announcement::create([
                        'userId' => Auth::id(),
                        'title' => $request->title,
                        'message' => $request->message,
                    ]);
                });
            }

            return response()->json(collect([
                'id' => $announce->id,
                'name' => 'You',
                'title' => $announce->title,
                'message' => $announce->message,
                'time' => $announce->created_at,
                'avatar' => Auth::user()->avatar
            ]), 200);

        } catch (\Throwable $th) {
            return response()->json($th->getMessage(), 400);
        }
    }

    public function surveysLvl()
    {
        try {
            $results = SelfSurvey::all()->unique('accredlvl');
            $surveys = collect([]);

            foreach($results as $survey)
            {
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
                        'level' => $accred->getLevelInstrument->title,
                        'program' => $accred->taggedPrograms->abbreviation,
                        'date' => $val->created_at,
                        'rate' => $val->rate
                    ]);
                });
        } catch (\Throwable $th) {
            return [];
        }
    }

    public function removeAnnounce(Request $request)
    {
        try {
            DB::transaction(function () use ($request) {
                $remove = Announcement::find($request->id);
                $remove->delete();
            });

            return back()->with('success', 'Removed successfully');
        }catch (\Throwable $th){
            return back()->with('error', 'Failed to remove');
        }
    }

    public function getLevelAreas($surveyId)
    {
        try {
            $areas = SelfSurveyRate::where('surveyId', $surveyId)
            ->whereNull('parent')
            ->get(['instrumentId', 'areaId', 'parent', 'rate'])
            ->map(function ($area) use ($surveyId) {
                $val = $area->getInstrument->only(['id', 'title', 'description']);
                return collect([
                    'id' => $val['id'],
                    'title' => $val['title'],
                    'description' => $val['description'],
                    'indicators' => collect([...$this->collectIndicators($val['id'], $surveyId)])
                ]);
            });

            $indicatorRates = collect([]);

            foreach ($areas as $key => $value) {
                $arrRate = array_map(function ($val) {
                    return floatval($val);
                }, array_filter($value['indicators']->pluck('rate')->all(), 'is_numeric'));

                $vals = collect([
                    'area' => $value['id'],
                    'ind' => $arrRate,
                    'isCluster' => count($arrRate) > 3?true:false
                ]);

                $indicatorRates->push($vals);
            }

            return response()->json(['areas'=>$areas, 'rates'=>$indicatorRates]);
        }catch (\Throwable $th) {
            return response()->json($th->getMessage(), 400);
        }
    }

    public function Rcommand(Request $request){

        $escapedN = str_replace('"', '\"', $request->clus); // Escape any special characters in the JSON string
        $quotedN = '"' . $escapedN . '"'; // Enclose the JSON string in double quotes
        //echo $quotedN;
        $command = "\"C:\Program Files\R\R-4.3.0\bin\Rscript.exe\" C:\\xampp\\htdocs\\CKmeans1D.R $quotedN";
        exec($command, $output);

        return response()->json(json_decode($output[0], true));
    }

    function collectIndicators ($area, $surveyId) {
        $ind = collect([...SelfSurveyRate::where('surveyId', $surveyId)
        ->where('areaId', $area)
        ->whereNotNull('parent')
        ->get(['instrumentId', 'areaId', 'rate'])
        ->map(function ($val) {
            $inst = $val->getInstrument->only(['id', 'title', 'description', 'category', 'parent']);
            return collect([
                'id' => $inst['id'],
                'title' => $inst['title'],
                'description' => $inst['description'],
                'area' => $val->areaId,
                'rate' => trim($val->rate),
                'parent' => $inst['parent'],
                'category' => $inst['category'],
            ]);
        })->filter(function ($filter) {
            if($filter['category'] == 'item')
                return $filter;
        })]);

        foreach ($ind as $key => $value) {
            foreach($ind as $key2 => $value2) {
                if($value['id'] == $value2['parent']) 
                    $ind->forget($key2);
            }
        }

        return $ind;
    }

    function updateAnounce (Request $request)
    {
        return DB::transaction(function () use ($request) {
            $update = Announcement::find($request->id);
            $update->title = $request->title;
            $update->message = $request->message;
            $update->save();

            return $update;
        });
    }
}
