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
        
        $this->runnableConstraints=[
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
        if(!$this->isRunnable()){
            throw new \RuntimeException('불가능한 커맨드를 강제로 실행 시도');
        }

        $db = DB::db();
        $env = $this->env;

        $general = $this->generalObj;
        $date = $general->getTurnTime($general::TURNTIME_HM);
        $generalName = $general->getName();
        $josaYi = JosaUtil::pick($generalName, '이');

        $nationID = $this->nation['nation'];
        $nationName = $this->nation['name'];
        $josaUl = JosaUtil::pick($nationName, '을');

        $dipState = Util::arrayGroupBy($db->query('SELECT no, state FROM diplomacy WHERE me = %i', $nationID), 'state');

        $logger = $general->getLogger();

        if(key_exists('3', $dipState) || key_exists('4', $dipState)){
            $logger->pushGeneralActionLog("통합에 반대하며 <D><b>{$nationName}</b></>에서 떠났습니다. <1>$date</>");
            $logger->pushGeneralHistoryLog("통합에 반대하며 <D><b>{$nationName}</b></>{$josaUl} 떠남");
            $logger->pushGlobalActionLog("<Y>{$generalName}</>{$josaYi} 통합에 반대하며 <D><b>{$nationName}</b></>{$josaUl} <R>떠났</>습니다.");
        }
        else if(key_exists('5', $dipState) || key_exists('6', $dipState)){
            $logger->pushGeneralActionLog("합병에 반대하며 <D><b>{$nationName}</b></>에서 떠났습니다. <1>$date</>");
            $logger->pushGeneralHistoryLog("합병에 반대하며 <D><b>{$nationName}</b></>{$josaUl} 떠남");
            $logger->pushGlobalActionLog("<Y>{$generalName}</>{$josaYi} 합병에 반대하며 <D><b>{$nationName}</b></>{$josaUl} <R>떠났</>습니다.");
        }
        else{
            $logger->pushGeneralActionLog("<D><b>{$nationName}</b></>에서 하야했습니다. <1>$date</>");
            $logger->pushGeneralHistoryLog("<D><b>{$nationName}</b></>에서 하야");
            $logger->pushGlobalActionLog("{$generalName}</>{$josaYi} <D><b>{$nationName}</b></>에서 <R>하야</>했습니다.");
            $general->setVar('experience', $general->getVar('experience') * (1 - 0.1 * $general->getVar('betray')));
            $general->setVar('dedication', $general->getVar('dedication') * (1 - 0.1 * $general->getVar('betray')));
            $general->increaseVar('betray', 1);

        }

        $newGold = Util::valueFit($general->getVar('gold'), null, GameConst::$defaultGold);
        $newRice = Util::valueFit($general->getVar('rice'), null, GameConst::$defaultRice);

        $lostGold = $general->getVar('gold') - $newGold;
        $lostRice = $general->getVar('rice') - $newRice;

        $general->setVar('gold', $newGold);
        $general->setVar('rice', $newRice);

        $db->update('nation', [
            'gold'=>$db->sqleval('gold + %i', $lostGold),
            'rice'=>$db->sqleval('rice + %i', $lostRice),
            'gennum'=>$db->sqleval('gennum - 1')
        ], 'nation=%i', $nationID);
        \sammo\refreshNationStaticInfo();

        $general->setVar('nation', 0);
        $general->setVar('level', 0);
        $general->setVar('belong', 0);
        $general->setVar('makelimit', 12);
        
        $general->setResultTurn(new LastTurn(static::getName(), $this->arg));
        $general->checkStatChange();
        $general->applyDB($db);

        return true;
    }

    
}