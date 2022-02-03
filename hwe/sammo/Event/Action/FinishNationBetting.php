<?php

namespace sammo\Event\Action;

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

        $bettingStor = KVStorage::getStorage($db, 'betting');
        $bettingInfoRaw = $bettingStor->getValue("id_{$this->bettingID}");
        if($bettingInfoRaw === null){
            return [__CLASS__, true];
        }

        try{
            $bettingHelper = new Betting($this->bettingID);
        }
        catch (\Exception $e){
            return [__CLASS__, false, $e->getMessage()];
        }

        $bettingInfo = $bettingHelper->getInfo();
        if($bettingInfo->type != 'nationBetting'){
            return [__CLASS__, false, 'invalid type', $bettingInfo->type];
        }

        $winnerNations = $bettingHelper->purifyBettingKey($db->queryFirstColumn('SELECT nation FROM nation WHERE level > 0'));
        if(count($winnerNations) != $bettingInfo->selectCnt){
            return [__CLASS__, false, 'invalid winner cnt', $bettingInfo->selectCnt];
        }

        $bettingHelper->giveReward($winnerNations);

        return [__CLASS__, true];
    }
}
