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

    protected $hidden = [
        'id',
        'room_id',
        'agent',
        'user_id',
        'created_at',
        'updated_at',
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

    public function createConversation($room)
    {
        $conversation = $this->conversations()->create([
            'room_id' => $room->id,
        ]);

        $conversation->participants()->create([
            'user_id' => $this->id
        ]);

        // TODO Remove this because it's actually just a status of the "queue"
        // this should be a sticky message until the user is assigned.
        $conversation->message('Venter på betjening fra ' . $room->name, true);

        return $conversation;
    }
}
