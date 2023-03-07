<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttachedDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'documentId',
        'instrumentId',
        'accredlvl',
        'isRemoved'
    ];

    public function getCurrentDocument()
    {
        return $this->belongsTo(DocumentCurrentVersion::class, 'documentId');
    }
}
