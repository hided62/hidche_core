<?php
namespace sammo;

class GameUnitInitTriggerBase extends BaseWarUnitTrigger{
    static protected $priority = 10000;

    protected function actionWar(WarUnit $self, WarUnit $oppose, array &$selfEnv, array &$opposeEnv):void{
        //TODO: 충차는 성벽 상대로 부상입지 않음
    }
}