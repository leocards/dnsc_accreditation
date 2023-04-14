<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentCurrentVersion extends Model
{
    use HasFactory;

    protected $fillable = [
        'accredlvl',
        'instrumentId',
        'documentId',
        'isRemoved',
        'evidence'
    ];

    public function get_document()
    {
        return $this->belongsTo(DocumentVersion::class, 'documentId');
    }

    public function attached()
    {
        return $this->hasMany(AttachedDocument::class, 'documentId');
    }
}
