<?php

namespace sammo\Event\Action;

use sammo\ActionLogger;
use sammo\Betting;
use \sammo\GameConst;
use \sammo\Util;
use \sammo\DB;
use sammo\DTO\BettingInfo;
use sammo\Json;
use sammo\KVStorage;

class FinishNationBetting extends \sammo\Event\Action
{
    public function __construct(private int $bettingID)
    {
    }

    public function run(array $env)
    {
        $db = DB::db();

        try {
            $bettingHelper = new Betting($this->bettingID);
        } catch (\Exception $e) {
            return [__CLASS__, false, $e->getMessage()];
        }

        $bettingInfo = $bettingHelper->getInfo();
        if ($bettingInfo->type != 'bettingNation') {
            return [__CLASS__, false, 'invalid type', $bettingInfo->type];
        }

        $winnerNations = $db->queryFirstColumn('SELECT nation FROM nation WHERE level > 0');
        if (count($winnerNations) != $bettingInfo->selectCnt) {
            return [__CLASS__, false, 'invalid winner cnt', $bettingInfo->selectCnt];
        }

        //nation_id와 betting_type이 일치하지 않으므로 정렬
        $nationIDMap = [];
        foreach ($bettingInfo->candidates as $idx => $candidate) {
            $aux = $candidate->aux;
            if (!$aux) {
                return [__CLASS__, false, "invalid aux {$idx}:{$candidate->title}"];
            }
            $nationID = $aux['nation'];
            $nationIDMap[$nationID] = $idx;
        }

        $winnerTypes = [];

        $notInBettingWinner = 0;
        foreach ($winnerNations as $winnerNationID) {
            if(key_exists($winnerNationID, $nationIDMap)){
                $winnerTypes[] = $nationIDMap[$winnerNationID];
            }
            else{
                //혹시라도 베팅 시점 이후에 생성된 국가라 없을 수 있다.
                $winnerTypes[] = count($nationIDMap) + $notInBettingWinner;
                $notInBettingWinner += 1;
            }
        }

        $winnerTypes = $bettingHelper->purifyBettingKey($winnerTypes, $notInBettingWinner > 0);

        $bettingHelper->giveReward($winnerTypes);
        $logger = new ActionLogger(0, 0, $env['year'], $env['month']);
        [$year, $month] = Util::parseYearMonth($bettingInfo->openYearMonth);
        $logger->pushGlobalHistoryLog("<B><b>【내기】</b></> {$year}년 {$month}월에 열렸던 {$bettingInfo->name} 내기의 결과가 나왔습니다!");

        return [__CLASS__, true];
    }
}
