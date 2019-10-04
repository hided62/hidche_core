<?php
namespace sammo\Command\General;

use \sammo\{
    DB, Util, JosaUtil,
    General, 
    ActionLogger,
    GameConst, GameUnitConst,
    LastTurn,
    Command
};


use function \sammo\{
    tryUniqueItemLottery
};

use \sammo\Constraint\Constraint;
use \sammo\Constraint\ConstraintHelper;
use sammo\CityConst;
use function sammo\DeleteConflict;
use function sammo\refreshNationStaticInfo;


class che_방랑 extends Command\GeneralCommand{
    static protected $actionName = '방랑';

    protected function argTest():bool{
        $this->arg = null;
        return true;
    }

    protected function init(){

        $general = $this->generalObj;
        $env = $this->env;

        $this->setCity();
        $this->setNation();

        $relYear = $env['year'] - $env['startyear'];
        
        $this->runnableConstraints=[
            ConstraintHelper::BeLord(),
            ConstraintHelper::NotWanderingNation(),
            ConstraintHelper::NotOpeningPart($relYear),
            ConstraintHelper::DisallowDiplomacyStatus($this->generalObj->getNationID(), [
                0 => '방랑할 수 없는 외교상태입니다.',
                1 => '방랑할 수 없는 외교상태입니다.',
                3 => '방랑할 수 없는 외교상태입니다.',
                4 => '방랑할 수 없는 외교상태입니다.',
                5 => '방랑할 수 없는 외교상태입니다.',
                6 => '방랑할 수 없는 외교상태입니다.'
            ]),
            //TODO:diplomacy status 상수화
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
        $date = substr($general->getVar('turntime'),11,5);
        $generalName = $general->getName();
        $josaYi = JosaUtil::pick($generalName, '이');
        $josaUn = JosaUtil::pick($generalName, '은');

        $nationID = $general->getNationID();
        $nationName = $this->nation['name'];
        $josaUl = JosaUtil::pick($nationName, '을');

        $logger = $general->getLogger();



        $logger->pushGeneralActionLog("영토를 버리고 방랑의 길을 떠납니다. <1>$date</>");
        $logger->pushGlobalActionLog("<Y>{$generalName}</>{$josaYi} 방랑의 길을 떠납니다.");

        $logger->pushGlobalHistoryLog("<R><b>【방랑】</b></><D><b>{$generalName}</b></>{$josaUn} <R>방랑</>의 길을 떠납니다.");
        $logger->pushGeneralHistoryLog("<D><b>{$nationName}</b></>{$josaUl} 버리고 방랑");

        
        //분쟁기록 모두 지움
        DeleteConflict($nationID);
        // 국명, 색깔 바꿈 국가 레벨 0, 성향리셋, 기술0
        $db->update('nation', [
            'name'=>$generalName,
            'color'=>'#330000', //TODO: 기본 방랑군색 별도 지정
            'level'=>0,
            'type'=>0,
            'tech'=>0,
            'capital'=>0
        ], 'nation=%i', $nationID);

        $db->update('general', [
            'makelimit'=>12
        ], 'nation=%i', $nationID);
        $general->setVar('makelimit', 12);

        $db->update('general', [
            'level'=>1,
        ], 'nation=%i AND level < 12', $nationID);

        $db->update('city', [
            'nation'=>0,
            'front'=>0,
            'officer4'=>0,
            'officer3'=>0,
            'officer2'=>0,
            'conflict'=>'{}'
        ], 'nation=%i', $nationID);

        $db->update('diplomacy', [
            'state'=>2,
            'term'=>0,
        ], 'me=%i OR you=%i', $nationID, $nationID);

        refreshNationStaticInfo();

        $general->setResultTurn(new LastTurn(static::getName(), $this->arg));
        $general->applyDB($db);

        return true;
    }
}