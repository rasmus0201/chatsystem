<?php

namespace Bundsgaard\ChatSupport\Events;

class MessageEvent extends Event
{
    public $connections;
    public $connection;
    public $data;
    public $type;

    private $_data;

    public function __construct($connections, $connection, $data)
    {
        $this->connections = $connections;
        $this->connection = $connection;
        $this->data = $data->data;
        $this->type = $data->type;

        $this->_data = $data;
    }
}
