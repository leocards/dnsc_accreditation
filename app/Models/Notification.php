<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'userId',
        'userNotifier',
        'documentId',
        'instrumentId',
        'seen',
        'isOwner',
        'action',
        'details'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'userNotifier')->select([DB::raw("CONCAT(first_name, ' ', last_name) as name")]);
    }
}
