<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskAssign extends Model
{
    use HasFactory;

    protected $fillable = [
        'userId',
        'areaId',
        'instrumentId',
        'accredId',
        'due',
        'complete',
        'notified',
        'removed',
    ];

    public function getInstrument()
    {
        return $this->belongsTo(Instrument::class, 'instrumentId');
    }

    public function assignedMember()
    {
        return $this->belongsTo(User::class, 'userId');
    }

    public function instrument()
    {
        return $this->belongsTo(Instrument::class, 'instrumentId')
            ->select(['id', 'title', 'category', 'parent']);
    }

    public function accreditation()
    {
        return $this->belongsTo(Accreditation::class, 'accredId');
    }
}
