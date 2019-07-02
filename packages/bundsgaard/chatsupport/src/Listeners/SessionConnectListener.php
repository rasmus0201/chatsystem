<?php

namespace Bundsgaard\ChatSupport\Listeners;

use Bundsgaard\ChatSupport\Storage\Room;
use Bundsgaard\ChatSupport\Storage\User;
use Bundsgaard\ChatSupport\Storage\UserStatus;
use Bundsgaard\ChatSupport\Events\MessageEvent;
use Bundsgaard\ChatSupport\Responders\MessageResponder;
use Bundsgaard\ChatSupport\Responders\UserListResponder;

class SessionConnectListener
{
    public $eventType = 'session:connect';

    private $userListResponder;
    private $messageResponder;

    public function __construct(UserListResponder $userListResponder, MessageResponder $messageResponder)
    {
        $this->userListResponder = $userListResponder;
        $this->messageResponder = $messageResponder;
    }

    /**
     * Handle the event.
     *
     * @param  MessageEvent  $event
     * @return void
     */
    public function handle(MessageEvent $event)
    {
        if (!isset($event->data->identifier, $event->data->name, $event->data->language)) {
            $event->connection->close();
            return false; // Stop next listeners
        }

        $session = $event->connection->session;

        $session['agent'] = false;
        $session['name'] = $event->data->name;
        $session['language'] = $event->data->language;
        $session['identifier'] = $event->data->identifier;

        $session['user_id'] = null;
        $session['room_id'] = null;
        // $session['room_id'] = isset($event->data->room_id) ? $event->data->room_id : null;

        if (isset($event->data->credentials)) {
            if (!$this->checkAuth($event)) {
                $event->connection->close();
                return false; // Stop next listeners
            }

            $session['user_id'] = isset($event->data->credentials->user_id) ? $event->data->credentials->user_id : null;
            $session['agent'] = true;
        }

        if (!$user = $this->findUser($session['identifier'], $session['agent'])) {
            $user = User::create([
                'user_id' => null,
                'status_id' => UserStatus::DISCONNECTED,
                'room_id' => $session['room_id'],
                'session_id' => $session['identifier'],
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

        if ($user->room_id) {
            $session['room_id'] = $user->room_id;
        }

        // if (!$room = Room::find($session['room_id'])) {
        //     $event->connection->close();
        //     return false; // Stop next listeners
        // }

        // Now the user is actually verified and therefor active.
        $user->status_id = UserStatus::ACTIVE;
        // $user->room_id = $session['room_id'];
        $user->save();

        // if (!$user->agent && !$user->activeConversations->first()) {
        //     $conversation = $user->conversations()->create([
        //         'room_id' => $session['room_id'],
        //     ]);
        //
        //     $conversation->participants()->create([
        //         'user_id' => $user->id
        //     ]);
        //
        //     $conversation->message('System: Venter på betjening fra ' . $room->name, true);
        // }

        $event->connection->session = $session;
        $event->connection->user = $user;

        // Multicast the user list for this room to agents in this room.
        // $this->userListResponder
        //     ->withConnections($event->connections->users($user->room_id))
        //     ->withReceivers($event->connections->agents($user->room_id))
        //     ->respond();

        // An agent can't receive initial messages
        // TODO check if conversation is set
        if (!$user->agent) {
            $this->messageResponder
                ->withReceiver($event->connection)
                ->withConversation($conversation)
                ->respond();
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
