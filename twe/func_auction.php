<?php

function GetStuffName($stuff) {
    $type1 = $stuff % 10;
    $type2 = floor($stuff / 10);

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

function registerAuction($connect) {
    $query = "select startyear,year,month,turnterm from game where no=1";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $admin = MYDB_fetch_array($result);

    switch($admin['turnterm']) {
    case 0: $unit = 7200; break;
    case 1: $unit = 3600; break;
    case 2: $unit = 1800; break;
    case 3: $unit = 1200; break;
    case 4: $unit = 600; break;
    case 5: $unit = 300; break;
    case 6: $unit = 120; break;
    case 7: $unit = 60; break;
    }

    // 장수들 평금,평쌀
    $query = "select avg(gold) as gold, avg(rice) as rice,max(gold) as maxgold from general where npc<2";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $general = MYDB_fetch_array($result);

    if($general['gold'] <  1000) { $general['gold'] =  1000; }
    if($general['gold'] > 20000) { $general['gold'] = 20000; }
    if($general['rice'] <  1000) { $general['rice'] =  1000; }
    if($general['rice'] > 20000) { $general['rice'] = 20000; }

/*
    // 유닉템 등록
    $query = "select * from auction where stuff!='0'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $count = MYDB_num_rows($result);

    // 유닉템 거래가 없을 경우 1% 확률로 등장
    if($count == 0 && rand()%100 < 1) {
        // 유닉템 선택
        $sel = rand() % 4 + 1;
        switch($sel) {
        case 1: $type = "weap"; break;
        case 2: $type = "book"; break;
        case 3: $type = "horse"; break;
        case 4: $type = "item"; break;
        }
        $query = "select no,{$type} from general where {$type}>6";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $count = MYDB_num_rows($result);
        if($count < 20) {
            for($i=0; $i < $count; $i++) {
                $gen = MYDB_fetch_array($result);
                $occupied[$gen[$type]] = 1;
            }
            for($i=7; $i <= 26; $i++) {
                if($occupied[$i] == 0) {
                    $item[count($item)] = $i;
                }
            }
            $it = $item[rand() % count($item)];
            $stuff = $it * 10 + $sel;

            //평균 금의 100%
            $amount = 1;
            $cost = $general['gold'];
            $topv = $general['maxgold'] * 10;
            if($cost < 5000)  { $cost = 5000; }
            if($topv < 10000) { $topv = 10000; }

            $cost = round($cost / 10) * 10;
            $topv = round($topv / 10) * 10;

            $term = 12;
            $date = date("Y-m-d H:i:s", strtotime(date("Y-m-d H:i:s")) + $unit * $term);
            $query = "insert into auction (type, no1, name1, stuff, amount, cost, value, topv, expire) values (0, '0', 'ⓝ암시장상인', '$stuff', '$amount', '$cost', '$cost', '$topv', '$date')";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

            $alllog[0] = "<C>●</>{$admin['month']}월:<C>".GetStuffName($stuff)."</>(이)가 거래장에 등장했습니다!";
            $history[0] = "<C>●</>{$admin['year']}년 {$admin['month']}월:<C><b>【암시장】</b></><Y>ⓝ암시장상인</>(이)가 <C>".GetStuffName($stuff)."</>(을)를 판다는 소문이 돌고 있습니다!";
            pushAllLog($alllog);
            pushHistory($connect, $history);
        }
    }
*/

    $query = "select * from auction where type='0' and stuff='0' and no1='0'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $count = MYDB_num_rows($result);
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

        $amount = round($amount / 10) * 10;
        $cost = round($cost / 10) * 10;
        $topv = round($topv / 10) * 10;

        $term = 3 + rand() % 10;
        $date = date("Y-m-d H:i:s", strtotime(date("Y-m-d H:i:s")) + $unit * $term);
        $query = "insert into auction (type, no1, name1, stuff, amount, cost, value, topv, expire) values (0, '0', 'ⓝ상인', '0', '$amount', '$cost', '$cost', '$topv', '$date')";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    }

    $query = "select * from auction where type='1' and stuff='0' and no1='0'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $count = MYDB_num_rows($result);
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

        $amount = round($amount / 10) * 10;
        $cost = round($cost / 10) * 10;
        $topv = round($topv / 10) * 10;

        $term = 3 + rand() % 10;
        $date = date("Y-m-d H:i:s", strtotime(date("Y-m-d H:i:s")) + $unit * $term);
        $query = "insert into auction (type, no1, name1, stuff, amount, cost, value, topv, expire) values (1, '0', 'ⓝ상인', '0', '$amount', '$cost', '$cost', '$topv', '$date')";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    }
}

function processAuction($connect) {
    $date = date("Y-m-d H:i:s");

    $query = "select year,month from game where no=1";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $admin = MYDB_fetch_array($result);

    $query = "select * from auction where expire<='$date'";
    $result2 = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $count2 = MYDB_num_rows($result2);

    for($i=0; $i < $count2; $i++) {
        $auction = MYDB_fetch_array($result2);

        // 유닉템 처리
        if($auction['stuff'] != 0) {
            if($auction[no2] == 0) {
                $auctionLog[0] = "<S>◆</>{$admin['year']}년 {$admin['month']}월, {$auction['no']}번 <O>판매</> <M>유찰</> : <Y>{$auction[name1]}</>(이)가 <C>".GetStuffName($auction['stuff'])."</>(을)를 판매, 그러나 입찰자 부재";
                pushAuctionLog($connect, $auctionLog);
            } else {
                $trader['no'] = 0;
                $trader['name'] = 'ⓝ암시장상인';
                $trader['gold'] = 99999999;
                $trader['rice'] = 99999999;

                $query = "select no,name,gold,rice from general where no='$auction[no2]'";
                $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
                $bidder = MYDB_fetch_array($result);

                $sel1 = $auction['stuff'] % 10;
                $sel2 = floor($auction['stuff'] / 10);
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
                    $auctionLog[0] = "<S>◆</>{$admin['year']}년 {$admin['month']}월, {$auction['no']}번 <O>판매</> <M>유찰</> : <Y>{$auction[name1]}</>(이)가 <C>".GetStuffName($auction['stuff'])."</>(을)를 판매, <Y>{$auction[name2]}</>(이)가 금 <C>{$auction['value']}</>(으)로 입찰, 그러나 아이템 이미 매진!";
                } elseif($auction['value'] > $bidder['gold'] - 1000) {
                    $gold = round($auction['value'] * 0.01);
                    $bidder['gold'] -= $gold;
                    if($bidder['gold'] < 0) $bidder['gold'] = 0;
                    $query = "update general set gold='{$bidder['gold']}' where no='$auction[no2]'";
                    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

                    $traderLog[0] = "<S>◆</>입찰자의 자금 부족으로 {$auction['no']}번 <C>".GetStuffName($auction['stuff'])."</> 거래 <M>유찰</>!";
                    $bidderLog[0] = "<S>◆</>입찰자의 자금 부족으로 {$auction['no']}번 <C>".GetStuffName($auction['stuff'])."</> 거래 <M>유찰</>! 벌금 <C>{$gold}</>";
                    $auctionLog[0] = "<S>◆</>{$admin['year']}년 {$admin['month']}월, {$auction['no']}번 <O>판매</> <M>유찰</> : <Y>{$auction[name1]}</>(이)가 <C>".GetStuffName($auction['stuff'])."</>(을)를 판매, <Y>{$auction[name2]}</>(이)가 금 <C>{$auction['value']}</>(으)로 입찰, 그러나 입찰자 자금부족, 벌금 <C>{$gold}</>";
                } else {
                    $traderLog[0] = "<S>◆</>{$auction['no']}번 거래 <C>성사</>로 <C>".GetStuffName($auction['stuff'])."</>(을)를 판매, 금 <C>{$auction['value']}</>(을)를 획득!";
                    $bidderLog[0] = "<S>◆</>{$auction['no']}번 거래 <C>성사</>로 금 <C>{$auction['value']}</>(을)를 지불, <C>".GetStuffName($auction['stuff'])."</> 구입!";
                    $auctionLog[0] = "<S>◆</>{$admin['year']}년 {$admin['month']}월, {$auction['no']}번 <O>판매</> <C>성사</> : <Y>{$auction[name1]}</>(이)가 <C>".GetStuffName($auction['stuff'])."</>(을)를 판매, <Y>{$auction[name2]}</>(이)가 금 <C>{$auction['value']}</>(으)로 구매";
                    $auctionLog[0] .= " <M>★ 아이템 거래 ★</>";

                    $query = "update general set gold=gold-'{$auction['value']}',{$type}='$sel2' where no='$auction[no2]'";
                    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

                    $alllog[0] = "<C>●</>{$admin['month']}월:<Y>$auction[name2]</>(이)가 <C>".GetStuffName($auction['stuff'])."</>(을)를 구매했습니다!";
                    $history[0] = "<C>●</>{$admin['year']}년 {$admin['month']}월:<C><b>【암시장】</b></><Y>$auction[name2]</>(이)가 <C>".GetStuffName($auction['stuff'])."</>(을)를 구매했습니다!";
                    pushAllLog($alllog);
                    pushHistory($connect, $history);
                }
                pushGenLog($trader, $traderLog);
                pushGenLog($bidder, $bidderLog);
                pushAuctionLog($connect, $auctionLog);
            }
        } else {
            // 쌀 처리
            if($auction['no2'] == 0) {
                // 상인건수가 아닌것만 출력
                if($auction['no1'] != 0) {
                    $query = "select no from general where no='$auction[no1]'";
                    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
                    $trader = MYDB_fetch_array($result);

                    $traderLog[0] = "<S>◆</>입찰자 부재로 {$auction['no']}번 거래 <M>유찰</>!";
                    if($auction['type'] == 0) {
                        $auctionLog[0] = "<S>◆</>{$admin['year']}년 {$admin['month']}월, {$auction['no']}번 <O>판매</> <M>유찰</> : <Y>{$auction[name1]}</>(이)가 쌀 <C>{$auction['amount']}</>(을)를 판매, 그러나 입찰자 부재";
                    } else {
                        $auctionLog[0] = "<S>◆</>{$admin['year']}년 {$admin['month']}월, {$auction['no']}번 <S>구매</> <M>유찰</> : <Y>{$auction[name1]}</>(이)가 쌀 <C>{$auction['amount']}</>(을)를 구매, 그러나 입찰자 부재";
                    }
                    pushGenLog($trader, $traderLog);
                    pushAuctionLog($connect, $auctionLog);
                }
            } else {
                if($auction[no1] == 0) {
                    $trader['no'] = 0;
                    $trader['name'] = 'ⓝ상인';
                    $trader['gold'] = 99999;
                    $trader['rice'] = 99999;
                } else {
                    $query = "select no,name,gold,rice from general where no='$auction[no1]'";
                    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
                    $trader = MYDB_fetch_array($result);
                }

                $query = "select no,name,gold,rice from general where no='$auction[no2]'";
                $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
                $bidder = MYDB_fetch_array($result);
                //판매거래
                if($auction['type'] == 0) {
                    if($auction['amount'] > $trader['rice'] - 1000) {
                        $gold = round($auction['value'] * 0.01);
                        $trader['gold'] -= $gold;
                        if($trader['gold'] < 0) $trader['gold'] = 0;
                        $query = "update general set gold='{$trader['gold']}' where no='$auction[no1]'";
                        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

                        $traderLog[0] = "<S>◆</>판매자의 군량 부족으로 {$auction['no']}번 거래 <M>유찰</>! 벌금 <C>{$gold}</>";
                        $bidderLog[0] = "<S>◆</>판매자의 군량 부족으로 {$auction['no']}번 거래 <M>유찰</>!";
                        $auctionLog[0] = "<S>◆</>{$admin['year']}년 {$admin['month']}월, {$auction['no']}번 <O>판매</> <M>유찰</> : <Y>{$auction[name1]}</>(이)가 쌀 <C>{$auction['amount']}</>(을)를 판매, <Y>{$auction[name2]}</>(이)가 금 <C>{$auction['value']}</>(으)로 입찰, 그러나 판매자 군량부족, 벌금 <C>{$gold}</>";
                    } elseif($auction['value'] > $bidder['gold'] - 1000) {
                        $gold = round($auction['value'] * 0.01);
                        $bidder['gold'] -= $gold;
                        if($bidder['gold'] < 0) $bidder['gold'] = 0;
                        $query = "update general set gold='{$bidder['gold']}' where no='$auction[no2]'";
                        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

                        $traderLog[0] = "<S>◆</>입찰자의 자금 부족으로 {$auction['no']}번 거래 <M>유찰</>!";
                        $bidderLog[0] = "<S>◆</>입찰자의 자금 부족으로 {$auction['no']}번 거래 <M>유찰</>! 벌금 <C>{$gold}</>";
                        $auctionLog[0] = "<S>◆</>{$admin['year']}년 {$admin['month']}월, {$auction['no']}번 <O>판매</> <M>유찰</> : <Y>{$auction[name1]}</>(이)가 쌀 <C>{$auction['amount']}</>(을)를 판매, <Y>{$auction[name2]}</>(이)가 금 <C>{$auction['value']}</>(으)로 입찰, 그러나 입찰자 자금부족, 벌금 <C>{$gold}</>";
                    } else {
                        $traderLog[0] = "<S>◆</>{$auction['no']}번 거래 <C>성사</>로 쌀 <C>{$auction['amount']}</>(을)를 판매, 금 <C>{$auction['value']}</>(을)를 획득!";
                        $bidderLog[0] = "<S>◆</>{$auction['no']}번 거래 <C>성사</>로 금 <C>{$auction['value']}</>(을)를 지불, 쌀 <C>{$auction['amount']}</>(을)를 구입!";
                        $auctionLog[0] = "<S>◆</>{$admin['year']}년 {$admin['month']}월, {$auction['no']}번 <O>판매</> <C>성사</> : <Y>{$auction[name1]}</>(이)가 쌀 <C>{$auction['amount']}</>(을)를 판매, <Y>{$auction[name2]}</>(이)가 금 <C>{$auction['value']}</>(으)로 구매";
                        if($auction['value'] >= $auction['amount'] * 2) {
                            $auctionLog[0] .= " <R>★ 최고가 거래 ★</>";
                        } elseif($auction['value'] >= $auction['topv']) {
                            $auctionLog[0] .= " <M>★ 즉시구매가 거래 ★</>";
                        } elseif($auction['value'] * 2 <= $auction['amount']) {
                            $auctionLog[0] .= " <R>★ 최저가 거래 ★</>";
                        }
                        $query = "update general set gold=gold+'{$auction['value']}',rice=rice-'{$auction['amount']}' where no='$auction[no1]'";
                        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
                        $query = "update general set gold=gold-'{$auction['value']}',rice=rice+'{$auction['amount']}' where no='$auction[no2]'";
                        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
                    }
                    pushGenLog($trader, $traderLog);
                    pushGenLog($bidder, $bidderLog);
                    pushAuctionLog($connect, $auctionLog);
                //구매거래
                } else {
                    if($auction['amount'] > $bidder['rice'] - 1000) {
                        $gold = round($auction['value'] * 0.01);
                        $bidder['gold'] -= $gold;
                        if($bidder['gold'] < 0) $bidder['gold'] = 0;
                        $query = "update general set gold='{$bidder['gold']}' where no='$auction[no2]'";
                        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

                        $traderLog[0] = "<S>◆</>입찰자의 군량 부족으로 {$auction['no']}번 거래 <M>유찰</>!";
                        $bidderLog[0] = "<S>◆</>입찰자의 군량 부족으로 {$auction['no']}번 거래 <M>유찰</>! 벌금 <C>{$gold}</>";
                        $auctionLog[0] = "<S>◆</>{$admin['year']}년 {$admin['month']}월, {$auction['no']}번 <S>구매</> <M>유찰</> : <Y>{$auction[name1]}</>(이)가 쌀 <C>{$auction['amount']}</>(을)를 구매, <Y>{$auction[name2]}</>(이)가 금 <C>{$auction['value']}</>(으)로 입찰, 그러나 입찰자 군량부족, 벌금 <C>{$gold}</>";
                    } elseif($auction['value'] > $trader['gold'] - 1000) {
                        $gold = round($auction['value'] * 0.01);
                        $trader['gold'] -= $gold;
                        if($trader['gold'] < 0) $trader['gold'] = 0;
                        $query = "update general set gold='{$trader['gold']}' where no='$auction[no1]'";
                        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

                        $traderLog[0] = "<S>◆</>구매자의 자금 부족으로 {$auction['no']}번 거래 <M>유찰</>! 벌금 <C>{$gold}</>";
                        $bidderLog[0] = "<S>◆</>구매자의 자금 부족으로 {$auction['no']}번 거래 <M>유찰</>!";
                        $auctionLog[0] = "<S>◆</>{$admin['year']}년 {$admin['month']}월, {$auction['no']}번 <S>구매</> <M>유찰</> : <Y>{$auction[name1]}</>(이)가 쌀 <C>{$auction['amount']}</>(을)를 구매, <Y>{$auction[name2]}</>(이)가 금 <C>{$auction['value']}</>(으)로 입찰, 그러나 구매자 자금부족, 벌금 <C>{$gold}</>";
                    } else {
                        $traderLog[0] = "<S>◆</>{$auction['no']}번 거래 <C>성사</>로 금 <C>{$auction['value']}</>(을)를 지불, 쌀 <C>{$auction['amount']}</>(을)를 구입!";
                        $bidderLog[0] = "<S>◆</>{$auction['no']}번 거래 <C>성사</>로 쌀 <C>{$auction['amount']}</>(을)를 판매, 금 <C>{$auction['value']}</>(을)를 획득!";
                        $auctionLog[0] = "<S>◆</>{$admin['year']}년 {$admin['month']}월, {$auction['no']}번 <S>구매</> <C>성사</> : <Y>{$auction[name1]}</>(이)가 쌀 <C>{$auction['amount']}</>(을)를 구매, <Y>{$auction[name2]}</>(이)가 금 <C>{$auction['value']}</>(으)로 판매";
                        if($auction['value'] >= $auction['amount'] * 2) {
                            $auctionLog[0] .= " <R>★ 최고가 거래 ★</>";
                        } elseif($auction['value'] * 2 <= $auction['amount']) {
                            $auctionLog[0] .= " <R>★ 최저가 거래 ★</>";
                        } elseif($auction['value'] <= $auction['topv']) {
                            $auctionLog[0] .= " <M>★ 즉시구매가 거래 ★</>";
                        }

                        $query = "update general set gold=gold-'{$auction['value']}',rice=rice+'{$auction['amount']}' where no='$auction[no1]'";
                        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
                        $query = "update general set gold=gold+'{$auction['value']}',rice=rice-'{$auction['amount']}' where no='$auction[no2]'";
                        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
                    }
                    pushGenLog($trader, $traderLog);
                    pushGenLog($bidder, $bidderLog);
                    pushAuctionLog($connect, $auctionLog);
                }
            }
        }
    }

    $query = "delete from auction where expire<='$date'";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
}

