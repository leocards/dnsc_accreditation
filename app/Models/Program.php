<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Program extends Model
{
    use HasFactory;

    protected $fillable = [
        'abbreviation', 
        'program_name', 
        'instituteId', 
        'bot', 
        'established'
    ];

    public function assignedLevel()
    {
        return $this->hasMany(Accreditation::class, 'programId');
    }
}
