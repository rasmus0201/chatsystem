<?php

// Add support for laravel lumen
if (
    !class_exists(Illuminate\Foundation\Application::class) &&
    class_exists(Laravel\Lumen\Application::class)
) {
    $this->app->alias(Illuminate\Foundation\Application::class, Laravel\Lumen\Application::class);
}
