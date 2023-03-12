<?php

namespace sammo\Event\Action;

use sammo\ActionLogger;
use sammo\DB;
use sammo\General;
use sammo\KVStorage;
use sammo\LiteHashDRBG;
use sammo\RandUtil;
use sammo\UniqueConst;
use sammo\Util;

use function sammo\SabotageInjury;

class RaiseDisaster extends \sammo\Event\Action
{
  public function run(array $env)
  {
    $db = DB::db();
    $startYear = $env['startyear'];
    $year = $env['year'];
    $month = $env['month'];

    $rng = new RandUtil(new LiteHashDRBG(Util::simpleSerialize(
      UniqueConst::$hiddenSeed,
      'disater',
      $year,
      $month,
  )));

    //재난표시 초기화
    $db->update('city', [
      'state' => 0,
    ], 'state <= 10');

    // 초반 3년은 스킵
    if ($startYear + 3 > $year) return;

    $boomingRate = [
      1 => 0,
      4 => 0.25,
      7 => 0.25,
      10 => 0
    ];

    $isGood = $rng->nextBool($boomingRate[$month]);


    $targetCityList = [];

    foreach ($db->query('SELECT city,name,secu,secu_max FROM city') as $city) {
      //호황 발생 도시 선택 ( 기본 2% )
      //재해 발생 도시 선택 ( 기본 6% )
      if ($isGood) {
        $raiseProp = 0.02 + ($city['secu'] / $city['secu_max']) * 0.05; // 2 ~ 7%
      } else {
        $raiseProp = 0.06 - ($city['secu'] / $city['secu_max']) * 0.05; // 1 ~ 6%
      }

      if ($rng->nextBool($raiseProp)) {
        $targetCityList[] = $city;
      }
    }

    if (!$targetCityList) {
      return;
    }

    $targetCityNames = "<G><b>" . join(' ', Util::squeezeFromArray($targetCityList, 'name')) . "</b></>";
    $disasterTextList = [
      1 => [
        ['<M><b>【재난】</b></>', 4, '역병이 발생하여 도시가 황폐해지고 있습니다.'],
        ['<M><b>【재난】</b></>', 5, '지진으로 피해가 속출하고 있습니다.'],
        ['<M><b>【재난】</b></>', 3, '추위가 풀리지 않아 얼어죽는 백성들이 늘어나고 있습니다.'],
        ['<M><b>【재난】</b></>', 9, '황건적이 출현해 도시를 습격하고 있습니다.'],
      ],
      4 => [
        ['<M><b>【재난】</b></>', 7, '홍수로 인해 피해가 급증하고 있습니다.'],
        ['<M><b>【재난】</b></>', 5, '지진으로 피해가 속출하고 있습니다.'],
        ['<M><b>【재난】</b></>', 6, '태풍으로 인해 피해가 속출하고 있습니다.'],
      ],
      7 => [
        ['<M><b>【재난】</b></>', 8, '메뚜기 떼가 발생하여 도시가 황폐해지고 있습니다.'],
        ['<M><b>【재난】</b></>', 5, '지진으로 피해가 속출하고 있습니다.'],
        ['<M><b>【재난】</b></>', 8, '흉년이 들어 굶어죽는 백성들이 늘어나고 있습니다.'],
      ],
      10 => [
        ['<M><b>【재난】</b></>', 3, '혹한으로 도시가 황폐해지고 있습니다.'],
        ['<M><b>【재난】</b></>', 5, '지진으로 피해가 속출하고 있습니다.'],
        ['<M><b>【재난】</b></>', 3, '눈이 많이 쌓여 도시가 황폐해지고 있습니다.'],
        ['<M><b>【재난】</b></>', 9, '황건적이 출현해 도시를 습격하고 있습니다.'],
      ]
    ];

    $boomingTextList = [
      1 => null,
      4 => [
        ['<C><b>【호황】</b></>', 2, '호황으로 도시가 번창하고 있습니다.'],
      ],
      7 => [
        ['<C><b>【풍작】</b></>', 1, '풍작으로 도시가 번창하고 있습니다.'],
      ],
      10 => null
    ];

    [$logTitle, $stateCode, $logBody] = $rng->choice(($isGood ? $boomingTextList : $disasterTextList)[$month]);

    $logger = new ActionLogger(0, 0, $year, $month, false);

    $logger->pushGlobalHistoryLog("{$logTitle}{$targetCityNames}에 {$logBody}");
    $logger->flush();

    if (!$isGood) {
      $generalListByCity = Util::arrayGroupBy($db->query('SELECT no, name, nation, city, officer_level, injury, leadership, strength, intel, horse, weapon, book, item, crew, crewtype, atmos, train, special, special2 FROM general WHERE city IN %li', Util::squeezeFromArray($targetCityList, 'city')), 'city');
      //NOTE: 쿼리 1번이지만 복잡하기 vs 쿼리 여러번이지만 조금 더 깔끔하기
      foreach ($targetCityList as $city) {
        $affectRatio = Util::valueFit($city['secu'] / $city['secu_max'] / 0.8, 0, 1);
        $affectRatio = 0.8 + $affectRatio * 0.15;

        $db->update('city', [
          'state' => $stateCode,
          'pop' => $db->sqleval('pop * %d', $affectRatio),
          'trust' => $db->sqleval('trust * %d', $affectRatio),
          'agri' => $db->sqleval('agri * %d', $affectRatio),
          'comm' => $db->sqleval('comm * %d', $affectRatio),
          'secu' => $db->sqleval('secu * %d', $affectRatio),
          'def' => $db->sqleval('def * %d', $affectRatio),
          'wall' => $db->sqleval('wall * %d', $affectRatio),
        ], 'city = %i', $city['city']);

        $generalList = array_map(
          function ($rawGeneral) use ($city, $year, $month) {
            return new General($rawGeneral, null, $city, null, $year, $month, false);
          },
          $generalListByCity[$city['city']] ?? []
        );

        SabotageInjury($rng, $generalList, '재난');
      }
    } else {
      foreach ($targetCityList as $city) {
        $affectRatio = Util::valueFit($city['secu'] / $city['secu_max'] / 0.8, 0, 1);
        $affectRatio = 1.01 + $affectRatio * 0.04;

        $db->update('city', [
          'state' => $stateCode,
          'pop' => $db->sqleval('least(pop * %d, pop_max)', $affectRatio),
          'trust' => $db->sqleval('least(trust * %d, 100)', $affectRatio),
          'agri' => $db->sqleval('least(agri * %d, agri_max)', $affectRatio),
          'comm' => $db->sqleval('least(comm * %d, comm_max)', $affectRatio),
          'secu' => $db->sqleval('least(secu * %d, secu_max)', $affectRatio),
          'def' => $db->sqleval('least(def * %d, def_max)', $affectRatio),
          'wall' => $db->sqleval('least(wall * %d, wall_max)', $affectRatio),
        ], 'city = %i', $city['city']);
      }
    }
  }
}
