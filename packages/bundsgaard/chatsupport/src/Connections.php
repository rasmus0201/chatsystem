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

    public function getAll($identifier = null)
    {
        return array_filter($this->connections, function($connection) use ($identifier) {
            $connectionId = $connection->session['identifier'];

            // We are not getting a specific user
            if ($identifier === null) {
                $identifier = $connectionId;
            }

            return $connectionId === $identifier;
        });
    }

    public function get($identifier = null, $type = null)
    {
        return array_filter($this->connections, function($connection) use ($identifier, $type) {
            $agent = $connection->session['agent'];
            $connectionId = $connection->session['identifier'];

            // We are not getting a specific user
            if ($identifier === null) {
                $identifier = $connectionId;
            }

            if ($type === 'agent') {
                return $agent && $connectionId === $identifier;
            }

            return !$agent && $connectionId === $identifier;
        });
    }

    public function getUnique($identifier = null, $type = null)
    {
        $uniqueSessions = [
            'agent' => [],
            'guest' => [],
        ];

        // Only 1 session pr. user type pr. connection
        $sessions = array_filter($this->connections, function($connection) use ($identifier, $type, &$uniqueSessions) {
            $agent = $connection->session['agent'];
            $connectionId = $connection->session['identifier'];
            $uniqueAgent = !isset($uniqueSessions['agent'][$connectionId]);
            $uniqueGuest = !isset($uniqueSessions['guest'][$connectionId]);

            if ($uniqueAgent && $agent) {
                $uniqueSessions['agent'][$connectionId] = true;
            }

            if ($uniqueGuest && !$agent) {
                $uniqueSessions['guest'][$connectionId] = true;
            }

            // We are not getting a specific user
            if ($identifier === null) {
                $identifier = $connectionId;
            }

            if ($type === 'agent') {
                return $agent && $connectionId == $identifier && $uniqueAgent;
            }

            return !$agent && $connectionId == $identifier && $uniqueGuest;
        });

        if (empty($sessions)) {
            return [];
        }

        if ($identifier) {
            return $sessions[0];
        }

        return $sessions;
    }
}
