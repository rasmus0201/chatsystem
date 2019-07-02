<?php

namespace Bundsgaard\ChatSupport\Storage;

class UserStatus extends Model
{
    const BANNED = 1;
    const DISCONNECTED = 2;
    const INACTIVE = 3;
    const ACTIVE = 4;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'chatsupport_chat_user_statuses';

    protected $guarded = ['id'];
}
