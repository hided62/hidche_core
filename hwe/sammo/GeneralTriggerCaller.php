<?php
namespace sammo;
class GeneralTriggerCaller extends TriggerCaller{
    function checkValidTrigger(ObjectTrigger $trigger):bool{
        if($trigger instanceof BaseGeneralTrigger){
            return true;
        }
        return false;
    }
}