<?php

namespace Bundsgaard\ChatSupport;

use Illuminate\Foundation\Application;
use Illuminate\Contracts\Config\Repository as Config;

class EventDispatcher
{
    private $app;
    private $listen = [];

    public function __construct(Application $app, Config $config)
    {
        $this->app = $app;
        $this->listen = $config->get('chatsupport.listen');
    }

    public function dispatch($event)
    {
        if (is_string($event)) {
            $event = $this->app->make($event);
        }

        $class = get_class($event);
        if (!isset($this->listen[$class])) {
            throw new \Exception('Could not dispatch event "' . $class . '" because it was not found.');
        }

        $properties = $this->getProperties($event);
        foreach ($this->listen[$class] as $listenerClass) {
            $listener = $this->app->make($listenerClass);

            // Automatically find out if the listener should be called
            // based on the event type
            if (isset($properties['type']) && property_exists($listener, 'eventType')) {
                if ($properties['type'] !== $listener->eventType) {
                    continue;
                }
            }

            $returns = $listener->handle($event);

            // Check if we should continue to call listeners
            // breaks if returning falsy value that is not null
            if ($returns !== null && !$returns) {
                break;
            }
        }
    }

    private function getProperties($class)
    {
        $properties = (new \ReflectionObject($class))->getProperties(\ReflectionProperty::IS_PUBLIC);

        $result = [];
        foreach ($properties as $property) {
            $result[$property->name] = $class->{$property->name};
        }

        return $result;
    }
}
