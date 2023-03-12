<?php
namespace sammo\Event\Action;

use Ds\Map;
use sammo\DB;
use sammo\Enums\InheritanceKey;
use sammo\Enums\RankColumn;
use sammo\General;
use sammo\InheritancePointManager;

class MergeInheritPointRank extends \sammo\Event\Action
{
  public function __construct()
  {
  }

  public function run(array $env)
  {
    $db = DB::db();

    $generals = General::createGeneralObjListFromDB(null, null, 2);

    $points = new Map();
    $points->allocate(count($generals));
    foreach($generals as $general){
        $generalID = $general->getID();
        $points[$generalID] = 0;
    }

    foreach(InheritanceKey::cases() as $key){
        if($key === InheritanceKey::previous){
            continue;
        }
        $subPoints = InheritancePointManager::getInstance()->getInheritancePointFromAll($generals, $key);
        foreach($generals as $general){
            $generalID = $general->getID();
            $points[$generalID] += $subPoints[$generalID] ?? 0;
        }
    }

    $pointsPairs = [];
    foreach($points as $generalID => $point){
        $pointsPairs[] = [
            'nation_id' => $generals[$generalID]->getNationID(),
            'general_id' => $generalID,
            'type' => RankColumn::inherit_point_earned_by_merge->value,
            'value' => $point,
        ];
    }
    //XXX: multiple batch update가 제공되지 않으므로..
    $db->delete('rank_data', '`type` = %s', RankColumn::inherit_point_earned_by_merge->value);
    $db->insert('rank_data', $pointsPairs);

    $db->query(
        'UPDATE `rank_data` D SET `value` = (SELECT SUM(`value`) FROM `rank_data` S WHERE S.general_id = D.general_id AND S.`type` IN %ls) WHERE D.`type` = %s',
        [RankColumn::inherit_point_earned_by_action->value, RankColumn::inherit_point_earned_by_merge->value],
        RankColumn::inherit_point_earned->value
    );

    $db->query(
        'UPDATE `rank_data` D SET `value` = (SELECT `value` FROM `rank_data` S WHERE S.general_id = D.general_id AND S.`type` = %s) WHERE D.`type` = %s',
        RankColumn::inherit_point_spent_dynamic->value,
        RankColumn::inherit_point_spent->value
    );
  }
}
