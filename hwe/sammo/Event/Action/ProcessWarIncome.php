<?php

namespace sammo\Event\Action;

use sammo\DB;
use sammo\Util;

use function sammo\getAllNationStaticInfo;
use function sammo\getWarGoldIncome;

class ProcessWarIncome extends \sammo\Event\Action
{
  public function run(array $env)
  {
    $db = DB::db();

    $cityListByNation = Util::arrayGroupBy($db->query('SELECT * FROM city'), 'nation');

    foreach(getAllNationStaticInfo() as $nation){
        if($nation['level'] <= 0){
            continue;
        }
        $nationID = $nation['nation'];
        $income = getWarGoldIncome($nation['type'], $cityListByNation[$nationID]??[]);
        $db->update('nation', [
            'gold'=>$db->sqleval('gold + %i', $income)
        ], 'nation=%i', $nationID);
    }

    // 10%수입, 20%부상병
    $db->update('city', [
        'pop'=>$db->sqleval('pop + dead * %d', 0.2),
        'dead'=>0
    ], true);
  }
}
