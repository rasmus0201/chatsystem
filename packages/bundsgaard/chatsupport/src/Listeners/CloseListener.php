<?php

namespace Bundsgaard\ChatSupport\Listeners;

use Bundsgaard\ChatSupport\Events\CloseEvent;
use Bundsgaard\ChatSupport\Storage\UserStatus;
use Bundsgaard\ChatSupport\Responders\UserListResponder;

class CloseListener
{
    public $eventType = 'close';

    private $userListResponder;

    public function __construct(UserListResponder $userListResponder)
    {
        $this->userListResponder = $userListResponder;
    }

    /**
     * Handle the event.
     *
     * @param  Event  $event
     * @return void
     */
    public function handle(CloseEvent $event)
    {
        $roomId = $event->connection->user->room_id;

        $event->connection->user->status_id = UserStatus::DISCONNECTED;
        $event->connection->user->room_id = null;
        $event->connection->user->save();

        $this->userListResponder
            ->withConnections($event->connections->users($roomId))
            ->withReceivers($event->connections->agents($roomId))
            ->respond();
    }
}
