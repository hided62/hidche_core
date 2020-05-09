<?php
namespace sammo;

function registerAuction() {
    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');

    $admin = $gameStor->getValues(['startyear', 'year', 'month', 'turnterm']);

    $unit = 60 * $admin['turnterm'];

    // 장수들 평금,평쌀
    $general = $db->queryFirstRow('SELECT avg(gold) as gold, avg(rice) as rice,max(gold) as maxgold from general where npc<2');

    if($general['gold'] <  1000) { $general['gold'] =  1000; }
    if($general['gold'] > 20000) { $general['gold'] = 20000; }
    if($general['rice'] <  1000) { $general['rice'] =  1000; }
    if($general['rice'] > 20000) { $general['rice'] = 20000; }

    $count = $db->queryFirstField('SELECT count(*) FROM auction WHERE type=0 AND no1=0');
    $count += 5;
    // 판매건 등록
    if(Util::randBool(1/$count)) {
        //평균 쌀의 5% ~ 25%
        $mul = rand() % 5 + 1;
        $amount = $general['rice'] / 20 * $mul;
        $cost = $general['gold'] / 20 * 0.9 * $mul;
        $topv = $amount * 2;
        if($cost <= $amount*0.8) { $cost = $amount*0.8; }
        if($cost >= $amount*1.2) { $cost = $amount*1.2; }

        $amount = Util::round($amount, -1);
        $cost = Util::round($cost, -1);
        $topv = Util::round($topv, -1);

        $term = 3 + rand() % 10;
        $date = date("Y-m-d H:i:s", strtotime(date("Y-m-d H:i:s")) + $unit * $term);
        $db->insert('auction', [
            'type'=>0,
            'no1'=>0,
            'name1'=>'ⓝ상인',
            'amount'=>$amount,
            'cost'=>$cost,
            'value'=>$cost,
            'topv'=>$topv,
            'expire'=>$date
        ]);
    }

    $count = $db->queryFirstField('SELECT count(*) FROM auction WHERE type=1 AND no1=0');
    $count += 5;
    // 구매건 등록
    if(Util::randBool(1/$count)) {
        //평균 쌀의 5% ~ 25%
        $mul = Util::randRangeInt(1, 5);
        $amount = $general['rice'] / 20 * $mul;
        $cost = $general['gold'] / 20 * 1.1 * $mul;
        $topv = $amount * 0.5;
        if($cost <= $amount*0.8) { $cost = $amount*0.8; }
        if($cost >= $amount*1.2) { $cost = $amount*1.2; }

        $amount = Util::round($amount, -1);
        $cost = Util::round($cost, -1);
        $topv = Util::round($topv, -1);

        $term = Util::randRangeInt(3, 12);
        $date = date("Y-m-d H:i:s", strtotime(date("Y-m-d H:i:s")) + $unit * $term);
        $db->insert('auction', [
            'type'=>1,
            'no1'=>0,
            'name1'=>'ⓝ상인',
            'amount'=>$amount,
            'cost'=>$cost,
            'value'=>$cost,
            'topv'=>$topv,
            'expire'=>$date
        ]);
    }
}

function processAuction() {
    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');

    $date = TimeUtil::now();
    [$year, $month] = $gameStor->getValuesAsArray(['year', 'month']);

    $admin = $gameStor->getValues(['year', 'month']);

    foreach($db->query('SELECT * from auction where expire<=%s', $date) as $auction){
        $josaYi1 = JosaUtil::pick($auction['name1'], '이');
        $josaYi2 = JosaUtil::pick($auction['name2'], '이');

        // 쌀 처리
        if($auction['no2'] == 0) {
            // 상인건수가 아닌것만 출력
            if($auction['no1'] != 0) {
                $traderID = $db->queryFirstField('SELECT no FROM general WHERE no=%i', $auction['no1']);
                $logger = new ActionLogger($traderID, 0, $year, $month);
                $logger->pushGeneralActionLog("입찰자 부재로 {$auction['no']}번 거래 <M>유찰</>!", ActionLogger::EVENT_PLAIN);
                $logger->flush();

                $auctionLog = [];
                if($auction['type'] == 0) {
                    $josaUlRice = JosaUtil::pick($auction['amount'], '을');
                    $auctionLog[] = "<S>◆</>{$admin['year']}년 {$admin['month']}월, {$auction['no']}번 <span class='sell'>판매</span> <M>유찰</> : <Y>{$auction['name1']}</>{$josaYi1} 쌀 <C>{$auction['amount']}</>{$josaUlRice} 판매, 그러나 입찰자 부재";
                } else {
                    $josaUlRice = JosaUtil::pick($auction['amount'], '을');
                    $auctionLog[] = "<S>◆</>{$admin['year']}년 {$admin['month']}월, {$auction['no']}번 <S>구매</> <M>유찰</> : <Y>{$auction['name1']}</>{$josaYi1} 쌀 <C>{$auction['amount']}</>{$josaUlRice} 구매, 그러나 입찰자 부재";
                }

                pushAuctionLog($auctionLog);
            }
            continue;
        }

        if($auction['no1'] == 0) {
            $trader = [
                'no'=>0,
                'name'=>'ⓝ상인',
                'gold'=>99999,
                'rice'=>99999
            ];
        } else {
            $trader = $db->queryFirstRow('SELECT no,name,gold,rice from general where no=%i', $auction['no1']);
        }

        $bidder = $db->queryFirstRow('SELECT no,name,gold,rice from general where no=%i', $auction['no2']);

        $traderLogger = new ActionLogger($trader['no'], 0, $year, $month, false);
        $bidderLogger = new ActionLogger($bidder['no'], 0, $year, $month, false);

        $auctionLog = [];

        
        //판매거래
        if($auction['type'] == 0) {
            if($auction['amount'] > $trader['rice'] - 1000) {
                $gold = Util::round($auction['value'] * 0.01);
                $db->update('general', [
                    'gold'=>Util::valueFit($trader['gold'] - $gold, 0)
                ], 'no=%i', $trader['no']);

                $josaUlRice = JosaUtil::pick($auction['amount'], '을');
                $josaRo = JosaUtil::pick($auction['value'], '로');
                $traderLogger->pushGeneralActionLog("판매자의 군량 부족으로 {$auction['no']}번 거래 <M>유찰</>! 벌금 <C>{$gold}</>", ActionLogger::EVENT_PLAIN);
                $bidderLogger->pushGeneralActionLog("판매자의 군량 부족으로 {$auction['no']}번 거래 <M>유찰</>!", ActionLogger::EVENT_PLAIN);
                $auctionLog[0] = "<S>◆</>{$admin['year']}년 {$admin['month']}월, {$auction['no']}번 <span class='sell'>판매</span> <M>유찰</> : <Y>{$auction['name1']}</>{$josaYi1} 쌀 <C>{$auction['amount']}</>{$josaUlRice} 판매, <Y>{$auction['name2']}</>{$josaYi2} 금 <C>{$auction['value']}</>{$josaRo} 입찰, 그러나 판매자 군량부족, 벌금 <C>{$gold}</>";
            } elseif($auction['value'] > $bidder['gold'] - 1000) {
                $gold = Util::round($auction['value'] * 0.01);
                $db->update('general', [
                    'gold'=>Util::valueFit($bidder['gold'] - $gold, 0)
                ], 'no=%i', $bidder['no']);

                $josaUlRice = JosaUtil::pick($auction['amount'], '을');
                $josaRo = JosaUtil::pick($auction['value'], '로');
                $traderLogger->pushGeneralActionLog("입찰자의 자금 부족으로 {$auction['no']}번 거래 <M>유찰</>!", ActionLogger::EVENT_PLAIN);
                $bidderLogger->pushGeneralActionLog("입찰자의 자금 부족으로 {$auction['no']}번 거래 <M>유찰</>! 벌금 <C>{$gold}</>", ActionLogger::EVENT_PLAIN);
                $auctionLog[0] = "<S>◆</>{$admin['year']}년 {$admin['month']}월, {$auction['no']}번 <span class='sell'>판매</span> <M>유찰</> : <Y>{$auction['name1']}</>{$josaYi1} 쌀 <C>{$auction['amount']}</>{$josaUlRice} 판매, <Y>{$auction['name2']}</>{$josaYi2} 금 <C>{$auction['value']}</>{$josaRo} 입찰, 그러나 입찰자 자금부족, 벌금 <C>{$gold}</>";
            } else {
                $josaUlGold = JosaUtil::pick($auction['value'], '을');
                $josaUlRice = JosaUtil::pick($auction['amount'], '을');
                $josaRo = JosaUtil::pick($auction['value'], '로');
                $traderLogger->pushGeneralActionLog("{$auction['no']}번 거래 <C>성사</>로 쌀 <C>{$auction['amount']}</>{$josaUlRice} 판매, 금 <C>{$auction['value']}</>{$josaUlGold} 획득!", ActionLogger::EVENT_PLAIN);
                $bidderLogger->pushGeneralActionLog("{$auction['no']}번 거래 <C>성사</>로 금 <C>{$auction['value']}</>{$josaUlGold} 지불, 쌀 <C>{$auction['amount']}</>{$josaUlRice} 구입!", ActionLogger::EVENT_PLAIN);
                $auctionLog[0] = "<S>◆</>{$admin['year']}년 {$admin['month']}월, {$auction['no']}번 <span class='sell'>판매</span> <C>성사</> : <Y>{$auction['name1']}</>{$josaYi1} 쌀 <C>{$auction['amount']}</>{$josaUlRice} 판매, <Y>{$auction['name2']}</>{$josaYi2} 금 <C>{$auction['value']}</>{$josaRo} 구매";
                if($auction['value'] >= $auction['amount'] * 2) {
                    $auctionLog[0] .= " <R>★ 최고가 거래 ★</>";
                } elseif($auction['value'] >= $auction['topv']) {
                    $auctionLog[0] .= " <M>★ 즉시구매가 거래 ★</>";
                } elseif($auction['value'] * 2 <= $auction['amount']) {
                    $auctionLog[0] .= " <R>★ 최저가 거래 ★</>";

                }

                $db->update('general', [
                    'gold'=>$db->sqleval('gold + %i', $auction['value']),
                    'rice'=>$db->sqleval('rice - %i', $auction['amount']),
                ], 'no=%i', $auction['no1']);
                $db->update('general', [
                    'gold'=>$db->sqleval('gold - %i', $auction['value']),
                    'rice'=>$db->sqleval('rice + %i', $auction['amount']),
                ], 'no=%i', $auction['no2']);
            }
            pushAuctionLog($auctionLog);
        //구매거래
        } else {
            if($auction['amount'] > $bidder['rice'] - 1000) {
                $gold = Util::round($auction['value'] * 0.01);
                $db->update('general', [
                    'gold'=>Util::valueFit($bidder['gold'] - $gold, 0)
                ], 'no=%i', $bidder['no']);

                $josaUlRice = JosaUtil::pick($auction['amount'], '을');
                $josaRo = JosaUtil::pick($auction['value'], '로');
                $traderLogger->pushGeneralActionLog("입찰자의 군량 부족으로 {$auction['no']}번 거래 <M>유찰</>!", ActionLogger::EVENT_PLAIN);
                $bidderLogger->pushGeneralActionLog("입찰자의 군량 부족으로 {$auction['no']}번 거래 <M>유찰</>! 벌금 <C>{$gold}</>", ActionLogger::EVENT_PLAIN);
                $auctionLog[0] = "<S>◆</>{$admin['year']}년 {$admin['month']}월, {$auction['no']}번 <S>구매</> <M>유찰</> : <Y>{$auction['name1']}</>{$josaYi1} 쌀 <C>{$auction['amount']}</>{$josaUlRice} 구매, <Y>{$auction['name2']}</>{$josaYi2} 금 <C>{$auction['value']}</>{$josaRo} 입찰, 그러나 입찰자 군량부족, 벌금 <C>{$gold}</>";
            } elseif($auction['value'] > $trader['gold'] - 1000) {
                $gold = Util::round($auction['value'] * 0.01);
                $db->update('general', [
                    'gold'=>Util::valueFit($trader['gold'] - $gold, 0)
                ], 'no=%i', $trader['no']);

                $josaUlRice = JosaUtil::pick($auction['amount'], '을');
                $josaRo = JosaUtil::pick($auction['value'], '로');
                $traderLogger->pushGeneralActionLog("구매자의 자금 부족으로 {$auction['no']}번 거래 <M>유찰</>! 벌금 <C>{$gold}</>", ActionLogger::EVENT_PLAIN);
                $bidderLogger->pushGeneralActionLog("구매자의 자금 부족으로 {$auction['no']}번 거래 <M>유찰</>!", ActionLogger::EVENT_PLAIN);
                $auctionLog[0] = "<S>◆</>{$admin['year']}년 {$admin['month']}월, {$auction['no']}번 <S>구매</> <M>유찰</> : <Y>{$auction['name1']}</>{$josaYi1} 쌀 <C>{$auction['amount']}</>{$josaUlRice} 구매, <Y>{$auction['name2']}</>{$josaYi2} 금 <C>{$auction['value']}</>{$josaRo} 입찰, 그러나 구매자 자금부족, 벌금 <C>{$gold}</>";
            } else {
                $josaUlGold = JosaUtil::pick($auction['value'], '을');
                $josaUlRice = JosaUtil::pick($auction['amount'], '을');
                $josaRo = JosaUtil::pick($auction['value'], '로');
                $traderLogger->pushGeneralActionLog(
                    "{$auction['no']}번 거래 <C>성사</>로 금 <C>{$auction['value']}</>{$josaUlGold} 지불, 쌀 <C>{$auction['amount']}</>{$josaUlRice} 구입!", ActionLogger::EVENT_PLAIN
                );
                $bidderLogger->pushGeneralActionLog("{$auction['no']}번 거래 <C>성사</>로 쌀 <C>{$auction['amount']}</>{$josaUlRice} 판매, 금 <C>{$auction['value']}</>{$josaUlGold} 획득!", ActionLogger::EVENT_PLAIN);
                $auctionLog[0] = "<S>◆</>{$admin['year']}년 {$admin['month']}월, {$auction['no']}번 <S>구매</> <C>성사</> : <Y>{$auction['name1']}</>{$josaYi1} 쌀 <C>{$auction['amount']}</>{$josaUlRice} 구매, <Y>{$auction['name2']}</>{$josaYi2} 금 <C>{$auction['value']}</>{$josaRo} 판매";
                if($auction['value'] >= $auction['amount'] * 2) {
                    $auctionLog[0] .= " <R>★ 최고가 거래 ★</>";
                } elseif($auction['value'] * 2 <= $auction['amount']) {
                    $auctionLog[0] .= " <R>★ 최저가 거래 ★</>";
                } elseif($auction['value'] <= $auction['topv']) {
                    $auctionLog[0] .= " <M>★ 즉시구매가 거래 ★</>";
                }

                $db->update('general', [
                    'gold'=>$db->sqleval('gold - %i', $auction['value']),
                    'rice'=>$db->sqleval('rice + %i', $auction['amount']),
                ], 'no=%i', $auction['no1']);
                $db->update('general', [
                    'gold'=>$db->sqleval('gold + %i', $auction['value']),
                    'rice'=>$db->sqleval('rice - %i', $auction['amount']),
                ], 'no=%i', $auction['no2']);
            }
            $traderLogger->flush();
            $bidderLogger->flush();
            pushAuctionLog($auctionLog);
        }

        $traderLogger->flush();
        $bidderLogger->flush();
    }

    $db->delete('auction', 'expire <= %s', $date);
}

