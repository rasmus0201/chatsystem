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
}
