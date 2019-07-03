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
        if (!isset($event->connection->user)) {
            return;
        }

        if (!$event->connection->user->agent) {
            return;
        }

        // TODO Check if user is participant in conversation
        // If so set the disconnected_at=null, connected_at=now
        // else create a new participant

        // TODO Create new message - "You got assigned"
        // Set the data for it, and create the receivers from active participants

        // TODO Send the DB Message to all active participants (with new structure)

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
