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

    public function conversations()
    {
        $this->hasMany(Conversations::class);
    }

    public function activeConversations()
    {
        $this->hasMany(Conversations::class)->whereNull('closed_at');
    }
}
