<?php

namespace Bundsgaard\ChatSupport\Responders;

abstract class Responder
{
    protected $connections = [];
    protected $receivers = [];

    /**
     * Respond to the user
     */
    abstract protected function respond();

    public function withConnections($connections)
    {
        $this->connections = $connections;

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
