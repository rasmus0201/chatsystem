<?php

namespace Bundsgaard\ChatSupport\Storage;

class User extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'chatsupport_chat_users';

    protected $casts = [
        'agent' => 'boolean'
    ];

    protected $guarded = ['id'];

    public function conversations()
    {
        return $this->hasMany(Conversations::class);
    }

    public function activeConversations()
    {
        return $this->hasMany(Conversations::class)->whereNull('closed_at');
    }

    public function status()
    {
        return $this->belongsTo(UserStatus::class);
    }
}
