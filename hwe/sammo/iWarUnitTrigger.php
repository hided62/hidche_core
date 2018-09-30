<?php
namespace sammo;

interface iWarUnitTrigger extends iObjectTrigger{
    public function __construct(WarUnit $general);
}
?>