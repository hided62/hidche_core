<?php
namespace sammo;

class WarUnitGeneral extends WarUnit{
    protected $rawCity;

    protected $bonusPhase = 0;

    function __construct($raw, $rawCity, $rawNation, $isAttacker, $year, $month){
        setLeadershipBonus($raw, $rawNation['level']);

        $this->raw = $raw;
        $this->rawCity = $rawCity; //read-only
        $this->rawNation = $rawNation; //read-only
        $this->isAttacker = $isAttacker;

        $this->logger = new ActionLogger(
            $this->getVar('no'), 
            $this->getVar('nation'), 
            $year, 
            $month,
            false
        );
        $this->crewType = GameUnitConst::byID($this->getVar('crewtype'));

        $cityLevel = $this->getCityVar('level');

        if($isAttacker){
            //공격자 보정
            if($cityLevel == 2){
                $this->atmosBonus += 5;
            }
            if($rawNation['capital'] == $this->getCityVar('city')){
                $this->atmosBonus += 5;
            }
        }
        else{
            //수비자 보정
            if($cityLevel == 1){
                $this->trainBonus += 5;
            }
            else if($cityLevel == 3){
                $this->trainBonus += 5;
            }
        }
    }

    function getName():string{
        return $this->getVar('name');
    }

    function getCityVar(string $key){
        return $this->rawCity[$key];
    }
    
    function getSpecialDomestic():int{
        return $this->getVar('special');
    }

    function setOppose(?WarUnit $oppose){
        parent::setOppose($oppose);
        $this->increaseVar('warnum', 1);

        if($this->isAttacker){
            $this->updateVar('recwar', $this->getVar('turntime'));
        }
        else if($oppose !== null){
            $this->updateVar('recwar', $oppose->getVar('turntime'));
        }
    }

    function getSpecialWar():int{
        return $this->getVar('special2');
    }

    function getCharacter():int{
        return $this->getVar('personal');
    }

    function getItem():int{
        return $this->getVar('item');
    }

    function getMaxPhase():int{
        $phase = $this->getCrewType()->speed;
        if($this->getSpecialWar() == 60){
            $phase += 1;
        }
        return $phase + $this->bonusPhase;
    }

    function addTrain(int $train){
        $this->increaseVarWithLimit('train', $train, 0, GameConst::$maxTrainByWar);
    }

    function addAtmos(int $atmos){
        $this->increaseVarWithLimit('atmos', $atmos, 0, GameConst::$maxAtmosByWar);
    }

    function getComputedTrain(){
        $train = $this->getVar('train');
        $train += $this->trainBonus;
        
        return $train;
    }

    function getComputedAtmos(){
        $atmos = $this->getVar('atmos');
        $atmos += $this->atmosBonus;
        
        return $atmos;
    }

    function getComputedCriticalRatio():float{
        $critialRatio = $this->getCrewType()->getCriticalRatio($this->getRaw());

        $specialWar = $this->getSpecialWar();
        $item = $this->getItem();

        if($specialWar == 61){
            if($this->isAttacker){
                $critialRatio += 0.1;
            }
        }
        else if($specialWar == 71){
            $critialRatio += 0.2;
        }
        return $critialRatio;
    }

    function getComputedAvoidRatio():float{
        $specialWar = $this->getSpecialWar();
        $item = $this->getItem();

        $avoidRatio = $this->getCrewType()->avoid / 100;
        $avoidRatio *= $this->getComputedTrain() / 100;

        //특기보정 : 궁병
        if($specialWar == 51){
            $avoidRatio += 0.2;
        }

        //도구 보정 : 둔갑천서, 태평요술
        if($item == 26 || $item == 25){
            $avoidRatio += 0.2;
        }

        if($this->getOppose()->getCrewType()->armType == GameUnitConst::T_FOOTMAN){
            $avoidRatio *= 0.75;
        }

        return $avoidRatio;
    }

    function addWin(){
        $this->increaseVar('killnum', 1);
        $this->multiplyVarWithLimit('atmos', 1.1, null, GameConst::$maxAtmosByWar);

        $this->addStatExp(1);
    }

    function addStatExp(int $value = 1){
        if($this->crewType->armType == GameUnitConst::T_WIZARD) {   // 귀병
            $this->increaseVar('intel2', $value);
        } elseif($this->crewType->armType == GameUnitConst::T_SIEGE) {   // 차병
            $this->increaseVar('leader2', $value);
        } else {
            $this->increaseVar('power2', $value);
        }
    }

    function addLevelExp(float $value){
        if(!$this->isAttacker){
            $value *= 0.8;
        }
        $value *= getCharExpMultiplier($this->getCharacter());
        $this->increaseVar('experience', $value);
    }

    function addDedication(float $value){
        $value *= getCharDedMultiplier($this->getCharacter());
        $this->increaseVar('dedication', $value);
    }

    function addLose(){
        $this->increaseVar('deathnum', 1);
        $this->addStatExp(1);
    }

    

    protected function getWarPowerMultiplyBySpecialWar():array{
        //TODO: 장기적으로 if문이 아니라 객체를 이용하여 처리해야함
        $myWarPowerMultiply = 1.0;
        $opposeWarPowerMultiply = 1.0;

        $specialWar = $this->getSpecialWar();

        if($specialWar == 53){
            if($this->getOppose() instanceof WarUnitCity){
                $myWarPowerMultiply *= 2;
            }
        }
        else if($specialWar == 52){
            if($this->isAttacker){
                $myWarPowerMultiply *= 1.20;
            }
            else{
                $myWarPowerMultiply *= 1.10;
            }
            
        }
        else if($specialWar == 60){
            if($this->isAttacker){
                $myWarPowerMultiply *= 1.10;
            }
            
        }
        else if($specialWar == 61){
            $myWarPowerMultiply *= 1.10;
        }
        else if($specialWar == 50){
            if($this->isAttacker){
                $opposeWarPowerMultiply *= 0.9;
            }
            else{
                $opposeWarPowerMultiply *= 0.8;
            }
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

        $generalNo = $this->getVar('no');
        $genLevel = $this->getVar('level');

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
            else if($genLevel == 4 && $generalNo == $this->getCityVar('gen1')){
                $opposeWarPowerMultiply *= 0.95;
            }
            else if($genLevel == 3 && $generalNo == $this->getCityVar('gen2')){
                $opposeWarPowerMultiply *= 0.95;
            }
            else if($genLevel == 2 && $generalNo == $this->getCityVar('gen3')){
                $opposeWarPowerMultiply *= 0.95;
            }
        }

        $expLevel = $this->getVar('explevel');

        if($this->getOppose() instanceof WarUnitCity){
            $warPower *= 1 + $expLevel / 600;
        }
        else{
            $warPower /= max(0.01, 1 - $expLevel / 300);
            $opposeWarPowerMultiply *= max(0.01, 1 - $expLevel / 300);
        }
        

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
            $this->addAtmos(3);
            $itemActivated = true;
            $itemConsumed = true;
        }
        else if($item >= 14 && $item <= 16){
            //의적주, 두강주, 보령압주 사용
            $this->addAtmosBonus(5);
            $itemActivated = true;
        }
        else if($item >= 19 && $item <= 20){
            //춘화첩, 초선화 사용
            $this->addAtmosBonus(7);
            $itemActivated = true;
        }
        else if($item == 4){
            //청주 사용
            $this->addTrain(3);
            $itemActivated = true;
            $itemConsumed = true;
        }
        else if($item >= 12 && $item <= 13){
            //과실주, 이강주 사용
            $this->addTrainBonus(5);
            $itemActivated = true;
        }
        else if($item >= 18 && $item <= 18){
            //철벽서, 단결도 사용
            $this->addTrainBonus(7);
            $itemActivated = true;
        }

        if($itemConsumed){
            $this->updateVar('item', 0);
            $josaUl = JosaUtil::pick($itemName, '을');
            $this->getLogger()->pushGeneralActionLog("<C>{$itemName}</>{$josaUl} 사용!", ActionLogger::PLAIN);
        }

        return $itemActivated;
    }

    ///전투 개시 시에 작동하여 매 장수마다 작동하는 스킬
    function checkBattleBeginSkill():bool{
        $skillResult = false;
        $oppose = $this->getOppose();

        $specialWar = $this->getSpecialWar();
        if (
            $specialWar == 70 &&
            $this->oppose instanceof WarUnitGeneral &&
            !$this->hasActivatedSkill('저격') &&
            Util::randBool(1/3)
        ) {
            $this->activateSkill('저격');
            $skillResult = true;
        }
        
        return $skillResult;
    }

    ///전투 개시 시에 작동하여 매 장수마다 작동하는 아이템
    function checkBattleBeginItem():bool{
        $item = $this->getItem();
        $oppose = $this->getOppose();
        if(!$item){
            return false;
        }

        $itemActivated = false;
        $itemConsumed = false;
        $itemName = getItemName($item);

        if(
            $item == 2 &&
            $this->oppose instanceof WarUnitGeneral &&
            !$this->hasActivatedSkill('저격') &&
            Util::randBool(1/5)
        ){
            //수극
            $itemActivated = true;
            $itemConsumed = true;
            $this->activateSkill('저격', '수극');
        }

        if($itemConsumed){
            //NOTE: 소비 아이템은 하나인가?, 1회용인가?
            $this->updateVar('item', 0);
            $josaUl = JosaUtil::pick($itemName, '을');
            $this->getLogger()->pushGeneralActionLog("<C>{$itemName}</>{$josaUl} 사용!", ActionLogger::PLAIN);
        }
        
        return $itemActivated;
    }

    function applyBattleBeginSkillAndItem():bool{
        $result = false;
        $oppose = $this->getOppose();

        if($oppose->hasActivatedSkill('저격')){
            $result = true;

            $oppose->getLogger()->pushGeneralActionLog("상대를 <C>저격</>했다!", ActionLogger::PLAIN);
            $oppose->getLogger()->pushGeneralBattleDetailLog("상대를 <C>저격</>했다!", ActionLogger::PLAIN);
            $this->getLogger()->pushGeneralActionLog("상대에게 <R>저격</>당했다!", ActionLogger::PLAIN);
            $this->getLogger()->pushGeneralBattleDetailLog("상대에게 <R>저격</>당했다!", ActionLogger::PLAIN);

            if($oppose->hasActivatedSkill('수극')){
                $this->increaseVarWithLimit('injury', Util::randRangeInt(20, 40), null, 80);
            }
            else{
                $this->increaseVarWithLimit('injury', Util::randRangeInt(20, 60), null, 80);
            }
            
        }

        return $result;
    }

    function getHP():int{
        return $this->getVar('crew');
    }

    function addDex(GameUnitDetail $crewType, float $exp){
        $armType = $crewType->armType;

        if($armType == GameUnitConst::T_CASTLE){
            $armType = GameUnitConst::T_SIEGE;
        }

        if($armType < 0){
            return;
        }

        if($armType == GameUnitConst::T_WIZARD) {
            $exp *= 0.9;
        }
        else if($armType == GameUnitConst::T_SIEGE) {
            $exp *= 0.9;
        }
        $exp *= ($this->getComputedTrain() + $this->getComputedAtmos()) / 200;

        $ntype = $armType*10;
        $dexType = "dex{$ntype}";

        $this->increaseVar($dexType, $exp);
    }

    function decreaseHP(int $damage):int{
        $damage = min($damage, $this->getVar('crew'));

        $this->dead += $damage;
        $this->deadCurr += $damage;
        $this->increaseVar('crew', -$damage);

        $addDex = $damage;
        if(!$this->isAttacker){
            $addDex *= 0.9;
        }
        $this->addDex($this->oppose->getCrewType(), $addDex);

        return $this->getVar('crew');
    }

    function increaseKilled(int $damage):int{
        $this->addLevelExp($damage / 50);

        $rice = $damage / 100;
        if(!$this->isAttacker){
            $rice *= 0.8;
        }

        $rice *= $this->crewType->rice;
        $rice *= getTechCost($this->getNationVar('tech'));

        $this->increaseVarWithLimit('rice', -$rice, 0);
        
        $addDex = $damage;
        if(!$this->isAttacker){
            $addDex *= 0.9;
        }
        $this->addDex($this->getCrewType(), $addDex);

        $this->killed += $damage;
        $this->killedCurr += $damage;
        return $this->killed;
    }

    function checkPreActiveSkill():bool{
        $activated = false;

        $oppose = $this->getOppose();
        $specialWar = $this->getSpecialWar();
        $item = $this->getItem();
        $crewType = $this->getCrewType();

        if(
            $specialWar == 63 &&
            $this->getPhase() == 0 &&
            $this->getHP() >= 1000 &&
            $this->getComputedAtmos() >= 90 &&
            $this->getComputedTrain() >= 90
        ){
            $this->activateSkill('위압');
            $activated = true;
        }

        if($specialWar == 62){
            $oppose->activateSkill('필살불가');
            $oppose->activateSkill('계략약화');
            $activated = true;
        }

        if($specialWar == 60){
            $oppose->activateSkill('회피불가');
            $oppose->activateSkill('저지불가');
        }

        return $activated;
    }

    function checkActiveSkill():bool{
        $activated = false;

        $oppose = $this->getOppose();
        $specialWar = $this->getSpecialWar();
        $item = $this->getItem();
        $crewType = $this->getCrewType();

        if(
            !$this->hasActivatedSkill('특수') &&
            !$this->hasActivatedSkill('저지불가') &&
            !$this->isAttacker &&
            $crewType->name == '목우'
        ){
            //XXX: 병종에 특수 스킬이 달려있도록 설정해야함
            $ratio = $this->getComputedAtmos() + $this->getComputedTrain();
            if(Util::randBool($ratio / 400)){
                $this->activateSkill('특수', '저지');
                $activated = true;
            }
        }

        if(
            !$this->hasActivatedSkill('특수') &&
            !$this->hasActivatedSkill('필살불가') &&
            Util::randBool($this->getComputedCriticalRatio())
        ){
            $this->activateSkill('특수', '필살시도', '필살');
            $activated = true;
        }

        if(
            !$this->hasActivatedSkill('특수') &&
            !$this->hasActivatedSkill('회피불가') &&
            Util::randBool($this->getComputedAvoidRatio())
        ){
            $this->activateSkill('특수', '회피시도', '회피');
            $activated = true;
        }

        //계략
        if($crewType->magicCoef){
            $magicRatio = getGeneralIntel($this->raw, true, true, true, false) / 100;
            $magicRatio *= $crewType->magicCoef;

            if($specialWar == 41){
                $magicRatio += 0.2;
            }

            if(Util::randBool($magicRatio)){
                $magicSuccessRatio = 0.7;
                if($specialWar == 40){
                    $magicSuccessRatio += 0.2;
                }
                if($specialWar == 41){
                    $magicSuccessRatio += 0.2;
                }
                if($specialWar == 42){
                    $magicSuccessRatio += 0.1;
                }
                if($specialWar == 44){
                    $magicSuccessRatio += 1;
                }
                if($this->hasActivatedSkill('계략약화')){
                    $magicSuccessRatio -= 0.1;
                }

                if($oppose instanceof WarUnitCity){
                    $magic = Util::choiceRandom(['급습', '위보', '혼란']);
                }
                else{
                    $magic = Util::choiceRandom(['위보', '매복', '반목', '화계', '혼란']);
                }
                $this->activateSkill('계략시도', $magic);
                
                if(Util::randBool($magicSuccessRatio)){
                    $this->activateSkill('계략');
                }
                else{
                    $this->activateSkill('계략실패');
                }
            }
        }

        //의술
        if($specialWar == 73 && Util::randBool(0.2)){
            $this->activateSkill('치료');
            $activated = true;
        }

        return $activated;
    }

    function checkPostActiveSkill():bool{
        $activated = false;

        $oppose = $this->getOppose();
        $specialWar = $this->getSpecialWar();
        $item = $this->getItem();
        $crewType = $this->getCrewType();

        if($specialWar == 74 && $oppose->hasActivatedSkill('필살')){
            if($this->isAttacker){
                if(Util::randBool(1/3)){
                    $this->activateSkill('진노', '격노');
                    $activated = true;
                }
                else if(Util::randBool(1/4)){
                    $this->activateSkill('격노');
                    $activated = true;
                }
            }
            else{
                if(Util::randBool(1/2)){
                    $this->activateSkill('격노');
                    $activated = true;
                }
            }
        }

        if(
            $specialWar == 74 &&
            $oppose->hasActivatedSkill('회피')
        ){
            if($this->isAttacker){
                if(Util::randBool(1/3)){
                    $this->activateSkill('진노', '격노');
                    $oppose->deactivateSkill('회피');
                    $activated = true;
                }
                else if(Util::randBool(1/4)){
                    $this->activateSkill('격노');
                    $oppose->deactivateSkill('회피');
                    $activated = true;
                }
            }
            else{
                if(Util::randBool(1/2)){
                    $this->activateSkill('격노');
                    $oppose->deactivateSkill('회피');
                    $activated = true;
                }
            }
        }

        if(
            ($item == 23 || $item == 24) &&
            !$this->hasActivatedSkill('치료') &&
            Util::randBool(0.2)
        ){
            $this->activateSkill('치료');
            $activated = true;
        }

        //계략
        if(
            $specialWar == 45 &&
            $oppose->hasActivatedSkill('계략') &&
            Util::randBool(0.3)
        ){
            $this->activateSkill('반계');
            $oppose->deactivateSkill('계략');
            $activated = true;
        }

        if(
            $specialWar == 42 && 
            $this->hasActivatedSkill('계략')
        ){
            $this->warPowerMultiply *= 1.3;
        }

        if(
            $specialWar == 43 && 
            $this->hasActivatedSkill('계략')
        ){
            $this->warPowerMultiply *= 1.5;
        }

        return $activated;
    }

    function applyActiveSkill(){
        $oppose = $this->getOppose();
        $crewType = $this->getCrewType();

        $specialWar = $this->getSpecialWar();

        $thisLogger = $this->getLogger();
        $opposeLogger = $oppose->getLogger();

        if($this->hasActivatedSkill('저지')){
            
            $this->addDex($oppose->getCrewType(), $oppose->getWarPower() * 0.5 * 0.9);
            $this->addDex($this->getCrewType(), $this->getWarPower() * 0.5 * 0.9);

            $this->setWarPowerMultiply(0);
            $oppose->setWarPowerMultiply(0);

            $thisLogger->pushGeneralBattleDetailLog('상대를 <C>저지</>했다!</>');
            $opposeLogger->pushGeneralBattleDetailLog('저지</>당했다!</>');
            //저지는 특수함.

            
            return;
        }

        yield true;

        //계략 세트
        if($this->hasActivatedSkill('계략')){
            $tableToGeneral = [
                '위보'=>1.2,
                '매복'=>1.4,
                '반목'=>1.6,
                '화계'=>1.8,
                '혼란'=>2.0
            ];
            $tableToCity = [
                '급습'=>1.2,
                '위보'=>1.4,
                '혼란'=>1.6
            ];
            if($specialWar == 45){
                $tableToGeneral['반목'] *= 2;
            }

            if($oppose instanceof WarUnitCity){
                $table = $tableToCity;
            }
            else{
                $table = $tableToGeneral;
            }

            foreach($table as $skillKey => $skillMultiply){
                if($this->hasActivatedSkill($skillKey)){
                    $josaUl = \sammo\JosaUtil::pick($skillKey, '을');
                    $thisLogger->pushGeneralBattleDetailLog("<D>{$skillKey}</>{$josaUl} <C>성공</>했다!");
                    $opposeLogger->pushGeneralBattleDetailLog("<D>{$skillKey}</>에 당했다!");

                    $this->multiplyWarPowerMultiply($skillMultiply);
                    break;
                }
            }
        }

        yield true;

        //반계 세트
        if($this->hasActivatedSkill('반계')){
            $table = [
                '위보'=>1.2,
                '매복'=>1.4,
                '반목'=>1.6,
                '화계'=>1.8,
                '혼란'=>2.0
            ];
            foreach($table as $skillKey => $skillMultiply){
                if($oppose->hasActivatedSkill($skillKey)){
                    $josaUl = \sammo\JosaUtil::pick($skillKey, '을');
                    $thisLogger->pushGeneralBattleDetailLog("<C>반계</>로 상대의 <D>{$skillKey}</>{$josaUl} 되돌렸다!");
                    $opposeLogger->pushGeneralBattleDetailLog("<D>{$skillKey}</>{$josaUl} <R>역으로</> 당했다!");

                    $this->multiplyWarPowerMultiply($skillMultiply);
                    break;
                }
            }
        }

        yield true;

        //계략 실패 세트
        if($this->hasActivatedSkill('계략실패')){
            $tableToGeneral = [
                '위보'=>1.1,
                '매복'=>1.2,
                '반목'=>1.3,
                '화계'=>1.4,
                '혼란'=>1.5
            ];
            $tableToCity = [
                '급습'=>1.1,
                '위보'=>1.2,
                '혼란'=>1.3
            ];
            if($oppose instanceof WarUnitCity){
                $table = $tableToCity;
            }
            else{
                $table = $tableToGeneral;
            }
            foreach($table as $skillKey => $skillMultiply){
                if($this->hasActivatedSkill($skillKey)){
                    $josaUl = \sammo\JosaUtil::pick($skillKey, '을');
                    $thisLogger->pushGeneralBattleDetailLog("<D>{$skillKey}</>{$josaUl} <R>실패</>했다!");
                    $opposeLogger->pushGeneralBattleDetailLog("<D>{$skillKey}</>{$josaUl} 간파했다!");

                    $this->multiplyWarPowerMultiply(1/$skillMultiply);
                    $oppose->multiplyWarPowerMultiply($skillMultiply);
                    break;
                }
            }
        }

        yield true;

        if($this->hasActivatedSkill('치료')){
            $thisLogger->pushGeneralBattleDetailLog("<C>치료</>했다!</>");
            $oppose->multiplyWarPowerMultiply(1/1.5);
        }

        yield true;

        if($this->hasActivatedSkill('필살')){
            $thisLogger->pushGeneralBattleDetailLog('<C>필살</>공격!</>');
            $opposeLogger->pushGeneralBattleDetailLog('상대의 <R>필살</>공격!</>');

            $this->multiplyWarPowerMultiply($this->criticalDamage());
        }

        yield true;

        if($this->hasActivatedSkill('회피')){
            $thisLogger->pushGeneralBattleDetailLog('<C>회피</>했다!</>');
            $opposeLogger->pushGeneralBattleDetailLog('상대가 <R>회피</>했다!</>');

            $oppose->multiplyWarPowerMultiply(0.2);
        }

        yield true;

        if($this->hasActivatedSkill('진노')){
            if($oppose->hasActivatedSkill('필살')){
                $thisLogger->pushGeneralBattleDetailLog('상대의 필살 공격에 <C>진노</>했다!</>');
                $opposeLogger->pushGeneralBattleDetailLog('필살 공격에 상대가 <R>진노</>했다!</>');
            }
            else if($oppose->hasActivatedSkill('회피시도')){
                $thisLogger->pushGeneralBattleDetailLog('상대의 회피 시도에 <C>진노</>했다!</>');
                $opposeLogger->pushGeneralBattleDetailLog('회피 시도에 상대가 <R>진노</>했다!</>');
            }
            
            $this->bonusPhase += 1;
            $this->multiplyWarPowerMultiply($this->criticalDamage());
        }
        else if($this->hasActivatedSkill('격노')){
            if($oppose->hasActivatedSkill('필살')){
                $thisLogger->pushGeneralBattleDetailLog('상대의 필살 공격에 <C>격노</>했다!</>');
                $opposeLogger->pushGeneralBattleDetailLog('필살 공격에 상대가 <R>격노</>했다!</>');
            }
            else if($oppose->hasActivatedSkill('회피시도')){
                $thisLogger->pushGeneralBattleDetailLog('상대의 회피 시도에 <C>격노</>했다!</>');
                $opposeLogger->pushGeneralBattleDetailLog('회피 시도에 상대가 <R>격노</>했다!</>');
            }

            $this->multiplyWarPowerMultiply($this->criticalDamage());
        }

        yield true;

        if($this->hasActivatedSkill('위압')){
            $thisLogger->pushGeneralBattleDetailLog('상대에게 <C>위압</>을 줬다!</>');
            $opposeLogger->pushGeneralBattleDetailLog('상대에게 <R>위압</>받았다!</>');

            $oppose->setWarPowerMultiply(0);
        }

        yield true;
    }

    function tryWound():bool{
        if(!Util::randBool(0.05)){
            return false;
        }

        $this->increaseVarWithLimit('injury', Util::randRangeInt(10, 80), null, 80);
        $this->getLogger()->pushGeneralActionLog("전투중 <R>부상</>당했다!", ActionLogger::PLAIN);

        return true;
    }

    function continueWar(&$noRice):bool{
        if($this->getHP() <= 0){
            $noRice = false;
            return false;
        }
        if($this->getVar('rice') <= $this->getHP() / 100){
            $noRice = true;
            return false;
        }
        return true;
    }

    ///checkAbility의 method 버전
    function checkStatChange():bool{
        //FIXME: 장기적으로는 General 클래스가 별도로 있어야 함
        $logger = $this->getLogger();
        $limit = GameConst::$upgradeLimit;

        $table = [
            ['통솔', 'leader'],
            ['무력', 'power'],
            ['지력', 'intel'],
        ];

        $result = false;

        foreach($table as [$statNickName, $statName]){
            $statExpName = $statName.'2';

            if($this->getVar($statExpName) < 0){
                $logger->pushGeneralActionLog("<R>{$statNickName}</>이 <C>1</> 떨어졌습니다!", ActionLogger::PLAIN);
                $this->increaseVar($statExpName, $limit);
                $this->increaseVar($statName, -1);
                $result = true;
            }
            else if($this->getVar($statExpName) >= $limit){
                $logger->pushGeneralActionLog("<R>{$statNickName}</>이 <C>1</> 올랐습니다!", ActionLogger::PLAIN);
                $this->increaseVar($statExpName, -$limit);
                $this->increaseVar($statName, 1);
                $result = true;
            }
        }

        return $result;
    }

    function finishBattle(){    
        if($this->isFinished){
            return;
        }
        $this->clearActivatedSkill();
        $this->isFinished = true;

        $this->increaseVar('killcrew', $this->killed);
        $this->increaseVar('deathcrew', $this->dead);
        
        $this->updateVar('rice', Util::round($this->getVar('rice')));
        $this->updateVar('experience', Util::round($this->getVar('experience')));
        $this->updateVar('dedication', Util::round($this->getVar('dedication')));

        $this->checkStatChange();
    }

    /**
     * @param \MeekroDB $db
     */
    function applyDB($db):bool{
        $updateVals = [];
        foreach(array_keys($this->updatedVar) as $key){
            $updateVals[$key] = $this->raw[$key];
        }

        if(!$updateVals){
            return false;
        }
        
        $db->update('general', $updateVals, 'no=%i', $this->raw['no']);
        $this->getLogger()->flush();
        return $db->affectedRows() > 0;
    }

}