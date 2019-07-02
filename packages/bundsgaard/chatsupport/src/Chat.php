<?php

namespace Bundsgaard\ChatSupport;

use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;
use Bundsgaard\ChatSupport\Events\OpenEvent;
use Bundsgaard\ChatSupport\Events\CloseEvent;
use Bundsgaard\ChatSupport\Events\ErrorEvent;
use Bundsgaard\ChatSupport\Events\MessageEvent;

class Chat implements MessageComponentInterface
{
    protected $connections;

    private $eventDispatcher;

    public function __construct(EventDispatcher $eventDispatcher, Connections $connections)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->connections = $connections;
    }

    public function onOpen(ConnectionInterface $conn)
    {
        $conn->session = [
            'typing' => false,
            'agent' => false,
            'room_id' => null,
            'identifier' => null,
            'name' => null,
            'language' => null,
        ];

        // Store the new connection to send messages to later
        $this->connections->add($conn);

        try {
            $this->eventDispatcher->dispatch(new OpenEvent($this->connections, $conn));
        } catch (\Exception $e) {
            \Log::error($e);

            $this->sendError($conn, [$e->getMessage(), explode("\n", $e->getTraceAsString())]);
        }
    }

    public function onMessage(ConnectionInterface $from, $msg)
    {
        try {
            $this->eventDispatcher->dispatch(new MessageEvent($this->connections, $from, json_decode($msg)));
        } catch (\Exception $e) {
            \Log::error($e);

            $this->sendError($from, [$e->getMessage(), explode("\n", $e->getTraceAsString())]);
        }
    }

    public function onClose(ConnectionInterface $conn)
    {
        // The connection is closed, remove it, as we can no longer send it messages
        $this->connections->remove($conn);

        try {
            $this->eventDispatcher->dispatch(new CloseEvent($this->connections, $conn));
        } catch (\Exception $e) {
            \Log::error($e);

            // Connection is closed now
        }
    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        try {
            $this->eventDispatcher->dispatch(new ErrorEvent($this->connections, $conn, $e));
        } catch (\Exception $eventException) {
            \Log::error($e);

            $this->sendError($conn, $e->getMessage());
        }

        $conn->close();
    }

    private function sendError($connection, $error = '')
    {
        $connection->send(json_encode([
            'type' => 'error',
            'message' => 'Something went wrong...',
            'data' => [
                'error' => $error
            ]
        ]));
    }
}
