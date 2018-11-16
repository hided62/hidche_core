<?php
namespace sammo\GeneralCommand;

use \sammo\{
    DB, Util, JosaUtil,
    General, 
    ActionLogger,
    GameConst, GameUnitConst,
    Command
};

class che_파괴 extends che_화계{
    static protected $actionName = '파괴';

    static protected $statType = 'power';
    static protected $injuryGeneral = true;

    protected function affectDestCity(int $injuryCount){
        $general = $this->generalObj;
        $date = substr($general->getVar('turntime'),11,5);

        $logger = $general->getLogger();

        $destCity = $this->destCity;

        $destCityName = $destCity['name'];
        $destCityID = $destCity['city'];

        $commandName = $this->getName();

        // 파괴
        $defAmount = Util::valueFit(Util::randRangeInt(GameConst::$sabotageDamageMin, GameConst::$sabotageDamageMax), null, $destCity['def']);
        $wallAmount = Util::valueFit(Util::randRangeInt(GameConst::$sabotageDamageMin, GameConst::$sabotageDamageMax), null, $destCity['wall']);
        if($defAmount < 0){ $defAmount = 0; }
        if($wallAmount < 0){ $wallAmount = 0; }

        $destCity['def'] -= $defAmount;
        $destCity['wall'] -= $wallAmount;

        $db->update('city', [
            'state'=>32,
            'def'=>$destCity['def'],
            'wall'=>$destCity['wall']
        ], 'city=%i', $destCityID);

        $defAmountText = number_format($defAmount);
        $wallAmountText = number_format($wallAmount);

        $josaYi = JosaUtil::pick($destCityName, '이');
        $logger->pushGlobalActionLog("누군가가 <G><b>{$destCityName}</b></>의 성벽을 허물었습니다.");
        $josaYi = JosaUtil::pick($commandName, '이');
        $logger->pushGeneralActionLog("<G><b>{$destCityName}</b></>에 {$commandName}{$josaYi} 성공했습니다. <1>$date</>");

        $logger->pushGeneralActionLog(
            "도시의 수비가 <C>{$defAmountText}</>, 성벽이 <C>{$wallAmountText}</>만큼 감소하고, 장수 <C>{$injuryCount}</>명이 부상 당했습니다.",
            ActionLogger::PLAIN
        );
    }
    
}