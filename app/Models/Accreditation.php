<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Accreditation extends Model
{
    use HasFactory;

    protected $fillable = [
        'date_self_survey', 'date_actual_survey', 'restrict', 'status', 'instrumentId', 'programId', 'survey', 'selfsurvey'
    ];

    public function taggedPrograms()
    {
        return $this->belongsTo(Program::class, 'programId');
    }

    public function getLevelInstrument()
    {
        return $this->belongsTo(Instrument::class, 'instrumentId');
    }
}
