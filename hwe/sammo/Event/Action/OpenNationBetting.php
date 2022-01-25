<?php

namespace sammo\Event\Action;

use \sammo\GameConst;
use \sammo\Util;
use \sammo\DB;
use sammo\DTO\NationBettingInfo;
use sammo\Json;
use sammo\KVStorage;

class OpenNationBetting extends \sammo\Event\Action
{
    public function __construct(private int $nationCnt = 1, private int $bonusPoint = 0)
    {
        if ($nationCnt < 1) {
            throw new \RuntimeException("1 미만의 숫자");
        }
        if ($bonusPoint < 0){
            throw new \RuntimeException("0 미만의 보너스 포인트");
        }
    }

    public function run(array $env)
    {
        $db = DB::db();

        $gameStor = KVStorage::getStorage($db, 'game_env');
        $gameStor->invalidateCacheValue('last_betting_id');
        $bettingID = ($gameStor->getValue('last_betting_id') ?? 0) + 1;
        $gameStor->setValue('last_betting_id', $bettingID);

        $nationBettingStor = KVStorage::getStorage($db, 'nation_betting');
        [$year, $month] = [$env['year'], $env['month']];

        if ($this->nationCnt == 1) {
            $name = "천통국";
        } else {
            $name = "최후 {$this->nationCnt}국";
        }


        $openYearMonth = Util::joinYearMonth($year, $month);
        $closeYearMonth = $openYearMonth + 24;

        $bettingInfo = new NationBettingInfo(
            id: $bettingID,
            name: "[{$year}년 {$month}월] {$name} 예상 베팅",
            finished: false,
            selectCnt: $this->nationCnt,
            reqInheritancePoint: true,
            openYearMonth: $openYearMonth,
            closeYearMonth: $closeYearMonth,
        );
        $nationBettingStor->setValue("id_{$bettingID}", $bettingInfo->toArray());

        $db->insert('event', [
            'target' => 'DESTROY_NATION',
            'priority' => 1000,
            'condition' => Json::encode(
                ["RemainNation", "<=", $this->nationCnt],
            ),
            'action' => Json::encode([
                ["FinishNationBetting", $bettingID],
                ["DeleteEvent"],
            ]),
        ]);

        if($this->bonusPoint > 0){
            $db->insert('ng_betting', [
                'betting_id' => $bettingID,
                'general_id' => 0,
                'betting_type' => '0',
                'amount' => $this->bonusPoint
            ]);
        }

        $logger = new \sammo\ActionLogger(0, 0, $year, $month);
        $logger->pushGlobalHistoryLog("<B><b>【내기】</b></>천하통일을 염원하는 <C>내기</>가 진행중입니다! 호사가의 참여를 기다립니다!");
        $logger->flush();

        return [__CLASS__, true];
    }
}
