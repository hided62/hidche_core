<?php
namespace sammo;

class WarUnitGeneral extends WarUnit{
    protected $bonusPhase = 0;

    function __construct(General $general, array $rawNation, bool $isAttacker){
        $this->general = $general;
        $this->raw = $general->getRaw();
        $this->rawNation = $rawNation; //read-only
        $this->isAttacker = $isAttacker;

        $this->logger = $this->general->getLogger();
        $this->crewType = $this->general->getCrewTypeObj();

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
        return $this->general->getName();
    }

    function getCityVar(string $key){
        return $this->general->getRawCity()[$key];
    }
    
    function setOppose(?WarUnit $oppose){
        parent::setOppose($oppose);
        $general = $this->general;
        $this->general->increaseVar('warnum', 1);

        if($this->isAttacker){
            $general->updateVar('recwar', $general->getVar('turntime'));
        }
        else if($oppose !== null){
            $general->updateVar('recwar', $oppose->getVar('turntime'));
        }
    }

    function getMaxPhase():int{
        $phase = $this->getCrewType()->speed;
        $phase = $this->general->onCalcStat($this->general, 'initWarPhase', $phase);
        return $phase + $this->bonusPhase;
    }

    function addTrain(int $train){
        $this->general->increaseVarWithLimit('train', $train, 0, GameConst::$maxTrainByWar);
    }

    function addAtmos(int $atmos){
        $this->general->increaseVarWithLimit('atmos', $atmos, 0, GameConst::$maxAtmosByWar);
    }

    function getComputedTrain(){
        $train = $this->general->getVar('train');
        $train = $this->general->onCalcStat($this->general, 'bonusTrain', $train);
        $train += $this->trainBonus;
        
        return $train;
    }

    function getComputedAtmos(){
        $atmos = $this->general->getVar('atmos');
        $atmos = $this->general->onCalcStat($this->general, 'bonusAtmos', $atmos);
        $atmos += $this->atmosBonus;
        
        return $atmos;
    }

    function getComputedCriticalRatio():float{
        $general = $this->general;
        $criticalRatio = $this->getCrewType()->getCriticalRatio($general);

        $criticalRatio = $general->onCalcStat($general, 'warCriticalRatio', $criticalRatio, ['isAttacker'=>$this->isAttacker]);
        return $criticalRatio;
    }

    function getComputedAvoidRatio():float{
        $general = $this->general;

        $avoidRatio = $this->getCrewType()->avoid / 100;
        $avoidRatio *= $this->getComputedTrain() / 100;

        $avoidRatio = $general->onCalcStat($general, 'warAvoidRatio', $avoidRatio, ['isAttacker'=>$this->isAttacker]);

        if($this->getOppose()->getCrewType()->armType == GameUnitConst::T_FOOTMAN){
            $avoidRatio *= 0.75;
        }

        return $avoidRatio;
    }

    function addWin(){
        $general = $this->general;
        $general->increaseVar('killnum', 1);
        $general->multiplyVarWithLimit('atmos', 1.1, null, GameConst::$maxAtmosByWar);

        $this->addStatExp(1);
    }

    function addStatExp(int $value = 1){
        $general = $this->general;
        if($this->crewType->armType == GameUnitConst::T_WIZARD) {   // 귀병
            $general->increaseVar('intel2', $value);
        } elseif($this->crewType->armType == GameUnitConst::T_SIEGE) {   // 차병
            $general->increaseVar('leader2', $value);
        } else {
            $general->increaseVar('power2', $value);
        }
    }

    function addLevelExp(float $value){
        $general = $this->general;
        if(!$this->isAttacker){
            $value *= 0.8;
        }
        $value = $general->onCalcStat($general, 'experience', $value);
        $general->increaseVar('experience', $value);
    }

    function addDedication(float $value){
        $general = $this->general;
        $value = $general->onCalcStat($general, 'dedication', $value);
        $general->increaseVar('dedication', $value);
    }

    function addLose(){
        $general = $this->general;
        $general->increaseVar('deathnum', 1);
        $this->addStatExp(1);
    }

    function computeWarPower(){
        [$warPower,$opposeWarPowerMultiply] = parent::computeWarPower();

        $general = $this->general;
        $generalNo = $general->getVar('no');
        $genLevel = $general->getVar('level');

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

        $expLevel = $general->getVar('explevel');

        if($this->getOppose() instanceof WarUnitCity){
            $warPower *= 1 + $expLevel / 600;
        }
        else{
            $warPower /= max(0.01, 1 - $expLevel / 300);
            $opposeWarPowerMultiply *= max(0.01, 1 - $expLevel / 300);
        }
        

        [$specialMyWarPowerMultiply, $specialOpposeWarPowerMultiply] = $this->general->getWarPowerMultiplier($this);
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
        $general = $this->general;

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
            $general->updateVar('item', 0);
            $josaUl = JosaUtil::pick($itemName, '을');
            $this->getLogger()->pushGeneralActionLog("<C>{$itemName}</>{$josaUl} 사용!", ActionLogger::PLAIN);
        }

        return $itemActivated;
    }

    ///전투 개시 시에 작동하여 매 장수마다 작동하는 스킬
    function checkBattleBeginSkill(){
        $oppose = $this->getOppose();

        $specialWar = $this->getSpecialWar();

        if(
            $this->getCrewType()->armType ==  GameUnitConst::T_SIEGE &&
            $oppose->getCrewType()->armType == GameUnitConst::T_CASTLE
        ){
            $this->activateSkill('부상무효');
        }
        yield true;

        if($specialWar == 62){
            $oppose->activateSkill('저격불가');
            $this->activateSkill('부상무효');
        }
        yield true;

        if (
            $specialWar == 70 &&
            $this->oppose instanceof WarUnitGeneral &&
            !$this->hasActivatedSkill('저격') &&
            !$this->hasActivatedSkill('저격불가') &&
            Util::randBool(1/3)
        ) {
            $this->activateSkill('저격');
        }
        yield true;
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
            !$this->hasActivatedSkill('저격불가') &&
            Util::randBool(1/5)
        ){
            //수극
            $itemActivated = true;
            $itemConsumed = true;
            $this->activateSkill('저격', '수극');
        }

        if($itemConsumed){
            //NOTE: 소비 아이템은 하나인가?, 1회용인가?
            $this->general->updateVar('item', 0);
            $josaUl = JosaUtil::pick($itemName, '을');
            $this->getLogger()->pushGeneralActionLog("<C>{$itemName}</>{$josaUl} 사용!", ActionLogger::PLAIN);
        }
        
        return $itemActivated;
    }

    function applyBattleBeginSkillAndItem():bool{
        $result = false;
        $oppose = $this->getOppose();
        $general = $this->general;

        if($oppose->hasActivatedSkill('저격')){
            $result = true;

            $oppose->getLogger()->pushGeneralActionLog("상대를 <C>저격</>했다!", ActionLogger::PLAIN);
            $oppose->getLogger()->pushGeneralBattleDetailLog("상대를 <C>저격</>했다!", ActionLogger::PLAIN);
            $this->getLogger()->pushGeneralActionLog("상대에게 <R>저격</>당했다!", ActionLogger::PLAIN);
            $this->getLogger()->pushGeneralBattleDetailLog("상대에게 <R>저격</>당했다!", ActionLogger::PLAIN);

            if($oppose->hasActivatedSkill('수극')){
                $general->increaseVarWithLimit('injury', Util::randRangeInt(20, 40), null, 80);
            }
            else{
                $general->increaseVarWithLimit('injury', Util::randRangeInt(20, 60), null, 80);
            }
            
        }

        return $result;
    }

    function getHP():int{
        return $this->general->getVar('crew');
    }

    function addDex(GameUnitDetail $crewType, float $exp){
        $general = $this->general;
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

        $general->increaseVar($dexType, $exp);
    }

    function decreaseHP(int $damage):int{
        $general = $this->general;
        $damage = min($damage, $general->getVar('crew'));

        $this->dead += $damage;
        $this->deadCurr += $damage;
        $general->increaseVar('crew', -$damage);

        $addDex = $damage;
        if(!$this->isAttacker){
            $addDex *= 0.9;
        }
        $this->addDex($this->oppose->getCrewType(), $addDex);

        return $general->getVar('crew');
    }

    function increaseKilled(int $damage):int{
        $general = $this->general;
        $this->addLevelExp($damage / 50);

        $rice = $damage / 100;
        if(!$this->isAttacker){
            $rice *= 0.8;
        }

        $rice *= $this->crewType->rice;
        $rice *= getTechCost($this->getNationVar('tech'));

        $general->increaseVarWithLimit('rice', -$rice, 0);
        
        $addDex = $damage;
        if(!$this->isAttacker){
            $addDex *= 0.9;
        }
        $this->addDex($this->getCrewType(), $addDex);

        $this->killed += $damage;
        $this->killedCurr += $damage;
        return $this->killed;
    }

    function checkPreActiveSkill(){
        $activated = false;

        $oppose = $this->getOppose();
        $specialWar = $this->getSpecialWar();
        $item = $this->getItem();
        $crewType = $this->getCrewType();

        if($specialWar == 62){
            $oppose->activateSkill('필살불가');
            $oppose->activateSkill('위압불가');
            $oppose->activateSkill('격노불가');
            $oppose->activateSkill('계략약화');
        }
        yield true;

        if($specialWar == 45){
            $oppose->activateSkill('계략약화');
        }
        yield true;

        if(
            $specialWar == 63 &&
            $this->getPhase() == 0 &&
            $this->getHP() >= 1000 &&
            $this->getComputedAtmos() >= 90 &&
            $this->getComputedTrain() >= 90 &&
            !$this->hasActivatedSkill('위압불가')
        ){
            $this->activateSkill('위압');
        }
        yield true;

        if($specialWar == 60){
            $oppose->activateSkill('회피불가');
        }
        yield true;
    }

    function checkActiveSkill(){
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
            }
        }
        yield true;

        if(
            !$this->hasActivatedSkill('특수') &&
            !$this->hasActivatedSkill('필살불가') &&
            Util::randBool($this->getComputedCriticalRatio())
        ){
            $this->activateSkill('특수', '필살시도', '필살');
        }
        yield true;

        if(
            !$this->hasActivatedSkill('특수') &&
            !$this->hasActivatedSkill('회피불가') &&
            Util::randBool($this->getComputedAvoidRatio())
        ){
            $this->activateSkill('특수', '회피시도', '회피');
        }
        yield true;

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
        yield true;

        //의술
        if($specialWar == 73 && Util::randBool(0.2)){
            $this->activateSkill('치료');
        }
        yield true;
    }

    function checkPostActiveSkill(){
        $activated = false;

        $oppose = $this->getOppose();
        $specialWar = $this->getSpecialWar();
        $item = $this->getItem();
        $crewType = $this->getCrewType();

        if(
            $specialWar == 74 &&
            $oppose->hasActivatedSkill('필살') &&
            !$this->hasActivatedSkill('격노불가')
        ){
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
        yield true;

        if(
            $specialWar == 74 &&
            $oppose->hasActivatedSkill('회피') &&
            !$this->hasActivatedSkill('격노불가')
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
        yield true;

        if(
            ($item == 23 || $item == 24) &&
            !$this->hasActivatedSkill('치료') &&
            Util::randBool(0.2)
        ){
            $this->activateSkill('치료');
            $activated = true;
        }
        yield true;

        //계략
        if(
            $specialWar == 45 &&
            $oppose->hasActivatedSkill('계략') &&
            Util::randBool(0.4)
        ){
            $this->activateSkill('반계');
            $oppose->deactivateSkill('계략');
            $activated = true;
        }
        yield true;

        if(
            $specialWar == 42 && 
            $this->hasActivatedSkill('계략')
        ){
            $this->warPowerMultiply *= 1.3;
        }
        yield true;

        if(
            $specialWar == 43 && 
            $this->hasActivatedSkill('계략')
        ){
            $this->warPowerMultiply *= 1.5;
        }
        yield true;
    }

    function applyActiveSkill(){
        $oppose = $this->getOppose();
        $crewType = $this->getCrewType();

        $specialWar = $this->getSpecialWar();

        $thisLogger = $this->getLogger();
        $opposeLogger = $oppose->getLogger();

        if($this->hasActivatedSkill('저지')){
            
            $this->addDex($oppose->getCrewType(), $oppose->getWarPower() * 0.9);
            $this->addDex($this->getCrewType(), $this->getWarPower() * 0.9);

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
        $general = $this->general;
        if($this->hasActivatedSkillOnLog('부상무효')){
            return false;
        }
        if(!Util::randBool(0.05)){
            return false;
        }

        $general->increaseVarWithLimit('injury', Util::randRangeInt(10, 80), null, 80);
        $this->getLogger()->pushGeneralActionLog("전투중 <R>부상</>당했다!", ActionLogger::PLAIN);

        return true;
    }

    function continueWar(&$noRice):bool{
        $general = $this->general;
        if($this->getHP() <= 0){
            $noRice = false;
            return false;
        }
        if($general->getVar('rice') <= $this->getHP() / 100){
            $noRice = true;
            return false;
        }
        return true;
    }

    function checkStatChange():bool{
        return $this->general->checkStatChange();
    }

    function finishBattle(){    
        if($this->isFinished){
            return;
        }
        $this->clearActivatedSkill();
        $this->isFinished = true;
        $general = $this->general;

        $general->increaseVar('killcrew', $this->killed);
        $general->increaseVar('deathcrew', $this->dead);
        
        $general->updateVar('rice', Util::round($general->getVar('rice')));
        $general->updateVar('experience', Util::round($general->getVar('experience')));
        $general->updateVar('dedication', Util::round($general->getVar('dedication')));

        $this->checkStatChange();
    }

    /**
     * @param \MeekroDB $db
     */
    function applyDB($db):bool{
        $updateVals = $this->getUpdatedValues();

        if(!$updateVals){
            return false;
        }
        
        $db->update('general', $updateVals, 'no=%i', $this->raw['no']);
        $this->getLogger()->flush();
        return $db->affectedRows() > 0;
    }

}