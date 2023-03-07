<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentVersion extends Model
{
    use HasFactory;

    protected $fillable = [
        'userId',
        'file',
        'type',
        'title',
        'description',
        'parent',
        'review',
        'isCurrent'
    ];
    
    public function get_user()
    {
        return $this->belongsTo(User::class, 'userId');
    }

    public function currentDocuments()
    {
        return $this->hasOne(DocumentCurrentVersion::class, 'documentId');
    }
}
