<?php

namespace Bundsgaard\ChatSupport\Listeners;

use Bundsgaard\ChatSupport\Events\MessageEvent;

class AssignListener
{
    public $eventType = 'assign';

    /**
     * Handle the event.
     *
     * @param  MessageEvent  $event
     * @return void
     */
    public function handle(MessageEvent $event)
    {
        // TODO Make sure only agents are allowed to do this.

        // Get the connections to send to
        $connections = $event->connections->get($event->data->to);

        // TODO Store this in DB
        foreach ($connections as $to) {
            $to->send(json_encode([
                'type' => $this->eventType,
                'message' => 'You got assigned',
                'data' => [
                    'assignee' => [
                        'name' => $event->connection->session['name'],
                        'identifier' => $event->connection->session['identifier'],
                        'typing' => $event->connection->session['typing'],
                    ],
                    'time' => date('H:i:s')
                ]
            ]));
        }
    }
}
