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
use sammo\Enums\MessageType;

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


    public function run(\Sammo\RandUtil $rng):bool{
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
        $josaRo = JosaUtil::pick($destNationName, '로');

        $logger = $general->getLogger();
        $destLogger = new ActionLogger(0, $destNationID, $env['year'], $env['month']);

        $logger->pushGeneralActionLog("<D><b>{$destNationName}</b></>{$josaRo} 불가침 파기 제의 서신을 보냈습니다.<1>$date</>");

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
            MessageType::diplomacy,
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

    public function exportJSVars(): array
    {
        $generalObj = $this->generalObj;
        $nationID = $generalObj->getNationID();
        $nationList = [];

        $db = DB::db();
        $diplomacyStatus = Util::convertArrayToDict(
            $db->query('SELECT * FROM diplomacy WHERE me = %i', $nationID),
            'you'
        );

        foreach (getAllNationStaticInfo() as $destNation) {
            $nationTarget = [
                'id' => $destNation['nation'],
                'name' => $destNation['name'],
                'color' => $destNation['color'],
                'power' => $destNation['power'],
            ];

            if(!key_exists($destNation['nation'], $diplomacyStatus)){
                //FIXME: DB에 당연히 있어야하지 않나? 왜 없는지 확인 필요
                $nationTarget['notAvailable'] = true;
                $nationList[] = $nationTarget;
                continue;
            }

            if($diplomacyStatus[$destNation['nation']]['state'] != 7){
                $nationTarget['notAvailable'] = true;
            }
            if ($destNation['nation'] == $nationID) {
                $nationTarget['notAvailable'] = true;
            }

            $nationList[] = $nationTarget;
        }
        return [
            'procRes' => [
                'nationList' => $nationList,
                'startYear' => $this->env['startyear'],
            ],
        ];
    }
}