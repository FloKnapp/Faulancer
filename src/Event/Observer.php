<?php

namespace Faulancer\Event;

use Faulancer\Service\Config;
use Faulancer\ServiceLocator\ServiceLocator;

/**
 * Class Observer
 * @package Faulancer\Event
 * @author  Florian Knapp <office@florianknapp.de>
 */
class Observer
{

    /** @var self */
    protected static $instance;

    /** @var AbstractListener[] */
    protected static $listener = [];

    /**
     * Observer constructor (private).
     */
    private function __construct() {}

    /**
     * Yeah... singleton... i know
     *
     * @return self
     */
    public static function instance()
    {
        if (!self::$instance) {

            /** @var Config $config */
            $config = ServiceLocator::instance()->get(Config::class);
            self::$listener = $config->get('eventListener');
            self::$instance = new self();

        }

        return self::$instance;
    }

    /**
     * Trigger listeners if registered for the type
     *
     * @param AbstractEvent $event
     */
    public function trigger(AbstractEvent $event)
    {
        if (!self::$listener) {
            return;
        }

        foreach (self::$listener as $typeName => $listenerList) {

            /** @var AbstractListener[] $listenerList */
            foreach ($listenerList as $listener) {

                if ($typeName === $event::NAME) {

                    /** @var AbstractListener $listener */
                    $listener = new $listener();
                    $listener->create();
                    $callback = $listener->getCallback();
                    $callback->execute($event);

                }

            }

        }

    }

}