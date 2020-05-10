<?php
namespace sammo\ActionItem;
use \sammo\iAction;
use \sammo\General;
use \sammo\GeneralTrigger;
use \sammo\GeneralTriggerCaller;
use sammo\WarUnitTriggerCaller;
use sammo\BaseWarUnitTrigger;
use \sammo\WarUnit;
use sammo\WarUnitTrigger\che_전투치료발동;
use sammo\WarUnitTrigger\che_전투치료시도;
use \sammo\WarUnitTrigger\che_저격시도;
use \sammo\WarUnitTrigger\che_저격발동;
use sammo\WarUnitTrigger\che_격노시도;
use sammo\WarUnitTrigger\che_격노발동;
use sammo\WarUnitTrigger\che_전멸시페이즈증가;
use sammo\WarUnitTrigger\che_부상무효;
use sammo\WarUnitTrigger\WarActivateSkills;

class che_치트_HideD의_사인검 extends \sammo\BaseItem{

    protected $rawName = 'HideD의 사인검';
    protected $name = 'HideD의 사인검(치트)';
    protected $info = <<<EOT
통솔 +100,무력 +100, 지력 +100<br>
[군사] 매 턴마다 자신(100%)과 소속 도시 장수(적 포함 50%) 부상 회복<br>
[전투] 페이즈마다 20% 확률로 치료 발동(아군 피해 1/3 감소)<br>
[전투] 새로운 상대와 전투 시 1/2 확률로 저격 발동, 성공 시 사기+10<br>
[전투] 훈련 보정 +30, 사기 보정 +30<br>
[전투] 상대방 필살 및 회피 시도시 일정 확률로 격노(필살) 발동, 공격 시 일정 확률로 진노(1페이즈 추가)<br>
[전투] 상대 필살, 격노, 위압, 저격 불가, 상대 계략 시도시 성공 확률 -10%p, 부상 없음, 아군 피해 -5%<br>
[전투] 상대 전멸 시 1페이즈 추가<br>
[전투] 계략 성공 확률 100%<br>
[계략] 화계·탈취·파괴·선동 : 성공률 100%
EOT;
    protected $cost = 9000000;
    protected $consumable = false;
    protected $buyable = false;

    public function onCalcDomestic(string $turnType, string $varType, float $value, $aux=null):float{
        if($turnType == '계략'){
            if($varType == 'success') return $value + 2;
        }
        
        return $value;
    }

    public function onCalcStat(General $general, string $statName, $value, $aux=null){
        $bonus = [
            'bonusTrain'=>30,
            'bonusAtmos'=>30,
            'leadership'=>100,
            'strength'=>100,
            'intel'=>100,
            'warMagicSuccessProb'=>1,
        ][$statName]??0;
        return $bonus + $value;
    }

    public function getPreTurnExecuteTriggerList(General $general):?GeneralTriggerCaller{
        return new GeneralTriggerCaller(
            new GeneralTrigger\che_도시치료($general)
        );
    }

    public function getBattleInitSkillTriggerList(WarUnit $unit):?WarUnitTriggerCaller{
        return new WarUnitTriggerCaller(
            new che_부상무효($unit, BaseWarUnitTrigger::TYPE_NONE),
            new WarActivateSkills($unit, BaseWarUnitTrigger::TYPE_NONE, false, '저격불가')
        );
    }

    public function getBattlePhaseSkillTriggerList(\sammo\WarUnit $unit): ?WarUnitTriggerCaller
    {
        return new WarUnitTriggerCaller(
            new WarActivateSkills($unit, BaseWarUnitTrigger::TYPE_NONE, false, '필살불가', '위압불가', '격노불가', '계략약화', '저격불가'),
            new che_전투치료시도($unit),
            new che_전투치료발동($unit),
            new che_저격시도($unit, che_저격시도::TYPE_NONE, 1/2, 20, 60),
            new che_저격발동($unit),
            new che_격노시도($unit),
            new che_격노발동($unit),
            new che_전멸시페이즈증가($unit),
        );
    }

    public function getWarPowerMultiplier(WarUnit $unit):array{
        return [1, 0.95];
    }

}