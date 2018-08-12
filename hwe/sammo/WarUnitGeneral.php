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
    
    function getCrewType():GameUnitDetail{
        return $this->crewType;
    }

    function getLogger():ActionLogger{
        return $this->logger;
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
            $this->getLogger()->generalActionLog("<C>●</><C>{$itemName}</>{$josaUl} 사용!");
        }

        return $itemActivated;
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