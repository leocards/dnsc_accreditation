<?php

namespace App\Http\Controllers;

use App\Models\InstrumentComment;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InstrumentCommentController extends Controller
{
    public function getComments(Request $request)
    {
        try {
            if(Auth::user()->auth == 5){
                return response()->json([
                    'comments' => InstrumentComment::where('instrumentId', $request->instrumentId)
                    ->where('accredId', $request->accredlvl)
                    ->where('userId', Auth::id())
                    ->get()->map(function ($val) {
                        $comment = $val;
                        $comment->user = User::find($val->userId)->only('first_name', 'last_name', 'avatar');
                        return $comment;
                    })
                ]);
            }else {
                return response()->json([
                    'comments' => InstrumentComment::where('instrumentId', $request->instrumentId)
                    ->where('accredId', $request->accredlvl)
                    ->get()->map(function ($val) {
                        $comment = $val;
                        $comment->user = User::find($val->userId)->only('first_name', 'last_name', 'avatar');
                        return $comment;
                    })
                ]);
            }
        } catch (\Throwable $th) {
            return response()->json(['error' => 'Something went wrong']);
        }
    }

    public function store(Request $request)
    {
        try {
            if(strlen(trim($request->comment)) > 0){
                $comment = DB::transaction(function () use ($request) {
                    $comment = InstrumentComment::create([
                        'userId' => Auth::id(),
                        'instrumentId' => $request->instrumentId,
                        'accredId' => $request->accredlvl,
                        'comment' => $request->comment,
                    ]);

                    return $comment;
                });

                $comment->user = collect(['first_name' => Auth::user()->first_name, 'last_name' => Auth::user()->last_name]);
            }else{
                throw new Exception('must contain character');
            }

            return response()->json(['response' => $comment]);
        } catch (\Throwable $th) {
            return response()->json(['response' => $th->getMessage()], 400);
        }
    }
}
