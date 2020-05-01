<?php
namespace sammo\Command\General;

use \sammo\{
    DB, Util, JosaUtil,
    General, 
    ActionLogger,
    GameConst, GameUnitConst,
    LastTurn,
    Command,
    Json
};


use function \sammo\{
    tryUniqueItemLottery,
    getInvitationList,
    getNationStaticInfo,
};

use \sammo\Constraint\Constraint;
use \sammo\Constraint\ConstraintHelper;
use sammo\CityConst;



class che_임관 extends Command\GeneralCommand{
    static protected $actionName = '임관';
    static public $reqArg = true;

    protected function argTest():bool{
        if($this->arg === null){
            return false;
        }
        $destNationID = $this->arg['destNationID']??null;
        $destGeneralID = $this->arg['destGeneralID']??null;

        if($destGeneralID === null && $destNationID === null){
            return false;
        }

        if($destGeneralID !== null && $destNationID !== null){
            return false;
        }
        
        if ($destNationID !== null) {
            if(!is_int($destNationID)){
                return false;
            }
            if($destNationID < 1){
                return false;
            }

            $this->arg = [
                'destNationID' => $destNationID
            ];
        }
        else{
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
        }
        
        return true;
    }

    protected function init(){

        $general = $this->generalObj;
        $env = $this->env;

        $this->setCity();
        $this->setNation();

        $destGeneralID = $this->arg['destGeneralID']??null;
        $destNationID = $this->arg['destNationID']??null;
        if($destGeneralID !== null){
            $this->setDestGeneral($destGeneralID);
            $this->setDestNation($this->destGeneralObj->getVar('nation'));
        }
        else{
            $this->setDestNation($destNationID, ['gennum', 'scout']);
        }

        $relYear = $env['year'] - $env['startyear'];
        
        $this->runnableConstraints=[
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
        $commandName = $this->getName();
        $destNationName = getNationStaticInfo($this->arg['destNationID'])['name'];
        $josaRo = JosaUtil::pick($destNationName, '로');
        return "【{$destNationName}】{$josaRo} {$commandName}";
    }

    public function run():bool{
        if(!$this->isRunnable()){
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
        $logger->pushGlobalActionLog("<D>{$generalName}</>{$josaYi} <D><b>{$destNationName}</b></>에 <S>임관</>했습니다.");

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
        if($general->getVar('npc') == 1 || $relYear >= 3){
            $joinedNations = $general->getAuxVar('joinedNations')??[];
            $joinedNations[] = $destNationID;
            $general->setAuxVar('joinedNations', $joinedNations);
        }

        $general->addExperience($exp);
        $general->setResultTurn(new LastTurn(static::getName(), $this->arg));
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

        $nationList = $db->query('SELECT nation,`name`,color,scout,scoutmsg,gennum FROM nation');
        shuffle($nationList);

        foreach($nationList as &$nation){
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
        }
        unset($nation);
        ob_start(); 
?>
국가에 임관합니다.<br>
이미 임관/등용되었던 국가는 다시 임관할 수 없습니다.<br>
바로 군주의 위치로 이동합니다.<br>
임관할 국가를 목록에서 선택하세요.<br>   
<select class='formInput' name="destNationID" id="destNationID" size='1' style='color:white;background-color:black;'>
<?php foreach($nationList as $nation): ?>
    <option 
        value='<?=$nation['nation']?>' 
        style='color:<?=$nation['color']?><?=$nation['availableJoin']?'':'background-color:red;'?>'
    >【<?=$nation['name']?> 】</option>
<?php endforeach; ?>
<input type=button id="commonSubmit" value="<?=$this->getName()?>">
<?=getInvitationList($nationList)?>
<?php
        return ob_get_clean();
    }
}