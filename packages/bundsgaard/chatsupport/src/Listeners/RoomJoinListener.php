<?php

namespace Bundsgaard\ChatSupport\Listeners;

use Bundsgaard\ChatSupport\Storage\Room;
use Bundsgaard\ChatSupport\Events\MessageEvent;
use Bundsgaard\ChatSupport\Responders\ConversationListResponder;
use Bundsgaard\ChatSupport\Responders\ConversationResponder;

class RoomJoinListener
{
<<<<<<< HEAD:packages/bundsgaard/chatsupport/src/Listeners/SessionRoomListener.php
    public $listen = 'session:room';
=======
    public $eventType = 'room:join';
>>>>>>> f5c50b7efe0d55f9546e29e608951b9b17a8cb81:packages/bundsgaard/chatsupport/src/Listeners/RoomJoinListener.php

    private $conversationListResponder;
    private $conversationResponder;

    public function __construct(ConversationListResponder $conversationListResponder, ConversationResponder $conversationResponder)
    {
        $this->conversationListResponder = $conversationListResponder;
        $this->conversationResponder = $conversationResponder;
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

        if (!$room = Room::find($event->data->room_id)) {
            $event->connection->close();
            return false; // Stop next listeners
        }

        // Check that this user is not an agent
        // Then get any active conversations or create a new one.
        if (!$user->agent && !$conversation = $user->activeConversations->first()) {
            $conversation = $user->conversations()->create([
                'room_id' => $event->data->room_id,
            ]);

            $conversation->participants()->create([
                'user_id' => $user->id
            ]);

            $conversation->message('Venter på betjening fra ' . $room->name, true);
            $session['room_id'] = $conversation->room_id;

            // Send the conversation to the user.
            $this->conversationResponder
                ->withReceiver($event->connection)
                ->withConversation($conversation)
                ->respond();
        } else {
            $session['room_id'] = $event->data->room_id;
        }

        // Sync the room
        $event->connection->session = $session;

        // Save the user current room_id
        $user->room_id = $session['room_id'];
        $user->save();

        // Multicast the user list for this room to agents in this room.
        $this->conversationListResponder
            ->withRoom($user->room_id)
            ->withReceivers($event->connections->agents($user->room_id))
            ->respond();
    }
}
