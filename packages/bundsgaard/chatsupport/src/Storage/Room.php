<?php

namespace Bundsgaard\ChatSupport\Storage;

class Room extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'chatsupport_chat_rooms';

    protected $casts = [
        'closed' => 'boolean'
    ];

    public function openingHours()
    {
        return $this->hasMany(RoomOpeningHour::class)->where('expires_at', '>', new \DateTime());
    }

    public function agents()
    {
        return $this->hasMany(User::class)->where('agent', 1);
    }

    public function conversations()
    {
        return $this->hasMany(Conversation::class)->whereNull('closed_at');
    }

    public function isOpen()
    {
        $now = new \DateTime();

        return !$this->closed && $this->openingHours()
            ->where('weekday', '=', $now->format('w'))
            ->where('to', '>', $now->format('H:i:s'))
            ->where('from', '<=', $now->format('H:i:s'))
            ->count() > 0;
    }
}
