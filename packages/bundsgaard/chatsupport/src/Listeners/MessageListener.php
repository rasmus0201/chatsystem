<?php

namespace Bundsgaard\ChatSupport\Listeners;

use Bundsgaard\ChatSupport\Events\MessageEvent;

class MessageListener
{
    public $eventType = 'message';

    /**
     * Handle the event.
     *
     * @param  MessageEvent  $event
     * @return void
     */
    public function handle(MessageEvent $event)
    {
        // Get the connections to send to
        $connectionsTo = $event->connections->getAll($event->data->to);
        $connectionsFrom = $event->connections->getAll($event->connection->session['identifier']);

        // Note: If a to/from connection is presented in both arrays
        // a user will receive the message twice.
        $connections = array_unique(array_merge($connectionsTo, $connectionsFrom), SORT_REGULAR);

        foreach ($connections as $to) {
            if ($to->resourceId === $event->connection->resourceId) {
                continue;
            }

            $to->send(json_encode([
                'type' => $this->eventType,
                'message' => 'New message',
                'data' => [
                    'from' => $event->connection->session['identifier'],
                    'sender' => $event->connection->session['name'],
                    'time' => date('H:i:s'),
                    'message' => $event->data->message
                ]
            ]));
        }
    }
}
