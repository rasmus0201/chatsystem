<?php

namespace Bundsgaard\ChatSupport\Events;

class OpenEvent extends Event
{
    public $connections;
    public $connection;

    public function __construct($connections, $connection)
    {
        $this->connections = $connections;
        $this->connection = $connection;
    }
}
