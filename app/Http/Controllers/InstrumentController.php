<?php

namespace App\Http\Controllers;

use App\Models\Accreditation;
use App\Models\AreaAssign;
use App\Models\AreaSelfAccreditor;
use App\Models\AttachedDocument;
use App\Models\DocumentCurrentVersion;
use App\Models\Instrument;
use App\Models\InstrumentComment;
use App\Models\Progress;
use App\Models\TaskAssign;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class InstrumentController extends Controller
{
    public $crumbs;
    public $current;
    
    public function __construct()
    {
        $this->crumbs = collect([]);
        $this->current = null;
    }

    public function index(Request $request, $id = null)
    {
        $createAs = 0;
        if($id){
            $this->checkInstrument($id) == "inst" ? $createAs = 1 :
                ($this->checkInstrument($id) == "lvl" ? $createAs = 2 : '');
        }
        
        $this->current = $this->crrentCrumbs($id);
        $this->getCrumbs();

        $level = $this->crumbs->count() > 0 ? $this->crumbs->filter(function ($value) {
            return $value->category == 'lvl';
        })->first() : null;
        
        return Inertia::render('Instrument', [
            'current' => $this->current,
            'instruments'=> $this->returnInstruments($id),
            'createAs' => $this->current ? $createAs : 0,
            'crumbs' => $this->crumbs,
            'lvlinst' => $level
        ]);
    }

    public function store(Request $request, $id = null)
    {
        if($request->createAs === 0)
        {
            $request->validate(['instrument'=>['required']]);

            if($id){
                $exist = Instrument::where('title', $request->instrument)->where('id', '!=', $id)->first();

                $exist ? $request->validate(['instrument'=>['unique:instruments,title']]):'';
            }else{
                $request->validate(['instrument'=>['unique:instruments,title']]);
            }

            $id ? $this->createInstrument($request, $id) : $this->createInstrument($request);
        }else if($request->createAs === 1){
            $request->validate(['level_and_phase'=>['required']]);
            
            $exist = Instrument::where('title', $request->level_and_phase)->where('parent', $request->parent)->first();

            if($exist)
            {
                $request->validate([
                    'level_and_phase'=>['required', 'unique:instruments,title']
                ]);
            }else{
                $id ? $this->createLevel($request, $id) : $this->createLevel($request);
            }
        }else if($request->createAs === 2){
            $request->validate([
                'area'=>['required'],
                'area_label'=>['required'],
            ]);

            if($id)
            {
                $exist = Instrument::where('title', $request->area)->where('parent', $request->parent)->where('id', '!=', $id)->first();

                $exist ? $request->validate(['area'=>['unique:instruments,title']]) : $this->createArea($request, $id);
            }else{
                $exist = Instrument::where('title', $request->area)->where('parent', $request->parent)->first();
    
                if($exist)
                {
                    $request->validate([
                        'area'=>['required', 'unique:instruments,title']
                    ]);
                }else{
                    $this->createArea($request);
                }
            }

        }else if($request->createAs === 3){
            $request->validate([
                'parameter'=>['required'],
                'parameter_label'=>['required'],
            ]);
            if($id)
            {
                $exist = Instrument::where('title', $request->parameter)->where('parent', $request->parent)->where('id', '!=', $id)->first();
                $exist ? $request->validate(['parameter'=>['unique:instruments,title']]) : $this->createParameter($request, $id);
            }else{
                $exist = Instrument::where('title', $request->parameter)->where('parent', $request->parent)->first();
    
                if($exist)
                {
                    $request->validate([
                        'parameter'=>['unique:instruments,title']
                    ]);
                }else{
                    $this->createParameter($request);
                }
            }

        }else if($request->createAs === 5){
            $request->validate([
                'indicator'=>['required'],
                'indicator_label'=>['required'],
                //'evidence_to_attach'=>['required'],
            ]);
            if($id)
            {
                $exist = Instrument::where('title', $request->indicator)->where('parent', $request->parent)->where('id', '!=', $id)->first();
                $exist ? $request->validate(['indicator'=>['unique:instruments,title']]) : $this->createIndicators($request, $id);
            }else{
                $exist = Instrument::where('title', $request->indicator)->where('parent', $request->parent)->first();
    
                if($exist)
                {
                    $request->validate([
                        'indicator'=>['unique:instruments,title']
                    ]);
                }else{
                    $this->createIndicators($request);
                }
            }
        }
    }

    public function excludeInComputation($inst, Request $request)
    {
        try {
            $isExclude = $request->include?null:true;
            DB::transaction(function () use ($inst, $isExclude) {
                Progress::where('instrumentId', $inst)->update(['exclude_rate' => $isExclude]);

                $instrument = Instrument::find($inst);
                $instrument->exclude_rate = $isExclude;
                $instrument->save();
            });

            return back()->with('success', 'Successful');
        } catch (\Throwable $th) {
            return back()->with('error', 'Something went wrong');
        }
    }

    public function destroy(Request $request) {
        DB::beginTransaction();
        try {

            $inst = Instrument::find($request->id);

            if($inst->category == 'lvl' || $inst->category == 'inst')
                return back()->with('error', 'Cannot proccess request');

            
            DB::transaction(function () use ($request) {
                $subs = Instrument::where('parent', $request->id)->get();
                $collects = collect([...$subs]);

                if($subs->count() > 0){
                    while($subs->count() > 0){
                        foreach ($subs as $value) {
                            $subs = Instrument::where('parent', $value->id)->get();
                            $collects->push(...$subs);
                        }
                    }
                }

                foreach($collects as $val) {
                    Progress::where('instrumentId', $val['id'])->delete();
                    TaskAssign::where('instrumentId', $val['id'])->orWhere('areaId', $val['id'])->delete();
                    AreaAssign::where('areaId', $val['id'])->delete();
                    AreaSelfAccreditor::where('instrumentId', $val['id'])->delete();
                    AttachedDocument::where('instrumentId', $val['id'])->delete();
                    InstrumentComment::where('instrumentId', $val['id'])->delete();
                    Instrument::where('id', $val['id'])->delete();
                    DocumentCurrentVersion::where('instrumentId', $val['id'])->update(['isRemoved'=>true]);
                }
            });
            Progress::where('instrumentId', $inst['id'])->delete();
            TaskAssign::where('instrumentId', $inst['id'])->orWhere('areaId', $inst['id'])->delete();
            AreaAssign::where('areaId', $inst['id'])->delete();
            AreaSelfAccreditor::where('instrumentId', $inst['id'])->delete();
            AttachedDocument::where('instrumentId', $inst['id'])->delete();
            InstrumentComment::where('instrumentId', $inst['id'])->delete();
            DocumentCurrentVersion::where('instrumentId', $inst['id'])->update(['isRemoved'=>true]);
            $inst->delete();

            DB::commit();

            return back()->with('success', 'Successfully deleted');

        } catch (\Throwable $th) {
            DB::rollBack();
            dd($th->getMessage());
            return back()->with('error', 'Failed to delete');

        }
    }

    function checkInstrument($inst)
    {
        return $inst?Instrument::find($inst)->category:null;
    }

    function returnInstruments($value)
    {
        !$value || $value == null ? $value = null : '';
        if(($this->current ? $this->current->category == 'lvl' : false))
        {
            Global $instruments;
            $instruments = collect([]);

            function getChildren($parent)
            {
                global $instruments;

                $instrument = Instrument::where('parent', $parent)->get();
                $instrument = $instrument ? $instrument->makeHidden(['created_at', 'updated_at', 'action']) : $instrument;

                if($instrument->count() !== 0)
                {
                    foreach ($instrument as $key => $value) {
                        $instruments->push($value);
                        getChildren($value->id);
                    }
                }
            }
            getChildren($value);

            return $instruments;
            
        }else{
            $instrument = Instrument::where('parent', $value)->get();
            return $instrument ? $instrument->makeHidden(['created_at', 'updated_at', 'action']) : $instrument;
        }
    }

    function crrentCrumbs($value)
    {
        if(!$value || $value === null)
        {
            return null;
        }else{
            $instrument = Instrument::find($value);
            return $instrument ? $instrument->makeHidden(['created_at', 'updated_at', 'action']) : $instrument;
        }
    }

    function getCrumbs()
    {
        Global $crumbs;
        $crumbs = [];
        
        if($this->current)
        {
            function getCrumb($id)
            {
                global $crumbs;

                $parent = Instrument::find($id);
                $parent ? $parent->makeHidden(['created_at', 'updated_at', 'action', 'attachment']) : '';
                $parent ? array_push($crumbs, $parent) : '';
                $parent ? getCrumb($parent->parent) : '';
            }

            getCrumb($this->current->id);

            $this->crumbs->push(...$crumbs);
        }
    }

    function createInstrument(Request $request, $id = null)
    {
        if($id){
            try{
                DB::transaction(function () use ($request, $id) {
                    Instrument::where('id', $id)->update([
                        'title' => $request->instrument, 
                    ]);
                });
                return back()->with('success', 'Updated successfully');
            }catch(\Throwable $e){
                return back()->with('error', 'Failed to update');
            }
        }else{
            try{
                DB::transaction(function () use ($request) {
                    Instrument::create([
                        'title' => $request->instrument, 
                        'category'=>'inst',
                    ]); 
                });
                return back()->with('success', 'Created successfully');
            }catch(\Throwable $e){
                return back()->with('error', 'Failed to create');
            }
        }
    }

    function createLevel(Request $request, $id = null)
    {
        if($id){
            try{
                DB::transaction(function () use ($request, $id) {
                    Instrument::where('id', $id)->update([
                        'title' => $request->level_and_phase, 
                    ]);
                });
                return back()->with('success', 'Updated successfully');
            }catch(\Throwable $e){
                return back()->with('error', 'Failed to updated');
            }
        }else{
            try{
                DB::transaction(function () use ($request) {
                    Instrument::create([
                        'title' => $request->level_and_phase, 
                        'parent' => $request->parent,
                        'category'=>'lvl',
                    ]); 
                });
                return back()->with('success', 'Created successfully');
            }catch(\Throwable $e){
                return back()->with('error', 'Failed to create');
            }
        }


    }

    function createProgress($instrument, $parent, $area, $accredLvl)
    {
        Progress::create([
            'instrumentId' => $instrument,
            'parent' => $parent,
            'area' => $area, 
            'accredlvlId' => $accredLvl,
        ]);
    }

    function createArea(Request $request, $id = null)
    {
        if($id)
        {
            try{
                DB::transaction(function () use ($request, $id) {
                    Instrument::where('id', $id)->update([
                        'title' => $request->area, 
                        'description' => $request->area_label,
                    ]);
                });
                return back()->with('success', 'Updated successfully');
            }catch(\Throwable $e){
                return back()->with('error', 'Failed to update');
            }
        }else{
            try{
                DB::transaction(function () use ($request) {
                    $area = Instrument::create([
                        'title' => $request->area, 
                        'parent' => $request->parent,
                        'description' => $request->area_label,
                        'category'=>'area',
                    ]);

                    $levelAccred = Accreditation::where('instrumentId', $request->level)->get(['id']);

                    if($levelAccred->count() > 0)
                    {
                        foreach($levelAccred as $lvl)
                        {
                            $this->createProgress($area->id, null, $area->id, $lvl->id);
                        }
                    }
                });
                return back()->with('success', 'Created successfully');
            }catch(\Throwable $e){
                return back()->with('error', 'Failed to create');
            }
        }
    }

    function createParameter(Request $request, $id = null)
    {
        if($id)
        {
            try{
                DB::transaction(function () use ($request, $id) {
                    Instrument::where('id', $id)->update([
                        'title' => $request->parameter, 
                        'description' => $request->parameter_label,
                    ]);
                });
                return back()->with('success', 'Updated successfully');
            }catch(\Throwable $e){
                return back()->with('error', 'Failed to update');
            }
        }else{
            /* 
                store the created indicators
            */
            $indicators = collect([]);

            try{
                DB::transaction(function () use ($request, $indicators) {
                    $parameter = Instrument::create([
                        'title' => $request->parameter, 
                        'parent' => $request->parent,
                        'description' => $request->parameter_label,
                        'category'=>'param',
                    ]);

                    foreach ($request->indicators as $key => $indicator) {
                        $inst = Instrument::create([
                            'title' => $indicator['title'], 
                            'parent' => $parameter->id,
                            'indicator' => $indicator['id'],
                            'category'=>'ind',
                        ]);
                        /* 
                            push the stored indicators to indicators collection
                        */
                        $indicators->push($inst);
                    }

                    $levelAccred = Accreditation::where('instrumentId', $request->level)->get(['id']);

                    if($levelAccred->count() > 0)
                    {
                        foreach($levelAccred as $lvl)
                        {
                            $this->createProgress($parameter->id, $request->parent, $request->parent, $lvl->id);
                            /* 
                                store the created indicators to progress
                            */
                            foreach($indicators as $ind)
                            {
                                $this->createProgress($ind->id, $ind->parent, $request->parent, $lvl->id);
                            }
                        }
                    }
                });
                return back()->with('success', 'Created successfully');
            }catch(\Throwable $e){
                return back()->with('error', 'Failed to create');
            }
        }
    }

    function createIndicators(Request $request, $id = null)
    {
        if($id)
        {
            try{
                DB::transaction(function () use ($request, $id) {
                    Instrument::where('id', $id)->update([
                        'title' => $request->indicator, 
                        'description' => $request->indicator_label,
                        'attachment' => json_encode($request->evidence_to_attach),
                    ]);
                });
                return back()->with('success', 'Updated successfully');
            }catch(\Throwable $e){
                return back()->with('error', 'Failed to update');
            }
        }else{
            try{
                DB::transaction(function () use ($request) {
                    $indicators = Instrument::create([
                        'title' => $request->indicator, 
                        'parent' => $request->parent,
                        'description' => $request->indicator_label,
                        'attachment' => json_encode($request->evidence_to_attach),
                        'category' => 'item',
                    ]);

                    $levelAccred = Accreditation::where('instrumentId', $request->level)->get(['id']);

                    if($levelAccred->count() > 0)
                    {
                        foreach($levelAccred as $lvl)
                        {
                            $this->createProgress($indicators->id, $indicators->parent, $request->area, $lvl->id);
                        }
                    }
                });
                return back()->with('success', 'Created successfully');
            }catch(\Throwable $e){
                return back()->with('error', 'Failed to create'.$e->getMessage());
            }
        }
    }
}
