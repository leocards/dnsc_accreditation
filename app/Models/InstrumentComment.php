<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InstrumentComment extends Model
{
    use HasFactory;

    protected $fillable = [
        'userId',
        'instrumentId',
        'accredId',
        'comment',
    ];
}
