<?php

namespace Bundsgaard\ChatSupport\Listeners;

use Bundsgaard\ChatSupport\Events\CloseEvent;
use Bundsgaard\ChatSupport\Storage\UserStatus;
use Bundsgaard\ChatSupport\Responders\ConversationListResponder;

class CloseListener
{
    public $eventType = 'close';

    private $conversationListResponder;

    public function __construct(ConversationListResponder $conversationListResponder)
    {
        $this->conversationListResponder = $conversationListResponder;
    }

    /**
     * Handle the event.
     *
     * @param  Event  $event
     * @return void
     */
    public function handle(CloseEvent $event)
    {
        if (!isset($event->connection->user)) {
            return;
        }

        $user = $event->connection->user;
        $roomId = $user->room_id;

        $user->status_id = UserStatus::DISCONNECTED; // Set status to disconnected
        $user->room_id = $user->agent ? $roomId : null; // Preserve room choice when agent.
        $user->save();

        $this->conversationListResponder
            ->withRoom($user->room_id)
            ->withReceivers($event->connections->agents($roomId))
            ->respond();
    }
}
