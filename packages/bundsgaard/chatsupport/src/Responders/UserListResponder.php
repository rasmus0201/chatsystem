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
        $users = $this->connections->getUnique();

        $sessions = array_values(array_map(function($user) {
            return $user->session;
        }, $users));

        foreach ($this->receivers as $to) {
            $to->send(json_encode([
                'type' => $this->eventType,
                'message' => 'List of connected users',
                'data' => ['users' => $sessions]
            ]));
        }
    }
}
