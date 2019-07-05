<?php

namespace Bundsgaard\ChatSupport\Responders;

use Bundsgaard\ChatSupport\Storage\Room;

class ConversationListResponder extends Responder
{
    public $listen = 'conversation:list';

    /**
     * Respond to the user with data
     */
    public function respond()
    {
        // TODO
        // Refactor this to be called conversation list
        // and get conversations from db then link the conversation users to active connections
        if (is_null($this->room)) {
            return;
        }

        if (is_integer($this->room)) {
            $this->room = Room::find($this->room);
        }

        foreach ($this->receivers as $to) {
            $to->send(json_encode([
                'type' => $this->listen,
                'message' => 'List of connections in room',
                'data' => [
                    'conversations' => $this->room->activeConversations()->with(['user'])->get()
                ]
            ]));
        }
    }
}
