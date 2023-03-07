<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class UserLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'userId',
        'accredlvl',
        'documentId',
        'instrumentId',
        'details'
    ];

    public function getDocument()
    {
        return $this->belongsTo(DocumentVersion::class, 'documentId');
    }

    public function getInstrument()
    {
        return $this->belongsTo(Instrument::class, 'instrumentId');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'userId')->select([DB::raw("CONCAT(first_name, ' ', last_name) as name")]);
    }
}
