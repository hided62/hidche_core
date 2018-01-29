<?php
require(__dir__.'/../vendor/autoload.php');

use utilphp\util as util;

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

function LogHistory($connect, $isFirst=0) {
    if(STEP_LOG) pushStepLog(date('Y-m-d H:i:s').', LogHistory Start');
    
    $query = "select startyear,year,month from game where no='1'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $admin = MYDB_fetch_array($result);
    if($isFirst == 1) {
        $admin['year'] -= 1;
        $admin['month'] = 12;
    }

    //TODO: 웹 접속이 아닌 콘솔일 경우에 대응책 필요. conf등에 저장하는 것을 고려
    $current_url = util::get_current_url();
    $map_path =  explode('/',parse_url($current_url, PHP_URL_PASS));
    array_pop($map_path);
    $map_path[] =  'map.php?type=2&graphic=0';
    $map_path = join('/', $map_path);
    
    $client = new GuzzleHttp\Client();
    $response = $client->get($map_path);
    
    $map = (string)$response->getBody();
    $map = str_replace("'", '<_quot_>', $map);
    $map = str_replace('"', '<_dquot_>', $map);
    
    $log = getHistory(20, $admin['year'], $admin['month'], $isFirst);
    $genlog = getGenHistory(50, $admin['year'], $admin['month'], $isFirst);

    $query = "select nation,color,name,power,gennum from nation where level>0 order by power desc";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $nationcount = MYDB_num_rows($result);

    $nationStr = "";
    $powerStr = "";
    $genStr = "";
    $cityStr = "";
    for($i=0; $i < $nationcount; $i++) {
        $nation = MYDB_fetch_array($result);

        $query = "select city from city where nation='{$nation['nation']}'";
        $cityresult = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $citycount = MYDB_num_rows($cityresult);

        $nationStr .= "<font color=cyan>◆</font> <font style=color:".newColor($nation['color']).";background-color:{$nation['color']};>{$nation['name']}</font><br>";
        $powerStr .= "국력 {$nation['power']}<br>";
        $genStr .= "장수 {$nation['gennum']}<br>";
        $cityStr .= "속령 $citycount<br>";
    }

    if(STEP_LOG) pushStepLog(date('Y-m-d H:i:s').', contents collected');
    
    @MYDB_query("
        insert into history (
            year, month, map, log, genlog, nation, power, gen, city
        ) values (
            '{$admin['year']}', '{$admin['month']}', '$map', '$log', '$genlog', '$nationStr', '$powerStr', '$genStr', '$cityStr'
        )",
    $connect) or Error(__LINE__.MYDB_error($connect),"");

    if(STEP_LOG) pushStepLog(date('Y-m-d H:i:s').', LogHistory Finish');
    return true;
}
