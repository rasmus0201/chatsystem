<?php

namespace Bundsgaard\ChatSupport\Listeners;

use Bundsgaard\ChatSupport\Events\CloseEvent;
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
        // Get the connections to send to
        $receivers = $event->connections->getUnique(null, 'auth');

        $this->userListResponder
            ->withConnections($event->connections)
            ->withReceivers($receivers)
            ->respond();
    }
}
