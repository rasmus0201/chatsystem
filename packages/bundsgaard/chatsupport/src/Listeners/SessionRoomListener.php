<?php

namespace Bundsgaard\ChatSupport\Listeners;

use Bundsgaard\ChatSupport\Storage\Room;
use Bundsgaard\ChatSupport\Events\MessageEvent;
use Bundsgaard\ChatSupport\Responders\UserListResponder;

class SessionRoomListener
{
    public $eventType = 'session:room';

    private $userListResponder;

    public function __construct(UserListResponder $userListResponder)
    {
        $this->userListResponder = $userListResponder;
    }

    /**
     * Handle the event.
     *
     * @param  MessageEvent  $event
     * @return void
     */
    public function handle(MessageEvent $event)
    {
        if (!isset($event->data->room_id)) {
            $event->connection->close();
            return false; // Stop next listeners
        }

        $session = $event->connection->session;
        $user = $event->connection->user;

        $session['room_id'] = $user->room_id ? $user->room_id : $event->data->room_id;

        if (!$room = Room::find($session['room_id'])) {
            $event->connection->close();
            return false; // Stop next listeners
        }

        if (!$user->agent && !$user->activeConversations->first()) {
            $conversation = $user->conversations()->create([
                'room_id' => $session['room_id'],
            ]);

            $conversation->participants()->create([
                'user_id' => $user->id
            ]);

            $conversation->message('System: Venter på betjening fra ' . $room->name, true);
        }

        $user->room_id = $session['room_id'];
        $user->save();

        // Multicast the user list for this room to agents in this room.
        $this->userListResponder
            ->withConnections($event->connections->users($user->room_id))
            ->withReceivers($event->connections->agents($user->room_id))
            ->respond();
    }
}
