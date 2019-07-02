<?php

namespace Bundsgaard\ChatSupport\Responders;

class MessageResponder extends Responder
{
    public $eventType = 'messages';

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

        // TODO get: 'from', 'sender', 'time'
        $receiver->send(json_encode([
            'type' => $this->eventType,
            'message' => 'Previous messages for conversation',
            'data' => ['messages' => $conversation->messages()->selectRaw('conversation_id, message, system, DATE_FORMAT(created_at, %T) as time')->get()]
        ]));
    }

    public function withConversation($conversation)
    {
        $this->conversation = $conversation;

        return $this;
    }
}
