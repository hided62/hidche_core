<?php
namespace sammo;

class WarUnitGeneral extends WarUnit{
    protected $raw;
    protected $rawCity;
    protected $rawNation;

    protected $logger;
    protected $crewType;

    protected $killed = 0;
    protected $death = 0;
    protected $win = 0;

    protected $updatedVar = [];

    protected $genAtmos = 0;
    protected $genTrain = 0;
    protected $genAtmosBonus = 0;
    protected $genTrainBonus = 0;

    protected $sniped = false;


    function __construct($raw, $rawCity, $rawNation, $isAttacker, $year, $month){
        setLeadershipBonus($raw, $rawNation['level']);

        $this->raw = $raw;
        $this->rawCity = $rawCity;
        $this->rawNation = $rawNation;
        $this->isAttacker = $isAttacker;

        $this->logger = new ActionLogger($this->raw['no'], $this->raw['nation'], $year, $month);
        $this->crewType = GameUnitConst::byID($this->raw['crewtype']);

        if($isAttacker){
            //공격자 보정
            if($rawCity['level'] == 2){
                $this->genAtmosBonus += 5;
            }
            if($rawNation['capital'] == $rawCity['city']){
                $this->genAtmosBonus += 5;
            }
        }
        else{
            //수비자 보정
            if($rawCity['level'] == 1){
                $this->genTrainBonus += 5;
            }
            else if($rawCity['level'] == 3){
                $this->genTrainBonus += 5;
            }
        }
    }

    function getRaw():array{
        return $this->raw;
    }

    function getName():string{
        return $this->raw['name'];
    }
    
    function getSpecialDomestic():int{
        return $this->raw['special'];
    }

    function getSpecialWar():int{
        return $this->raw['special2'];
    }

    function getItem():int{
        return $this->raw['item'];
    }

    function getMaxPhase():int{
        $phase = $this->getCrewType()->speed;
        if($this->getSpecialWar() == 60){
            $phase += 1;
        }
        return $phase;
    }

    function addTrain(int $train){
        $this->raw['train'] += $train;
        $this->updatedVar['train'] = true;
    }

    function addAtmos(int $atmos){
        $this->raw['atmos'] += $atmos;
        $this->updatedVar['atmos'] = true;
    }

    protected function getWarPowerMultiplyBySpecialWar():array{
        //TODO: 장기적으로 if문이 아니라 객체를 이용하여 처리해야함
        $myWarPowerMultiply = 1.0;
        $opposeWarPowerMultiply = 1.0;

        $specialWar = $this->getSpecialWar();

        if($specialWar == 52){
            $myWarPowerMultiply *= 1.20;
        }
        else if($specialWar == 60){
            $myWarPowerMultiply *= 1.10;
        }
        else if($specialWar == 61){
            $myWarPowerMultiply *= 1.10;
        }
        else if($specialWar == 50){
            $opposeWarPowerMultiply *= 0.9;
        }
        else if($specialWar == 62){
            $opposeWarPowerMultiply *= 0.9;
        }
        else if($specialWar == 75){
            $opposeCrewType = $this->oppose->getCrewType();
            if($opposeCrewType->reqCities || $opposeCrewType->reqRegions){
                $myWarPowerMultiply *= 1.1;
                $opposeWarPowerMultiply *= 0.9;
            }
        }


        return [$myWarPowerMultiply, $opposeWarPowerMultiply];
    }

    function computeWarPower(){
        [$warPower,$opposeWarPowerMultiply] = parent::computeWarPower();

        $genLevel = $this->raw['level'];

        if($this->isAttacker){
            if($genLevel == 12){
                $warPower *= 1.10;
            }
            else if($genLevel == 11 | $genLevel == 10 || $genLevel == 8 || $genLevel == 6){
                $warPower *= 1.05;
            }
        }
        else{
            if($genLevel == 12){
                $opposeWarPowerMultiply *= 0.90;
            }
            else if($genLevel == 11 || $genLevel == 9 || $genLevel == 7 || $genLevel == 5){
                $opposeWarPowerMultiply *= 0.95;
            }
            else if($genLevel == 4 && $this->raw['no'] == $this->rawCity['gen1']){
                $opposeWarPowerMultiply *= 0.95;
            }
            else if($genLevel == 3 && $this->raw['no'] == $this->rawCity['gen2']){
                $opposeWarPowerMultiply *= 0.95;
            }
            else if($genLevel == 2 && $this->raw['no'] == $this->rawCity['gen3']){
                $opposeWarPowerMultiply *= 0.95;
            }
        }

        $expLevel = $this->raw['explevel'];
        $warPower /= max(0.01, 1 - $expLevel / 300);
        $opposeWarPowerMultiply *= max(0.01, 1 - $expLevel / 300);

        [$specialMyWarPowerMultiply, $specialOpposeWarPowerMultiply] = $this->getWarPowerMultiplyBySpecialWar();
        $warPower *= $specialMyWarPowerMultiply;
        $opposeWarPowerMultiply *= $specialOpposeWarPowerMultiply;

        $this->warPower = $warPower;
        $this->oppose->setWarPowerMultiply($opposeWarPowerMultiply);
        return [$warPower,$opposeWarPowerMultiply];
    }

    ///전투 개시 시에 작동하여 1회에만 작동하는 아이템
    function useBattleInitItem():bool{
        $item = $this->getItem();

        if($item == 0){
            return false;
        }

        $itemActivated = false;
        $itemConsumed = false;
        $itemName = getItemName($item);

        if($item == 3){
            //탁주 사용
            $this->genAtmos += 3;
            $itemActivated = true;
            $itemConsumed = true;
        }
        else if($item >= 14 && $item <= 16){
            //의적주, 두강주, 보령압주 사용
            $this->genAtmos += 5;
            $itemActivated = true;
        }
        else if($item >= 19 && $item <= 20){
            //춘화첩, 초선화 사용
            $this->genAtmos += 7;
            $itemActivated = true;
        }
        else if($item == 4){
            //청주 사용
            $this->genTrain += 3;
            $itemActivated = true;
            $itemConsumed = true;
        }
        else if($item >= 12 && $item <= 13){
            //과실주, 이강주 사용
            $this->genTrain += 5;
            $itemActivated = true;
        }
        else if($item >= 18 && $item <= 18){
            //철벽서, 단결도 사용
            $this->genTrain += 7;
            $itemActivated = true;
        }

        if($itemConsumed){
            $this->raw['item'] = 0;
            $this->updatedVar['item'] = true;
            $josaUl = JosaUtil::pick($itemName, '을');
            $this->getLogger()->pushGeneralActionLog("<C>{$itemName}</>{$josaUl} 사용!", ActionLogger::PLAIN);
        }

        return $itemActivated;
    }

    ///전투 개시 시에 작동하여 매 장수마다 작동하는 스킬
    function checkBattleBeginSkill():bool{
        $skillResult = false;
        if (!$this->sniped  && $this->oppose instanceof WarUnitGeneral) {
            if($this->raw['special2'] == 70 && Util::randBool(1/3)){
                $snipe = true;
            }

            if($snipe){
                $this->sniped = true;
                $skillResult = true;
            }
        }
        return $skillResult;
    }

    ///전투 개시 시에 작동하여 매 장수마다 작동하는 아이템
    function checkBattleBeginItem():bool{
        $item = $this->getItem();
        if(!$item){
            return false;
        }

        $itemActivated = false;
        $itemConsumed = false;
        $itemName = getItemName($item);

        if($item == 2){
            if(!$this->sniped && $this->oppose instanceof WarUnitGeneral && Util::randBool(1/5)){
                $itemActivated = true;
                $itemConsumed = true;
                $this->sniped = true;
            }
        }

        if($itemConsumed){
            $this->raw['item'] = 0;
            $this->updatedVar['item'] = true;
            $josaUl = JosaUtil::pick($itemName, '을');
            $this->getLogger()->pushGeneralActionLog("<C>{$itemName}</>{$josaUl} 사용!", ActionLogger::PLAIN);
        }
        
        return $itemActivated;
    }

    function tryWound():bool{
        if(Util::randBool(0.95)){
            return false;
        }

        $wound = max(80, $this->raw['injury'] + rand(10, 80));
        if($wound < $this->raw['injury']){
            return false;
        }

        $this->raw['injury'] = $wound;
        $this->updatedVar['injury'] = true;
        $this->getLogger()->pushGeneralActionLog("전투중 <R>부상</>당했다!", ActionLogger::PLAIN);

        return true;
    }

    function continueWar(&$noRice):bool{
        if($this->raw['crew'] <= 0){
            $noRice = false;
            return false;
        }
        if($this->raw['rice'] <= 0){
            $noRice = true;
            return false;
        }
        return true;
    }


}