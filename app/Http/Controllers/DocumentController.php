<?php

namespace App\Http\Controllers;

use App\Http\Traits\InstrumentsTrait;
use App\Http\Traits\NotificationTrait;
use App\Http\Traits\UserTrait;
use App\Models\Accreditation;
use App\Models\AreaAssign;
use App\Models\AttachedDocument;
use App\Models\DocumentComment;
use App\Models\DocumentCurrentVersion;
use App\Models\DocumentVersion;
use App\Models\Institute;
use App\Models\Instrument;
use App\Models\Program;
use App\Models\Share;
use App\Models\TaskAssign;
use App\Models\TemporaryFile;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DocumentController extends Controller
{
    use InstrumentsTrait;
    use NotificationTrait;
    use UserTrait;

    public function index(Request $request)
    {
        return Inertia::render('Documents', [
            'documents' => collect([...DocumentVersion::query()
                ->when($request->search, function ($query, $search) {
                    $query->whereNotNull('isCurrent')
                        ->where('title', 'LIKE', "%{$search}%")
                        ->where('userId', Auth::id())
                        ->orWhere('description', 'LIKE', "%{$search}%")
                        ->whereNotNull('isCurrent')
                        ->where('userId', Auth::id());
                })
                ->whereNotNull('isCurrent')
                ->where('userId', Auth::id())
                ->limit(50)
                ->get(['id', 'title', 'description', 'type', 'file', 'updated_at', 'userId', 'created_at'])
                ->filter(function ($doc) {
                    $docu = $doc->currentDocuments;
                    if(!$docu->isRemoved)
                    {
                        return $doc;
                    }
                })
                ->map(function ($docu) {
                    $currDoc = $docu->currentDocuments;
                    $docu->docuCurrentId = $currDoc->id;
                    $docu->accredId = $currDoc->accredlvl;
                    $docu->instrumentId = collect([ "id" => $currDoc->instrumentId, "title" => Instrument::find($currDoc->instrumentId)->title ]);
                    $docu->comment = DocumentComment::where('documentId', $currDoc->id)->get()->count();
                    $docu->get_user = $docu->get_user->only('first_name', 'last_name');
                    $docu->area = $this->getArea($currDoc->instrumentId)->last()->id;

                    return $docu->only(
                        'id', 'title', 'type', 'docuCurrentId', 
                            'updated_at', 'comment', 'instrumentId',
                            'accredId', 'description', 'file', 'get_user', 'area', 'created_at'
                    );
                })]),
            'search' => $request->search
        ]);
    }

    public function lazyLoadDocument(Request $request)
    {
        return response()
            ->json(
                DocumentVersion::query()
                    ->when($request->search, function ($query, $search) {
                        $query->whereNotNull('isCurrent')
                            ->where('title', 'LIKE', "%{$search}%")
                            ->where('userId', Auth::id())
                            ->orWhere('description', 'LIKE', "%{$search}%")
                            ->whereNotNull('isCurrent')
                            ->where('userId', Auth::id());
                    })
                    ->whereNotNull('isCurrent')
                    ->where('userId', Auth::id())
                    ->paginate(50)
                    ->map(function ($docu) {
                        $currDoc = $docu->currentDocuments;
                        $docu->docuCurrentId = $currDoc->id;
                        $docu->accredId = $currDoc->accredlvl;
                        $docu->instrumentId = collect([ "id" => $currDoc->instrumentId, "title" => Instrument::find($currDoc->instrumentId)->title ]);
                        $docu->comment = DocumentComment::where('documentId', $currDoc->id)->get()->count();
                        $docu->get_user = $docu->get_user->only('first_name', 'last_name');
                        $docu->area = $this->getArea($currDoc->instrumentId)->last()->id;
                        return $docu->only(
                            'id', 'title', 'type', 'docuCurrentId', 
                            'updated_at', 'comment', 'instrumentId', 'area',
                            'accredId', 'description', 'file', 'get_user', 'created_at'
                        );
                    })
            );
    }

    public function sharedIndex(Request $request)
    {

        $shared = Share::query()
            ->when($request->search, function ($query, $search) {
                $query->join('document_current_versions as dcv', 'dcv.id', '=', 'shares.documentId')
                    ->join('document_versions as dv', 'dv.id', '=', 'dcv.documentId')
                    ->where('shares.userId', Auth::id())
                    ->where('dv.title', 'LIKE', "%{$search}%")
                    ->whereNull('dcv.isRemoved')
                    ->whereNotNull('dv.isCurrent')
                    ->orWhere('dv.description', 'LIKE', "%{$search}%")
                    ->where('shares.userId', Auth::id())
                    ->whereNull('dcv.isRemoved')
                    ->whereNotNull('dv.isCurrent');
            })
            ->where('shares.userId', Auth::id())
            ->whereNull('shares.isRemoved')
            ->limit(30)
            ->get(['shares.documentId'])
            ->map(function ($value) use ($request) {
                $docu = $value->documents->get_document;
                $accreditDocu = $value->documents;
                $value->id = $docu->id;
                $value->type = $docu->type;
                $value->file = $docu->file;
                $value->title = $docu->title;
                $value->updated_at = $docu->updated_at;
                $value->description = $docu->description;
                $value->docuCurrentId = $accreditDocu->id;
                $value->accredId = $accreditDocu->accredlvl;
                $value->instrumentId = collect([ "id" => $accreditDocu->instrumentId ]);
                $value->get_user = $docu->get_user->only('first_name', 'last_name');
                $value->comment = DocumentComment::where('documentId', $value->documentId)->get()->count();

                return $value->only('id', 'title', 'description', 'file', 'type', 'updated_at', 'comment', 'instrumentId', 'docuCurrentId', 'accredId', 'get_user');
            });

        return Inertia::render('Documents', [
            'documents' => $shared,
            'search' => $request->search
        ]);
    }
    
    public function temporaryUpload(Request $request, $accredlvl)
    {   
        if($request->hasFile('fileUpload'))
        {
            $file = $request->file('fileUpload');//get file
            $newfilename = Carbon::now()->format('YmdHis').'.'.$file->getClientOriginalExtension();//generate new file name
            $folder = uniqid().'-'. now()->timestamp;//genearate folder name
            $file->storeAs('tmp/'.$folder, $newfilename);//store file inside generated folder
            
            //store file in the temporary table
            $tmpUp = TemporaryFile::create([
                'folder'=>$folder,
                'file'=>$newfilename
            ]);

            $file_upload = collect([]);
            $file_upload['id'] = $tmpUp->id;
            $file_upload['file_name'] = explode(".", $request->file('fileUpload')->getClientOriginalName())[0];
            $file_upload['file_type'] = $request->file('fileUpload')->getClientOriginalExtension();

            return $file_upload;
        }

        return '';
    }

    public function revertTemporaryUpload(Request $request)
    {   
        try {
            $revert = TemporaryFile::find($request->tempId);
    
            $path = storage_path('app/tmp/'.$revert->folder.'/'.$revert->file);
            unlink($path);
            rmdir(storage_path('app/tmp/'.$revert->folder));
    
            $revert->delete();

            return response()->json(['revert'=>'success']);
        } catch (\Throwable $e) {
            return response()->json(['revert'=>$e->getMessage()]);
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => ['required'],
        ]);

        DB::beginTransaction();
        try {
            $documentFile = null;
            $file = null;
            
            if($request->documentType == 'link')
            {
                $documentFile = $request->link;
            }else{
                $file = TemporaryFile::find($request->tempId);
                $documentFile = $file->file;
            }

            $document = DocumentVersion::create([
                'userId' => Auth::id(),
                'file' => $documentFile,
                'type' => $request->documentType,
                'title' => $request->title,
                'description' => $request->description,
                'parent' => null,
                'isCurrent' => true
            ]);

            $currentDocu = DocumentCurrentVersion::create([
                'accredlvl' => $request->accredlvl,
                'instrumentId' => $request->instrument,
                'documentId' => $document->id,
            ]);

            $request->documentType == 'link' ? '' : $this->processFile($file->folder, $file->file);

            $areaTfc = $this->getArea($request->instrument)->last()->id;
            $tfc = AreaAssign::where('areaId', $areaTfc)->where('levelId', $request->accredlvl)->where('role', 'tfc')->first();

            $docuUpload = collect([
                "userId" => $tfc->userId,
                "docuCurrId" => $currentDocu->id,
                "details" => 'Uploaded new document '. $request->title
                
            ]);

            //log user activity getArea
            $this->userLog($request->accredlvl, $document->id, $request->instrument, 'uploaded this document');

            $this->DocumentUpload($docuUpload);

            DB::commit(); 
            return back()->with('success', 'Uploaded successfully');
        } catch (\Throwable $th) {

            DB::rollBack();
            return back()->with('error', $th->getMessage());
        }
    }

    public function update(Request $request)
    {
        $request->validate([
            'title' => ['required'],
        ]);

        try{
            DB::transaction(function () use ($request) {
                $update = DocumentVersion::where('id', $request->id)
                            ->update([
                                'title'=>$request->title,
                                'description'=>$request->description
                            ]);

                //log user activity delete
                $this->userLog($request->accredlvl, $request->id, $request->instrument, 'updated this document');
            });

            return back()->with('success', 'Successfully updated');
        }catch (\Throwable $th) {
            return back()->with('error', 'Failed to update');

        }
    }

    public function validateDocument(Request $request)
    {
        DB::beginTransaction();
        try {

            $docu = DocumentVersion::find($request->documentId);
            $docu->review = $request->review;
            $docu->save();

            $currentDocu = DocumentCurrentVersion::where('documentId', $request->documentId)->first();

            $document = collect([
                "userId" => $docu->userId,
                "docuCurrId" => $currentDocu->id,
                "details" => 'Validate the document you uploaded named '. $docu->title .' as '.$request->review
            ]);

            $this->DocumentReview($document);

            DB::commit();

            return response()->json(['validate'=>$docu]);

        }catch (\Throwable $th) {
            DB::rollBack();

            return response()->json(['error'=>$th->getMessage()], 400);
        }
    }

    public function downloadDocu($id)
    {
        $file = DocumentVersion::find($id);

        if($file->type == 'link')
            return back()->with('error', 'You cannot download link');

        $filePath = public_path('storage/files/'.$file->file);

        return response()->download($filePath, $file->title.'.'.$file->type);
    }

    public function getComment(Request $request)
    {
        try {
            if(Auth::user()->auth == 5){
                return response()->json([
                    'comments' => DocumentComment::where('documentId', $request->documentCurrentId)
                    ->where('userId', Auth::id())
                    ->get()->map(function ($val) {
                        $comment = $val;
                        $comment->user = User::find($val->userId)->only('first_name', 'last_name', 'avatar');
                        return $comment;
                    })
                ]);
            }else {
                return response()->json([
                    'comments' => DocumentComment::where('documentId', $request->documentCurrentId)
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

    //document comment
    public function storeDocument(Request $request)
    {
        DB::beginTransaction();
        try {
            //verify document has character
            if(strlen(trim($request->comment)) > 0){
                //store comment
                $comment =  DocumentComment::create([
                    'userId' => Auth::id(),
                    'instrumentId' => $request->instrumentId,
                    'accredId' => $request->accredlvl,
                    'documentId' => $request->documentId,
                    'comment' => $request->comment
                ]);

                //get commentor's name
                $comment->user = collect(['first_name' => Auth::user()->first_name, 'last_name' => Auth::user()->last_name]);

                $document = $comment->currentDocument->get_document;//get the document

                //get all the user to notify when user commented
                $users = $this->getInvolvedUser($request->area, $request->accredlvl, $document->userId);

                $collection = collect([]);

                foreach ($users as $key => $value) {
                    $new = collect(["userId" => $value, "docuCurrId" => $request->documentId, "title" => $document->title]);
                    $collection->push($new);//push the user involved
                }

                //notify each user involve
                foreach ($collection as $key => $value) {
                    $this->DocumentComment($value, $document->userId);
                }

                //log user activity
                $this->userLog($request->accredlvl, $document->id, $request->instrumentId, 'commented on this document');

            }else{
                throw new Exception('must contain character');
            }

            DB::commit();
            return response()->json(['response' => $comment]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json(['response' => $th->getMessage()], 400);
        }
    }

    public function getVersions(Request $request)
    {
        try {

            return response()->json([
                'versions' => DocumentVersion::where('parent', $request->id)
                    ->get(['id', 'title', 'created_at', 'updated_at', 'userId'])
                    ->map(function ($version) {
                        $version->user = User::find($version->userId)->only('id', 'first_name', 'last_name');
                        return $version;
                    })->sortBy('created_at', SORT_NATURAL)->values()->all()
            ]);

        } catch (\Throwable $th) {
            return response()->json(['error' => $th], 400);
        }
    }

    public function uploadNewVersion(Request $request)
    {
        if($request->link) { $request->validate(['link'=>'required|url']); }
        else
            $request->validate([
                'title' => ['required'],
            ]);

        DB::beginTransaction();
        try {
            $documentFile = null;
            $file = null;
            
            if($request->documentType == 'link')
            {
                $documentFile = $request->link;
            }else{
                $file = TemporaryFile::find($request->tempId);
                $documentFile = $file->file;
            }

            $document = DocumentVersion::create([
                'userId' => Auth::id(),
                'file' => $documentFile,
                'type' => $request->documentType,
                'title' => $request->title,
                'description' => $request->description,
                'parent' => null,
                'isCurrent' => true
            ]);

            //set current document to old document
            $currentToOld = DocumentVersion::find($request->parent);
            $currentToOld->isCurrent = null;
            $currentToOld->parent = $document->id;
            $currentToOld->save();

            $get_current = DocumentCurrentVersion::where('documentId', $request->parent)->first();

            $current = DocumentCurrentVersion::find($get_current->id);
            $current->documentId = $document->id;
            $current->save();

            DocumentVersion::where('parent', $request->parent)
                ->update(['parent'=>$document->id, 'updated_at'=>DB::raw('updated_at')]);

            $request->documentType == 'link' ? '' : $this->processFile($file->folder, $file->file);

            //log user activity
            $this->userLog($current->accredlvl, $document->id, $current->instrumentId, 'uploaded new version of this document');

            DB::commit(); 
            return back()->with('success', 'Uploaded successfully');
        } catch (\Throwable $th) {

            DB::rollBack();
            return back()->with('error', $th->getMessage());
        }
    }

    public function setAsCurrent(Request $request)
    {
        DB::beginTransaction();
        try {

            $currentToOld = DocumentVersion::find($request->current);
            $currentToOld->isCurrent = null;
            $currentToOld->parent = $request->newCurrent;
            $currentToOld->save();

            $oldToCurrent = DocumentVersion::find($request->newCurrent);
            $oldToCurrent->isCurrent = true;
            $oldToCurrent->parent = null;
            $oldToCurrent->save();

            $get_current = DocumentCurrentVersion::where('documentId', $request->current)->first();

            $doc = DocumentCurrentVersion::find($get_current->id);
            $doc->documentId = $request->newCurrent;
            $doc->save();
            
            DocumentVersion::where('parent', $request->current)
                ->update(['parent'=>$request->newCurrent, 'updated_at'=>DB::raw('updated_at')]);

            //log user activity
            $this->userLog($doc->accredlvl, $request->newCurrent, $get_current->instrumentId, 'Set old version of document to current version');

            DB::commit(); 
            return back()->with('success', 'Changes saved');

        } catch (\Throwable $th) {
            DB::rollBack();
            return back()->with('error', $th->getMessage());
        }
    }

    public function getInstrumentDocuments(Request $request)
    {
        return response()->json([
            'documents' => $this->getDocuments($request->instrument, $request->accredlvl)
        ]);
    }

    public function removeDocument(Request $request)
    {
        try {

            DB::transaction(function () use ($request) {
                $remove = DocumentCurrentVersion::find($request->id);
                $remove->isRemoved = true;
                $remove->save();

                Share::where('documentId', $request->id)
                    ->update(['isRemoved' => true]);
                
                AttachedDocument::where('documentId', $request->id)
                    ->update(['isRemoved' => true]);

            });

            return back()->with('success', 'Document removed successfully');
        } catch (\Throwable $th) {
            return back()->with('error', 'Failed to remove document');
        }
    }

    function processFile($folder, $file)
    {
        $path = storage_path('app/tmp/'.$folder.'/'.$file);

        if(file_exists($path))
        {
            copy($path, public_path('storage/files/'.$file));
            unlink($path);
            rmdir(storage_path('app/tmp/'.$folder));
        }
    }

    function getInvolvedUser($area, $accredId, $owner)
    {
        $users = collect([]);

        $program = Accreditation::find($accredId)
            ->taggedPrograms;
        
        $assignedUser = AreaAssign::where('areaId', $area)
            ->where('levelId', $accredId)
            ->where('userId', Auth::id())
            ->first();

        if(Auth::user()->auth == 5){
            /* $progChair = User::where('auth', 4)
                ->where('programId', $program->id)
                ->where('instituteId', $program->instituteId)
                ->whereNot('id', Auth::id())
                ->first();
            
            $dean = User::where('auth', 3)
                ->whereNot('id', Auth::id())
                ->where('instituteId', $program->instituteId)
                ->first(); 
                
            if($progChair)
                if(!$assigned->contains($progChair->id))
                {
                    $users->push($progChair->id);
                }
            
            if($dean)
                if(!$assigned->contains($dean->id))
                {
                    $users->push($dean->id);
                }
                */

            $assigned = AreaAssign::where('areaId', $area)
                ->where('levelId', $accredId)
                ->whereNotIn('userId', [Auth::id()/* , $dean->id, $progChair->id */])
                ->where('userId', $owner)
                ->orWhere('role', 'tfc')
                ->where('areaId', $area)
                ->where('levelId', $accredId)
                ->whereNotIn('userId', [Auth::id()/* , $dean->id, $progChair->id */])
                ->get(['userId']);
        }else{
            if($assignedUser)
            {
                if($assignedUser->role == 'tfc' && $owner != Auth::id())
                {
                    $assigned = AreaAssign::where('areaId', $area)
                        ->where('levelId', $accredId)
                        ->where('userId', $owner)
                        ->get(['userId']);
                }else if($assignedUser->role == 'member' && $owner == Auth::id()){
                    $assigned = AreaAssign::where('areaId', $area)
                        ->where('levelId', $accredId)
                        ->where('role', 'tfc')
                        ->get(['userId']);
                }else if($assignedUser->role == 'member' && $owner != Auth::id()){
                    $assigned = AreaAssign::where('areaId', $area)
                        ->where('levelId', $accredId)
                        ->where('userId', $owner)
                        ->orWhere('role', 'tfc')
                        ->where('areaId', $area)
                        ->where('levelId', $accredId)
                        ->get(['userId']);
                }else{
                    $assigned = [];
                }
            }else{
                $assigned = AreaAssign::where('areaId', $area)
                        ->where('levelId', $accredId)
                        ->where('userId', $owner)
                        ->orWhere('role', 'tfc')
                        ->where('areaId', $area)
                        ->where('levelId', $accredId)
                        ->get(['userId']);
            }

        }

        foreach ($assigned as $value) {
            $users->push($value->userId);
        }
        
        return $users;
    }

    function getAreaOfInstrument($id)
    {
        return $this->getParents($id, true, null)->last()['id'];
    }

    function getArea($id)
    {
        Global $instruments;
        $instruments = collect([]);

        $parent = Instrument::find($id)->makeHidden(['created_at', 'updated_at']);

        while ($parent->category != 'lvl' && $parent->parent) {
            $instruments->push($parent);
            $parent = Instrument::find($parent->parent)->makeHidden(['created_at', 'updated_at']);
        }

        return $instruments;
    }
}
