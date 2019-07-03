<?php

namespace Bundsgaard\ChatSupport\Console;

use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;
use Illuminate\Console\Command;
use Bundsgaard\ChatSupport\Chat;
use Illuminate\Foundation\Application;

class ServerCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'chatsupport:run';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run the websocket server.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @param Application $app
     *
     * @return void
     */
    public function handle(Application $app)
    {
        $server = IoServer::factory(
            new HttpServer(
                new WsServer(
                    $app->make(Chat::class)
                )
            ),
            9000
        );

        // TODO https://github.com/ratchetphp/Ratchet/issues/650#issuecomment-390548074
        // https://stackoverflow.com/questions/31375492/how-to-define-a-route-when-using-ratchet-web-socket
        // https://gist.github.com/cboden/3119135
        // Create routes for each room.
        // Optionally create routes for each conversation in room.
        // Maybe look at the WAMP / PubSub
        //
        // http://socketo.me/docs/wamp  http://socketo.me/docs/sessions
        // https://eole-io.github.io/sandstone-doc/examples/multichannel-chat.html

        // $server->route('/dog', $chat, ['*']);

        $server->run();
    }
}
