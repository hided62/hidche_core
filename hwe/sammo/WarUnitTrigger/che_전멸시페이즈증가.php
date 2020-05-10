<?php
namespace sammo\WarUnitTrigger;
use sammo\BaseWarUnitTrigger;
use sammo\WarUnitGeneral;
use sammo\WarUnitCity;
use sammo\WarUnit;
use sammo\GameUnitDetail;
use sammo\Util;
use sammo\ObjectTrigger;
use sammo\ActionLogger;

class che_전멸시페이즈증가 extends BaseWarUnitTrigger{
    protected $priority = ObjectTrigger::PRIORITY_POST + 700;

    protected function actionWar(WarUnit $self, WarUnit $oppose, array &$selfEnv, array &$opposeEnv):bool{
        /** @var WarUnitGeneral $self */
        if($self->getPhase() !== 0 && $oppose->getPhase() === 0){
            $self->addBonusPhase(1);
            $self->getLogger()->pushGeneralBattleDetailLog("적군의 전멸에 <C>진격</>이 이어집니다!", ActionLogger::PLAIN);
            $oppose->getLogger()->pushGeneralBattleDetailLog("아군의 전멸에 상대의 <R>진격</>이 이어집니다!", ActionLogger::PLAIN);
        }
        return true;
    }
}