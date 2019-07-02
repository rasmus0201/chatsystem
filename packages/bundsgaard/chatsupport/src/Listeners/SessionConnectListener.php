<?php

namespace Bundsgaard\ChatSupport\Listeners;

use Bundsgaard\ChatSupport\Storage\Room;
use Bundsgaard\ChatSupport\Storage\User;
use Bundsgaard\ChatSupport\Storage\UserStatus;
use Bundsgaard\ChatSupport\Events\MessageEvent;
use Bundsgaard\ChatSupport\Responders\UserListResponder;

class SessionConnectListener
{
    public $eventType = 'session:connect';

    private $userListResponder;

    public function __construct(UserListResponder $userListResponder)
    {
        $this->userListResponder = $userListResponder;
    }

    /**
     * Handle the event.
     *
     * @param  MessageEvent  $event
     * @return void
     */
    public function handle(MessageEvent $event)
    {
        $session = $event->connection->session;

        \Log::debug((array)$event->data);

        if (!isset($event->data->identifier, $event->data->name, $event->data->language)) {
            $event->connection->close();
            return false; // Stop next listeners
        }

        $session['agent'] = false;
        $session['name'] = $event->data->name;
        $session['language'] = $event->data->language;
        $session['identifier'] = $event->data->identifier;

        $session['user_id'] = null;
        $session['room_id'] = isset($event->data->room_id) ? $event->data->room_id : null;

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
                'room_id' => $session['room_id'],
                'status_id' => UserStatus::DISCONNECTED,
                'user_id' => null,
                'session_id' => $session['identifier'],
                'agent' => $session['agent'],
                'name' => $session['name'],
                'language' => $session['language'],
                'user_agent' => null,
                'ip' => null,
            ]);
        }

        // TODO
        // Check if user has an ongoing conversation
        // If so set the room_id from it
        // if not create new conversation and all data with it.

        if ($user->room_id) {
            $session['room_id'] = $user->room_id;
        }

        if (!Room::find($session['room_id'])) {
            $event->connection->close();
            return false; // Stop next listeners
        }

        // Now the user is actually verified and therefor active.
        $user->status_id = UserStatus::ACTIVE;
        $user->room_id = $session['room_id'];
        $user->save();

        $event->connection->session = $session;
        $event->connection->user = $user;

        // Multicast the user list for this room to agents in this room.
        $this->userListResponder
            ->withConnections($event->connections->users($user->room_id))
            ->withReceivers($event->connections->agents($user->room_id))
            ->respond();
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
