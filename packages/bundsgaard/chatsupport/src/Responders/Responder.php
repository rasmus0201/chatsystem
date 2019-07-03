<?php

namespace Bundsgaard\ChatSupport\Responders;

abstract class Responder
{
    protected $connections = [];
    protected $receivers = [];

    protected $from;
    protected $room;

    /**
     * Respond to the user
     */
    abstract protected function respond();

    public function withRoom($room)
    {
        $this->room = $room;

        return $this;
    }

    public function withConnections($connections)
    {
        $this->connections = $connections;

        return $this;
    }

    public function withFrom($connection)
    {
        $this->from = $connection;

        return $this;
    }

    public function withReceiver($connection)
    {
        $this->receivers[] = $connection;

        return $this;
    }

    public function withReceivers($connections)
    {
        $this->receivers = array_merge($this->receivers, $connections);

        return $this;
    }
}
