<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SelfSurveyRate extends Model
{
    use HasFactory;

    protected $fillable = [
        'surveyId',
        'instrumentId',
        'areaId',
        'parent',
        'rate',
    ];

    public function getSurveyId()
    {
        return $this->belongsTo(SelfSurvey::class, 'surveyId');
    }

    public function getInstrument()
    {
        return $this->belongsTo(Instrument::class, 'instrumentId');
    }

    public function getArea()
    {
        return $this->belongsTo(Instrument::class, 'areaId');
    }
    
}
