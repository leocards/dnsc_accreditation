<?php

namespace App\Http\Controllers;

use App\Models\FacultyStaff;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FacultyStaffController extends Controller
{
    public function index($program, $institute)
    {
        $fs = FacultyStaff::where('programId', $program)
            ->where('instituteId', $institute)
            ->orWhere('instituteId', $institute)
            ->whereNull('programId')
            ->orderBy('designation')
            ->get(['id', 'programId', 'instituteId', 'designation', 'name'])
            ->map(function ($val) {
                return collect([
                    'id' => $val->id, 
                    'programId' => $val->programId, 
                    'instituteId' => $val->instituteId, 
                    'designation' => $val->designation,
                    'name' => $val->name,
                    'isEditable' => true,
                ]);
            });

        $users = User::where('programId', $program)
            ->where('instituteId', $institute)
            ->orWhere('instituteId', $institute)
            ->whereNull('programId')
            ->orderBy('designation')
            ->get(['id', 'programId', 'instituteId', 'designation', 'first_name', 'last_name'])
            ->map(function ($val) {
                return collect([
                    'id' => $val->id, 
                    'programId' => $val->programId, 
                    'instituteId' => $val->instituteId, 
                    'designation' => $val->designation,
                    'name' => $val->first_name.' '.$val->last_name,
                    'isEditable' => false,
                ]);
            });

        $fs->push(...$users);

        return response()->json([
            'facultyStaff' => $fs->count() > 0 ? $fs : [],
        ], 200);
    }

    public function createFS(Request $request)
    {
        try{

            DB::transaction(function () use ($request) {
                FacultyStaff::create([
                    'name' => $request->name,
                    'designation' => $request->designation,
                    'programId' => $request->designation == 3 || $request->designation == 2 ? $request->program : null,
                    'instituteId' => $request->institute,
                ]);
            });
            
            return back()->with(['success' => 'Successfully created']);

        }catch(\Throwable $e){
            return back()->with(['error' => 'Failed to create']);
        }
    }

    public function updateFS(Request $request, $id)
    {
        try{

            DB::transaction(function () use ($request, $id) {
                FacultyStaff::where('id', $id)->update([
                    'name' => $request->name,
                    'designation' => $request->designation,
                    'programId' => $request->program,
                    'instituteId' => $request->institute,
                ]);
            });
            
            return back()->with(['success' => 'Successfully updated']);

        }catch(\Throwable $e){
            return back()->with(['error' => 'Failed to update']);
        }
    }

    public function suggestNameFS(Request $request)
    {
        try {
            return response()->json(
                User::where('first_name', 'LIKE', "%{$request->search}%")
                    ->whereNotIn('designation', [7, 8, 9])
                    ->orWhere('last_name', 'LIKE', "%{$request->search}%")
                    ->whereNotIn('designation', [7, 8, 9])
                    ->get(['id', 'first_name', 'last_name'])
                    ->map(function ($val) {
                        return collect([
                            'id' => $val->id,
                            'name' => $val->first_name.' '.$val->last_name
                        ]);
                    })
            );
        } catch (\Throwable $th) {
            return response()->json('error', 400);
        }
    }
}
