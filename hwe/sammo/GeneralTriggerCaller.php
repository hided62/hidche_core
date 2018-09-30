<?php
namespace sammo;
class GeneralTriggerCaller extends TriggerCaller{
    function checkValidTrigger(iObjectTrigger $trigger):bool{
        if($trigger instanceof iGeneralTrigger){
            return true;
        }
        return false;
    }
}