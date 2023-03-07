<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SelfSurvey extends Model
{
    use HasFactory;

    protected $fillable = [
        'accredlvl',
        'rate'
    ];

    public function getAccred()
    {
        return $this->belongsTo(Accreditation::class, 'accredlvl');
    }

    public function getSurveyRate()
    {
        return $this->hasMany(SelfSurveyRate::class, 'surveyId');
    }
}
