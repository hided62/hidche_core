<?php
namespace sammo\Command\General;

use \sammo\{
    DB, JosaUtil,
    General,
    GameConst,
    Command,
};

use \sammo\Constraint\ConstraintHelper;
use sammo\Enums\InheritanceKey;

class che_등용수락 extends Command\GeneralCommand{
    static protected $actionName = '등용 수락';
    static public $reqArg = true;

    protected function argTest():bool{
        if($this->arg === null){
            return false;
        }

        if(!key_exists('destGeneralID', $this->arg)){
            return false;
        }
        $destGeneralID = $this->arg['destGeneralID'];
        if(!is_int($destGeneralID)){
            return false;
        }
        if($destGeneralID <= 0){
            return false;
        }
        if($destGeneralID == $this->generalObj->getID()){
            return false;
        }

        if(!key_exists('destNationID', $this->arg)){
            return false;
        }
        $destNationID = $this->arg['destNationID'];
        if(!is_int($destNationID)){
            return false;
        }
        if($destNationID <= 0){
            return false;
        }
        if($destNationID == $this->generalObj->getNationID()){
            return false;
        }

        $this->arg = [
            'destGeneralID'=>$destGeneralID,
            'destNationID'=>$destNationID,
        ];
        return true;
    }

    protected function init()
    {

        $general = $this->generalObj;
        $this->setNation(['gennum', 'scout']);

        $this->permissionConstraints = [
            ConstraintHelper::AlwaysFail('예약 불가능 커맨드')
        ];
    }

    protected function initWithArg()
    {
        $destGeneral = General::createGeneralObjFromDB($this->arg['destGeneralID'], null, 2);
        $this->setDestGeneral($destGeneral);
        $this->setDestNation($this->arg['destNationID'], ['gennum', 'scout']);

        $relYear = $this->env['year'] - $this->env['startyear'];

        $this->fullConditionConstraints=[
            ConstraintHelper::ReqEnvValue('join_mode', '!=', 'onlyRandom', '랜덤 임관만 가능합니다'),
            ConstraintHelper::ExistsDestNation(),
            ConstraintHelper::AllowJoinDestNation($relYear),
            ConstraintHelper::ReqDestNationValue('level', '국가규모', '>', 0, '방랑군에는 임관할 수 없습니다.'),
            ConstraintHelper::DifferentDestNation(),
            ConstraintHelper::ReqGeneralValue('officer_level', '직위', '!=', 12, '군주는 등용장을 수락할 수 없습니다')
        ];
    }

    public function canDisplay():bool{
        return false;
    }

    public function getCost():array{
        return [0, 0];
    }

    public function getPreReqTurn():int{
        return 0;
    }

    public function getPostReqTurn():int{
        return 0;
    }

    public function run(\Sammo\RandUtil $rng):bool{
        if(!$this->hasFullConditionMet()){
            throw new \RuntimeException('불가능한 커맨드를 강제로 실행 시도');
        }

        $db = DB::db();
        $env = $this->env;

        $general = $this->generalObj;
        $generalID = $general->getID();
        $generalName = $general->getName();
        $cityID = $general->getVar('city');
        $nationID = $general->getNationID();

        $destGeneral = $this->destGeneralObj;
        $destNationID = $this->destNation['nation'];
        $destNationName = $this->destNation['name'];

        $isTroopLeader = ($generalID == $general->getVar('troop'));

        $destGeneral->addExperience(100);
        $destGeneral->addDedication(100);

        $setOriginalNationValues = [
            'gennum'=>$db->sqleval('gennum - 1')
        ];

        $setScoutNationValues = [
            'gennum'=>$db->sqleval('gennum + 1')
        ];

        if($nationID != 0){
            // 기본 금액 남기고 환수
            if($general->getVar('gold') > GameConst::$defaultGold){
                $setOriginalNationValues['gold'] = $db->sqleval('gold + %i', $general->getVar('gold') - GameConst::$defaultGold);
                $general->setVar('gold', GameConst::$defaultGold);
            }

            if($general->getVar('rice') > GameConst::$defaultRice){
                $setOriginalNationValues['rice'] = $db->sqleval('rice + %i', $general->getVar('rice') - GameConst::$defaultRice);
                $general->setVar('rice', GameConst::$defaultRice);
            }

            // 재야가 아니면 명성N*10% 공헌N*10%감소
            $general->setVar('experience', $general->getVar('experience') * (1 - 0.1 * $general->getVar('betray')));
            $general->addExperience(0, false);
            $general->setVar('dedication', $general->getVar('dedication') * (1 - 0.1 * $general->getVar('betray')));
            $general->addDedication(0, false);
            $general->increaseVarWithLimit('betray', 1, null, GameConst::$maxBetrayCnt);
        }
        else{
            $general->addExperience(100);
            $general->addDedication(100);
        }

        if($general->getNPCType() < 2){
            $general->setVar('killturn', $env['killturn']);
        }


        $logger = $general->getLogger();
        $destLogger = $destGeneral->getLogger();

        $josaRo = JosaUtil::pick($destNationName, '로');
        $josaYi = JosaUtil::pick($generalName, '이');
        $logger->pushGeneralActionLog("<D>{$destNationName}</>{$josaRo} 망명하여 수도로 이동합니다.");
        $destLogger->pushGeneralActionLog("<Y>{$generalName}</> 등용에 성공했습니다.");

        $logger->pushGeneralHistoryLog("<D>{$destNationName}</>{$josaRo} 망명");
        $destLogger->pushGeneralHistoryLog("<Y>{$generalName}</> 등용에 성공");

        $logger->pushGlobalActionLog("<Y>{$generalName}</>{$josaYi} <D><b>{$destNationName}</b></>{$josaRo} <S>망명</>하였습니다.");

        if($nationID != 0){
            $db->update('nation', $setOriginalNationValues, 'nation=%i', $nationID);
        }
        $db->update('nation', $setScoutNationValues, 'nation=%i', $destNationID);

        $general->increaseInheritancePoint(InheritanceKey::active_action, 1);
        if($general->getNPCType() < 2){
            $general->setAuxVar(
                InheritanceKey::max_belong->value,
                max(
                    $general->getVar('belong'),
                    $general->getAuxVar(InheritanceKey::max_belong->value) ?? 0
                )
            );
        }
        $general->setVar('permission', 'normal');
        $general->setVar('belong', 1);
        $general->setVar('officer_level', 1);
        $general->setVar('officer_city', 0);
        $general->setVar('nation', $destNationID);
        $general->setVar('city', $this->destNation['capital']);
        $general->setVar('troop', 0);

        if($isTroopLeader){
            // 모두 탈퇴
            $db->update('general', [
                'troop'=>0,
            ], 'troop_leader=%i', $generalID);
            // 부대 삭제
            $db->delete('troop', 'troop_leader=%i', $generalID);
        }


        $general->applyDB($db);
        $destGeneral->applyDB($db);

        return true;
    }
}