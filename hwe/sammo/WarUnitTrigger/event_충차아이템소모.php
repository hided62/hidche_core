<?php
namespace sammo\WarUnitTrigger;

use sammo\ActionItem\event_충차;
use sammo\BaseWarUnitTrigger;
use sammo\WarUnitGeneral;
use sammo\WarUnitCity;
use sammo\WarUnit;
use sammo\GameUnitDetail;
use sammo\ObjectTrigger;
use sammo\Util;
use sammo\ActionLogger;

class event_충차아이템소모 extends BaseWarUnitTrigger{
    protected $priority = ObjectTrigger::PRIORITY_PRE + 200;

    protected function actionWar(WarUnit $self, WarUnit $oppose, array &$selfEnv, array &$opposeEnv):bool{
        if($self->hasActivatedSkillOnLog('충차공격') && $self->getPhase() == $self->getMaxPhase() - 1){
            //TODO: 전투 종료시 소모를 예약하는 기능이 있으면 매우 좋을 것 같다.
            $general = $self->getGeneral();
            $remain = $general->getAuxVar(event_충차::REMAIN_KEY) ?? 0;
            if($remain <= 0){
                $this->processConsumableItem();
            }
            return true;
        }

        if(!($self instanceof WarUnitGeneral)){
            return true;
        }
        if(!($oppose instanceof WarUnitCity)){
            return true;
        }
        if($self->hasActivatedSkillOnLog('충차공격')){
            return true;
        }
        
        $self->getLogger()->pushGeneralBattleDetailLog("<C>충차</>로 성벽을 공격합니다.", ActionLogger::PLAIN);

        $general = $self->getGeneral();
        $remain = $general->getAuxVar(event_충차::REMAIN_KEY) ?? 0;
        $self->activateSkill('충차공격');
        $general->setAuxVar(event_충차::REMAIN_KEY, $remain - 1);

        return true;
    }
}