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

use function \sammo\getAllNationStaticInfo;
use function \sammo\getNationStaticInfo;
use function \sammo\GetImageURL;

use \sammo\Constraint\Constraint;
use \sammo\Constraint\ConstraintHelper;

class che_불가침파기제의 extends Command\NationCommand{
    static protected $actionName = '불가침 파기 제의';
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

        $this->arg = [
            'destNationID'=>$destNationID,
        ];
        return true;
    }

    protected function init(){
        $general = $this->generalObj;

        $env = $this->env;

        $this->setCity();
        $this->setNation();



        $this->minConditionConstraints=[
            ConstraintHelper::BeChief(),
            ConstraintHelper::NotBeNeutral(),
            ConstraintHelper::OccupiedCity(),
            ConstraintHelper::SuppliedCity(),
        ];
    }

    protected function initWithArg()
    {
        $this->setDestNation($this->arg['destNationID'], null);

        $this->fullConditionConstraints=[
            ConstraintHelper::BeChief(),
            ConstraintHelper::NotBeNeutral(),
            ConstraintHelper::OccupiedCity(),
            ConstraintHelper::SuppliedCity(),
            ConstraintHelper::ExistsDestNation(),
            ConstraintHelper::AllowDiplomacyBetweenStatus(
                [7],
                '불가침 중인 상대국에게만 가능합니다.'
            ),
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
        return "【{$destNationName}】에게 {$commandName}";
    }


    public function run():bool{
        if(!$this->hasFullConditionMet()){
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

        $logger = $general->getLogger();
        $destLogger = new ActionLogger(0, $destNationID, $env['year'], $env['month']);

        $logger->pushGeneralActionLog("<D><b>{$destNationName}</b></>으로 불가침 파기 제의 서신을 보냈습니다.<1>$date</>");

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
            "{$nationName}의 불가침 파기 제의 서신",
            $now,
            $validUntil,
            [
                'action'=>DiplomaticMessage::TYPE_CANCEL_NA,
                'deletable'=>false,
            ]
        );
        $msg->send();

        $this->setResultTurn(new LastTurn(static::getName(), $this->arg));
        $general->applyDB($db);
        $destLogger->flush();

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

        $db = DB::db();
        $diplomacyStatus = Util::convertArrayToDict(
            $db->query('SELECT * FROM diplomacy WHERE me = %i', $nationID),
            'you'
        );

        $nationList = [];
        foreach(getAllNationStaticInfo() as $destNation){
            if($destNation['nation'] == $nationID){
                continue;
            }

            if($diplomacyStatus[$destNation['nation']]['state'] != 7){
                $destNation['cssBgColor'] = 'background-color:red;';
            }
            else{
                $destNation['cssBgColor'] = '';
            }

            $nationList[] = $destNation;
        }

        ob_start();
?>
<?=\sammo\getMapHtml()?><br>
불가침중인 국가에 조약 파기를 제의합니다.<br>
제의할 국가를 목록에서 선택하세요.<br>
배경색은 현재 제의가 불가능한 국가는 <font color=red>붉은색</font>으로 표시됩니다.<br>
<br>
<select class='formInput' name="destNationID" id="destNationID" size='1' style='color:white;background-color:black;'>
<?php foreach($nationList as $nation): ?>
    <option
        value='<?=$nation['nation']?>'
        style='color:<?=$nation['color']?>;<?=$nation['cssBgColor']?>'
    >【<?=$nation['name']?> 】</option>
<?php endforeach; ?>
</select>에게
<input type=button id="commonSubmit" value="<?=$this->getName()?>">
<?php
        return ob_get_clean();
    }
}