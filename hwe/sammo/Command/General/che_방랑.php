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

use \sammo\Constraint\Constraint;
use \sammo\Constraint\ConstraintHelper;
use sammo\CityConst;
use function sammo\DeleteConflict;
use function sammo\refreshNationStaticInfo;
use function sammo\tryRollbackInheritUniqueItem;

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

        $this->fullConditionConstraints=[
            ConstraintHelper::BeLord(),
            ConstraintHelper::NotWanderingNation(),
            ConstraintHelper::NotOpeningPart($relYear),
            ConstraintHelper::AllowDiplomacyStatus($this->generalObj->getNationID(), [
                2, 7
            ], '방랑할 수 없는 외교상태입니다.'),
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
        if(!$this->hasFullConditionMet()){
            throw new \RuntimeException('불가능한 커맨드를 강제로 실행 시도');
        }

        $db = DB::db();
        $env = $this->env;

        $general = $this->generalObj;
        $date = $general->getTurnTime($general::TURNTIME_HM);
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
            'type'=>'None',
            'tech'=>0,
            'capital'=>0
        ], 'nation=%i', $nationID);

        $db->update('general', [
            'makelimit'=>12
        ], 'nation=%i', $nationID);
        $general->setVar('makelimit', 12);
        $general->setVar('officer_city', 0);

        $db->update('general', [
            'officer_level'=>1,
            'officer_city'=>0,
        ], 'nation=%i AND officer_level < 12', $nationID);

        $db->update('city', [
            'nation'=>0,
            'front'=>0,
            'conflict'=>'{}'
        ], 'nation=%i', $nationID);

        $db->update('diplomacy', [
            'state'=>2,
            'term'=>0,
        ], 'me=%i OR you=%i', $nationID, $nationID);

        refreshNationStaticInfo();
        $general->increaseInheritancePoint('active_action', 1);
        $this->setResultTurn(new LastTurn(static::getName(), $this->arg));
        tryRollbackInheritUniqueItem($general);
        $general->applyDB($db);

        return true;
    }
}