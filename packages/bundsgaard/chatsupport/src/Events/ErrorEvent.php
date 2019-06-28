<?php

namespace Bundsgaard\ChatSupport\Events;

class ErrorEvent extends Event
{
    public $connections;
    public $connection;
    public $error;

    public function __construct($connections, $connection, $e)
    {
        $this->connections = $connections;
        $this->connection = $connection;
        $this->error = $e;
    }
}
