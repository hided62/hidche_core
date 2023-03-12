<?php

namespace sammo\Event\Action;

use sammo\DB;
use sammo\LiteHashDRBG;
use sammo\RandUtil;
use sammo\UniqueConst;
use sammo\Util;

class RandomizeCityTradeRate extends \sammo\Event\Action
{
  public function run(array $env)
  {
    $year = $env['year'];
    $month = $env['month'];

    $rng = new RandUtil(new LiteHashDRBG(Util::simpleSerialize(
      UniqueConst::$hiddenSeed,
      'randomizeCityTradeRate',
      $year,
      $month,
    )));

    $db = DB::db();
    foreach ($db->query('SELECT city,level FROM city') as $city) {
      //시세
      $prob = [
        1 => 0,
        2 => 0,
        3 => 0,
        4 => 0.2,
        5 => 0.4,
        6 => 0.6,
        7 => 0.8,
        8 => 1
      ][$city['level']];
      if ($prob > 0 && $rng->nextBool($prob)) {
        $trade = $rng->nextRangeInt(95, 105);
      } else {
        $trade = null;
      }
      $db->update('city', [
        'trade' => $trade
      ], 'city=%i', $city['city']);
    }
  }
}
