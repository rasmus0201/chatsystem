<?php

namespace Bundsgaard\ChatSupport\Storage;

class RoomOpeningHour extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'chatsupport_chat_room_opening_hours';

    protected $casts = [
        'weekday' => 'integer',
    ];

    protected $guarded = ['id'];
}
