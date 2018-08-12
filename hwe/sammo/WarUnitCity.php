<?php
namespace sammo;

class WarUnitCity extends WarUnit{
    protected $raw;
    
    protected $logger;
    protected $crewType;

    protected $killed = 0;
    protected $death = 0;
    protected $rice = 0;

    protected $updatedVar = [];

    protected $def;
    protected $wall;
    

    function __construct($raw, $rawNation, $year, $month){
        $this->raw = $raw;
        $this->rawNation = $rawNation;
        $this->isAttacker = false;

        $this->logger = new ActionLogger(0, $raw['nation'], $year, $month);
        $this->crewType = GameUnitConst::byID($raw['crewtype']);

        $this->def = $raw['def'] * 10;
        $this->wall = $raw['wall'] * 10;
        $this->rice = $rawNation['rice'];

        $this->crewType = GameUnitConst::byID(GameUnitConst::T_CASTLE);
    }

    function getRaw():array{
        return $this->raw;
    }

    function getName():string{
        return $this->raw['name'];
    }

    function getCrewType():GameUnitConst{
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
        //전투가 가능하면 true
        $noRice = false;
        if($this->def <= 0){
            return false;
        }

        //도시 성벽은 쌀이 소모된다고 항복하지 않음
        return true;
    }

}