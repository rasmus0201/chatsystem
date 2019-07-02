<?php

namespace Bundsgaard\ChatSupport\Storage;

class Message extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'chatsupport_chat_messages';

    protected $guarded = ['id'];

    public function receivers()
    {
        return $this->hasMany(MessageReceiver::class);
    }

    public function attachments()
    {
        return $this->hasMany(MessageAttachment::class);
    }
}
