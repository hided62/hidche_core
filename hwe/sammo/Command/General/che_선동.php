<?php
namespace sammo\Command\General;

use \sammo\DB;
use \sammo\Util;
use \sammo\JosaUtil;
use \sammo\General;
use \sammo\ActionLogger;
use \sammo\GameConst;
use \sammo\GameUnitConst;
use \sammo\Command;

class che_선동 extends che_화계{
    static protected $actionName = '선동';

    static protected $statType = 'leadership';
    static protected $injuryGeneral = true;

    protected function affectDestCity(int $injuryCount){
        $general = $this->generalObj;
        $date = $general->getTurnTime($general::TURNTIME_HM);

        $logger = $general->getLogger();

        $destCity = $this->destCity;

        $destCityName = $destCity['name'];
        $destCityID = $destCity['city'];

        $commandName = $this->getName();

        // 선동 최대 10
        $secuAmount = Util::valueFit(Util::randRangeInt(GameConst::$sabotageDamageMin, GameConst::$sabotageDamageMax), null, $destCity['secu']);
        $trustAmount = Util::valueFit(
            Util::randRange(GameConst::$sabotageDamageMin, GameConst::$sabotageDamageMax) / 50,
            null, 
            $destCity['trust']
        );
        $destCity['secu'] -= $secuAmount;
        $destCity['trust'] -= $trustAmount;
        
        DB::db()->update('city', [
            'state'=>32,
            'secu'=>$destCity['secu'],
            'trust'=>$destCity['trust']
        ], 'city=%i', $destCityID);

        $secuAmountText = number_format($secuAmount);
        $trustAmountText = number_format($trustAmount, 1);

        $logger->pushGlobalActionLog("<G><b>{$destCityName}</b></>의 백성들이 동요하고 있습니다.");
        $josaYi = JosaUtil::pick($commandName, '이');
        $logger->pushGeneralActionLog("<G><b>{$destCityName}</b></>에 {$commandName}{$josaYi} 성공했습니다. <1>$date</>");

        $logger->pushGeneralActionLog(
            "도시의 치안이 <C>{$secuAmountText}</>, 민심이 <C>{$trustAmountText}</>만큼 감소하고, 장수 <C>{$injuryCount}</>명이 부상 당했습니다.",
            ActionLogger::PLAIN
        );
    }
    
}