<?php

namespace sammo;

use sammo\Enums\AuctionType;

function registerAuction(RandUtil $rng)
{
    $db = DB::db();

    // 장수들 평금,평쌀
    [$avgGold, $avgRice] = $db->queryFirstList('SELECT avg(gold), avg(rice) from general where npc<2');
    $avgGold = Util::valueFit($avgGold, 1000, 20000);
    $avgRice = Util::valueFit($avgRice, 1000, 20000);

    $neutralAuctionCnt = Util::convertPairArrayToDict($db->queryAllLists(
        'SELECT `type`, count(*) FROM ng_auction WHERE `type` IN %ls AND `host_general_id`=0 GROUP BY `type`',
        [AuctionType::BuyRice->value, AuctionType::SellRice->value],
    ));

    $neutralbuyRiceCnt = $neutralAuctionCnt[AuctionType::BuyRice->value];

    // 판매건 등록
    if ($rng->nextBool(1 / ($neutralbuyRiceCnt + 5))) {
        //평균 쌀의 5% ~ 25%
        $mul = $rng->nextRangeInt(1, 5);
        $amount = $avgRice / 20 * $mul;
        $cost = $avgGold / 20 * 0.9 * $mul;
        $topv = $amount * 2;
        $cost = Util::valueFit($cost, $amount * 0.8, $amount * 1.2);

        $amount = Util::round($amount, -1);
        $cost = Util::round($cost, -1);
        $topv = Util::round($topv, -1);

        $term = $rng->nextRangeInt(3, 12);
        $dummyGeneral = AuctionBasicResource::genDummy();
        AuctionBuyRice::openResourceAuction($dummyGeneral, $amount, $term, $cost, $topv);
    }

    $neutralSellRiceCnt = $neutralAuctionCnt[AuctionType::SellRice->value];
    // 구매건 등록
    if ($rng->nextBool(1 / ($neutralSellRiceCnt + 5))) {
        //평균 쌀의 5% ~ 25%
        $mul = $rng->nextRangeInt(1, 5);
        $amount = $avgGold / 20 * $mul;
        $cost = $avgRice / 20 * 1.1 * $mul;
        $topv = $amount * 2;
        $cost = Util::valueFit($cost, $amount * 0.8, $amount * 1.2);

        $amount = Util::round($amount, -1);
        $cost = Util::round($cost, -1);
        $topv = Util::round($topv, -1);

        $term = $rng->nextRangeInt(3, 12);
        $dummyGeneral = AuctionBasicResource::genDummy();
        AuctionSellRice::openResourceAuction($dummyGeneral, $amount, $term, $cost, $topv);
    }
}

function processAuction()
{
    $db = DB::db();

    $now = TimeUtil::now();

    $auctionList = $db->queryAllLists(
        'SELECT id, `type` FROM ng_auction WHERE `close_date` <= %s AND finished = 0',
        $now
    );

    if (!$auctionList) {
        return;
    }

    $dummyGeneral = AuctionBasicResource::genDummy();
    foreach ($auctionList as [$auctionID, $rawAuctionType]) {
        $auctionType = AuctionType::from($rawAuctionType);
        if ($auctionType === AuctionType::BuyRice) {
            $auction = new AuctionBuyRice($auctionID, $dummyGeneral);
        } else if ($auctionType === AuctionType::SellRice) {
            $auction = new AuctionSellRice($auctionID, $dummyGeneral);
        } else if ($auctionType === AuctionType::UniqueItem) {
            $auction = new AuctionUniqueItem($auctionID, $dummyGeneral);
        } else {
            throw new \Exception('Unknown auction type');
        }
        $auction->tryFinish();
    }
}
