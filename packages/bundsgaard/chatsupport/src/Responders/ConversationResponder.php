<?php

namespace Bundsgaard\ChatSupport\Responders;

class ConversationResponder extends Responder
{
    public $eventType = 'conversation';

    private $conversation;

    /**
     * Respond to the user with data
     */
    public function respond()
    {
        if (!$this->conversation || !isset($this->receivers[0])) {
            return;
        }

        $receiver = $this->receivers[0];
        $conversation = $this->conversation;

        $messages = $conversation->messages()->with(['from']);

        $receiver->send(json_encode([
            'type' => $this->eventType,
            'message' => 'Previous messages for conversation',
            'data' => [
                'room_id' => $conversation->room_id,
                'messages' => $messages->get()
            ]
        ]));
    }

    public function withConversation($conversation)
    {
        $this->conversation = $conversation;

        return $this;
    }
}
