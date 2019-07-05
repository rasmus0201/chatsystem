<?php

namespace Bundsgaard\ChatSupport;

use Bundsgaard\ChatSupport\Storage\Conversation;

class Connections
{
    public $connections = [];

    private $exclude = [];

    public function add($conn)
    {
        $this->connections[$conn->resourceId] = $conn;
    }

    public function remove($conn)
    {
        unset($this->connections[$conn->resourceId]);
    }

    public function exclude($users)
    {
        if (!is_array($users)) {
            $users = [$users];
        }

        // Get user ids if user model is provided.
        for ($i = 0; $i < count($users); $i++) {
            if (is_integer($users[$i])) {
                continue;
            }

            // Set the user from a provided model/object.
            $users[$i] = $users[$i]->id;
        }

        $this->exclude = array_unique(array_merge($this->exclude, $users));

        return $this;
    }

    public function agents($roomId)
    {
        return array_filter($this->connections, function($connection) use ($roomId) {
            return !in_array($connection->user->id, $this->exclude) && $this->findActive($connection->user, $agent = true, $roomId);
        });
    }

    public function users($roomId)
    {
        return array_filter($this->connections, function($connection) use ($roomId) {
            return !in_array($connection->user->id, $this->exclude) && $this->findActive($connection->user, $agent = false, $roomId);
        });
    }

    public function activeParticipants($conversation)
    {
        if (is_integer($conversation)) {
            $conversation = Conversation::find($conversation);
        }

        $userIds = $conversation->activeParticipants()
                    ->select('user_id')
                    ->get()
                    ->pluck('user_id')
                    ->toArray();

        return array_filter($this->connections, function($connection) use ($userIds) {
            return !in_array($connection->user->id, $this->exclude) && in_array($connection->user->id, $userIds);
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
