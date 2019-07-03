<?php

namespace Bundsgaard\ChatSupport\Listeners;

use Bundsgaard\ChatSupport\Events\MessageEvent;
use Bundsgaard\ChatSupport\Storage\Conversation;

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
        if (!isset($event->connection->user, $event->data->conversation_id)) {
            return;
        }

        if (!$event->connection->user->agent) {
            return;
        }

        if (!$conversation = Conversation::find($event->data->conversation_id)) {
            return;
        }

        $agent = $event->connection->user;

        if (!$participant = $conversation->participants()->where('user_id', $agent->id)->get()) {
            $participant = $conversation->participants()->create([
                'user_id' => $agent->id
            ]);
        }

        // Update the connected_at/disconnected_at
        $participant->connected_at = new \DateTime();
        $participant->disconnected_at = null;
        $participant->save();

        // Create new message - "You got assigned"
        $message = $conversation->messages()->create([
            'system' => 1,
            'message' => 'Du bliver nu betjent af ' . $agent->name
        ]);

        // Set the receiver to the user who initiated the conversation
        $message->receivers()->create([
            'user_id' => $conversation->user_id
        ]);

        // Get the connections to send to
        $connections = $event->connections->getByUserId($conversation->user_id);

        // TODO Store this in DB
        foreach ($connections as $to) {
            $to->send(json_encode([
                'type' => $this->eventType,
                'message' => 'You got assigned',
                'data' => [
                    'conversation_id' => $conversation->user_id,
                    'message' => $message->with(['from'])->get()
                ]
            ]));
        }
    }
}
