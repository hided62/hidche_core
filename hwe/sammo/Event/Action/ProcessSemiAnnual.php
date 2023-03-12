<?php

namespace sammo\Event\Action;

use RuntimeException;
use sammo\DB;
use sammo\Enums\ResourceType;
use sammo\GameConst;
use sammo\KVStorage;

use function sammo\buildNationTypeClass;
use function sammo\popIncrease;
use function sammo\pushGlobalHistoryLog;

class ProcessSemiAnnual extends \sammo\Event\Action
{
  public function __construct(public string $resource)
  {
    if(ResourceType::tryFrom($resource) === null){
      throw new RuntimeException('잘못된 자원 타입');
    }
  }

  public function popIncrease()
  {
    $db = DB::db();

    $nationList = $db->queryAllLists('SELECT nation,rate_tmp,type FROM nation');

    // 인구 및 민심

    $db->update('city', [
      'trust' => 50,
      'agri' => $db->sqleval('agri * 0.99'),
      'comm' => $db->sqleval('comm * 0.99'),
      'secu' => $db->sqleval('secu * 0.99'),
      'def' => $db->sqleval('def * 0.99'),
      'wall' => $db->sqleval('wall * 0.99'),
    ], 'nation=0');

    foreach ($nationList as [$nationID, $taxRate, $nationType]) {
      $nationTypeObj = buildNationTypeClass($nationType);


      $popRatio = (30 - $taxRate) / 200;  // 20일때 5% 5일때 12.5% 50일때 -10%
      $popRatio = $nationTypeObj->onCalcNationalIncome('pop', $popRatio);

      $updateVar = [];
      if ($popRatio >= 0) {
        $updateVar['pop'] = $db->sqleval('least(pop_max, %i + pop * (1 + %d * (1 + secu / secu_max / 10)))', GameConst::$basePopIncreaseAmount, $popRatio);
      } else {
        $updateVar['pop'] = $db->sqleval('least(pop_max, %i + pop * (1 + %d * (1 - secu / secu_max / 10)))', GameConst::$basePopIncreaseAmount, $popRatio);
      }

      $genericRatio = (20 - $taxRate) / 200; // 20일때 0% 0일때 10% 100일때 -40%
      foreach (['agri', 'comm', 'secu', 'def', 'wall'] as $key) {
        $updateVar[$key] = $db->sqleval('least(%b, %b * (1 + %d))', $key . '_max', $key, $genericRatio);
      }

      $trustDiff = 20 - $taxRate;
      $updateVar['trust'] = $db->sqleval('greatest(0, least(100, trust + %i))', $trustDiff);

      $db->update('city', $updateVar, 'nation = %i AND supply = 1', $nationID);
    }
  }

  public function run(array $env)
  {
    $db = DB::db();


    $resource = $this->resource;

    // 내정 1% 감소
    $db->update('city', [
      'dead' => 0,
      'agri' => $db->sqleval('agri * 0.99'),
      'comm' => $db->sqleval('comm * 0.99'),
      'secu' => $db->sqleval('secu * 0.99'),
      'def' => $db->sqleval('def * 0.99'),
      'wall' => $db->sqleval('wall * 0.99'),
    ], true);

    //인구 증가
    popIncrease();

    // > 10000 유지비 3%, > 1000 유지비 1%
    // 유지비 1%
    $db->update('general', [
      $resource => $db->sqleval('IF(%b > 10000, %b * 0.97, %b * 0.99)', $resource, $resource, $resource)
    ], '%b > 1000', $resource);

    // > 100000 유지비 5%, > 100000 유지비 3%, > 1000 유지비 1%
    $db->update('nation', [
      $resource => $db->sqleval('IF(%b > 100000, %b * 0.95, IF(%b > 10000, %b * 0.97, %b * 0.99))', $resource, $resource, $resource, $resource, $resource)
    ], '%b > 1000', $resource);
  }
}
