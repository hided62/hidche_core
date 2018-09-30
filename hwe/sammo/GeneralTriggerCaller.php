<?php
namespace sammo;
class GeneralTriggerCaller extends TriggerCaller{
    function checkValidTrigger(ObjectTrigger $trigger):bool{
        if($trigger instanceof GeneralTrigger){
            return true;
        }
        return false;
    }
}