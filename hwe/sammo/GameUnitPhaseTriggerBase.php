<?php
namespace sammo;

class GameUnitPhaseTriggerBase extends BaseWarUnitTrigger{
    static protected $priority = 10000;

    protected function actionWar(WarUnit $self, WarUnit $oppose, array &$selfEnv, array &$opposeEnv):void{
        //TODO: 목우 목우
    }
}