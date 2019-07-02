<?php

namespace Bundsgaard\ChatSupport;

class Connections
{
    public $connections = [];

    public function add($conn)
    {
        $this->connections[$conn->resourceId] = $conn;
    }

    public function remove($conn)
    {
        unset($this->connections[$conn->resourceId]);
    }

    public function agents($roomId)
    {
        return array_filter($this->connections, function($connection) use ($roomId) {
            return $this->findActive($connection->user, $agent = true, $roomId);
        });
    }

    public function users($roomId)
    {
        return array_filter($this->connections, function($connection) use ($roomId) {
            return $this->findActive($connection->user, $agent = false, $roomId);
        });
    }

    public function get($identifier)
    {
        return array_filter($this->connections, function($connection) use ($identifier) {
            return $connection->session['identifier'] === $identifier;
        });
    }

    private function findActive($user, $agent, $roomId)
    {
        return $user->agent === $agent && $user->room_id === $roomId && $user->status->priority >= 8;
    }
}
