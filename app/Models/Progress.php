<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Progress extends Model
{
    use HasFactory;

    protected $table = 'progress';

    protected $fillable = [
        'instrumentId', 'parent', 'area', 'accredlvlId', 'isComplete', 'progress', 'isRemoved', 'rate'
    ];

    public function instrument()
    {
        return $this->belongsTo(Instrument::class, 'instrumentId');
    }
}
