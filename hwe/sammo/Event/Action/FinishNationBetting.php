<?php

namespace sammo\Event\Action;

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
        [$year, $month] = [$env['year'], $env['month']];

        $bettingStor = KVStorage::getStorage($db, 'betting');
        $bettingInfoRaw = $bettingStor->getValue("id_{$this->bettingID}");
        if($bettingInfoRaw === null){
            return [__CLASS__, true];
        }
        $bettingInfo = new BettingInfo($bettingInfoRaw);
        if($bettingInfo->type != 'nationBetting'){
            return [__CLASS__, false, 'invalid type', $bettingInfo->type];
        }
        $bettingInfo->finished = true;
        $bettingStor->setValue("id_{$this->bettingID}", $bettingInfo->toArray());

        //TODO: 포인트를 배분해주어야 함
        //TODO: 이후 토너먼트 베팅 결과도 같이 처리할 것이므로, 별도의 함수나 모듈을 생성하여 처리!

        //NOTE: 완료되었음을 알릴 것인가?

        return [__CLASS__, false, 'NYI'];
    }
}
