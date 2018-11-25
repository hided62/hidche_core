<?php
namespace sammo\Command\General;

use \sammo\{
    DB, Util, JosaUtil,
    General, 
    ActionLogger,
    GameConst, GameUnitConst,
    Command
};

class che_탈취 extends che_화계{
    static protected $actionName = '탈취';

    static protected $statType = 'power';
    static protected $injuryGeneral = false;

    protected function affectDestCity(int $injuryCount){
        $general = $this->generalObj;
        $nationID = $general->getNationID();
        $date = substr($general->getVar('turntime'),11,5);

        $logger = $general->getLogger();

        $destCity = $this->destCity;

        $destCityName = $destCity['name'];
        $destCityID = $destCity['city'];

        $commandName = $this->getName();

        // 탈취 최대 400 * 8
        $gold = Util::randRangeInt(GameConst::$sabotageDamageMin, GameConst::$sabotageDamageMax) * $destCity['level'];
        $rice = Util::randRangeInt(GameConst::$sabotageDamageMin, GameConst::$sabotageDamageMax) * $destCity['level'];

        if($destCity['supply']){
            [$destNationGold, $destNationRice] = $db->queryFirstList('SELECT gold,rice FROM nation WHERE nation=%i', $destNationID);
    
            $destNationGold -= $gold;
            $destNationRice -= $rice;
    
            if($destNationGold < GameConst::$minNationalGold) { 
                $gold += $destNationGold - GameConst::$minNationalGold;
                $destNationGold = GameConst::$minNationalGold;
            }
            if($destNationRice < GameConst::$minNationalRice) {
                $rice += $destNationRice - GameConst::$minNationalRice;
                $destNationRice = GameConst::$minNationalRice;
            }
    
            $db->update('nation', [
                'gold'=>$destNationGold,
                'rice'=>$destNationRice
            ], 'nation=%i', $destNationID);
            $db->update('city', [
                'state'=>34
            ], 'city=%i', $destCityID);
        }
        else{
            $db->update('city', [
                'comm'=>Util::valueFit($destCity['comm'] - $gold / 12, 0),
                'agri'=>Util::valueFit($destCity['agri'] - $rice / 12, 0),
                'state'=>34
            ], 'city=%i', $destCityID);
        }

        // 본국으로 일부 회수, 재야이면 본인이 전량 소유
        if($nationID  != 0) {
            $db->update('nation', [
                'gold' => $db->sqleval('gold + %i', Util::round($gold * 0.7)),
                'rice' => $db->sqleval('rice + %i', Util::round($rice * 0.7))
            ], 'nation=%i', $nationID);
            $general->increaseVar('gold', $gold - Util::round($gold * 0.7));
            $general->increaseVar('rice', $rice - Util::round($rice * 0.7));
        } else {
            $general->increaseVar('gold', $gold);
            $general->increaseVar('rice', $rice);
        }

        $db->update('city', [
            'state'=>32,
            'agri'=>$destCity['agri'],
            'comm'=>$destCity['comm']
        ], 'city=%i', $destCityID);

        $goldText = number_format($gold);
        $riceText = number_format($rice);

        $josaYi = JosaUtil::pick($destCityName, '이');
        $logger->pushGlobalActionLog("<G><b>{$destCityName}</b></>에서 금과 쌀을 도둑맞았습니다.");
        $josaYi = JosaUtil::pick($commandName, '이');
        $logger->pushGeneralActionLog("<G><b>{$destCityName}</b></>에 {$commandName}{$josaYi} 성공했습니다. <1>$date</>");

        $logger->pushGeneralActionLog("금<C>{$goldText}</> 쌀<C>{$riceText}</>을 획득했습니다.", ActionLogger::PLAIN);
    }
    
}