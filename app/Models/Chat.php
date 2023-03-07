<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
    use HasFactory;

    protected $fillable = [
        'sender',
        'receiver',
        'seen'
    ];

    public function senders()
    {
        return $this->belongsTo(User::class, 'sender');
    }

    public function receivers()
    {
        return $this->belongsTo(User::class, 'receiver');
    }
}
