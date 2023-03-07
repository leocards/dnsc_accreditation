<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Instrument extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 
        'description', 
        'attachment', 
        'parent', 
        'category', 
        'indicator', 
        'action',
        'exclude_rate'
    ];
}
