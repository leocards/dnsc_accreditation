<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AreaSelfAccreditor extends Model
{
    use HasFactory;

    protected $fillable = [
        'userId',
        'instrumentId',
        'accredlvl',
    ];

    public function area() {
        return $this->belongsTo(Instrument::class, 'instrumentId');
    }

    public function selfAccreditor() {
        return $this->belongsTo(User::class, 'userId');
    }
}
