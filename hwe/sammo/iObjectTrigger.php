<?php
namespace sammo;

interface iObjectTrigger{
    public function getPriority():int;
    public function action(?array $env=null, $arg=null):?array;
    public function getUniqueID():string;
}
?>