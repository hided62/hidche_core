<?php
namespace sammo\Command\General;

use \sammo\{
    DB, Util, JosaUtil,
    General, 
    ActionLogger,
    GameConst,
    LastTurn,
    GameUnitConst,
    Command
};

use function \sammo\{
    getDomesticExpLevelBonus,
    CriticalRatioDomestic, 
    CriticalScoreEx,
    tryUniqueItemLottery
};

use \sammo\Constraint\Constraint;
use \sammo\Constraint\ConstraintHelper;


class che_물자조달 extends Command\GeneralCommand{
    static protected $actionName = '물자조달';

    protected function argTest():bool{
        $this->arg = null;
        return true;
    }

    protected function init(){

        $general = $this->generalObj;

        $this->setCity();
        $this->setNation();
        
        $this->runnableConstraints=[
            ConstraintHelper::NotBeNeutral(), 
            ConstraintHelper::NotWanderingNation(),
            ConstraintHelper::OccupiedCity(),
            ConstraintHelper::SuppliedCity()
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

        $general = $this->generalObj;
        $date = $general->getTurnTime($general::TURNTIME_HM);

        [$resName, $resKey] = Util::choiceRandom([
            ['금', 'gold'],
            ['쌀', 'rice']
        ]);

        $score = $general->getLeadership() + $general->getStrength() + $general->getIntel();
        $score *= getDomesticExpLevelBonus($general->getVar('explevel'));
        $score *= Util::randRange(0.8, 1.2);

        $pick = Util::choiceRandomUsingWeight([
            'fail'=>0.3,
            'success'=>0.1,
            'normal'=>0.6
        ]);
        $score *= CriticalScoreEx($pick);
        

        $score = Util::round($score);
        $scoreText = number_format($score, 0);
        
        $logger = $general->getLogger();

        if($pick == 'fail'){
            $logger->pushGeneralActionLog("조달을 <span class='ev_failed'>실패</span>하여 {$resName}을 <C>$scoreText</> 조달했습니다. <1>$date</>");
        }
        else if($pick == 'success'){
            $logger->pushGeneralActionLog("조달을 <S>성공</>하여 {$resName}을 <C>$scoreText</> 조달했습니다. <1>$date</>");
        }
        else{
            $logger->pushGeneralActionLog("{$resName}을 <C>$scoreText</> 조달했습니다. <1>$date</>");
        }

        $exp = $score * 0.7 / 3;
        $ded = $score * 1.0 / 3;

        $exp = $general->onCalcStat($general, 'experience', $exp);
        $ded = $general->onCalcStat($general, 'dedication', $ded);

        $incStat = Util::choiceRandomUsingWeight([
            'leadership2'=>$general->getLeadership(false, false, false, false),
            'strength2'=>$general->getStrength(false, false, false, false),
            'intel2'=>$general->getIntel(false, false, false, false)
        ]);

        $general->increaseVar('experience', $exp);
        $general->increaseVar('dedication', $ded);
        $general->increaseVar($incStat, 1);

        $db->update('nation', [
            $resKey=>$db->sqleval('%b + %i', $resKey, $score)
        ], 'nation=%i',$general->getNationID());

        $general->setResultTurn(new LastTurn(static::getName(), $this->arg));
        $general->checkStatChange();
        $general->applyDB($db);

        return true;
    }

    
}