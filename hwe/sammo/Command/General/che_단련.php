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



class che_단련 extends Command\GeneralCommand{
    static protected $actionName = '단련';

    protected function argTest():bool{
        $this->arg = null;
        return true;
    }

    protected function init(){

        $general = $this->generalObj;

        $this->setCity();
        $this->setNation();

        [$reqGold, $reqRice] = $this->getCost();
        
        $this->runnableConstraints=[
            ConstraintHelper::NotBeNeutral(), 
            ConstraintHelper::ReqGeneralCrew(),
            ConstraintHelper::ReqGeneralValue('train', '훈련', '<', GameConst::$defaultTrainHigh),
            ConstraintHelper::ReqGeneralValue('atmos', '사기', '<', GameConst::$defaultAtmosHigh),
            ConstraintHelper::ReqGeneralGold($reqGold),
            ConstraintHelper::ReqGeneralRice($reqRice),
        ];

    }

    public function getCommandDetailTitle():string{
        $name = $this->getName();
        [$reqGold, $reqRice] = $this->getCost();

        $title = "{$name}(병종숙련";
        if($reqGold > 0){
            $title .= ", 자금{$reqGold}";
        }
        if($reqRice > 0){
            $title .= ", 군량{$reqRice}";
        }
        $title .= ')';
        return $title;
    }

    public function getCost():array{
        $env = $this->env;
        return [$env['develcost'], $env['develcost']];
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

        [$pick, $multiplier] = Util::choiceRandomUsingWeightPair([
            [['success', 3], 0.34],
            [['normal', 2], 0.33],
            [['fail', 1], 0.33]
        ]);

        $score = Util::round($general->getVar('crew') * $general->getVar('train') * $general->getVar('atmos') / 20 / 10000);
        $score *= $multiplier;
        $scoreText = number_format($score, 0);

        $armTypeText = GameUnitConst::allType()[$general->getCrewTypeObj()->armType];

        $logger = $general->getLogger();

        if($pick == 'fail'){
            $logger->pushGeneralActionLog("단련이 <span class='ev_failed'>지지부진</span>하여 {$armTypeText} 숙련도가 <C>{$scoreText}</> 향상되었습니다. <1>$date</>");
        }
        else if($pick == 'success'){
            $logger->pushGeneralActionLog("단련이 <S>일취월장</>하여 {$armTypeText} 숙련도가 <C>{$scoreText}</> 향상되었습니다. <1>$date</>");
        }
        else{
            $logger->pushGeneralActionLog("{$armTypeText} 숙련도가 <C>{$scoreText}</> 향상되었습니다. <1>$date</>");
        }

        $exp = $general->getVar('crew') / 400;
        $exp = $general->onCalcStat($general, 'experience', $exp);

        $general->addDex($general->getCrewTypeObj(), $score, false);

        $incStat = Util::choiceRandomUsingWeight([
            'leadership_max'=>$general->getLeadership(false, false, false, false),
            'strength_max'=>$general->getStrength(false, false, false, false),
            'intel_max'=>$general->getIntel(false, false, false, false)
        ]);
        [$reqGold, $reqRice] = $this->getCost();

        $general->increaseVarWithLimit('gold', -$reqGold, 0);
        $general->increaseVarWithLimit('rice', -$reqRice, 0);
        $general->increaseVar('experience', $exp);
        $general->increaseVar($incStat, 1);
        $general->setResultTurn(new LastTurn(static::getName(), $this->arg));
        $general->checkStatChange();
        tryUniqueItemLottery($general);
        $general->applyDB($db);

        return true;
    }

    
}