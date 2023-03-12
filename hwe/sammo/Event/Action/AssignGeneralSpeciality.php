<?php

namespace sammo\Event\Action;

use sammo\ActionLogger;
use sammo\DB;
use sammo\GameConst;
use sammo\JosaUtil;
use sammo\Json;
use sammo\LiteHashDRBG;
use sammo\RandUtil;
use sammo\SpecialityHelper;
use sammo\UniqueConst;
use sammo\Util;

use function sammo\buildGeneralSpecialDomesticClass;
use function sammo\buildGeneralSpecialWarClass;

class AssignGeneralSpeciality extends \sammo\Event\Action
{
  public function run(array $env)
  {
    $startYear = $env['startyear'];
    $year = $env['year'];
    $month = $env['month'];

    if ($year < $startYear + 3) {
      return;
    }

    $db = DB::db();

    $rng = new RandUtil(new LiteHashDRBG(Util::simpleSerialize(
      UniqueConst::$hiddenSeed,
      'assignGeneralSpeciality',
      $year,
      $month,
    )));

    foreach ($db->query('SELECT no,name,nation,leadership,strength,intel,aux from general where specage<=age and special=%s', GameConst::$defaultSpecialDomestic) as $general) {
      $generalID = $general['no'];
      $special = SpecialityHelper::pickSpecialDomestic(
        $rng,
        $general,
        (Json::decode($general['aux'])['prev_types_special']) ?? []
      );
      $specialClass = buildGeneralSpecialDomesticClass($special);
      $specialText = $specialClass->getName();
      $db->update('general', [
        'special' => $special
      ], 'no=%i', $generalID);

      $logger = new ActionLogger($generalID, $general['nation'], $year, $month);

      $josaUl = JosaUtil::pick($specialText, '을');
      $logger->pushGeneralActionLog("특기 【<b><L>{$specialText}</></b>】{$josaUl} 익혔습니다!", ActionLogger::PLAIN);
      $logger->pushGeneralHistoryLog("특기 【<b><C>{$specialText}</></b>】{$josaUl} 습득");
    }

    foreach ($db->query('SELECT no,name,nation,leadership,strength,intel,npc,dex1,dex2,dex3,dex4,dex5,aux from general where specage2<=age and special2=%s', GameConst::$defaultSpecialWar) as $general) {
      $generalID = $general['no'];
      $generalAux = Json::decode($general['aux']);

      $updateVars = [];
      if (key_exists('inheritSpecificSpecialWar', $generalAux)) {
        $special2 = $generalAux['inheritSpecificSpecialWar'];
        unset($generalAux['inheritSpecificSpecialWar']);
        $updateVars['aux'] = Json::encode($generalAux);
      } else {
        $special2 = SpecialityHelper::pickSpecialWar(
          $rng,
          $general,
          ($generalAux['prev_types_special2']) ?? []
        );
      }

      $specialClass = buildGeneralSpecialWarClass($special2);
      $specialText = $specialClass->getName();

      $updateVars['special2'] = $special2;
      $db->update('general', $updateVars, 'no=%i', $general['no']);

      $logger = new ActionLogger($generalID, $general['nation'], $year, $month);

      $josaUl = JosaUtil::pick($specialText, '을');
      $logger->pushGeneralActionLog("특기 【<b><L>{$specialText}</></b>】{$josaUl} 익혔습니다!", ActionLogger::PLAIN);
      $logger->pushGeneralHistoryLog("특기 【<b><C>{$specialText}</></b>】{$josaUl} 습득");
    }
  }
}
