<?php
namespace sammo;

require(__dir__.'/../vendor/autoload.php');



function getHistory($count, $year, $month, $isFirst=0) {
    $fp = @fopen("logs/_history.txt", "r");
    @fseek($fp, -$count*300, SEEK_END);
    $file = @fread($fp, $count*300);
    @fclose($fp);
    $log = explode("\r\n",$file);

    $str = "";
    $prefix = "</>{$year}년 {$month}월:";
    for($i=0; $i < $count; $i++) {
        $line = $log[count($log)-2-$i];
        if($line == "") {
            continue;
        }
        
        if(strpos($line, $prefix) || $isFirst == 1) {
            $str = ConvertLog($line).'<br>'.$str;
        } else {
            break;
        }
    }
    if($str == "") {
        $str = "<C>●</>{$year}년 {$month}월: 기록 없음";
        $str = ConvertLog($str);
    }
    return $str;
}

function getGenHistory($count, $year, $month, $isFirst=0) {
    $fp = @fopen("logs/_alllog.txt", "r");
    @fseek($fp, -$count*300, SEEK_END);
    $file = @fread($fp, $count*300);
    @fclose($fp);
    $log = explode("\n",$file);

    $str = "";
    $prefix = "</>{$month}월:";
    $year -= 1;
    $prefixYear = "</>{$month}월:<C>{$year}</>";
    for($i=0; $i < $count; $i++) {
        $line = $log[count($log)-2-$i];
        if($line == "") {
            continue;
        }
        
        if(strpos($line, $prefixYear)) {
            break;
        } elseif(strpos($line, $prefix) || $isFirst == 1) {
            $str = ConvertLog($line).'<br>'.$str;
        } else {
            break;
        }
    }
    if($str == "") {
        $str = "<C>●</>{$month}월: 기록 없음";
        $str = ConvertLog($str);
    }
    return $str;
}

function LogHistory($isFirst=0) {
    if(STEP_LOG) pushStepLog(date('Y-m-d H:i:s').', LogHistory Start');

    //TODO: 새롭게 추가할 지도 값 받아오는 함수를 이용하여 재구성
    $map = getWorldMap([
        'neutralView'=>true,
        'showMe'=>false
    ]);

    if($isFirst == 1){
        $map['year'] -= 1;
        $map['month'] = 12;
    }

    $startYear = $map['startYear'];
    $year = $map['year'];
    $month = $map['month'];

    $map_json = json_encode($map, JSON_UNESCAPED_UNICODE);
    
    $log = getHistory(20, $year, $month, $isFirst);
    $genlog = getGenHistory(50, $year, $month, $isFirst);

    $nationStr = "";
    $powerStr = "";
    $genStr = "";
    $cityStr = "";

    $db = getDB();
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
