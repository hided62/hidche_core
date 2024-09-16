<?php
namespace sammo;

class StaticEventHandler {
    // This is a static class, so we don't want to instantiate it
    public function __construct() {
        throw new \Exception('This is a static class');
    }

    public static function handleEvent(General $general, string $eventType, array ...$params): void {
        $handlersList = GameConst::$staticEventHandlers[ $eventType ] ?? null;
        if( $handlersList === null ) {
            return;
        }


    }
}