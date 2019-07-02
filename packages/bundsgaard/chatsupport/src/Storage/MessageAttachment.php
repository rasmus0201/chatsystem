<?php

namespace Bundsgaard\ChatSupport\Storage;

class MessageAttachment extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'chatsupport_chat_message_attachments';

    protected $guarded = ['id'];
}
