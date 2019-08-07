<?php

namespace Bundsgaard\ChatSupport\Listeners;

use Bundsgaard\ChatSupport\Events\MessageEvent;
use Bundsgaard\ChatSupport\Storage\Conversation;

class TypingListener
{
    public $listen = 'message:typing';

    /**
     * Handle the event.
     *
     * @param  MessageEvent  $event
     * @return void
     */
    public function handle(MessageEvent $event)
    {
        // Check the conversation exists
        if (!$conversation = Conversation::find($event->data->conversation_id)) {
            return;
        }

        $participants = $conversation->activeParticipants()->with(['user'])->get();

        // Check that the user typing is active participant in conversation
        $participantIds = $participants->pluck('user_id')->toArray();
        if (!in_array($event->connection->user->id, $participantIds)) {
            return;
        }

        foreach ($participants as $participant) {
            $user = $participant->user;

            // Get the connections to send to
            $connections = $event->connections->get($user->session_id);

            foreach ($connections as $to) {
                if ($to->user->id == $event->connection->user->id) {
                    continue;
                }

                $to->send(json_encode([
                    'type' => $this->listen,
                    'message' => 'Client is typing',
                    'data' => [
                        'user' => [
                            'session_id' => $event->connection->user->session_id,
                            'name' => $event->connection->user->name,
                        ],
                    ]
                ]));
            }
        }
    }
}
