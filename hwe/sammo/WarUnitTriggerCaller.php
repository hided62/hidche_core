<?php
namespace sammo;
class WarUnitTriggerCaller extends TriggerCaller{
    function checkValidTrigger(ObjectTrigger $trigger):bool{
        if($trigger instanceof BaseWarUnitTrigger){
            return true;
        }
        return false;
    }
}