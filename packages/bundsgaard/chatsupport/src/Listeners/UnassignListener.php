<?php

namespace Bundsgaard\ChatSupport\Listeners;

use Bundsgaard\ChatSupport\Events\MessageEvent;

class UnassignListener
{
    public $eventType = 'unassign';

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
                'message' => 'You got unassigned',
                'data' => [
                    'assignee' => [
                        'name' => $event->connection->session['name'],
                        'identifier' => $event->connection->session['identifier']
                    ],
                    'time' => date('H:i:s')
                ]
            ]));
        }
    }
}
