<?php
namespace sammo\WarUnitTrigger;
use sammo\BaseWarUnitTrigger;
use sammo\WarUnitGeneral;
use sammo\WarUnitCity;
use sammo\WarUnit;
use sammo\GameUnitDetail;
use sammo\ObjectTrigger;
use sammo\Util;

class che_계략시도 extends BaseWarUnitTrigger{
    protected $priority = ObjectTrigger::PRIORITY_PRE + 300;

    static protected $tableToGeneral = [
        '위보'=>[1.2, 1.1],
        '매복'=>[1.4, 1.2],
        '반목'=>[1.6, 1.3],
        '화계'=>[1.8, 1.4],
        '혼란'=>[2.0, 1.5]
    ];
    static protected $tableToCity = [
        '급습'=>[1.2, 1.1],
        '위보'=>[1.4, 1.2],
        '혼란'=>[1.6, 1.3]
    ];

    protected function actionWar(WarUnit $self, WarUnit $oppose, array &$selfEnv, array &$opposeEnv):bool{
        assert($self instanceof WarUnitGeneral, 'General만 발동 가능');

        $general = $self->getGeneral();
        $crewType = $general->getCrewTypeObj();

        if($self->hasActivatedSkill('계략불가')){
            return true;
        }
        
        $magicTrialProb = $general->getIntel(true, true, true, false) / 100;
        $magicTrialProb *= $crewType->magicCoef;

        $magicTrialProb = $general->onCalcStat($general, 'warMagicTrialProb', $magicTrialProb);
        if($magicTrialProb <= 0){
            return true;
        }

        if($self->getPhase() == 0){
            $magicTrialProb *= 3;
        }
        
        if(!Util::randBool($magicTrialProb)){
            return true;
        }

        $magicSuccessProb = 0.7;
        $magicSuccessProb = $general->onCalcStat($general, 'warMagicSuccessProb', $magicSuccessProb);
        if($self->hasActivatedSkill('계략약화')){
            $magicSuccessProb -= 0.1; //NOTE: 앞으로 이건 oppose의 onCalcStat에 들어가야하지 않을까?
        }

        if($oppose instanceof WarUnitCity){
            $magic = Util::choiceRandom(array_keys(static::$tableToCity));
            [$successDamage, $failDamage] = static::$tableToCity[$magic];
        }
        else{
            $magic = Util::choiceRandom(array_keys(static::$tableToGeneral));
            [$successDamage, $failDamage] = static::$tableToGeneral[$magic];
        }

        $successDamage = $general->onCalcStat($general, 'warMagicSuccessDamage', $successDamage, $magic);

        $self->activateSkill('계략시도', $magic);
        if(Util::randBool($magicSuccessProb)){
            $self->activateSkill('계략');
            $selfEnv['magic'] = [$magic, $successDamage];
        }
        else{
            $self->activateSkill('계략실패');
            $selfEnv['magic'] = [$magic, $failDamage];
        }
        
        return true;
    }
}