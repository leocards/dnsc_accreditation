<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Share extends Model
{
    use HasFactory;

    protected $fillable = [
        'userId',
        'documentId',
        'isRemoved'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'userId');
    }

    public function documents()
    {
        return $this->belongsTo(DocumentCurrentVersion::class, 'documentId');
    }
}
