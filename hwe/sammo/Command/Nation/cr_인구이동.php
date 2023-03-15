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
use \sammo\CityConst;
use \sammo\Constraint\Constraint;
use \sammo\Constraint\ConstraintHelper;
use sammo\Enums\InheritanceKey;
use sammo\Event\Action;

class cr_인구이동 extends Command\NationCommand
{
  static protected $actionName = '인구이동';
  static public $reqArg = true;

  const AMOUNT_LIMIT = 100000; //그냥!

  protected function argTest(): bool
  {
    if ($this->arg === null) {
      return false;
    }

    if (!key_exists('destCityID', $this->arg)) {
      return false;
    }
    if (CityConst::byID($this->arg['destCityID']) === null) {
      return false;
    }
    $destCityID = $this->arg['destCityID'];


    if (!key_exists('amount', $this->arg)) {
      return false;
    }
    $amount = $this->arg['amount'];
    if (!is_numeric($amount)) {
      return false;
    }
    $amount = (int) $amount;
    if ($amount > static::AMOUNT_LIMIT){
      $amount = static::AMOUNT_LIMIT;
    }
    if ($amount < 0) {
      return false;
    }

    $this->arg = [
      'destCityID' => $destCityID,
      'amount' => $amount,
    ];
    return true;
  }

  protected function init()
  {
    $general = $this->generalObj;

    $env = $this->env;

    $this->setCity();
    $this->setNation(['gold', 'rice']);

    $this->minConditionConstraints = [
      ConstraintHelper::OccupiedCity(),
      ConstraintHelper::BeChief(),
      ConstraintHelper::SuppliedCity(),
      ConstraintHelper::ReqCityCapacity('pop', '주민', GameConst::$minAvailableRecruitPop + 100),
    ];
  }

  protected function initWithArg()
  {
    $this->setDestCity($this->arg['destCityID']);

    [$reqGold, $reqRice] = $this->getCost();
    $this->fullConditionConstraints = [
      ConstraintHelper::NotSameDestCity(),
      ConstraintHelper::OccupiedCity(),
      ConstraintHelper::ReqCityCapacity('pop', '주민', GameConst::$minAvailableRecruitPop + 100),
      ConstraintHelper::OccupiedDestCity(),
      ConstraintHelper::NearCity(1),
      ConstraintHelper::BeChief(),
      ConstraintHelper::SuppliedCity(),
      ConstraintHelper::SuppliedDestCity(),
      ConstraintHelper::ReqNationGold(GameConst::$basegold + $reqGold),
      ConstraintHelper::ReqNationRice(GameConst::$baserice + $reqRice),
    ];
  }

  public function getCommandDetailTitle(): string
  {
    $name = $this->getName();

    $amount = number_format($this->env['develcost']);

    return "{$name}(금쌀 {$amount}×인구[만])";
  }

  public function getCost(): array
  {
    $amount = Util::round($this->env['develcost'] * $this->arg['amount'] / 10000);

    return [$amount, $amount];
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
    $commandName = $this->getName();
    $destCityName = CityConst::byID($this->arg['destCityID'])->name;
    $josaRo = JosaUtil::pick($destCityName, '로');
    $amount = number_format($this->arg['amount']);
    return "【{$destCityName}】{$josaRo} {$amount}명 {$commandName}";
  }

  public function run(\Sammo\RandUtil $rng): bool
  {
    if (!$this->hasFullConditionMet()) {
      throw new \RuntimeException('불가능한 커맨드를 강제로 실행 시도');
    }

    $db = DB::db();

    $general = $this->generalObj;
    $date = $general->getTurnTime($general::TURNTIME_HM);

    $srcCity = $this->city;
    $srcCityID = $srcCity['city'];

    $destCity = $this->destCity;
    $destCityID = $destCity['city'];
    $destCityName = $destCity['name'];

    $amount = $this->arg['amount'];
    $amount = Util::clamp($amount, null, $this->city['pop'] - GameConst::$minAvailableRecruitPop);

    $josaRo = JosaUtil::pick($destCityName, '로');

    $logger = $general->getLogger();

    $general->addExperience(5);
    $general->addDedication(5);

    $db->update('city', [
      'pop' => $db->sqleval('pop + %i', $amount),
    ], 'city=%i', $destCityID);
    $db->update('city', [
      'pop' => $db->sqleval('pop - %i', $amount),
    ], 'city=%i', $srcCityID);

    [$reqGold, $reqRice] = $this->getCost();
    $db->update('nation', [
      'gold' => $db->sqleval('gold - %i', $reqGold),
      'rice' => $db->sqleval('rice - %i', $reqRice),
    ], 'nation=%i', $this->nation['nation']);

    $logger->pushGeneralActionLog("<G><b>{$destCityName}</b></>{$josaRo} 인구 <C>{$amount}</>명을 옮겼습니다. <1>$date</>");

    $this->setResultTurn(new LastTurn($this->getName(), $this->arg, 0));
    $general->applyDB($db);
    return true;
  }

  public function exportJSVars(): array
  {
    return [
      'procRes' => [
        'cities' => \sammo\JSOptionsForCities(),
        'distanceList' => \sammo\JSCitiesBasedOnDistance($this->generalObj->getCityID(), 1),
      ],
    ];
  }
}
