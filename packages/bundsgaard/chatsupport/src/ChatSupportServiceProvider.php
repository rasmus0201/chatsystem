<?php

namespace Bundsgaard\ChatSupport;

use Bundsgaard\ChatSupport\Commands\ServerCommand;
use Illuminate\Support\ServiceProvider;

class ChatSupportServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return  void
     */
    public function boot(Chat $chat)
    {
        $this->publishes([
            __DIR__.'/../config/chatsupport.php' => $this->app->basePath() . '/config',
        ], 'chatsupport-config');

        $this->app->singleton(Chat::class, function ($app) use ($chat) {
            return $chat;
        });

        if ($this->app->runningInConsole()) {
            $this->commands([
                ServerCommand::class,
            ]);
        }
    }
    /**
     * Register the application services.
     *
     * @return  void
     */
    public function register()
    {
        // Load the config file
        $this->mergeConfigFrom(__DIR__.'/../config/chatsupport.php', 'chatsupport');
    }
}
