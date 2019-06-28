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
            $auth = $connection->session['auth'];
            $connectionId = $connection->session['identifier'];

            // We are not getting a specific user
            if ($identifier === null) {
                $identifier = $connectionId;
            }

            if ($type === 'auth') {
                return $auth && $connectionId === $identifier;
            }

            return !$auth && $connectionId === $identifier;
        });
    }

    public function getUnique($identifier = null, $type = null)
    {
        $uniqueSessions = [
            'auth' => [],
            'guest' => [],
        ];

        // Only 1 session pr. user type pr. connection
        $sessions = array_filter($this->connections, function($connection) use ($identifier, $type, &$uniqueSessions) {
            $auth = $connection->session['auth'];
            $connectionId = $connection->session['identifier'];
            $uniqueAuth = !isset($uniqueSessions['auth'][$connectionId]);
            $uniqueGuest = !isset($uniqueSessions['guest'][$connectionId]);

            if ($uniqueAuth && $auth) {
                $uniqueSessions['auth'][$connectionId] = true;
            }

            if ($uniqueGuest && !$auth) {
                $uniqueSessions['guest'][$connectionId] = true;
            }

            // We are not getting a specific user
            if ($identifier === null) {
                $identifier = $connectionId;
            }

            if ($type === 'auth') {
                return $auth && $connectionId == $identifier && $uniqueAuth;
            }

            return !$auth && $connectionId == $identifier && $uniqueGuest;
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
