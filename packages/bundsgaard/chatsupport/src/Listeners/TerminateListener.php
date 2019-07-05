<?php

namespace Bundsgaard\ChatSupport\Listeners;

use Bundsgaard\ChatSupport\Events\MessageEvent;

class TerminateListener
{
    public $listen = 'terminate';

    /**
     * Handle the event.
     *
     * @param  MessageEvent  $event
     * @return void
     */
    public function handle(MessageEvent $event)
    {
        // TODO
        // Make this when the user closes the chat.
        // Reset user status
        // Close users current conversation
        // Store the last message in db (user disconnected / closed chat)
        // after that multicast it.
    }
}
