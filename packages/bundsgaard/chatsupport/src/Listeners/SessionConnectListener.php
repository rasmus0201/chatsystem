<?php

namespace Bundsgaard\ChatSupport\Listeners;

use Bundsgaard\ChatSupport\Storage\Room;
use Bundsgaard\ChatSupport\Events\MessageEvent;
use Bundsgaard\ChatSupport\Responders\UserListResponder;

class SessionConnectListener
{
    public $eventType = 'session:connect';

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
        $session = $event->connection->session;

        $session['agent'] = false;
        $session['name'] = isset($event->data->name) ? $event->data->name : null;
        $session['room_id'] = isset($event->data->room_id) ? $event->data->room_id : null;
        $session['language'] = isset($event->data->language) ? $event->data->language : null;
        $session['identifier'] = isset($event->data->identifier) ? $event->data->identifier : null;

        if (isset($event->data->credentials)) {
            $username = isset($event->data->credentials->username) ? $event->data->credentials->username : null;
            $password = isset($event->data->credentials->password) ? $event->data->credentials->password : null;

            if (!$this->auth($username, $password)) {
                $event->connection->close();

                return;
            }

            $session['agent'] = true;
        }

        if ($session['room_id'] === null) {
            $event->connection->close();
        }

        if (!Room::find($session['room_id'])) {
            $event->connection->close();
        }

        $event->connection->session = $session;

        // Get the connections to send to
        $receivers = $event->connections->getUnique(null, 'agent');

        // Add the new auth user to receive the user list.
        if ($session['agent'] === true) {
            $receivers[] = $event->connection;
        }

        $this->userListResponder
            ->withConnections($event->connections)
            ->withReceivers($receivers)
            ->respond();
    }

    private function auth($username, $password)
    {
        if ($username == 'test' && $password == 'test') {
            return true;
        }
    }
}
