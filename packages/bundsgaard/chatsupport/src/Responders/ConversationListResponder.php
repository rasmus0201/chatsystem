<?php

namespace Bundsgaard\ChatSupport\Responders;

use Bundsgaard\ChatSupport\Storage\Room;

class ConversationListResponder extends Responder
{
    public $type = 'conversation:list';

    /**
     * Respond to the user with data
     */
    public function respond()
    {
        if (is_null($this->room)) {
            return;
        }

        if (is_integer($this->room)) {
            $this->room = Room::find($this->room);
        }

        $conversations = $this->room->activeConversations()->with(['user'])->get();
        foreach ($this->receivers as $to) {
            $to->send(json_encode([
                'type' => $this->type,
                'message' => 'List of connections in room',
                'data' => [
                    'conversations' => $conversations
                ]
            ]));
        }
    }
}
