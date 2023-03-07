<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Announcement extends Model
{
    use HasFactory;

    protected $fillable = [
        'userId',
        'title',
        'message',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'userId')->select(['id', DB::raw("CONCAT(first_name, ' ', last_name) as name"), 'avatar']);
    }
}
