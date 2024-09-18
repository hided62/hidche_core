<?php

namespace sammo;

class StaticEventHandler
{
    // This is a static class, so we don't want to instantiate it
    public function __construct()
    {
        throw new \Exception('This is a static class');
    }

    public static function handleEvent(General|GeneralLite $general, null|General|GeneralLite $destGeneral, string $eventType, array $env, array $params): void
    {
        $handlersList = GameConst::$staticEventHandlers[$eventType] ?? null;
        if ($handlersList === null) {
            return;
        }

        foreach ($handlersList as $handlerName) {
            $handler = buildStaticEventClass($handlerName);
            $handler->run($general, $destGeneral, $env, $params);
        }
    }
}
