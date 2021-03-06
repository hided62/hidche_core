<?php

namespace sammo\ActionItem;

use sammo\DB;
use \sammo\iAction;
use \sammo\General;
use sammo\KVStorage;
use sammo\RandUtil;
use sammo\Util;

class che_보물_도기 extends \sammo\BaseItem
{

    protected $rawName = '도기';
    protected $name = '도기(보물)';
    protected $info = '[개인] 판매 시 장수 소지금과 국고에 금, 쌀 중 하나를 추가 (총 +10,000, 2년마다 +5,000)';
    protected $cost = 200;
    protected $consumable = false;

    public function onArbitraryAction(General $general, RandUtil $rng, string $actionType, ?string $phase = null, $aux = null): ?array
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

        $score = 10000 + 5000 * Util::valueFit(intdiv($relYear, 2), 0);

        [$resName, $resKey] = $rng->choice([
            ['금', 'gold'],
            ['쌀', 'rice']
        ]);

        $nationId = $general->getVar('nation');

        if ($nationId != 0) {
          $db->update('nation', [
            $resKey => $db->sqleval('%b + %i', $resKey, Util::toInt($score / 2))
          ], 'nation=%i', $nationId);
        }
        $general->increaseVar($resKey, $score - Util::toInt($score / 2));

        $score = Util::round($score);
        $scoreText = number_format($score, 0);

        $logger = $general->getLogger();
        $logger->pushGeneralActionLog("재산과 국고에 총 {$resName} <C>{$scoreText}</>을 보충합니다.");
        return $aux;
    }
}
