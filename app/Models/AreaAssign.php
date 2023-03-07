<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AreaAssign extends Model
{
    use HasFactory;

    protected $fillable = [
        'userId', 'areaId', 'parent', 'role', 'action', 'levelId'
    ];

    public function userAssigned()
    {
        return $this->belongsTo(User::class, 'userId');
    }

    public function assignedAccreditation()
    {
        return $this->belongsTo(Accreditation::class, 'levelId');
    }

    public function instruments()
    {
        return $this->belongsTo(Instrument::class, 'areaId');
    }

}
