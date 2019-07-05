<?php

namespace Bundsgaard\ChatSupport\Listeners;

use Bundsgaard\ChatSupport\Storage\Room;
use Bundsgaard\ChatSupport\Events\MessageEvent;
use Bundsgaard\ChatSupport\Responders\ConversationListResponder;
use Bundsgaard\ChatSupport\Responders\ConversationResponder;

class RoomJoinListener
{
    public $listen = 'room:join';

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
            return;
        }

        $session = $event->connection->session;
        $user = $event->connection->user;

        if (!$room = Room::find($event->data->room_id)) {
            return;
        }

        // Check that this user is not an agent
        // Then get any active conversations or create a new one.
        if (!$user->agent) {
            if (!$conversation = $user->activeConversations->first()) {
                $conversation = $user->createConversation($room);
            }

            $session['room_id'] = $conversation->room_id;

            // Send the conversation to the user.
            $this->conversationResponder
                ->withReceiver($event->connection)
                ->respondWith($conversation);
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
