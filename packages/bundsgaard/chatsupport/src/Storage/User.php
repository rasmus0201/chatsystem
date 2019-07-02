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
        return $this->hasMany(Conversation::class);
    }

    public function activeConversations()
    {
        return $this->hasMany(Conversation::class)->whereNull('closed_at')->orderBy('created_at', 'DESC');
    }

    public function status()
    {
        return $this->belongsTo(UserStatus::class);
    }
}
