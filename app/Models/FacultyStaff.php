<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FacultyStaff extends Model
{
    use HasFactory;

    protected $table = 'faculty_staff';

    protected $fillable = [
        'name',
        'designation',
        'programId',
        'instituteId',
    ];
}
