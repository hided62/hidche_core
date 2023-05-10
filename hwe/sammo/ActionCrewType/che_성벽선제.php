<?php
namespace sammo\ActionCrewType;
use \sammo\iAction;
use \sammo\General;

class che_성벽선제 implements iAction{
    use \sammo\DefaultAction;

    protected $name = '성벽선제';
    protected $info = '전투 가능한 성벽이라면 선제공격을 합니다.';

    public function onCalcOpposeStat(General $general, string $statName, $value, $aux = null)
    {
        if($statName == 'cityBattleOrder'){
            // battleOrder는 스탯과 유사한 수치를 가지므로, 아주 충분히 높은값을 설정한다.
            return 10000;
        }
        return $value;
    }
}