<?php
namespace sammo\Command\Nation;

use \sammo\{
    DB, Util, JosaUtil,
    General, DummyGeneral,
    ActionLogger,
    GameConst,
    LastTurn,
    GameUnitConst,
    Command
};

use function \sammo\{
    getDomesticExpLevelBonus,
    CriticalRatioDomestic, 
    CriticalScoreEx
};

use \sammo\Constraint\Constraint;
use \sammo\Constraint\ConstraintHelper;

class che_불가침제의 extends Command\NationCommand{
    static protected $actionName = '불가침제의';

    protected function argTest():bool{
        //NOTE: 멸망 직전에 턴을 넣을 수 있으므로, 존재하지 않는 국가여도 argTest에서 바로 탈락시키지 않음
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

        $optionText = $this->arg['optionText']??'';
        if(!is_string($optionText)){
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
            'optionText'=>$optionText,
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

        $this->setDestNation($this->arg['destNationID'], null);

        //NOTE: 개월에서 기한으로 바뀜
        $year = $this->arg['year'];
        $month = $this->arg['month'];

        $currentMonth = $env['year'] * 12 + $env['month'] - 1;
        $reqMonth = $year *12 + $month - 1;

        if ($reqMonth < $currentMonth + 12) {
            $this->runnableConstraints = [
                ConstraintHelper::AlwaysFail('기한은 1년 이상이어야 합니다.')
            ];
            return;
        }        

        $this->runnableConstraints=[
            ConstraintHelper::BeChief(),
            ConstraintHelper::NotBeNeutral(), 
            ConstraintHelper::OccupiedCity(),
            ConstraintHelper::SuppliedCity(),
            ConstraintHelper::ExistsDestNation(),
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
        $generalName = $general->getName();
        $date = substr($general->getVar('turntime'),11,5);

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

        $logger->pushGeneralActionLog("<D><b>{$destNationName}</b></>으로 불가침 제의 서신을 보냈습니다.<1>$date</>");

        // 상대에게 발송
        $src = new MessageTarget(
            $general->getID(), 
            $general->getName(),
            $nationID,
            $nationName,
            $nation['color'],
            GetImageURL($general->getVar('imgsvr'), $general->getVar('picture'))
        );
        $dest = new MessageTarget(
            0,
            '',
            $destNationID,
            $destNationName,
            $destNation['color']
        );

        $now = new \DateTime($date);
        $validUntil = new \DateTime($date);
        $validMinutes = max(30, $turnterm*3);
        $validUntil->add(new \DateInterval("PT{$validMinutes}M"));

        $msg = new DiplomaticMessage(
            Message::MSGTYPE_DIPLOMACY,
            $src,
            $dest,
            "{$srcNation['name']}의 {$year}년 {$month}까지 불가침 제의 서신",
            $now,
            $validUntil,
            [
                'action'=>DiplomaticMessage::TYPE_NO_AGGRESSION,
                'year'=>$year,
                'month'=>$month,
                'option'=>$this->arg['optionText'],
            ]
        );
        $msg->send();

        //FIXME: 현재 내무부, 외교란이 구형 코드임. diplomacy_ticket을 이용하여 재구현
        $db->update('diplomacy', [
            'showing'=>$validUntil->format('Y-m-d H:i:s'),
            'reserved'=>$this->arg['optionText'],
        ], 'me=%i AND you=%i', $nationID, $destNationID);

        $general->setResultTurn(new LastTurn(static::getName(), $this->arg));
        $general->applyDB($db);
        $destGeneral->applyDB($db);

        return true;
    }
}