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
    tryUniqueItemLottery,
    printCitiesBasedOnDistance
};

use \sammo\Constraint\Constraint;
use \sammo\Constraint\ConstraintHelper;
use sammo\CityConst;



class che_강행 extends Command\GeneralCommand{
    static protected $actionName = '강행';
    static public $reqArg = true;

    protected function argTest():bool{
        if($this->arg === null){
            return false;
        }
        if(!key_exists('destCityID', $this->arg)){
            return false;
        }
        if(!key_exists($this->arg['destCityID'], CityConst::all())){
            return false;
        }
        $this->arg = [
            'destCityID'=>$this->arg['destCityID']
        ];
        return true;
    }

    protected function init(){

        $general = $this->generalObj;

        $this->setCity();
        $this->setNation();
        $this->setDestCity($this->arg['destCityID'], []);

        [$reqGold, $reqRice] = $this->getCost();
        
        $this->runnableConstraints=[
            ConstraintHelper::NotSameDestCity(), 
            ConstraintHelper::NearCity(3),
            ConstraintHelper::ReqGeneralGold($reqGold),
            ConstraintHelper::ReqGeneralRice($reqRice),
        ];
    }
    
    public function getCost():array{
        $env = $this->env;
        return [$env['develcost'] * 5, 0];
    }
    
    public function getPreReqTurn():int{
        return 0;
    }

    public function getPostReqTurn():int{
        return 0;
    }

    public function getFailString():string{
        $commandName = $this->getName();
        $failReason = $this->testRunnable();
        if($failReason === null){
            throw new \RuntimeException('실행 가능한 커맨드에 대해 실패 이유를 수집');
        }
        $destCityName = CityConst::byID($this->arg['destCityID'])->name;
        $josaRo = JosaUtil::pick($destCityName, '로');
        return "{$failReason} <G><b>{$destCityName}</b></>{$josaRo} {$commandName} 실패.";
    }

    public function run():bool{
        if(!$this->isRunnable()){
            throw new \RuntimeException('불가능한 커맨드를 강제로 실행 시도');
        }

        $db = DB::db();
        $env = $this->env;

        $general = $this->generalObj;
        $date = substr($general->getVar('turntime'),11,5);

        $destCityName = $this->destCity['name'];
        $destCityID = $this->destCity['city'];
        $josaRo = JosaUtil::pick($destCityName, '로');

        $logger = $general->getLogger();

        $logger->pushGeneralActionLog("<G><b>{$destCityName}</b></>{$josaRo} 강행했습니다. <1>$date</>");

        $exp = 100;

        $exp = $general->onCalcStat($general, 'experience', $exp);
        $general->setVar('city', $destCityID);

        if($general->getVar('level') == 12 && $this->nation['level'] == 0){
            
            $generalList = $db->queryFirstColumn('SELECT no FROM general WHERE nation=%i AND no!=%i', $general->getNationID(), $general->getID());
            if($generalList){
                $db->update('general', [
                    'city'=>$destCityID
                ], 'no IN %li and nation=%i', $generalList, $general->getNationID());
            }
            foreach($generalList as $targetGeneralID){
                $targetGeneral = General::createGeneralObjFromDB($targetGeneralID, [], 1);
                $targetLogger = new ActionLogger($targetGeneralID, $general->getNationID(), $env['year'], $env['month']);
                $targetLogger->pushGeneralActionLog("방랑군 세력이 <G><b>{$destCityName}</b></>{$josaRo} 강행했습니다.", ActionLogger::PLAIN);
                $targetLogger->flush();
            }
        }

        [$reqGold, $reqRice] = $this->getCost();
        $general->increaseVarWithLimit('gold', -$reqGold, 0);
        $general->increaseVar('experience', $exp);
        $general->increaseVar('leader2', 1);
        $general->setResultTurn(new LastTurn(static::getName(), $this->arg));
        $general->checkStatChange();
        $general->applyDB($db);

        return true;

    }

    public function getJSFiles(): array
    {
        return [
            'js/defaultSelectCityByMap.js'
        ];
    }

    public function getForm(): string
    {
        $form = [];
        $form[] = \sammo\getMapHtml();

        $form[] = <<<EOT
선택된 도시로 강행합니다.<br>
최대 3칸내 도시로만 강행이 가능합니다.<br>
목록을 선택하거나 도시를 클릭하세요.<br>
<select class='formInput' name="destCityID" id="destCityID" size='1' style='color:white;background-color:black;'>
EOT;
        $form[] = \sammo\optionsForCities();
        $form[] = '</select>';
        $form[] = '<input type=submit value="강행">';
        $form[] = printCitiesBasedOnDistance($this->generalObj->getCityID(), 3);
        
        return join("\n",$form);
    }

    
}