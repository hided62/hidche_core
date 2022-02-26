<?php

namespace sammo\ActionItem;

use sammo\DB;
use \sammo\iAction;
use \sammo\General;
use sammo\KVStorage;
use sammo\Util;

class che_보물_도기 extends \sammo\BaseItem
{

    protected $rawName = '도기';
    protected $name = '도기(보물)';
    protected $info = '[개인] 판매 시 국고에 금, 쌀 중 하나를 추가 (+10,000, 5년마다 +10,000)';
    protected $cost = 200;
    protected $consumable = false;

    public function onArbitraryAction(General $general, string $actionType, ?string $phase = null, $aux = null): ?array
    {
        if ($aux === null){
            return $aux;
        }
        if ($actionType !== '장비매매') {
            return $aux;
        }
        if ($phase !== '판매') {
            return $aux;
        }
        if (($aux['itemCode']??'') !== Util::getClassNameFromObj($this)){
            return $aux;
        }

        $db = DB::db();
        $gameStor = KVStorage::getStorage($db, 'game_env');
        [$year, $startYear] = $gameStor->getValuesAsArray(['year', 'startyear']);
        $relYear = $year - $startYear;

        $score = 10000 * (1 + Util::valueFit(intdiv($relYear, 5), 0));

        [$resName, $resKey] = Util::choiceRandom([
            ['금', 'gold'],
            ['쌀', 'rice']
        ]);

        $nationId = general['nation'];

        if ($nationId != 0) {
          $db->update('nation', [
            $resKey => $db->sqleval('%b + %i', $resKey, $score)
          ], 'nation=%i', $nationId);
        }

        $score = Util::round($score);
        $scoreText = number_format($score, 0);

        $logger = $general->getLogger();
        $logger->pushGeneralActionLog("국고에 {$resName} <C>{$scoreText}</>을 보충합니다.");
        return $aux;
    }
}
