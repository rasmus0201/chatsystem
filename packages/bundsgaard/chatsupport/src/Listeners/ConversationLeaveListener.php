<?php

namespace Bundsgaard\ChatSupport\Listeners;

use Bundsgaard\ChatSupport\Events\MessageEvent;
use Bundsgaard\ChatSupport\Responders\MessageResponder;
use Bundsgaard\ChatSupport\Responders\ConversationListResponder;

class ConversationLeaveListener
{
    public $listen = 'conversation:leave';

    private $conversationListResponder;
    private $messageResponder;

    public function __construct(ConversationListResponder $conversationListResponder, MessageResponder $messageResponder)
    {
        $this->conversationListResponder = $conversationListResponder;
        $this->messageResponder = $messageResponder;
    }

    /**
     * Handle the event.
     *
     * @param  MessageEvent  $event
     * @return void
     */
    public function handle(MessageEvent $event)
    {
        if (!isset($event->data->conversation_id)) {
            return;
        }

        $user = $event->connection->user;

        if (!$conversation = $user->activeConversations()->where('id', $event->data->conversation_id)->first()) {
            return; // User diddn't have an active conversation with that id
        }

        $session = $event->connection->session;
        $roomId = $user->room_id; // Store the room id, as it can possibly be reset.
        $user->room_id = $user->agent ? $roomId : null; // Preserve room choice when agent.
        $user->save();

        // Store the last message in db (user disconnected / closed chat)
        $message = $conversation->exclude($user->id)->message($user->name . ' forlod chatten.', true);

        // Update the user to be disconnected participant
        $conversation->disconnect($user);

        // Close the conversation
        $conversation->close();

        // Update session
        $session['room_id'] = $user->room_id;
        $event->connection->session = $session;

        // Multicast the message to the active participants
        $activeParticipants = $event->connections->exclude($user)->activeParticipants($conversation);
        $this->messageResponder
            ->withReceivers($activeParticipants)
            ->respondWith($message);

        // Multicast the user list for this room to agents in this room.
        $this->conversationListResponder
            ->withRoom($roomId)
            ->withReceivers($event->connections->agents($roomId))
            ->respond();
    }
}
