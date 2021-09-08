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
    KVStorage,
    Message, MessageTarget
};

use function \sammo\buildNationCommandClass;
use function \sammo\getAllNationStaticInfo;
use function \sammo\getNationStaticInfo;

use \sammo\Constraint\Constraint;
use \sammo\Constraint\ConstraintHelper;

class che_피장파장 extends Command\NationCommand{
    static protected $actionName = '피장파장';
    static public $reqArg = true;
    static public $delayCnt = 60;

    protected function argTest():bool{
        if($this->arg === null){
            return false;
        }
        //NOTE: 멸망 직전에 턴을 넣을 수 있으므로, 존재하지 않는 국가여도 argTest에서 바로 탈락시키지 않음
        if(!key_exists('destNationID', $this->arg)){
            return false;
        }
        $destNationID = $this->arg['destNationID'];
        $commandType = $this->arg['commandType'];

        if(!is_int($destNationID)){
            return false;
        }
        if($destNationID < 1){
            return false;
        }

        if(!is_string($commandType)){
            return false;
        }
        if(!in_array($commandType, GameConst::$availableChiefCommand['전략'])){
            return false;
        }


        $this->arg = [
            'destNationID'=>$destNationID,
            'commandType'=>$commandType
        ];
        return true;
    }

    protected function init(){
        $general = $this->generalObj;

        $env = $this->env;

        $this->setCity();
        $this->setNation();

        $this->minConditionConstraints=[
            ConstraintHelper::OccupiedCity(),
            ConstraintHelper::BeChief(),
        ];
    }

    protected function initWithArg()
    {
        $this->setDestNation($this->arg['destNationID'], null);

        if($this->getNationID() == 0){
            $this->fullConditionConstraints=[
                ConstraintHelper::OccupiedCity()
            ];
            return;
        }

        $cmd = buildNationCommandClass($this->arg['commandType'], $this->generalObj, $this->env, new LastTurn());

        $currYearMonth = Util::joinYearMonth($this->env['year'], $this->env['month']);
        $nextAvailableTurn = $cmd->getNextAvailableTurn();
        if($currYearMonth < $nextAvailableTurn){
            $this->fullConditionConstraints=[
                ConstraintHelper::AlwaysFail('해당 전략을 아직 사용할 수 없습니다')
            ];
            return;
        }

        $this->fullConditionConstraints=[
            ConstraintHelper::OccupiedCity(),
            ConstraintHelper::BeChief(),
            ConstraintHelper::ExistsDestNation(),
            ConstraintHelper::AllowDiplomacyBetweenStatus(
                [0, 1],
                '선포, 전쟁중인 상대국에게만 가능합니다.'
            ),
        ];
    }

    public function getCommandDetailTitle():string{
        $name = $this->getName();
        $reqTurn = $this->getPreReqTurn()+1;

        return "{$name}/{$reqTurn}턴(대상 재사용 대기 {$this->getTargetPostReqTurn()})";
    }

    public function getCost():array{
        return [0, 0];
    }

    public function getPreReqTurn():int{
        return 1;
    }

    public function getPostReqTurn():int{
        return 0;
    }

    public function getTargetPostReqTurn():int{
        $genCount = Util::valueFit($this->nation['gennum'], GameConst::$initialNationGenLimit);
        $nextTerm = Util::round(sqrt($genCount*2)*10);

        $nextTerm = $this->generalObj->onCalcStrategic($this->getName(), 'delay', $nextTerm);
        return $nextTerm;
    }

    public function getBrief():string{
        $commandName = $this->getName();
        $cmd = buildNationCommandClass($this->arg['commandType'], $this->generalObj, $this->env, new LastTurn());
        $targetCommandName = $cmd->getName();
        $destNationName = getNationStaticInfo($this->arg['destNationID'])['name'];
        return "【{$destNationName}】에 【{$targetCommandName}】 {$commandName}";
    }


    public function run():bool{
        if(!$this->hasFullConditionMet()){
            throw new \RuntimeException('불가능한 커맨드를 강제로 실행 시도');
        }

        $db = DB::db();
        $env = $this->env;

        $general = $this->generalObj;
        $generalID = $general->getID();
        $generalName = $general->getName();
        $date = $general->getTurnTime($general::TURNTIME_HM);

        $year = $this->env['year'];
        $month = $this->env['month'];

        $nation = $this->nation;
        $nationID = $nation['nation'];
        $nationName = $nation['name'];

        $destNation = $this->destNation;
        $destNationID = $destNation['nation'];
        $destNationName = $destNation['name'];

        $josaYi = JosaUtil::pick($generalName, '이');
        $josaYiNation = JosaUtil::pick($nationName, '이');

        $commandName = $this->getName();
        $josaUl = JosaUtil::pick($commandName, '을');

        $cmd = buildNationCommandClass($this->arg['commandType'], $this->generalObj, $this->env, new LastTurn());


        $logger = $general->getLogger();
        $logger->pushGeneralActionLog("<G><b>{$cmd->getName()}</b></> 전략의 {$commandName} 발동! <1>$date</>");

        $general->addExperience(5 * ($this->getPreReqTurn() + 1));
        $general->addDedication(5 * ($this->getPreReqTurn() + 1));

        $broadcastMessage = "<Y>{$generalName}</>{$josaYi} <G><b>{$destNationName}</b></>에 <G><b>{$cmd->getName()}</b></> 전략의 <M>{$commandName}</>{$josaUl} 발동하였습니다.";

        $nationGeneralList = $db->queryFirstColumn('SELECT no FROM general WHERE nation=%i AND no != %i', $nationID, $generalID);
        foreach($nationGeneralList as $nationGeneralID){
            $nationGeneralLogger = new ActionLogger($nationGeneralID, $nationID, $year, $month);
            $nationGeneralLogger->pushGeneralActionLog($broadcastMessage, ActionLogger::PLAIN);
            $nationGeneralLogger->flush();
        }

        $josaYiCommand = JosaUtil::pick($commandName, '이');

        $broadcastMessage = "아국에 <G><b>{$cmd->getName()}</b></> 전략의 <M>{$commandName}</>{$josaYiCommand} 발동되었습니다.";

        $destNationGeneralList = $db->queryFirstColumn('SELECT no FROM general WHERE nation=%i AND no != %i', $destNationID, $generalID);
        foreach($destNationGeneralList as $destNationGeneralID){
            $destNationGeneralLogger = new ActionLogger($destNationGeneralID, $destNationID, $year, $month);
            $destNationGeneralLogger->pushGeneralActionLog($broadcastMessage, ActionLogger::PLAIN);
            $destNationGeneralLogger->flush();
        }

        $destNationLogger = new ActionLogger(0, $destNationID, $year, $month);
        $destNationLogger->pushNationalHistoryLog("<D><b>{$nationName}</b></>의 <Y>{$generalName}</>{$josaYi} 아국에 <G><b>{$cmd->getName()}</b></> <M>{$commandName}</>{$josaUl} 발동");
        $destNationLogger->flush();

        $logger->pushNationalHistoryLog("<Y>{$generalName}</>{$josaYi} <D><b>{$destNationName}</b></>에 <G><b>{$cmd->getName()}</b></> <M>{$commandName}</>{$josaUl} 발동");

        $nationStor = KVStorage::getStorage($db, $nationID, 'nation_env');
        $destNationStor = KVStorage::getStorage($db, $destNationID, 'nation_env');

        $yearMonth = Util::joinYearMonth($env['year'], $env['month']);
        $nationStor->setValue($cmd->getNextExecuteKey(), $yearMonth + $this->getTargetPostReqTurn());
        $destNationStor->setValue($cmd->getNextExecuteKey(), $yearMonth + static::$delayCnt);

        $general->applyDB($db);

        return true;
    }

    public function getJSPlugins(): array
    {
        return [
            'defaultSelectNationByMap'
        ];
    }

    public function getForm(): string
    {
        $generalObj = $this->generalObj;
        $nationID = $generalObj->getNationID();
        $nationList = [];
        $testTurn = new LastTurn($this->getName(), null, $this->getPreReqTurn());
        foreach(getAllNationStaticInfo() as $destNation){
            if($destNation['nation'] == $nationID){
                continue;
            }

            $testTurn->setArg(['destNationID'=>$destNation['nation']]);
            $testCommand = new static($generalObj, $this->env, $testTurn, ['destNationID'=>$destNation['nation']]);
            if($testCommand->hasFullConditionMet()){
                $destNation['availableCommand'] = true;
            }
            else{
                $destNation['availableCommand'] = false;
            }

            $nationList[] = $destNation;
        }

        $availableCommandTypeList = [];
        $currYearMonth = Util::joinYearMonth($this->env['year'], $this->env['month']);
        foreach(GameConst::$availableChiefCommand['전략'] as $commandType){
            $cmd = buildNationCommandClass($commandType, $generalObj, $this->env, new LastTurn());
            $cmdName = $cmd->getName();
            $remainTurn = 0;
            $nextAvailableTurn = $cmd->getNextAvailableTurn();

            if($nextAvailableTurn !== null && $currYearMonth < $nextAvailableTurn){
                $remainTurn = $nextAvailableTurn - $currYearMonth;
            }
            $availableCommandTypeList[$commandType] = [$cmdName, $remainTurn];
        }

        ob_start();
?>
<?=\sammo\getMapHtml()?><br>
선택된 국가에 피장파장을 발동합니다.<br>
지정한 전략을 상대국이 <?=static::$delayCnt?>턴 동안 사용할 수 없게됩니다.<br>
대신 아국은 지정한 전략을<?=$this->getTargetPostReqTurn()?>턴 동안 사용할 수 없습니다.<br>
선포, 전쟁중인 상대국에만 가능합니다.<br>
상대 국가를 목록에서 선택하세요.<br>
배경색은 현재 피장파장 불가능 국가는 <font color=red>붉은색</font>으로 표시됩니다.<br>
<select class='formInput' name="destNationID" id="destNationID" size='1' style='color:white;background-color:black;'>
<?php foreach($nationList as $nation): ?>
    <option
        value='<?=$nation['nation']?>'
        style='color:<?=$nation['color']?>;<?=$nation['availableCommand']?'':'background-color:red;'?>'
    >【<?=$nation['name']?> 】</option>
<?php endforeach; ?>
</select>에
<select class='formInput' name="commandType" id="commandType" size='1' style='color:white;background-color:black;'>
<?php foreach($availableCommandTypeList as $commandType=>[$cmdName, $cmdRemainTurn]):
    /** @var \sammo\Command\NationCommand $cmdObj */
?>
    <option
        value='<?=$commandType?>'
        style='color:white;<?=$cmdRemainTurn?'background-color:red;':''?>'
    ><?=$cmdName?><?=$cmdRemainTurn?"({$cmdRemainTurn}턴 뒤)":''?></option>
<?php endforeach; ?>
</select> 전략을
<input type=button id="commonSubmit" value="<?=$this->getName()?>">
<?php
        return ob_get_clean();
    }
}