<?php
namespace sammo;

function GetStuffName($stuff) {
    $type1 = $stuff % 10;
    $type2 = intdiv($stuff, 10);

    switch($type1) {
    case 0: $str = "쌀"; break;
    case 1: $str = getWeapName($type2); break;
    case 2: $str = getBookName($type2); break;
    case 3: $str = getHorseName($type2); break;
    case 4: $str = getItemName($type2); break;
    default:$str = "?"; break;
    }
    return $str;
}

function registerAuction() {
    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');
    $connect=$db->get();

    $admin = $gameStor->getValues(['startyear', 'year', 'month', 'turnterm']);

    $unit = 60 * $admin['turnterm'];

    // 장수들 평금,평쌀
    $query = "select avg(gold) as gold, avg(rice) as rice,max(gold) as maxgold from general where npc<2";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $general = MYDB_fetch_array($result);

    if($general['gold'] <  1000) { $general['gold'] =  1000; }
    if($general['gold'] > 20000) { $general['gold'] = 20000; }
    if($general['rice'] <  1000) { $general['rice'] =  1000; }
    if($general['rice'] > 20000) { $general['rice'] = 20000; }

    $count = $db->queryFirstField('SELECT count(*) FROM auction WHERE type=0 AND stuff=0 AND no1=0');
    $count += 5;
    // 판매건 등록
    if(rand()%$count == 0) {
        //평균 쌀의 5% ~ 25%
        $mul = rand() % 5 + 1;
        $amount = $general['rice'] / 20 * $mul;
        $cost = $general['gold'] / 20 * 0.9 * $mul;
        $topv = $amount * 2;
        if($cost <= $amount*0.8) { $cost = $amount*0.8; }
        if($cost >= $amount*1.2) { $cost = $amount*1.2; }

        $amount = Util::round($amount / 10) * 10;
        $cost = Util::round($cost / 10) * 10;
        $topv = Util::round($topv / 10) * 10;

        $term = 3 + rand() % 10;
        $date = date("Y-m-d H:i:s", strtotime(date("Y-m-d H:i:s")) + $unit * $term);
        $query = "insert into auction (type, no1, name1, stuff, amount, cost, value, topv, expire) values (0, '0', 'ⓝ상인', '0', '$amount', '$cost', '$cost', '$topv', '$date')";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    }

    $count = $db->queryFirstField('SELECT count(*) FROM auction WHERE type=1 AND stuff=0 AND no1=0');
    $count += 5;
    // 구매건 등록
    if(rand()%$count == 0) {
        //평균 쌀의 5% ~ 25%
        $mul = rand() % 5 + 1;
        $amount = $general['rice'] / 20 * $mul;
        $cost = $general['gold'] / 20 * 1.1 * $mul;
        $topv = $amount * 0.5;
        if($cost <= $amount*0.8) { $cost = $amount*0.8; }
        if($cost >= $amount*1.2) { $cost = $amount*1.2; }

        $amount = Util::round($amount / 10) * 10;
        $cost = Util::round($cost / 10) * 10;
        $topv = Util::round($topv / 10) * 10;

        $term = 3 + rand() % 10;
        $date = date("Y-m-d H:i:s", strtotime(date("Y-m-d H:i:s")) + $unit * $term);
        $query = "insert into auction (type, no1, name1, stuff, amount, cost, value, topv, expire) values (1, '0', 'ⓝ상인', '0', '$amount', '$cost', '$cost', '$topv', '$date')";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    }
}

function processAuction() {
    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');
    $connect=$db->get();

    $trader = [];
    $traderLog = [];
    $auctionLog = [];
    $bidderLog = [];
    $alllog = [];
    $history = [];

    $date = TimeUtil::now();

    $admin = $gameStor->getValues(['year', 'month']);

    $query = "select * from auction where expire<='$date'";
    $result2 = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $count2 = MYDB_num_rows($result2);

    for($i=0; $i < $count2; $i++) {
        $auction = MYDB_fetch_array($result2);

        $josaYi1 = JosaUtil::pick($auction['name1'], '이');
        $josaYi2 = JosaUtil::pick($auction['name2'], '이');

        // 유닉템 처리
        if($auction['stuff'] != 0) {
            if($auction['no2'] == 0) {
                $josaUl = JosaUtil::pick(GetStuffName($auction['stuff']), '을');
                $auctionLog[0] = "<S>◆</>{$admin['year']}년 {$admin['month']}월, {$auction['no']}번 <span class='sell'>판매</span> <M>유찰</> : <Y>{$auction['name1']}</>{$josaYi1} <C>".GetStuffName($auction['stuff'])."</>{$josaUl} 판매, 그러나 입찰자 부재";
                pushAuctionLog($auctionLog);
            } else {
                $trader['no'] = 0;
                $trader['name'] = 'ⓝ암시장상인';
                $trader['gold'] = 99999999;
                $trader['rice'] = 99999999;

                $query = "select no,name,gold,rice from general where no='{$auction['no2']}'";
                $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
                $bidder = MYDB_fetch_array($result);

                $sel1 = $auction['stuff'] % 10;
                $sel2 = intdiv($auction['stuff'], 10);
                switch($sel1) {
                case 1: $type = "weap"; break;
                case 2: $type = "book"; break;
                case 3: $type = "horse"; break;
                case 4: $type = "item"; break;
                }
                // 이미 유닉템이 풀렸는지 검사
                $query = "select no from general where {$type}='$sel2'";
                $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
                $count = MYDB_num_rows($result);

                // 판매거래만 존재
                if($count > 0) {
                    $traderLog[0] = "<S>◆</>{$auction['no']}번 거래 <M>유찰</>! 이미 아이템을 누군가가 가로챘습니다!";
                    $bidderLog[0] = "<S>◆</>{$auction['no']}번 거래 <M>유찰</>! 이미 아이템을 누군가가 가로챘습니다!";
                    $josaUl = JosaUtil::pick(GetStuffName($auction['stuff']), '을');
                    $josaRo = JosaUtil::pick($auction['value'], '로');
                    $auctionLog[0] = "<S>◆</>{$admin['year']}년 {$admin['month']}월, {$auction['no']}번 <span class='sell'>판매</span> <M>유찰</> : <Y>{$auction['name1']}</>{$josaYi1} <C>".GetStuffName($auction['stuff'])."</>$josaUl 판매, <Y>{$auction['name2']}</>{$josaYi2} 금 <C>{$auction['value']}</>{$josaRo} 입찰, 그러나 아이템 이미 매진!";
                } elseif($auction['value'] > $bidder['gold'] - 1000) {
                    $gold = Util::round($auction['value'] * 0.01);
                    $bidder['gold'] -= $gold;
                    if($bidder['gold'] < 0) $bidder['gold'] = 0;
                    $query = "update general set gold='{$bidder['gold']}' where no='{$auction['no2']}'";
                    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

                    $traderLog[0] = "<S>◆</>입찰자의 자금 부족으로 {$auction['no']}번 <C>".GetStuffName($auction['stuff'])."</> 거래 <M>유찰</>!";
                    $bidderLog[0] = "<S>◆</>입찰자의 자금 부족으로 {$auction['no']}번 <C>".GetStuffName($auction['stuff'])."</> 거래 <M>유찰</>! 벌금 <C>{$gold}</>";
                    $josaUl = JosaUtil::pick(GetStuffName($auction['stuff']), '을');
                    $josaRo = JosaUtil::pick($auction['value'], '로');
                    $auctionLog[0] = "<S>◆</>{$admin['year']}년 {$admin['month']}월, {$auction['no']}번 <span class='sell'>판매</span> <M>유찰</> : <Y>{$auction['name1']}</>{$josaYi1} <C>".GetStuffName($auction['stuff'])."</>$josaUl 판매, <Y>{$auction['name2']}</>{$josaYi2} 금 <C>{$auction['value']}</>{$josaRo} 입찰, 그러나 입찰자 자금부족, 벌금 <C>{$gold}</>";
                } else {
                    $josaUl = JosaUtil::pick(GetStuffName($auction['stuff']), '을');
                    $josaUlGold = JosaUtil::pick($auction['value'], '을');
                    $josaRo = JosaUtil::pick($auction['value'], '로');
                    $traderLog[0] = "<S>◆</>{$auction['no']}번 거래 <C>성사</>로 <C>".GetStuffName($auction['stuff'])."</>$josaUl 판매, 금 <C>{$auction['value']}</>{$josaUlGold} 획득!";
                    $bidderLog[0] = "<S>◆</>{$auction['no']}번 거래 <C>성사</>로 금 <C>{$auction['value']}</>{$josaUlGold} 지불, <C>".GetStuffName($auction['stuff'])."</> 구입!";
                    $auctionLog[0] = "<S>◆</>{$admin['year']}년 {$admin['month']}월, {$auction['no']}번 <span class='sell'>판매</span> <C>성사</> : <Y>{$auction['name1']}</>{$josaYi1} <C>".GetStuffName($auction['stuff'])."</>$josaUl 판매, <Y>{$auction['name2']}</>{$josaYi2} 금 <C>{$auction['value']}</>{$josaRo} 구매";
                    $auctionLog[0] .= " <M>★ 아이템 거래 ★</>";

                    $query = "update general set gold=gold-'{$auction['value']}',{$type}='$sel2' where no='{$auction['no2']}'";
                    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

                    $alllog[0] = "<C>●</>{$admin['month']}월:<Y>{$auction['name2']}</>{$josaYi2} <C>".GetStuffName($auction['stuff'])."</>$josaUl 구매했습니다!";
                    $history[0] = "<C>●</>{$admin['year']}년 {$admin['month']}월:<C><b>【암시장】</b></><Y>{$auction['name2']}</>{$josaYi2} <C>".GetStuffName($auction['stuff'])."</>$josaUl 구매했습니다!";
                    pushGeneralPublicRecord($alllog, $admin['year'], $admin['month']);
                    pushWorldHistory($history, $admin['year'], $admin['month']);
                }
                pushGenLog($trader, $traderLog);
                pushGenLog($bidder, $bidderLog);
                pushAuctionLog($auctionLog);
            }
        } else {
            // 쌀 처리
            if($auction['no2'] == 0) {
                // 상인건수가 아닌것만 출력
                if($auction['no1'] != 0) {
                    $query = "select no from general where no='{$auction['no1']}'";
                    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
                    $trader = MYDB_fetch_array($result);

                    $traderLog[0] = "<S>◆</>입찰자 부재로 {$auction['no']}번 거래 <M>유찰</>!";
                    if($auction['type'] == 0) {
                        $josaUlRice = JosaUtil::pick($auction['amount'], '을');
                        $auctionLog[0] = "<S>◆</>{$admin['year']}년 {$admin['month']}월, {$auction['no']}번 <span class='sell'>판매</span> <M>유찰</> : <Y>{$auction['name1']}</>{$josaYi1} 쌀 <C>{$auction['amount']}</>{$josaUlRice} 판매, 그러나 입찰자 부재";
                    } else {
                        $josaUlRice = JosaUtil::pick($auction['amount'], '을');
                        $auctionLog[0] = "<S>◆</>{$admin['year']}년 {$admin['month']}월, {$auction['no']}번 <S>구매</> <M>유찰</> : <Y>{$auction['name1']}</>{$josaYi1} 쌀 <C>{$auction['amount']}</>{$josaUlRice} 구매, 그러나 입찰자 부재";
                    }
                    pushGenLog($trader, $traderLog);
                    pushAuctionLog($auctionLog);
                }
            } else {
                if($auction['no1'] == 0) {
                    $trader['no'] = 0;
                    $trader['name'] = 'ⓝ상인';
                    $trader['gold'] = 99999;
                    $trader['rice'] = 99999;
                } else {
                    $query = "select no,name,gold,rice from general where no='{$auction['no1']}'";
                    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
                    $trader = MYDB_fetch_array($result);
                }

                $query = "select no,name,gold,rice from general where no='{$auction['no2']}'";
                $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
                $bidder = MYDB_fetch_array($result);
                //판매거래
                if($auction['type'] == 0) {
                    if($auction['amount'] > $trader['rice'] - 1000) {
                        $gold = Util::round($auction['value'] * 0.01);
                        $trader['gold'] -= $gold;
                        if($trader['gold'] < 0) $trader['gold'] = 0;
                        $query = "update general set gold='{$trader['gold']}' where no='{$auction['no1']}'";
                        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

                        $josaUlRice = JosaUtil::pick($auction['amount'], '을');
                        $josaRo = JosaUtil::pick($auction['value'], '로');
                        $traderLog[0] = "<S>◆</>판매자의 군량 부족으로 {$auction['no']}번 거래 <M>유찰</>! 벌금 <C>{$gold}</>";
                        $bidderLog[0] = "<S>◆</>판매자의 군량 부족으로 {$auction['no']}번 거래 <M>유찰</>!";
                        $auctionLog[0] = "<S>◆</>{$admin['year']}년 {$admin['month']}월, {$auction['no']}번 <span class='sell'>판매</span> <M>유찰</> : <Y>{$auction['name1']}</>{$josaYi1} 쌀 <C>{$auction['amount']}</>{$josaUlRice} 판매, <Y>{$auction['name2']}</>{$josaYi2} 금 <C>{$auction['value']}</>{$josaRo} 입찰, 그러나 판매자 군량부족, 벌금 <C>{$gold}</>";
                    } elseif($auction['value'] > $bidder['gold'] - 1000) {
                        $gold = Util::round($auction['value'] * 0.01);
                        $bidder['gold'] -= $gold;
                        if($bidder['gold'] < 0) $bidder['gold'] = 0;
                        $query = "update general set gold='{$bidder['gold']}' where no='{$auction['no2']}'";
                        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

                        $josaUlRice = JosaUtil::pick($auction['amount'], '을');
                        $josaRo = JosaUtil::pick($auction['value'], '로');
                        $traderLog[0] = "<S>◆</>입찰자의 자금 부족으로 {$auction['no']}번 거래 <M>유찰</>!";
                        $bidderLog[0] = "<S>◆</>입찰자의 자금 부족으로 {$auction['no']}번 거래 <M>유찰</>! 벌금 <C>{$gold}</>";
                        $auctionLog[0] = "<S>◆</>{$admin['year']}년 {$admin['month']}월, {$auction['no']}번 <span class='sell'>판매</span> <M>유찰</> : <Y>{$auction['name1']}</>{$josaYi1} 쌀 <C>{$auction['amount']}</>{$josaUlRice} 판매, <Y>{$auction['name2']}</>{$josaYi2} 금 <C>{$auction['value']}</>{$josaRo} 입찰, 그러나 입찰자 자금부족, 벌금 <C>{$gold}</>";
                    } else {
                        $josaUlGold = JosaUtil::pick($auction['value'], '을');
                        $josaUlRice = JosaUtil::pick($auction['amount'], '을');
                        $josaRo = JosaUtil::pick($auction['value'], '로');
                        $traderLog[0] = "<S>◆</>{$auction['no']}번 거래 <C>성사</>로 쌀 <C>{$auction['amount']}</>{$josaUlRice} 판매, 금 <C>{$auction['value']}</>{$josaUlGold} 획득!";
                        $bidderLog[0] = "<S>◆</>{$auction['no']}번 거래 <C>성사</>로 금 <C>{$auction['value']}</>{$josaUlGold} 지불, 쌀 <C>{$auction['amount']}</>{$josaUlRice} 구입!";
                        $auctionLog[0] = "<S>◆</>{$admin['year']}년 {$admin['month']}월, {$auction['no']}번 <span class='sell'>판매</span> <C>성사</> : <Y>{$auction['name1']}</>{$josaYi1} 쌀 <C>{$auction['amount']}</>{$josaUlRice} 판매, <Y>{$auction['name2']}</>{$josaYi2} 금 <C>{$auction['value']}</>{$josaRo} 구매";
                        if($auction['value'] >= $auction['amount'] * 2) {
                            $auctionLog[0] .= " <R>★ 최고가 거래 ★</>";
                        } elseif($auction['value'] >= $auction['topv']) {
                            $auctionLog[0] .= " <M>★ 즉시구매가 거래 ★</>";
                        } elseif($auction['value'] * 2 <= $auction['amount']) {
                            $auctionLog[0] .= " <R>★ 최저가 거래 ★</>";
                        }
                        $query = "update general set gold=gold+'{$auction['value']}',rice=rice-'{$auction['amount']}' where no='{$auction['no1']}'";
                        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
                        $query = "update general set gold=gold-'{$auction['value']}',rice=rice+'{$auction['amount']}' where no='{$auction['no2']}'";
                        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
                    }
                    pushGenLog($trader, $traderLog);
                    pushGenLog($bidder, $bidderLog);
                    pushAuctionLog($auctionLog);
                //구매거래
                } else {
                    if($auction['amount'] > $bidder['rice'] - 1000) {
                        $gold = Util::round($auction['value'] * 0.01);
                        $bidder['gold'] -= $gold;
                        if($bidder['gold'] < 0) $bidder['gold'] = 0;
                        $query = "update general set gold='{$bidder['gold']}' where no='{$auction['no2']}'";
                        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

                        $josaUlRice = JosaUtil::pick($auction['amount'], '을');
                        $josaRo = JosaUtil::pick($auction['value'], '로');
                        $traderLog[0] = "<S>◆</>입찰자의 군량 부족으로 {$auction['no']}번 거래 <M>유찰</>!";
                        $bidderLog[0] = "<S>◆</>입찰자의 군량 부족으로 {$auction['no']}번 거래 <M>유찰</>! 벌금 <C>{$gold}</>";
                        $auctionLog[0] = "<S>◆</>{$admin['year']}년 {$admin['month']}월, {$auction['no']}번 <S>구매</> <M>유찰</> : <Y>{$auction['name1']}</>{$josaYi1} 쌀 <C>{$auction['amount']}</>{$josaUlRice} 구매, <Y>{$auction['name2']}</>{$josaYi2} 금 <C>{$auction['value']}</>{$josaRo} 입찰, 그러나 입찰자 군량부족, 벌금 <C>{$gold}</>";
                    } elseif($auction['value'] > $trader['gold'] - 1000) {
                        $gold = Util::round($auction['value'] * 0.01);
                        $trader['gold'] -= $gold;
                        if($trader['gold'] < 0) $trader['gold'] = 0;
                        $query = "update general set gold='{$trader['gold']}' where no='{$auction['no1']}'";
                        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

                        $josaUlRice = JosaUtil::pick($auction['amount'], '을');
                        $josaRo = JosaUtil::pick($auction['value'], '로');
                        $traderLog[0] = "<S>◆</>구매자의 자금 부족으로 {$auction['no']}번 거래 <M>유찰</>! 벌금 <C>{$gold}</>";
                        $bidderLog[0] = "<S>◆</>구매자의 자금 부족으로 {$auction['no']}번 거래 <M>유찰</>!";
                        $auctionLog[0] = "<S>◆</>{$admin['year']}년 {$admin['month']}월, {$auction['no']}번 <S>구매</> <M>유찰</> : <Y>{$auction['name1']}</>{$josaYi1} 쌀 <C>{$auction['amount']}</>{$josaUlRice} 구매, <Y>{$auction['name2']}</>{$josaYi2} 금 <C>{$auction['value']}</>{$josaRo} 입찰, 그러나 구매자 자금부족, 벌금 <C>{$gold}</>";
                    } else {
                        $josaUlGold = JosaUtil::pick($auction['value'], '을');
                        $josaUlRice = JosaUtil::pick($auction['amount'], '을');
                        $josaRo = JosaUtil::pick($auction['value'], '로');
                        $traderLog[0] = "<S>◆</>{$auction['no']}번 거래 <C>성사</>로 금 <C>{$auction['value']}</>{$josaUlGold} 지불, 쌀 <C>{$auction['amount']}</>{$josaUlRice} 구입!";
                        $bidderLog[0] = "<S>◆</>{$auction['no']}번 거래 <C>성사</>로 쌀 <C>{$auction['amount']}</>{$josaUlRice} 판매, 금 <C>{$auction['value']}</>{$josaUlGold} 획득!";
                        $auctionLog[0] = "<S>◆</>{$admin['year']}년 {$admin['month']}월, {$auction['no']}번 <S>구매</> <C>성사</> : <Y>{$auction['name1']}</>{$josaYi1} 쌀 <C>{$auction['amount']}</>{$josaUlRice} 구매, <Y>{$auction['name2']}</>{$josaYi2} 금 <C>{$auction['value']}</>{$josaRo} 판매";
                        if($auction['value'] >= $auction['amount'] * 2) {
                            $auctionLog[0] .= " <R>★ 최고가 거래 ★</>";
                        } elseif($auction['value'] * 2 <= $auction['amount']) {
                            $auctionLog[0] .= " <R>★ 최저가 거래 ★</>";
                        } elseif($auction['value'] <= $auction['topv']) {
                            $auctionLog[0] .= " <M>★ 즉시구매가 거래 ★</>";
                        }

                        $query = "update general set gold=gold-'{$auction['value']}',rice=rice+'{$auction['amount']}' where no='{$auction['no1']}'";
                        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
                        $query = "update general set gold=gold+'{$auction['value']}',rice=rice-'{$auction['amount']}' where no='{$auction['no2']}'";
                        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
                    }
                    pushGenLog($trader, $traderLog);
                    pushGenLog($bidder, $bidderLog);
                    pushAuctionLog($auctionLog);
                }
            }
        }
    }

    $query = "delete from auction where expire<='$date'";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
}

