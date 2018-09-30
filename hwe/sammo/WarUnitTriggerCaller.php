<?php
namespace sammo;
class WarUnitTriggerCaller extends TriggerCaller{
    function checkValidTrigger(iObjectTrigger $trigger):bool{
        if($trigger instanceof iWarUnitTrigger){
            return true;
        }
        return false;
    }
}