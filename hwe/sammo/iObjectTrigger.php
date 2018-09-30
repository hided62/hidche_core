<?php
namespace sammo;

interface iObjectTrigger{
    const PRIORITY_MAX   = 99999;
    const PRIORITY_BEGIN = 50000;
    const PRIORITY_PRE   = 40000;
    const PRIORITY_BODY  = 30000;
    const PRIORITY_POST  = 20000;
    const PRIORITY_FINAL = 10000;

    public function getPriority():int;
    public function action(?array $env=null, $arg=null):?array;
    public function getUniqueID():string;
}
?>