<?php

namespace App\Http\Controllers;

use App\Http\Traits\UserTrait;
use App\Models\DocumentCurrentVersion;
use App\Models\Share;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ShareController extends Controller
{
    use UserTrait;

    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $checkIfNotRemoved = DocumentCurrentVersion::where('id', $request->documentId)
                ->whereNull('isRemoved')->first();

            if(!$checkIfNotRemoved)
                throw new Exception('The document you want to share might be removed');
                

            Share::create([
                'userId' => $request->userId,
                'documentId' => $request->documentId
            ]);

            //log user activity
            $this->userLog(
                $checkIfNotRemoved->accredlvl, 
                $checkIfNotRemoved->documentId, 
                $checkIfNotRemoved->instrumentId, 
                'shared this document to '. User::find($request->userId)->getFullName());

            DB::commit();
            return back()->with('success', 'Shared successfuly');
        } catch (\Throwable $th) {
            DB::rollback();
            return back()->with('error', $th->getMessage());
        }
    }

    public function getShared($docuId)
    {
        return response()
            ->json(
                Share::where('documentId', $docuId)
                    ->get(['userId', 'id'])
                    ->map(function ($user) {
                        $u = $user->user->only('first_name', 'last_name');
                        $user->last_name = $u['last_name'];
                        $user->first_name = $u['first_name'];

                        return $user->only('last_name', 'first_name', 'userId', 'id');
                    })
                );
    }
}
