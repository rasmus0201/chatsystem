<?php

namespace Bundsgaard\ChatSupport\Responders;

class UserListResponder extends Responder
{
    public $eventType = 'user:list';

    /**
     * Respond to the user with data
     */
    public function respond()
    {
        // TODO
        // Refactor this to be called conversation list
        // and get conversations from db then link the conversation users to active connections

        $uniqueSessions = [];

        $connections = array_filter($this->connections, function($connection) use (&$uniqueSessions) {
            $sessionId = $connection->session['identifier'];

            if (isset($uniqueSessions[$sessionId])) {
                return false;
            }

            $uniqueSessions[$sessionId] = true;
            return true;
        });

        $sessions = array_column($connections, 'session');

        foreach ($this->receivers as $to) {
            $to->send(json_encode([
                'type' => $this->eventType,
                'message' => 'List of connections in room',
                'data' => ['users' => $sessions]
            ]));
        }
    }
}
