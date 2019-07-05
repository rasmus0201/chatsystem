<?php

namespace Bundsgaard\ChatSupport\Responders;

class ConversationResponder extends Responder
{
    public $type = 'conversation';

    /**
     * Respond to the user with data
     */
    public function respondWith($conversation)
    {
        if (empty($conversation) || !isset($this->receivers[0])) {
            return;
        }

        $receiver = $this->receivers[0];
        $messages = $conversation->messages()->with(['from']);

        $result = $conversation->toArray();
        $result['messages'] = $messages->get();

        $receiver->send(json_encode([
            'type' => $this->type,
            'message' => 'Previous messages for conversation',
            'data' => [
                'room_id' => $conversation->room_id,
                'conversation' => $result,
            ]
        ]));
    }
}
