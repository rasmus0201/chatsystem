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

    public function get($sessionId)
    {
        return array_filter($this->connections, function($connection) use ($sessionId) {
            return $connection->session['session_id'] === $sessionId;
        });
    }

    /**
     * Method to get connections by user id.
     * Can ethier take a single user id, or an array of user ids.
     *
     * @param integer|integer[] $userId
     */
    public function getByUserId($userId)
    {
        if (!is_array($userId)) {
            $userId = [$userId];
        }

        $userId = array_unique($userId);

        return array_filter($this->connections, function($connection) use ($userId) {
            return in_array($connection->user->id, $userId);
        });
    }

    private function findActive($user, $agent, $roomId)
    {
        return $user->agent === $agent && $user->room_id === $roomId && $user->status->priority >= 8;
    }
}
