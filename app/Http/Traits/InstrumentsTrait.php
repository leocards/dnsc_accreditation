<?php
namespace App\Http\Traits;

use App\Models\AttachedDocument;
use App\Models\DocumentComment;
use App\Models\DocumentCurrentVersion;
use App\Models\Instrument;
use App\Models\InstrumentComment;
use App\Models\Progress;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Auth;

trait InstrumentsTrait {

    public function getInstruments ($area, $accredlvl = null, $rate=false)
    {
        Global $instruments;
        $instruments = collect([]);

        function getChildren($id, $accred = null, $rate=false)
        {
            global $instruments;
            
            $childrens = Instrument::where('parent', $id)->get();

            if($childrens->count() > 0 )
            {
                $childrens->makeHidden(['created_at', 'updated_at', 'action']);  
                
                if($childrens->first()->category != 'area'){
                    if($childrens->first()->category != 'ind'){ $childrens = $childrens->sortBy('title', SORT_NATURAL); $childrens->values()->all(); }
                    else $childrens = $childrens->sortBy('indicator');
                }

                foreach ($childrens as $child) {
                    if($accred && !$rate){
                        $child->progress = Progress::where('instrumentId', $child->id)->where('accredlvlId', $accred)->first(['id', 'progress', 'isComplete']);
                        $child->comment = InstrumentComment::where('instrumentId', $child->id)
                        ->where('accredId', $accred)->get(['id'])->count();
                    }

                    if($rate)
                    {
                        $child->rate = Progress::where('instrumentId', $child->id)->where('accredlvlId', $accred)->first(['id', 'rate']);
                        $child->comment = InstrumentComment::where('instrumentId', $child->id)
                        ->where('accredId', $accred)->where('userId', Auth::id())->get(['id'])->count();
                    }
                    
                    $instruments->push($child);

                    getChildren($child->id, $accred, $rate);
                }
            }
        }

        getChildren($area, $accredlvl, $rate);

        return $instruments;
    }

    public function getFilteredInstruments ($area, $accredlvl = null, $inst = null)
    {
        Global $instruments;
        $instruments = collect([]);

        function getQueryChild($id, $accred = null, $inst=null)
        {
            global $instruments;
            
            if($inst)
                $childrens = Instrument::where('parent', $id)->where('id', $inst)->get();
            else
                $childrens = Instrument::where('parent', $id)->get();

            if($childrens->count() > 0 )
            {
                $childrens->makeHidden(['created_at', 'updated_at', 'action', 'indicator']);                    

                foreach ($childrens as $child) {
                    if($accred){
                        $child->progress = Progress::where('instrumentId', $child->id)->where('accredlvlId', $accred)->first(['id', 'progress', 'isComplete']);
                        $child->comment = InstrumentComment::where('instrumentId', $child->id)
                        ->where('accredId', $accred)->get(['id'])->count();
                    }

                    $instruments->push($child);

                    getQueryChild($child->id, $accred);
                }
            }
        }

        getQueryChild($area, $accredlvl, $inst);

        return $instruments;
    }

    public function tree($rows, $parent)
    {
        function buildTree($elements, $parentId) {
            $branch = collect([]);

            foreach ($elements as $element) {
                if ($element->parent == $parentId) {
                    $children = buildTree($elements, $element->id);
                    if ($children) {
                        $element->children = $children;
                    }
                    $branch->push($element);
                }
            }
        
            return $branch;
        }
        
        return buildTree($rows, $parent);
    }

    public function getDocuments($instrument, $accredlvl)
    {
        $doc = DocumentCurrentVersion::where('accredlvl', $accredlvl)
        ->where('instrumentId', $instrument)
        ->whereNull('isRemoved')
        ->get();
        
        $attached = $this->getAttached($instrument, $accredlvl);

        $doc->push(...$attached);

        $document = $doc->map(function ($docu) {
            $document = $docu->get_document;
            $document->docuCurrentId = $docu->id;
            $document->evidence = $docu->evidence;
            if(isset($docu->attachedId))
                $document->isAttached = $docu->attachedId;

            return $document;
        })->map(function ($docs) {
            $docu = $docs;
            
            if(Auth::user()->auth == 5)
            {
                $docu->comment_count = DocumentComment::where('documentId', $docs->docuCurrentId)
                    ->where('userId', Auth::id())
                    ->get(['id'])
                    ->count();
            }else{
                $docu->comment_count = DocumentComment::where('documentId', $docs->docuCurrentId)->get(['id'])->count();
                $docu->get_user = User::find($docs->userId)->makeHidden(['created_at', 'updated_at']);
            }

            return $docs;
        });

        return $document->makeHidden(['isRemoved'])->sortBy('created_at', SORT_NATURAL)->values()->all();
    }

    public function buildTreeInstruments ($area, $accredId=null, $rate=false, $instSelect = null)
    {
        if($instSelect)
            return $this->tree(
                $this->getFilteredInstruments($area, $accredId, $instSelect), $area);
        else
            return $this->tree(
                $this->getInstruments($area, $accredId, $rate), $area);
    }

    public function getParents($inst, $res=false, $callBack=null)
    {
        $instruments = collect([]);

        $parent = Instrument::find($inst);
        $parent?$parent->makeHidden(['created_at', 'updated_at']):'';

        while ($parent->category != 'lvl') {
            $instruments->push($parent);
            $callBack?$callBack($parent):'';
            $parent = Instrument::find($parent->parent);//category
            $parent?$parent->makeHidden(['created_at', 'updated_at']):'';
        }

        if($res)
            return $instruments;
    }

    function getAttached($instrument, $accredlvl)
    {
        return DocumentCurrentVersion::join('attached_documents as ad', 'document_current_versions.id', '=', 'ad.documentId')->where('ad.accredlvl', $accredlvl)
        ->where('ad.instrumentId', $instrument)
        ->whereNull('ad.isRemoved')
        ->get([
            'document_current_versions.id', 
            'ad.id as attachedId',
            'ad.accredlvl', 
            'ad.instrumentId',
            'ad.evidence',
            'document_current_versions.documentId', 
            'document_current_versions.isRemoved', 
            'document_current_versions.created_at', 
            'document_current_versions.updated_at',
        ]);
    }
}