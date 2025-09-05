<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Message extends Model
{
    protected $guarded = [];


    public function messageStatus()
    {
        return $this->hasMany(MessageStatus::class, 'message_id', 'id');
    }

    /**
     * Get the user associated with the Message
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function messageFile(): HasOne
    {
        return $this->hasOne(MessageFile::class, 'message_id', 'id');
    }


    public function getFormattedCreatedAtAttribute()
    {
        return $this->created_at->format('M j \a\t g:i A');
    }

    public function setMessageAttribute($value)
    {
        $this->attributes['message'] = htmlentities($value, ENT_QUOTES, 'UTF-8');
    }

    public function getMessageAttribute($value)
    {
        return html_entity_decode($value, ENT_QUOTES, 'UTF-8');
    }
}