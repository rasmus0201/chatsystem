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
        require __DIR__ . '/Lumen.php';

        $this->registerPublishing();

        $this->app->singleton(Chat::class, function ($app) use ($chat) {
            return $chat;
        });

        $this->loadViewsFrom(
            __DIR__ . '/../views', 'chatsupport'
        );
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

        $this->commands([
            ServerCommand::class,
        ]);
    }

    /**
     * Register the package's publishable resources.
     *
     * @return void
     */
    private function registerPublishing()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/chatsupport.php' => $this->app->basePath() . '/config',
            ], 'chatsupport-config');

            $this->publishes([
                __DIR__ . '/../public' => $this->app->basePath() . '/public/vendor/chatsupport'
            ], 'chatsupport-assets');
        }
    }
}
