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

use function sammo\getAllNationStaticInfo;
use function \sammo\tryUniqueItemLottery;
use function \sammo\getNationStaticInfo;

use \sammo\Constraint\ConstraintHelper;
use sammo\Enums\GeneralQueryMode;
use sammo\Enums\InheritanceKey;

class che_장수대상임관 extends Command\GeneralCommand{
    static protected $actionName = '장수를 따라 임관';
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
        $destGeneral = General::createGeneralObjFromDB($this->arg['destGeneralID'], ['nation'], GeneralQueryMode::Lite);
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

    public function run(\Sammo\RandUtil $rng):bool{
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

        $general->increaseInheritancePoint(InheritanceKey::active_action, 1);
        $general->addExperience($exp);
        $this->setResultTurn(new LastTurn(static::getName(), $this->arg));
        $general->checkStatChange();
        tryUniqueItemLottery(\sammo\genGenericUniqueRNGFromGeneral($general), $general);
        $general->applyDB($db);

        return true;
    }

    public function exportJSVars(): array
    {
        $db = DB::db();
        $destRawGenerals = $db->queryAllLists('SELECT no,name,nation,officer_level,npc,leadership,strength,intel FROM general WHERE no != %i ORDER BY npc,binary(name)', $this->generalObj->getID());
        $nationList = [];
        $scoutMsgs = KVStorage::getValuesFromInterNamespace($db, 'nation_env', 'scout_msg');

        foreach ([getNationStaticInfo(0), ...getAllNationStaticInfo()] as $destNation) {
            $nationList[] = [
                'id' => $destNation['nation'],
                'name' => $destNation['name'],
                'color' => $destNation['color'],
                'power' => $destNation['power'],
                'scoutMsg' => $scoutMsgs[$destNation['nation']]??' '
            ];
        }

        return [
            'procRes' => [
                'nationList' => $nationList,
                'generals' => $destRawGenerals,
                'generalsKey' => ['no', 'name', 'nationID', 'officerLevel', 'npc', 'leadership', 'strength', 'intel']
            ]
        ];
    }
}