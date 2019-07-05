<?php

namespace Bundsgaard\ChatSupport\Responders;

class MessageResponder extends Responder
{
    public $type = 'message';

    /**
     * Respond to the user with data
     */
    public function respondWith($message)
    {
        if (empty($message) || !isset($this->receivers[0])) {
            return;
        }

        foreach ($this->receivers as $to) {
            $to->send(json_encode([
                'type' => $this->type,
                'message' => 'New message in conversation',
                'data' => [
                    'conversation_id' => $message->conversation_id,
                    'message' => $message,
                ]
            ]));
        }

    }
}
