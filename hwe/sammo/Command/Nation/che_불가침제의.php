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

class che_불가침제의 extends Command\NationCommand{
    static protected $actionName = '불가침 제의';
    static public $reqArg = true;

    protected function argTest():bool{
        if($this->arg === null){
            return false;
        }
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

        $nationID = $this->nation['nation'];

        if ($reqMonth < $currentMonth + 12) {
            $this->reservableConstraints = [
                ConstraintHelper::AlwaysFail('기한은 1년 이상이어야 합니다.')
            ];

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
        return "【{$destNationName}】에게 {$year}년 {$month}월 전까지 {$commandName}";
    }


    public function run():bool{
        if(!$this->isRunnable()){
            throw new \RuntimeException('불가능한 커맨드를 강제로 실행 시도');
        }

        $db = DB::db();
        $env = $this->env;

        $general = $this->generalObj;
        $generalName = $general->getName();
        $date = $general->getTurnTime($general::TURNTIME_HM);

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
        $validMinutes = max(30, $env['turnterm']*3);
        $validUntil->add(new \DateInterval("PT{$validMinutes}M"));

        $msg = new DiplomaticMessage(
            Message::MSGTYPE_DIPLOMACY,
            $src,
            $dest,
            "{$nationName}와 {$year}년 {$month}월 전까지 불가침 제의 서신",
            $now,
            $validUntil,
            [
                'action'=>DiplomaticMessage::TYPE_NO_AGGRESSION,
                'year'=>$year,
                'month'=>$month,
            ]
        );
        $msg->send();

        $this->setResultTurn(new LastTurn(static::getName(), $this->arg));
        $general->applyDB($db);
        $destLogger->flush();

        return true;
    }

    public function getJSFiles(): array
    {
        return [
            'js/defaultSelectNationByMap.js'
        ];
    }

    public function getForm(): string
    {
        $generalObj = $this->generalObj;
        $nationID = $generalObj->getNationID();
        
        $db = DB::db();

        $currYear = $this->env['year'];

        $diplomacyStatus = Util::convertArrayToDict(
            $db->query('SELECT * FROM diplomacy WHERE me = %i', $nationID),
            'you'
        );

        $nationList = [];
        foreach(getAllNationStaticInfo() as $destNation){
            if($destNation['nation'] == $nationID){
                continue;
            }

            $testCommand = new static($generalObj, $this->env, $this->getLastTurn(), [
                'destNationID'=>$destNation['nation'],
                'year'=>$currYear+1,
                'month'=>12
            ]);
            if(!$testCommand->isRunnable()){
                $destNation['cssBgColor'] = 'background-color:red;';
            }
            else if($diplomacyStatus[$destNation['nation']]['state'] == 7){
                $destNation['cssBgColor'] = 'background-color:blue;';
            }
            else{
                $destNation['cssBgColor'] = '';
            }

            $nationList[] = $destNation;
        }

        ob_start(); 
?>
<?=\sammo\getMapHtml()?><br>
타국에게 불가침을 제의합니다.<br>
제의할 국가를 목록에서 선택하세요.<br>
불가침 기한 다음 달부터 선포 가능합니다.<br>
배경색은 현재 제의가 불가능한 국가는 <font color=red>붉은색</font>, 현재 불가침중인 국가는 <font color=blue>푸른색</font>으로 표시됩니다.<br>
<br>
<select class='formInput' name="destNationID" id="destNationID" size='1' style='color:white;background-color:black;'>
<?php foreach($nationList as $nation): ?>
    <option 
        value='<?=$nation['nation']?>' 
        style='color:<?=$nation['color']?>;<?=$nation['cssBgColor']?>'
    >【<?=$nation['name']?> 】</option>
<?php endforeach; ?>
</select>에게
<select class='formInput' name="year" id="year" size='1' style='color:white;background-color:black;'>
<?php foreach(range($currYear+1, $currYear+20) as $formYear): ?>
    <option value='<?=$formYear?>'><?=$formYear?></option>
<?php endforeach; ?>
</select>년
<select class='formInput' name="month" id="month" size='1' style='color:white;background-color:black;'>
<?php foreach(range(1, 12) as $formMonth): ?>
    <option value='<?=$formMonth?>'><?=$formMonth?></option>
<?php endforeach; ?>
</select>월 전까지 
<input type=button id="commonSubmit" value="<?=$this->getName()?>">
<?php
        return ob_get_clean();
    }
}