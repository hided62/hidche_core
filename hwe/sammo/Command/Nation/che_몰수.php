<?php

namespace sammo\Command\Nation;

use \sammo\DB;
use \sammo\Util;
use \sammo\JosaUtil;
use \sammo\General;
use \sammo\DummyGeneral;
use \sammo\ActionLogger;
use \sammo\GameConst;
use \sammo\LastTurn;
use \sammo\GameUnitConst;
use \sammo\Command;
use \sammo\MessageTarget;
use \sammo\Message;

use function \sammo\GetImageURL;

use \sammo\Constraint\Constraint;
use \sammo\Constraint\ConstraintHelper;

class che_몰수 extends Command\NationCommand
{
    static protected $actionName = '몰수';
    static public $reqArg = true;

    protected function argTest(): bool
    {
        if ($this->arg === null) {
            return false;
        }
        //NOTE: 사망 직전에 턴을 넣을 수 있으므로, 존재하지 않는 장수여도 argTest에서 바로 탈락시키지 않음
        if (!key_exists('isGold', $this->arg)) {
            return false;
        }
        if (!key_exists('amount', $this->arg)) {
            return false;
        }
        if (!key_exists('destGeneralID', $this->arg)) {
            return false;
        }
        $isGold = $this->arg['isGold'];
        $amount = $this->arg['amount'];
        $destGeneralID = $this->arg['destGeneralID'];
        if (!is_numeric($amount)) {
            return false;
        }
        $amount = Util::round($amount, -2);
        $amount = Util::valueFit($amount, 100, GameConst::$maxResourceActionAmount);
        if ($amount <= 0) {
            return false;
        }
        if (!is_bool($isGold)) {
            return false;
        }
        if (!is_int($destGeneralID)) {
            return false;
        }
        if ($destGeneralID <= 0) {
            return false;
        }
        $this->arg = [
            'isGold' => $isGold,
            'amount' => $amount,
            'destGeneralID' => $destGeneralID
        ];
        return true;
    }

    protected function init()
    {
        $general = $this->generalObj;

        $env = $this->env;

        $this->setCity();
        $this->setNation(['gold', 'rice']);

        $relYear = $env['year'] - $env['startyear'];

        $this->minConditionConstraints = [
            ConstraintHelper::NotBeNeutral(),
            ConstraintHelper::OccupiedCity(),
            ConstraintHelper::BeChief(),
            ConstraintHelper::NotOpeningPart($relYear),
            ConstraintHelper::SuppliedCity(),
        ];
    }

    protected function initWithArg()
    {
        $destGeneral = General::createGeneralObjFromDB($this->arg['destGeneralID'], ['gold', 'rice', 'npc', 'nation', 'imgsvr', 'picture'], 1);
        $this->setDestGeneral($destGeneral);

        $env = $this->env;
        $relYear = $env['year'] - $env['startyear'];

        if($this->arg['destGeneralID'] == $this->getGeneral()->getID()){
            $this->fullConditionConstraints=[
                ConstraintHelper::AlwaysFail('본인입니다')
            ];
            return;
        }

        $this->fullConditionConstraints = [
            ConstraintHelper::NotBeNeutral(),
            ConstraintHelper::OccupiedCity(),
            ConstraintHelper::BeChief(),
            ConstraintHelper::NotOpeningPart($relYear),
            ConstraintHelper::SuppliedCity(),
            ConstraintHelper::ExistsDestGeneral(),
            ConstraintHelper::FriendlyDestGeneral()
        ];
    }

    public function getCost(): array
    {
        return [0, 0];
    }

    public function getPreReqTurn(): int
    {
        return 0;
    }

    public function getPostReqTurn(): int
    {
        return 0;
    }

    public function getBrief(): string
    {
        $isGold = $this->arg['isGold'];
        $amount = $this->arg['amount'];
        $amountText = number_format($amount, 0);
        $resName = $isGold ? '금' : '쌀';
        $destGeneral = $this->destGeneralObj;
        $commandName = $this->getName();
        return "【{$destGeneral->getName()}】 {$resName} $amountText {$commandName}";
    }


    public function run(): bool
    {
        if (!$this->hasFullConditionMet()) {
            throw new \RuntimeException('불가능한 커맨드를 강제로 실행 시도');
        }

        $db = DB::db();

        $general = $this->generalObj;
        $date = $general->getTurnTime($general::TURNTIME_HM);

        $nation = $this->nation;
        $nationID = $nation['nation'];

        $isGold = $this->arg['isGold'];
        $amount = $this->arg['amount'];
        $resKey = $isGold ? 'gold' : 'rice';
        $resName = $isGold ? '금' : '쌀';
        $destGeneral = $this->destGeneralObj;

        $amount = Util::valueFit(
            $amount,
            0,
            $destGeneral->getVar($resKey)
        );
        $amountText = number_format($amount, 0);

        if ($destGeneral->getNPCType() >= 2 && Util::randBool(0.01)) {
            $npcTexts = [
                '몰수를 하다니... 이것이 윗사람이 할 짓이란 말입니까...',
                '사유재산까지 몰수해가면서 이 나라가 잘 될거라 믿습니까? 정말 이해할 수가 없군요...',
                '내 돈 내놔라! 내 돈! 몰수가 왠 말이냐!',
                '몰수해간 내 자금... 언젠가 몰래 다시 빼내올 것이다...',
                '몰수로 인한 사기 저하는 몰수로 얻은 물자보다 더 손해란걸 모른단 말인가!'
            ];
            $text = Util::choiceRandom($npcTexts);
            $src = new MessageTarget(
                $destGeneral->getID(),
                $destGeneral->getName(),
                $nationID,
                $nation['name'],
                $nation['color'],
                GetImageURL($destGeneral->getVar('imgsvr'), $destGeneral->getVar('picture'))
            );
            $msg = new Message(
                Message::MSGTYPE_PUBLIC,
                $src,
                $src,
                $text,
                new \DateTime(),
                new \DateTime('9999-12-31'),
                []
            );
            $msg->send();
        }

        $logger = $general->getLogger();

        $destGeneral->increaseVarWithLimit($resKey, -$amount, 0);
        $db->update('nation', [
            $resKey => $db->sqleval('%b + %i', $resKey, $amount)
        ], 'nation=%i', $nationID);

        $destGeneral->getLogger()->pushGeneralActionLog("{$resName} {$amountText}을 몰수 당했습니다.", ActionLogger::PLAIN);
        $logger->pushGeneralActionLog("<Y>{$destGeneral->getName()}</>에게서 {$resName} <C>$amountText</>을 몰수했습니다. <1>$date</>");

        $this->setResultTurn(new LastTurn(static::getName(), $this->arg));
        $general->applyDB($db);
        $destGeneral->applyDB($db);

        return true;
    }

    public function exportJSVars(): array
    {
        $db = DB::db();
        $nationID = $this->getNationID();
        $troops = Util::convertArrayToDict($db->query('SELECT * FROM troop WHERE nation=%i', $nationID), 'troop_leader');
        $destRawGenerals = $db->queryAllLists('SELECT no,name,officer_level,npc,gold,rice,leadership,strength,intel,city,crew,train,atmos,troop FROM general WHERE nation = %i ORDER BY npc,binary(name)', $nationID);
        return [
            'procRes' => [
                'troops' => $troops,
                'generals' => $destRawGenerals,
                'generalsKey' => ['no', 'name', 'officerLevel', 'npc', 'gold', 'rice', 'leadership', 'strength', 'intel', 'cityID', 'crew', 'train', 'atmos', 'troopID'],
                'cities' => \sammo\JSOptionsForCities(),
                'minAmount' => 100,
                'maxAmount' => GameConst::$maxResourceActionAmount,
                'amountGuide' => GameConst::$resourceActionAmountGuide,
            ]
        ];
    }
}
