<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatConversation extends Model
{
    use HasFactory;

    protected $fillable = [
        'convoId',
        'sender',
        'message'
    ];

    public function sender() 
    {
        return $this->belongsTo(User::class, 'sender');
    }

    public function chated() 
    {
        return $this->belongsTo(ChatConversation::class, 'convoId');
    }
}
