<?php

namespace Bundsgaard\ChatSupport\Storage;

class ConversationParticipant extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'chatsupport_chat_conversation_participants';

    protected $guarded = ['id'];

    public $timestamps = false;

    public function disconnect()
    {
        $this->disconnected_at = new \DateTime();
        $this->save();

        return $this;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
