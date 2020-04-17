<?php
namespace sammo\Command\General;

use \sammo\{
    DB, Util, JosaUtil,
    General, DummyGeneral,
    ActionLogger,
    GameConst,
    LastTurn,
    Json,
    GameUnitConst,
    Command,
    ScoutMessage
};

use function \sammo\{
    getDomesticExpLevelBonus,
    CriticalRatioDomestic, 
    CriticalScoreEx,
    tryUniqueItemLottery,
    getAllNationStaticInfo,
    getNationStaticInfo
};

use \sammo\Constraint\Constraint;
use \sammo\Constraint\ConstraintHelper;


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

    protected function init(){

        $general = $this->generalObj;
        $this->setNation(['gennum', 'scout']);

        $destGeneral = General::createGeneralObjFromDB($this->arg['destGeneralID'], ['nation'], 0);
        $this->setDestGeneral($destGeneral);
        $this->setDestNation($this->arg['destNationID'], ['gennum', 'scout']);

        $relYear = $this->env['year'] - $this->env['startyear'];

        $this->reservableConstraints = [
            ConstraintHelper::AlwaysFail('예약 불가능 커맨드')
        ];
        
        $this->runnableConstraints=[
            ConstraintHelper::ReqEnvValue('join_mode', '==', 'onlyRandom', '랜덤 임관만 가능합니다'),
            ConstraintHelper::NotOpeningPart($relYear),
            ConstraintHelper::ExistsDestNation(),
            ConstraintHelper::AllowJoinDestNation($relYear),
            ConstraintHelper::ReqDestNationValue('level', '국가규모', '>', 0, '방랑군에는 임관할 수 없습니다.'),
            ConstraintHelper::DifferentDestNation(),
            ConstraintHelper::ReqGeneralValue('officer_level', '직위', '!=', 12, '군주는 등용장을 수락할 수 없습니다')
        ];
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

    public function run():bool{
        if(!$this->isRunnable()){
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

        $relYear = $env['year'] - $env['startyear'];
        if($general->getVar('npc') == 1 || $relYear >= 3){
            $joinedNations = $general->getAuxVar('joinedNations')??[];
            $joinedNations[] = $destNationID;
            $general->setAuxVar('joinedNations', $joinedNations);
        }

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

            $officerLevel = $general->getVar('officer_level');
            if(5 <= $officerLevel && $officerLevel <= 11){
                $setOriginalNationValues["l{$officerLevel}set"] = 0;
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

        if($general->getVar('npc') < 2){
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

    public function getForm(): string
    {
        $db = DB::db();

        $destGenerals = [];
        $destRawGenerals = $db->query('SELECT no,name,npc,nation FROM general WHERE npc < 2 AND no != %i AND officer_level != 12 ORDER BY npc,binary(name)',$this->generalObj->getID());
        foreach($destRawGenerals as $destGeneral){
            $destNationID = $destGeneral['nation'];
            if(!key_exists($destNationID, $destGenerals)){
                $destGenerals[$destNationID] = [];
            }
            $destGenerals[$destNationID] = [$destGeneral];
        }

        $nationList = array_merge([0=>getNationStaticInfo(0)], getAllNationStaticInfo());

        ob_start();
?>
재야나 타국의 장수를 등용합니다.<br>
서신은 개인 메세지로 전달됩니다.<br>
등용할 장수를 목록에서 선택하세요.<br>
<select class='formInput' name="destGeneralID" id="destGeneralID" size='1' style='color:white;background-color:black;'>
<?php foreach($nationList as $destNation): ?>
    <optgroup style='color:<?=$destNation['color']?>'>【<?=$destNation['name']?>】
<?php   foreach($destGenerals[$destNation['nation']]??[] as $destGeneral):
            $nameColor = \sammo\getNameColor($destGeneral['npc']);
            if($nameColor){
                $nameColor = " style='color:{$nameColor}'";
            }
?>
            <option value='<?=$destGeneral['no']?>' <?=$nameColor?>><?=$destGeneral['name']?></option>
<?php   endforeach; ?>
    </optgroup>
<?php endforeach; ?>

</select> <input type=button id="commonSubmit" value="<?=$this->getName()?>"><br>
        <?php
                return ob_get_clean();
    }
}