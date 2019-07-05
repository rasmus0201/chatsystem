<?php

namespace Bundsgaard\ChatSupport\Listeners;

use Bundsgaard\ChatSupport\Storage\Message;
use Bundsgaard\ChatSupport\Events\MessageEvent;
use Bundsgaard\ChatSupport\Storage\Conversation;

class AssignListener
{
    public $listen = 'assign';

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
        $message = null;

        if (!$participant = $conversation->participants()->where('user_id', $agent->id)->first()) {
            $participant = $conversation->participants()->create([
                'user_id' => $agent->id
            ]);

            // Create new message - "You got assigned"
            $message = $conversation->messages()->create([
                'system' => 1,
                'message' => 'Du bliver nu betjent af ' . $agent->name
            ]);

            // Set the receiver to the user who initiated the conversation
            $message->receivers()->create([
                'user_id' => $conversation->user_id
            ]);
        }

        // Update the connected_at/disconnected_at
        $participant->connected_at = new \DateTime();
        $participant->disconnected_at = null;
        $participant->save();

        // Get the connections to send to
        $connections = $event->connections->getByUserId($conversation->user_id);

        foreach ($connections as $to) {
            $to->send(json_encode([
                'type' => $this->listen,
                'message' => 'You got assigned',
                'data' => [
                    'conversation_id' => $conversation->user_id,
                    'assignee' => $agent->only([
                        'session_id',
                        'name',
                        'language'
                    ]),
                    'message' => $message ? Message::where('id', $message->id)->with(['from'])->first() : null
                ]
            ]));
        }
    }
}
