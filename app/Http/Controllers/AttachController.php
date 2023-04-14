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
                ->where('evidence', json_encode($request['evidence']))
                ->first('id');

            $exist2 = DocumentCurrentVersion::where('accredlvl', $request->accred)
                ->where('id', $request->document)
                ->where('instrumentId', $request->instrument)
                ->where('evidence', json_encode($request['evidence']))
                ->first('id');

            if($exist || $exist2)
                throw new Exception("exist");

            AttachedDocument::create([
                'accredlvl' => $request->accred,
                'documentId' => $request->document,
                'instrumentId' => $request->instrument,
                'evidence' => json_encode($request['evidence']),
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
            return response()->json('Failed to attach document', 400);
        }
    }
}
