<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MessageStatus extends Model
{
    protected $guarded = [];


    public function message()
    {
        return $this->belongsTo(Message::class, 'message_id', 'id');
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id', 'id');
    }
    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id', 'id');
    }
}
