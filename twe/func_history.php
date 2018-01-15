<?php
include "func_http.php";

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
    $log = explode("\r\n",$file);

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

    $file = explode('/', __FILE__);
    $url = '/'.$file[count($file)-3].'/'.$file[count($file)-2].'/map.php?type=2&graphic=0';

    /* 소켓 통신을 통하여 필요한 html정보를 가져옴 */
    if(STEP_LOG) pushStepLog(date('Y-m-d H:i:s').', Start HTTP');
    $http = new HTTP("62che.com", 80, 10);
    if(STEP_LOG) pushStepLog(date('Y-m-d H:i:s').', Connect end, '.$http->GetError());
    if($http->GetErr() == true) { return false; }
    $http->setHttpVersion("1.1");
    $cookie = "";
    $http->Get($url, $cookie);
    if(STEP_LOG) pushStepLog(date('Y-m-d H:i:s').', Response end, '.$http->GetError());
    if($http->GetErr() == true) { return false; }
    $map = $http->Response["body"];
    $map = str_replace("'", '<_quot_>', $map);
    $map = str_replace('"', '<_dquot_>', $map);
    $http->Close();
    if($http->GetErr() == true) { return false; }
    
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

        $nationStr .= "<font color=cyan>◆</font> <font style=color:".newColor($nation['color']).";background-color:$nation['color'];>$nation['name']</font><br>";
        $powerStr .= "국력 $nation['power']<br>";
        $genStr .= "장수 $nation['gennum']<br>";
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
