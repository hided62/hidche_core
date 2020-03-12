<?php
namespace sammo\Command\Nation;

use \sammo\{
    DB, Util, JosaUtil,
    General, DummyGeneral,
    ActionLogger,
    GameConst,
    LastTurn,
    GameUnitConst,
    Command,
    MessageTarget,
    DiplomaticMessage,
    Message,
};

use function \sammo\{
    getDomesticExpLevelBonus,
    CriticalRatioDomestic, 
    CriticalScoreEx,
    getAllNationStaticInfo,
    getNationStaticInfo,
    GetImageURL
};

use \sammo\Constraint\Constraint;
use \sammo\Constraint\ConstraintHelper;

class che_불가침수락 extends Command\NationCommand{
    static protected $actionName = '불가침';
    static public $reqArg = true;

    protected function argTest():bool{
        if($this->arg === null){
            return false;
        }

        if(!key_exists('destNationID', $this->arg)){
            return false;
        }
        $destNationID = $this->arg['destNationID'];
        if(!is_int($destNationID)){
            return false;
        }
        if($destNationID < 1){
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

        if(!key_exists('year', $this->arg) || !key_exists('month', $this->arg) ){
            return false;
        }
        $year = $this->arg['year'];
        $month = $this->arg['month'];
        if(!is_int($year) || !is_int($month)){
            return false;
        }

        if($month < 1 || 12 < $month){
            return false;
        }

        if($year < $this->env['startyear']){
            return false;
        }

        $this->arg = [
            'destNationID'=>$destNationID,
            'destGeneralID'=>$destGeneralID,
            'year'=>$year,
            'month'=>$month,
        ];
        return true;
    }

    protected function init(){
        $general = $this->generalObj;

        $env = $this->env;
        $relYear = $env['year'] - $env['startyear'];

        $this->setCity();
        $this->setNation();

        $destGeneral = General::createGeneralObjFromDB($this->arg['destGeneralID'], ['imgsvr', 'picture'], 1);
        $this->setDestGeneral($destGeneral);
        $this->setDestNation($this->arg['destNationID']);

        //NOTE: 개월에서 기한으로 바뀜
        $year = $this->arg['year'];
        $month = $this->arg['month'];

        $currentMonth = $env['year'] * 12 + $env['month'] - 1;
        $reqMonth = $year *12 + $month - 1;

        $nationID = $this->nation['nation'];

        $this->reservableConstraints = [
            ConstraintHelper::AlwaysFail('예약 불가능 커맨드')
        ];

        if ($reqMonth <= $currentMonth) {
            $this->runnableConstraints = [
                ConstraintHelper::AlwaysFail('이미 기한이 지났습니다.')
            ];
            return;
        }        

        $this->runnableConstraints=[
            ConstraintHelper::BeChief(),
            ConstraintHelper::NotBeNeutral(), 
            ConstraintHelper::OccupiedCity(),
            ConstraintHelper::SuppliedCity(),
            ConstraintHelper::ExistsDestNation(),
            ConstraintHelper::ExistsDestGeneral(),
            ConstraintHelper::DisallowDiplomacyBetweenStatus([
                0 => '아국과 이미 교전중입니다.',
                1 => '아국과 이미 선포중입니다.',
                3 => '아국과 외교 진행중입니다.',
                4 => '아국과 외교 진행중입니다.',
                5 => '아국과 외교 진행중입니다.',
                6 => '아국과 외교 진행중입니다.',
            ]),
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
        $year = $this->arg['year'];
        $month = $this->arg['month'];
        return "{$year}년 {$month}월 전까지 ";
    }

    public function run():bool{
        if(!$this->isRunnable()){
            throw new \RuntimeException('불가능한 커맨드를 강제로 실행 시도');
        }

        $db = DB::db();
        $env = $this->env;

        $general = $this->generalObj;

        $nation = $this->nation;
        $nationID = $nation['nation'];
        $nationName = $nation['name'];

        $destNation = $this->destNation;
        $destNationID = $destNation['nation'];
        $destNationName = $destNation['name'];

        $year = $this->arg['year'];
        $month = $this->arg['month'];

        $logger = $general->getLogger();
        $destLogger = new ActionLogger(0, $destNationID, $env['year'], $env['month']);
        
        $currentMonth = $env['year'] * 12 + $env['month'] - 1;
        $reqMonth = $year *12 + $month - 1;

        $db->update('diplomacy',[
            'state'=>7,
            'term'=>$reqMonth - $currentMonth
        ],
        '(me=%i AND you=%i) OR (you=%i AND me=%i)',
        $nationID, $destNationID,
        $nationID, $destNationID);

        $josaWa = JosaUtil::pick($destNationName, '와');
        $logger->pushGeneralActionLog("<D><b>{$destNationName}</b></>{$josaWa} <C>$year</>년 불가침에 성공했습니다.", ActionLogger::PLAIN);
        $logger->pushGeneralHistoryLog("<D><b>{$destNationName}</b></>{$josaWa} {$year}년 {$month}월 전까지 불가침 성공");


        $josaWa = JosaUtil::pick($nationName, '와');
        $destLogger->pushGeneralActionLog("<D><b>{$nationName}</b></>{$josaWa} <C>$year</>년 불가침에 성공했습니다.", ActionLogger::PLAIN);
        $destLogger->pushGeneralHistoryLog("<D><b>{$nationName}</b></>{$josaWa} {$year}년 {$month}월 전까지 불가침 성공");

        $general->applyDB($db);
        $destLogger->flush();

        return true;
    }
}