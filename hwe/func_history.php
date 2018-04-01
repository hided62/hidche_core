<?php
namespace sammo;



function pushTrickLog($log) {
    $size = count($log);
    if($size > 0) {
        $fp = fopen("logs/_tricklog.txt", "a");
        for($i=0; $i < $size; $i++) {
            fwrite($fp, $log[$i]."\n");
        }
        fclose($fp);
    }
}

function pushProcessLog($log) {
    $size = count($log);
    if($size > 0) {
        $date = date('Y_m_d');
        $fp = fopen("logs/_{$date}_processlog.txt", "a");
        for($i=0; $i < $size; $i++) {
            fwrite($fp, $log[$i]."\n");
        }
        fclose($fp);
    }
}

function delStepLog() {
    $date = date('Y_m_d');
    @unlink("logs/_{$date}_steplog.txt");
}

function pushStepLog($log) {
    $date = date('Y_m_d');
    $fp = fopen("logs/_{$date}_steplog.txt", "a");
    fwrite($fp, $log."\n");
    fclose($fp);
}

function pushLockLog($log) {
    $size = count($log);
    if($size > 0) {
        $date = date('Y_m_d');
        $fp = fopen("logs/_{$date}_locklog.txt", "a");
        for($i=0; $i < $size; $i++) {
            fwrite($fp, $log[$i]."\n");
        }
        fclose($fp);
    }
}

function pushAdminLog($log) {
    $size = count($log);
    if($size > 0) {
        $fp = fopen("logs/_adminlog.txt", "a");
        for($i=0; $i < $size; $i++) {
            fwrite($fp, $log[$i]."\n");
        }
        fclose($fp);
    }
}

function pushAuctionLog($log) {
    $size = count($log);
    if($size > 0) {
        $fp = fopen("logs/_auctionlog.txt", "a");
        for($i=0; $i < $size; $i++) {
            fwrite($fp, $log[$i]."\n");
        }
        fclose($fp);
    }
}

function pushGenLog($general, $log) {
    $size = count($log);
    if($size > 0) {
        $fp = fopen("logs/gen{$general['no']}.txt", "a");
        for($i=0; $i < $size; $i++) {
            fwrite($fp, $log[$i]."\n");
        }
        fclose($fp);
    }
}

function pushBatRes($general, $log) {
    $size = count($log);
    if($size > 0) {
        $fp = fopen("logs/batres{$general['no']}.txt", "a");
        for($i=0; $i < $size; $i++) {
            fwrite($fp, $log[$i]."\n");
        }
        fclose($fp);
    }
}

function pushBatLog($general, $log) {
    $size = count($log);
    if($size > 0) {
        $fp = fopen("logs/batlog{$general['no']}.txt", "a");
        for($i=0; $i < $size; $i++) {
            fwrite($fp, $log[$i]."\n");
        }
        fclose($fp);
    }
}



function pushWorldHistory(array $history, $year=null, $month=null) {
    $db = DB::db();
    if($year === null || $month === null){
        $game = $db->queryFirstRow('SELECT year, month FROM game LIMIT 1');
        $year = $game['year'];
        $month = $game['month'];
    }
    $request = array_map(function($text) use ($year, $month) {
        return ['year'=>$year, 'month'=>$month, 'text'=>$text];
    }, array_values($history));
    $db->insert('world_history', $request);
}

function getWorldHistoryRecent(int $count) {
    $db = DB::db();

    $texts = [];
    foreach($db->queryFirstColumn('SELECT `text` from world_history order by id desc limit %i', $count) as $text){
        $texts[] = ConvertLog($text);
    }
    return join('<br>', $texts);
}

function getWorldHistoryWithDate($year, $month) {
    $db = DB::db();

    $texts = [];
    foreach(
        $db->queryFirstColumn(
            'SELECT `text` from world_history where year = %i and month = %i order by id desc', 
            $year, 
            $month
        ) as $text
    ){
        $texts[] = ConvertLog($text);
    }

    if(!$texts){
        return ConvertLog("<C>●</>{$year}년 {$month}월: 기록 없음");
    }

    return join('<br>', $texts);
}


function pushGeneralPublicRecord(array $history, $year=null, $month=null) {
    $db = DB::db();
    if($year === null || $month === null){
        $game = $db->queryFirstRow('SELECT year, month FROM game LIMIT 1');
        $year = $game['year'];
        $month = $game['month'];
    }
    $request = array_map(function($text) use ($year, $month) {
        return ['year'=>$year, 'month'=>$month, 'text'=>$text];
    }, array_values($history));
    $db->insert('general_public_record', $request);
}

function getGeneralPublicRecordRecent($count) {
    $db = DB::db();

    $texts = [];
    foreach($db->queryFirstColumn('SELECT `text` from general_public_record order by id desc limit %i', $count) as $text){
        $texts[] = ConvertLog($text);
    }
    return join('<br>', $texts);
}

function getGeneralPublicRecordWithDate($year, $month) {
    $db = DB::db();

    $texts = [];
    foreach(
        $db->queryFirstColumn(
            'SELECT `text` from general_public_record where year = %i and month = %i order by id desc', 
            $year, 
            $month
        ) as $text
    ){
        $texts[] = ConvertLog($text);
    }

    if(!$texts){
        return ConvertLog("<C>●</>{$month}월: 기록 없음");
    }
}

function LogHistory($isFirst=0) {
    if(STEP_LOG) pushStepLog(date('Y-m-d H:i:s').', LogHistory Start');

    $db = DB::db();
    $obj = $db->queryFirstRow('SELECT year, month, startyear FROM game limit 1');

    //TODO: 새롭게 추가할 지도 값 받아오는 함수를 이용하여 재구성
    $map = getWorldMap([
        'year'=>null,
        'month'=>null,
        'neutralView'=>true,
        'showMe'=>false,
        'aux'=>[]
    ]);


    $map['month'] = $obj['month'];
    $map['year'] = $obj['year'];
    $map['startYear'] = $obj['startyear'];
    if($isFirst == 1){
        $map['month'] -= 1;
        if($map['month'] == 0){
            $map['month'] = 12;
            $map['year'] -= 1;
        }       
    }

    $startYear = $obj['startyear'];
    $year = $map['year'];
    $month = $map['month'];

    $map_json = Json::encode($map);
    
    $log = getWorldHistoryWithDate($year, $month);
    $genlog = getGeneralPublicRecordWithDate($year, $month);

    $nationStr = "";
    $powerStr = "";
    $genStr = "";
    $cityStr = "";

    
    foreach($db->query('select nation,color,name,power,gennum from nation where level>0 order by power desc') as $nation){
        $cityCount = $db->queryFirstField('select count(*) from city where nation = %i',$nation['nation']);

        $nationStr .= "<font color=cyan>◆</font> <font style=color:".newColor($nation['color']).";background-color:{$nation['color']};>{$nation['name']}</font><br>";
        $powerStr .= "국력 {$nation['power']}<br>";
        $genStr .= "장수 {$nation['gennum']}<br>";
        $cityStr .= "속령 $citycount<br>";
    }

    if(STEP_LOG) pushStepLog(date('Y-m-d H:i:s').', contents collected');
    
    $db->insert('history', [
        'year' => $year,
        'month' => $month,
        'map' => $map_json,
        'log' => $log,
        'genlog' => $genlog,
        'nation' => $nationStr,
        'power' => $powerStr,
        'gen' => $genStr,
        'city' => $cityStr
    ]);

    if(STEP_LOG) pushStepLog(date('Y-m-d H:i:s').', LogHistory Finish');
    return true;
}
