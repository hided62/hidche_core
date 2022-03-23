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

use function sammo\tryRollbackInheritUniqueItem;

class che_하야 extends Command\GeneralCommand{
    static protected $actionName = '하야';

    protected function argTest():bool{
        $this->arg = null;
        return true;
    }

    protected function init(){

        $general = $this->generalObj;
        $env = $this->env;

        $this->setNation();

        $relYear = $env['year'] - $env['startyear'];

        $this->fullConditionConstraints=[
            ConstraintHelper::NotBeNeutral(),
            ConstraintHelper::NotOpeningPart($relYear),
            ConstraintHelper::NotLord(),
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
        $generalID = $general->getID();
        $generalName = $general->getName();
        $josaYi = JosaUtil::pick($generalName, '이');

        $nationID = $this->nation['nation'];
        $nationName = $this->nation['name'];

        $logger = $general->getLogger();

        $logger->pushGeneralActionLog("<D><b>{$nationName}</b></>에서 하야했습니다. <1>$date</>");
        $logger->pushGeneralHistoryLog("<D><b>{$nationName}</b></>에서 하야");
        $logger->pushGlobalActionLog("<Y>{$generalName}</>{$josaYi} <D><b>{$nationName}</b></>에서 <R>하야</>했습니다.");
        $general->setVar('experience', $general->getVar('experience') * (1 - 0.1 * $general->getVar('betray')));
        $general->addExperience(0, false);
        $general->setVar('dedication', $general->getVar('dedication') * (1 - 0.1 * $general->getVar('betray')));
        $general->addDedication(0, false);
        $general->increaseVarWithLimit('betray', 1, null, GameConst::$maxBetrayCnt);
        $general->setVar('permission', 'normal');

        $newGold = Util::valueFit($general->getVar('gold'), null, GameConst::$defaultGold);
        $newRice = Util::valueFit($general->getVar('rice'), null, GameConst::$defaultRice);

        $lostGold = $general->getVar('gold') - $newGold;
        $lostRice = $general->getVar('rice') - $newRice;

        $general->setVar('gold', $newGold);
        $general->setVar('rice', $newRice);

        $db->update('nation', [
            'gold'=>$db->sqleval('gold + %i', $lostGold),
            'rice'=>$db->sqleval('rice + %i', $lostRice),
            'gennum'=>$db->sqleval('gennum - %i', $general->getNPCType()!=5?1:0)
        ], 'nation=%i', $nationID);
        \sammo\refreshNationStaticInfo();

        $general->setVar('nation', 0);
        $general->setVar('officer_level', 0);
        $general->setVar('officer_city', 0);
        $general->setVar('belong', 0);
        $general->setVar('makelimit', 12);

        //부대장일 경우
        if($general->getVar('troop') == $generalID){
            // 모두 탈퇴
            $db->update('general', [
                'troop'=>0,
            ], 'troop = %i', $generalID);
            $db->delete('troop', 'troop_leader=%i', $generalID);
        }
        $general->setVar('troop', 0);

        $general->increaseInheritancePoint('active_action', 1);

        $this->setResultTurn(new LastTurn(static::getName(), $this->arg));
        $general->checkStatChange();
        tryRollbackInheritUniqueItem($general);
        $general->applyDB($db);

        return true;
    }


}