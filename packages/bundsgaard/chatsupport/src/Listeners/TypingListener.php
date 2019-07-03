<?php

namespace Bundsgaard\ChatSupport\Listeners;

use Bundsgaard\ChatSupport\Events\MessageEvent;

class TypingListener
{
    public $eventType = 'typing';

    /**
     * Handle the event.
     *
     * @param  MessageEvent  $event
     * @return void
     */
    public function handle(MessageEvent $event)
    {
        // Get the connections to send to
        $connections = $event->connections->get($event->data->to);

        foreach ($connections as $to) {
            $to->send(json_encode([
                'type' => $this->eventType,
                'message' => 'Is typing',
                'data' => [
                    'from' => $event->connection->session['session_id'],
                    'sender' => $event->connection->session['name'],
                    'typing' => true,
                ]
            ]));
        }
    }
}
