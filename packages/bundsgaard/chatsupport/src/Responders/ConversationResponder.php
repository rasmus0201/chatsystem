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


        $participants = $conversation->activeParticipants()->with(['user'])->where('user_id', '!=', $conversation->user_id)->get();
        $result['clients'] = $participants->pluck('user')->map(function ($item) {
            return $item->only([
                'session_id',
                'name',
                'language'
            ]);
        });

        $receiver->send(json_encode([
            'type' => $this->type,
            'message' => 'Data for ongoing conversation',
            'data' => [
                'room_id' => $conversation->room_id,
                'conversation' => $result,
            ]
        ]));
    }
}
