<?php

namespace Bundsgaard\ChatSupport\Listeners;

use Bundsgaard\ChatSupport\Storage\Room;
use Bundsgaard\ChatSupport\Storage\User;
use Bundsgaard\ChatSupport\Storage\UserStatus;
use Bundsgaard\ChatSupport\Events\MessageEvent;
use Bundsgaard\ChatSupport\Responders\ConversationListResponder;
use Bundsgaard\ChatSupport\Responders\ConversationResponder;

class SessionConnectListener
{
    public $eventType = 'session:connect';

    private $conversationListResponder;
    private $conversationResponder;

    public function __construct(ConversationListResponder $conversationListResponder, ConversationResponder $conversationResponder)
    {
        $this->conversationListResponder = $conversationListResponder;
        $this->conversationResponder = $conversationResponder;
    }

    /**
     * Handle the event.
     *
     * @param  MessageEvent  $event
     * @return void
     */
    public function handle(MessageEvent $event)
    {
        if (!isset($event->data->session_id, $event->data->name, $event->data->language)) {
            $event->connection->close();
            return false; // Stop next listeners
        }

        $session = $event->connection->session;

        $session['agent'] = false;
        $session['name'] = $event->data->name;
        $session['language'] = $event->data->language;
        $session['session_id'] = $event->data->session_id;

        $session['user_id'] = null;
        $session['room_id'] = null;

        if (isset($event->data->credentials)) {
            if (!$this->checkAuth($event)) {
                $event->connection->close();
                return false; // Stop next listeners
            }

            $session['user_id'] = isset($event->data->credentials->user_id) ? $event->data->credentials->user_id : null;
            $session['agent'] = true;
        }

        if (!$user = $this->findUser($session['session_id'], $session['agent'])) {
            $user = User::create([
                'user_id' => null,
                'status_id' => UserStatus::DISCONNECTED,
                'room_id' => $session['room_id'],
                'session_id' => $session['session_id'],
                'agent' => $session['agent'],
                'name' => $session['name'],
                'language' => $session['language'],
                'user_agent' => null,
                'ip' => null,
            ]);
        }

        // Check if user has an ongoing conversation
        if ($conversation = $user->activeConversations->first()) {
            $user->room_id = $conversation->room_id;
        }

        // If the connected user already is associated with a room,
        // then we can set it now.
        if ($user->room_id) {
            $session['room_id'] = $user->room_id;
        }

        // Now the user is actually verified and therefor active.
        $user->status_id = UserStatus::ACTIVE;
        $user->save();

        $event->connection->session = $session;
        $event->connection->user = $user;

        // If there is an ongoing conversation
        if (!$user->agent && isset($conversation)) {
            $this->conversationResponder
                ->withReceiver($event->connection)
                ->withConversation($conversation)
                ->respond();
        }

        // Multicast the user list for this room to agents in this room.
        if ($user->room_id) {
            $receivers = $event->connections->agents($user->room_id);

            if ($user->agent) {
                $receivers[] = $event->connection;
            }

            $this->conversationListResponder
                ->withRoom($user->room_id)
                ->withReceivers($receivers)
                ->respond();

            $event->connection->send(json_encode([
                'type' => 'room',
                'message' => 'Set the selected room',
                'data' => ['room_id' => $user->room_id]
            ]));
        }
    }

    private function findUser($sessionId, $agent)
    {
        return User::where('session_id', $sessionId)->where('agent', $agent)->first();
    }

    private function checkAuth($event)
    {
        $credentials = $event->data->credentials;

        if (!isset($credentials->username, $credentials->password)) {
            return false;
        }

        return $this->attemptAuth($credentials->username, $credentials->password);
    }

    private function attemptAuth($username, $password)
    {
        return $username == 'test' && $password == 'test';
    }
}
