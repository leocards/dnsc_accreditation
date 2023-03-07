<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentComment extends Model
{
    use HasFactory;

    protected $fillable = [
        'userId',
        'instrumentId',
        'accredId',
        'documentId',
        'comment',
    ];

    public function currentDocument()
    {
        return $this->belongsTo(DocumentCurrentVersion::class, 'documentId');
    }
}
