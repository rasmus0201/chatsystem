<?php

namespace Bundsgaard\ChatSupport\Commands;

use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Laravel\Lumen\Application;
use Ratchet\WebSocket\WsServer;
use Illuminate\Console\Command;
use Bundsgaard\ChatSupport\Chat;

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

        $server->run();
    }
}
