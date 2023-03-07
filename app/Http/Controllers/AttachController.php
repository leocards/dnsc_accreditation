<?php

namespace App\Http\Controllers;

use App\Events\Attachment;
use App\Http\Traits\UserTrait;
use App\Models\AttachedDocument;
use App\Models\DocumentCurrentVersion;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AttachController extends Controller
{
    use UserTrait;

    public function attachDocument(Request $request)
    {
        DB::beginTransaction();
        try {
            
            $exist = AttachedDocument::where('accredlvl', $request->accred)
                ->where('documentId', $request->document)
                ->where('instrumentId', $request->instrument)
                ->first('id');

            $exist2 = DocumentCurrentVersion::where('accredlvl', $request->accred)
                ->where('id', $request->document)
                ->where('instrumentId', $request->instrument)
                ->first('id');

            if($exist || $exist2)
                throw new Exception("exist");

            AttachedDocument::create([
                'accredlvl' => $request->accred,
                'documentId' => $request->document,
                'instrumentId' => $request->instrument,
            ]);

            $document = collect(DocumentCurrentVersion::find($request->document)->get_document);
            $document['instrument'] = $request->instrument;
            $document['accred'] = $request->accred;
            $document['get_user'] = DocumentCurrentVersion::find($request->document)->get_document->get_user;
            
            Attachment::dispatch($document);
            
            //log user activity
            $this->userLog($request->accred, $document['id'], $request->instrument, 'attached this document to other indicator');

            DB::commit();
            return response()->json(
                $document
            );
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json($th->getMessage(), 400);
        }
    }
}
/* 

id:8
userId:1
file:"20230106020419.pdf"
type:"pdf"
title:"Final Exam SY 22-23"
description:"adfasdfasdf"
parent:null
review:null
isCurrent:1
created_at:"2023-01-06T02:04:26.000000Z"
updated_at:"2023-01-06T02:04:26.000000Z"
docuCurrentId:6
comment_count:0
get_user:Object
auth:1
avatar:null
designation:6
first_name:"Leomas"
id:1
instituteId:null
last_name:"Cardenio"
programId:null
status:null


*/