<?php

namespace App\Http\Controllers;

use App\Models\Institute;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class InstituteController extends Controller
{
    public function index()
    {
        try{
            if(Auth::user()->auth == 3)
                return Inertia::render('Institute', [
                    'institutes' => Institute::leftjoin('users as u', 'u.id', '=', 'institutes.dean')
                        ->where('institutes.id', Auth::user()->instituteId)
                        ->get(['institutes.id', 'u.first_name', 'u.last_name', 'institutes.abbreviation', 'institutes.institute_name', 'institutes.established', 'institutes.dean'])
                ]);

            $institutes = Institute::leftjoin('users as u', 'u.id', '=', 'institutes.dean')
            ->get(['institutes.id', 'u.first_name', 'u.last_name', 'institutes.abbreviation', 'institutes.institute_name', 'institutes.established', 'institutes.dean']);
    
            return Inertia::render('Institute', ['institutes'=>$institutes]);
        }catch(\Throwable $th){
            
        }

    }

    public function store(Request $request)
    {
        $request->validate([
            'abbreviation' => ['required', 'unique:institutes,abbreviation'],
            'institute_name' => ['required', 'unique:institutes,institute_name']
        ]);

        try{

            DB::transaction(function () use ($request) {
                Institute::create([
                    'abbreviation' => $request->abbreviation, 
                    'institute_name' => $request->institute_name,
                    'established' => $request->established
                ]);
            });

            return back()->with('success', 'Created successfully');

        }catch(\Throwable $e){
            return back()->withErrors('failed to create');
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'abbreviation' => ['required', 'unique:institutes,abbreviation,'.$id],
            'institute_name' => ['required', 'unique:institutes,institute_name,'.$id]
        ]);

        $exist = Institute::where('id', '!=', $id)->where('abbreviation', $request->abbreviation)->first();
        if($exist){
            $request->validate([
                'abbreviation' => ['unique:institutes,abbreviation']
            ]);
        }else{
            $exist = Institute::where('id', '!=', $id)->where('institute_name', $request->abbreviation)->first();
            if($exist){
                $request->validate([
                    'institute_name' => ['unique:institutes,institute_name']
                ]);
            }
        }

        try{

            DB::transaction(function () use ($request, $id) {
                Institute::where('id', $id)->update([
                    'abbreviation' => $request->abbreviation, 
                    'institute_name' => $request->institute_name,
                    'dean' => $request->dean,
                    'established' => $request->established
                ]);
            });

            return back()->with('success', 'Updated successfully');
            
        }catch(\Throwable $e){
            return back()->withErrors('failed to update');
        }
    }

    public function getInstitutes()
    {
        return response()->json(['institutes' => Institute::all('id', 'abbreviation')]);
    }

    public function getDeans(Request $request)
    {
        try{
            return response()->json(User::where('instituteId', $request->d)->whereNull('programId')->get(['last_name','first_name','id']), 200);
        }catch(\Throwable $th){
            return response()->json('error', 400);
        }   
    }
}
