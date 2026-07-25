<?php

namespace sammo\Event\Action;

use sammo\ActionLogger;
use sammo\CentennialAllStarGrowthService;
use sammo\DB;
use sammo\General;
use sammo\Json;

class AdvanceCentennialAllStar extends \sammo\Event\Action
{
    public function run(array $env)
    {
        if (!CentennialAllStarGrowthService::isActive()) {
            return [__CLASS__, 0];
        }

        $db = DB::db();
        $updated = 0;
        foreach ($db->query(
            'SELECT general.no, select_pool.info
             FROM general
             JOIN select_pool ON select_pool.general_id = general.no'
        ) as $row) {
            $general = General::createObjFromDB((int) $row['no']);
            $targetInfo = Json::decode($row['info']);
            $result = CentennialAllStarGrowthService::applyTarget($general, $targetInfo, $env);

            if ($result['milestone'] > $result['previousMilestone']) {
                $percent = $result['milestone'] * 20;
                $general->getLogger()->pushGeneralActionLog(
                    "<L>올스타 동조율</>이 <C>{$percent}%</>에 도달했습니다!",
                    ActionLogger::PLAIN
                );
                $general->getLogger()->pushGeneralHistoryLog(
                    "<L>올스타 동조율 {$percent}% 달성</>"
                );
            }
            if ($general->applyDB($db)) {
                $updated++;
            }
        }

        return [__CLASS__, $updated];
    }
}
