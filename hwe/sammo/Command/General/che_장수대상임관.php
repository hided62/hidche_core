<?php
namespace sammo\Command\General;

use \sammo\{
    DB, Util, JosaUtil,
    General, 
    ActionLogger,
    GameConst, GameUnitConst,
    LastTurn,
    Command,
    Json,
    KVStorage
};

use function \sammo\getColoredName;
use function \sammo\tryUniqueItemLottery;
use function \sammo\getInvitationList;
use function \sammo\getNationStaticInfo;

use \sammo\Constraint\Constraint;
use \sammo\Constraint\ConstraintHelper;
use sammo\CityConst;



class che_장수대상임관 extends Command\GeneralCommand{
    static protected $actionName = '따라 임관';
    static public $reqArg = true;

    protected function argTest():bool{
        if($this->arg === null){
            return false;
        }
        $destGeneralID = $this->arg['destGeneralID']??null;

        if($destGeneralID === null){
            return false;
        }

        if(!is_int($destGeneralID)){
            return false;
        }
        if($destGeneralID < 1){
            return false;
        }
        if($destGeneralID == $this->generalObj->getID()){
            return false;
        }

        $this->arg = [
            'destGeneralID' => $destGeneralID
        ];

        return true;
    }

    protected function init(){

        $general = $this->generalObj;
        $env = $this->env;

        $this->setCity();
        $this->setNation();

        $relYear = $env['year'] - $env['startyear'];

        $this->permissionConstraints=[
            ConstraintHelper::ReqEnvValue('join_mode', '!=', 'onlyRandom', '랜덤 임관만 가능합니다')
        ];

        $this->minConditionConstraints=[
            ConstraintHelper::ReqEnvValue('join_mode', '!=', 'onlyRandom', '랜덤 임관만 가능합니다'),
            ConstraintHelper::BeNeutral(),
            ConstraintHelper::AllowJoinAction()
        ];
        
    }

    public function getCommandDetailTitle():string{
        return '장수를 따라 임관';
    }

    public function canDisplay():bool{
        return $this->env['join_mode'] !== 'onlyRandom';
    }

    protected function initWithArg()
    {
        $destGeneralID = $this->arg['destGeneralID'];
        $destGeneral = General::createGeneralObjFromDB($this->arg['destGeneralID'], ['nation'], 0);
        $this->setDestGeneral($destGeneral);
        $this->setDestNation($this->destGeneralObj->getVar('nation'), ['gennum', 'scout']);

        $env = $this->env;
        $relYear = $env['year'] - $env['startyear'];
        $this->fullConditionConstraints=[
            ConstraintHelper::ReqEnvValue('join_mode', '!=', 'onlyRandom', '랜덤 임관만 가능합니다'),
            ConstraintHelper::BeNeutral(),
            ConstraintHelper::ExistsDestNation(),
            ConstraintHelper::AllowJoinDestNation($relYear),
            ConstraintHelper::AllowJoinAction()
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

    public function getBrief():string{
        $destGeneralName = $this->destGeneralObj->getName();
        $josaUl = JosaUtil::pick($destGeneralName, '을');
        return "【{$destGeneralName}】{$josaUl} 따라 임관";
    }

    public function run():bool{
        if(!$this->hasFullConditionMet()){
            throw new \RuntimeException('불가능한 커맨드를 강제로 실행 시도');
        }

        $db = DB::db();
        $env = $this->env;

        $general = $this->generalObj;
        $date = $general->getTurnTime($general::TURNTIME_HM);
        $generalName = $general->getName();
        $josaYi = JosaUtil::pick($generalName, '이');

        $destNation = $this->destNation;
        $gennum = $destNation['gennum'];
        $destNationID = $destNation['nation'];
        $destNationName = $destNation['name'];

        $logger = $general->getLogger();

        $logger->pushGeneralActionLog("<D>{$destNationName}</>에 임관했습니다. <1>$date</>");
        $logger->pushGeneralHistoryLog("<D><b>{$destNationName}</b></>에 임관");
        $logger->pushGlobalActionLog("<Y>{$generalName}</>{$josaYi} <D><b>{$destNationName}</b></>에 <S>임관</>했습니다.");

        if($gennum < GameConst::$initialNationGenLimit) {
            $exp = 700;
        }
        else {
            $exp = 100;
        }

        $general->setVar('nation', $destNationID);
        $general->setVar('officer_level', 1);
        $general->setVar('officer_city', 0);
        $general->setVar('belong', 1);
        
        if($this->destGeneralObj !== null){
            $general->setVar('city', $this->destGeneralObj->getCityID());
        }
        else{
            $targetCityID = $db->queryFirstField('SELECT city FROM general WHERE nation = %i AND officer_level=12', $destNationID);
            $general->setVar('city', $targetCityID);
        }

        $db->update('nation', [
            'gennum'=>$db->sqleval('gennum + 1')
        ], 'nation=%i', $destNationID);
        \sammo\refreshNationStaticInfo();

        $relYear = $env['year'] - $env['startyear'];
        if($general->getNPCType() == 1 || $relYear >= 3){
            $joinedNations = $general->getAuxVar('joinedNations')??[];
            $joinedNations[] = $destNationID;
            $general->setAuxVar('joinedNations', $joinedNations);
        }

        $general->addExperience($exp);
        $this->setResultTurn(new LastTurn(static::getName(), $this->arg));
        $general->checkStatChange();
        tryUniqueItemLottery($general);
        $general->applyDB($db);

        return true;
    }

    public function getForm(): string
    {
        $db = DB::db();

        $generalObj = $this->generalObj;

        $env = $this->env;

        $joinedNations = $generalObj->getAuxVar('joinedNations')??[];
        $generalList = $db->query('SELECT no,name,nation,npc FROM general WHERE no!=%i ORDER BY name ASC', $generalObj->getID());


        $nationList = $db->query('SELECT nation,`name`,color,scout,gennum FROM nation');
        shuffle($nationList);
        $nationList = Util::convertArrayToDict($nationList, 'nation');
        //NOTE: join 안할것임
        $scoutMsgs = KVStorage::getValuesFromInterNamespace($db, 'nation_env', 'scout_msg');
        foreach($scoutMsgs as $nationID=>$scoutMsg){
            $nationList[$nationID]['scoutmsg'] = $scoutMsg;
        }

        $hiddenItems = [];

        foreach($nationList as &$nation){
            $nation['hideen'] = false;
            if($env['year'] < $env['startyear']+3 && $nation['gennum'] >= GameConst::$initialNationGenLimit){
                $nation['availableJoin'] = false;
            }
            else if($nation['scout'] == 1) {
                $nation['availableJoin'] = false;
            }
            else{
                $nation['availableJoin'] = true;
            }

            if(in_array($nation['nation'], $joinedNations)){
                $nation['availableJoin'] = false;
            }

            if(Util::starts_with($nation['name'], 'ⓤ')){
                $hiddenItems[$nation['nation']] = $nation['nation'];
            }
        }
        unset($nation);
        ob_start(); 
?>
장수를 따라 임관합니다.<br>
이미 임관/등용되었던 국가는 다시 임관할 수 없습니다.<br>
바로 군주의 위치로 이동합니다.<br>
임관할 국가를 목록에서 선택하세요.<br>   
<select class='formInput' name="destGeneralID" id="destGeneralID" size='1' style='color:white;background-color:black;'>
<?php foreach($generalList as $targetGeneral): ?>
    <?php if(key_exists($targetGeneral['nation'], $hiddenItems)){ continue; } ?>
            <option value='<?=$targetGeneral['no']?>'><?=getColoredName($targetGeneral['name'],$targetGeneral['npc'])?>【<?=getNationStaticInfo($targetGeneral['nation'])['name']??'재야'?>】</option>
<?php endforeach; ?>
</select>
<input type=button id="commonSubmit" value="<?=$this->getName()?>">
<?=getInvitationList($nationList)?>
<?php
        return ob_get_clean();
    }
}