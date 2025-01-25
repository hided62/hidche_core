<?php
namespace sammo\Command\Nation;

use \sammo\Command;
use sammo\Constraint\ConstraintHelper;
use sammo\DB;
use sammo\Enums\InheritanceKey;
use sammo\Enums\NationAuxKey;
use \sammo\Util;
use \sammo\JosaUtil;
use sammo\Json;
use sammo\LastTurn;
use sammo\StaticEventHandler;
use sammo\GameConst;

class event_대검병연구 extends Command\NationCommand{
    static protected $actionName = '대검병 연구';
    static protected $auxType = NationAuxKey::can_대검병사용;

    protected function argTest():bool{
        return true;
    }

    protected function init(){
        $this->setCity();
        $this->setNation(['gold','rice','aux']);
        $this->fullConditionConstraints=[];

        $name = static::$actionName;

        [$reqGold, $reqRice] = $this->getCost();
        $this->minConditionConstraints = [
            ConstraintHelper::OccupiedCity(),
            ConstraintHelper::BeChief(),
            ConstraintHelper::ReqNationAuxValue(static::$auxType->value, 0, "<", 1, "{$name}가 이미 완료되었습니다."),
            ConstraintHelper::ReqNationGold(GameConst::$basegold + $reqGold),
            ConstraintHelper::ReqNationRice(GameConst::$baserice + $reqRice),
        ];

        $this->fullConditionConstraints = $this->minConditionConstraints;
    }

    public function getCommandDetailTitle():string{
        $name = $this->getName();

        [$reqGold, $reqRice] = $this->getCost();
        $reqTurn = $this->getPreReqTurn()+1;

        $reqGoldD5 = number_format($reqGold / 10000);
        $reqRiceD5 = number_format($reqRice / 10000);

        return "{$name}/{$reqTurn}턴(금/쌀 {$reqGoldD5}만)";
    }

    public function getPreReqTurn():int{
        return 11;
    }

    public function getPostReqTurn():int{
        return 0;
    }

    public function getCost():array{
        return [50000, 50000];
    }

    public function run(\Sammo\RandUtil $rng):bool{
        if(!$this->hasFullConditionMet()){
            throw new \RuntimeException('불가능한 커맨드를 강제로 실행 시도');
        }
        
        $db = DB::db();
        $general = $this->generalObj;
        $nationID = $general->getNationID();

        $actionName = static::$actionName;
        
        $aux = $this->nation['aux'];
        $aux[static::$auxType->value] = 1;
        [$reqGold, $reqRice] = $this->getCost();

        $logger = $general->getLogger();

        $general->addExperience(5 * ($this->getPreReqTurn() + 1));
        $general->addDedication(5 * ($this->getPreReqTurn() + 1));

        $generalName = $general->getName();
        $josaYi = JosaUtil::pick($generalName, '이');
        
        $db->update('nation', [
            'gold' => $db->sqleval('gold - %i', $reqGold),
            'rice' => $db->sqleval('rice - %i', $reqRice),
            'aux' => Json::encode($aux),
        ], 'nation=%i', $nationID);

        $logger->pushGeneralActionLog("<M>{$actionName}</> 완료");
        $logger->pushGeneralHistoryLog("<M>{$actionName}</> 완료");
        $logger->pushNationalHistoryLog("<Y>{$generalName}</>{$josaYi} <M>{$actionName}</> 완료");

        $general->increaseInheritancePoint(InheritanceKey::active_action, 1);
        StaticEventHandler::handleEvent($this->generalObj, $this->destGeneralObj, $this::class, $this->env, $this->arg ?? []);
        $this->setResultTurn(new LastTurn($this->getName(), $this->arg, 0));
        $general->applyDB($db);
        
        return true;
    }
}