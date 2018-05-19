<?php
namespace sammo;

//File-based

function delStepLog() {
    $date = date('Y_m_d');
    @unlink(__dir__."/logs/_{$date}_steplog.txt");
}

function pushRawFileLog($path, $lines){
    if(!$lines){
        return;
    }
    if(is_string($lines)){
        $lines = [$lines];
    }
    $text = join("\n", $lines)."\n";
    file_put_contents($path, $text, FILE_APPEND);
}

function getRawFileLogRecent(string $path, int $count, $expectedLineLength=null){
    $tail = new FileTail($path);
    return $tail->smart($count, $expectedLineLength, true);
}

function getRawFileLogAll(string $path){
    if(!file_exists($path)){
        return [];
    }
    return explode("\n", file_get_contents($path));
}

function getFormattedFileLogRecent(string $path, int $count, $expectedLineLength=null){
    return array_map(function($text){
        return ConvertLog($text);
    }, getRawFileLogRecent($path, $count, $expectedLineLength));
}

function getFormattedFileLogAll(string $path){
    return array_map(function($text){
        return ConvertLog($text);
    }, getRawFileLogAll($path));
}

function eraseTnmtFightLogAll(){
    foreach(range(0, 49) as $i){
        eraseTnmtFightLog($i);
    }
}

function eraseTnmtFightLog(int $group){
    $filepath = __dir__."/logs/fight{$group}.txt";
    if(file_exists($filepath)){
        @unlink($filepath);
    }
}

function pushTnmtFightLog(int $group, $log) {
    pushRawFileLog(__dir__."/logs/fight{$group}.txt", $log);
}

function getTnmtFightLogAll(int $group) {
    return join('<br>',getFormattedFileLogAll(__dir__."/logs/fight{$group}.txt"));
}

function pushSabotageLog($log) {
    pushRawFileLog(__dir__."/logs/_sabotagelog.txt", $log);
}

function getSabotageLogRecent($count) {
    return join('<br>', getFormattedFileLogRecent(__dir__."/logs/_sabotagelog.txt", $count, 150));
}

function pushProcessLog($log) {
    $date = date('Y_m_d');
    pushRawFileLog(__dir__."/logs/_{$date}_processlog.txt", $log);
}


function pushStepLog($log) {
    $date = date('Y_m_d');
    pushRawFileLog(__dir__."/logs/_{$date}_steplog.txt", $log);
}

function pushLockLog($log) {
    $date = date('Y_m_d');
    pushRawFileLog(__dir__."/logs/_{$date}_locklog.txt", $log);
}

function pushAdminLog($log) {
    pushRawFileLog(__dir__."/logs/_adminlog.txt", $log);
}

function pushAuctionLog($log) {
    pushRawFileLog(__dir__."/logs/_auctionlog.txt", $log);
}

function getAuctionLogRecent(int $count) {
    return join('<br>', array_reverse(getFormattedFileLogRecent(__dir__."/logs/_auctionlog.txt", $count, 300)));
}

function pushGenLog($general, $log) {
    $no = Util::toInt($general['no']);
    pushRawFileLog(__dir__."/logs/gen{$no}.txt", $log);
}

function getGenLogRecent(int $no, int $count) {
    return join('<br>', array_reverse(getFormattedFileLogRecent(__dir__."/logs/gen{$no}.txt", $count, 300)));
}

function pushBatRes($general, $log) {
    $no = Util::toInt($general['no']);
    pushRawFileLog(__dir__."/logs/batres{$no}.txt", $log);
}

function getBatResRecent(int $no, int $count) {
    return join('<br>', array_reverse(getFormattedFileLogRecent(__dir__."/logs/batres{$no}.txt", $count, 300)));
}

function pushBatLog($general, $log) {
    $no = Util::toInt($general['no']);
    pushRawFileLog(__dir__."/logs/batlog{$no}.txt", $log);
}

function getBatLogRecent(int $no, int $count) {
    return join('<br>', array_reverse(getFormattedFileLogRecent(__dir__."/logs/batlog{$no}.txt", $count, 300)));
}

//DB-based
function pushNationHistory($nation, $history) {
    if(!$nation || !$nation['nation']){
        return;
    }
    DB::db()->query("UPDATE nation set history=concat(%s, history) where nation=%i",
        $history.'<br>', $nation['nation']);
}

function pushGeneralHistory($me, $history) {
    DB::db()->query("UPDATE general set history=concat(%s, history) where no=%i",
        $history.'<br>', $me['no']);
}

function getGeneralHistoryAll(int $no) {
    $history = DB::db()->queryFirstField('SELECT history FROM general WHERE `no`=%i',$no);
    return ConvertLog($history);
}

function pushWorldHistory(array $history, $year=null, $month=null) {
    if(!$history){
        return;
    }
    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');
    if($year === null || $month === null){
        list($year, $month) = $gameStor->getValuesAsArray(['year', 'month']);
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
    if(!$history){
        return;
    }
    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');
    if($year === null || $month === null){
        list($year, $month) = $gameStor->getValuesAsArray(['year', 'month']);
    }
    $request = array_map(function($text) use ($year, $month) {
        return ['year'=>$year, 'month'=>$month, 'text'=>$text];
    }, array_values($history));
    $db->insert('general_public_record', $request);
}

function getGeneralPublicRecordRecent($count) {
    $db = DB::db();

    $texts = [];
    foreach(
        $db->queryFirstColumn(
            'SELECT `text` from general_public_record order by id desc limit %i',
            $count
        ) as $text
    ){
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
    return join('<br>', $texts);
}

function LogHistory($isFirst=0) {
    if(STEP_LOG) pushStepLog(date('Y-m-d H:i:s').', LogHistory Start');

    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');
    $obj = $gameStor->getValues(['startyear', 'year', 'month']);

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
        $cityStr .= "속령 $cityCount<br>";
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
