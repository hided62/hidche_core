<?php
namespace sammo;

//File-based

function delStepLog() {
    $date = date('Y_m_d');
    @unlink(__DIR__."/logs/".UniqueConst::$serverID."/_{$date}_steplog.txt");
}

function pushRawFileLog($path, $lines){
    if(!$lines){
        return;
    }
    if(is_string($lines)){
        $lines = [$lines];
    }
    $text = join("\n", $lines)."\n";
    file_put_contents($path, $text, FILE_APPEND | LOCK_EX);
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
    foreach(Util::range(50) as $i){
        eraseTnmtFightLog($i);
    }
}

function eraseTnmtFightLog(int $group){
    $filepath = __DIR__."/logs/".UniqueConst::$serverID."/fight{$group}.txt";
    if(file_exists($filepath)){
        @unlink($filepath);
    }
}

function pushTnmtFightLog(int $group, $log) {
    pushRawFileLog(__DIR__."/logs/".UniqueConst::$serverID."/fight{$group}.txt", $log);
}

function getTnmtFightLogAll(int $group) {
    return join('<br>',getFormattedFileLogAll(__DIR__."/logs/".UniqueConst::$serverID."/fight{$group}.txt"));
}

function pushSabotageLog($log) {
    pushRawFileLog(__DIR__."/logs/".UniqueConst::$serverID."/_sabotagelog.txt", $log);
}

function getSabotageLogRecent($count) {
    return join('<br>', getFormattedFileLogRecent(__DIR__."/logs/".UniqueConst::$serverID."/_sabotagelog.txt", $count, 150));
}

function pushProcessLog($log) {
    $date = date('Y_m_d');
    pushRawFileLog(__DIR__."/logs/".UniqueConst::$serverID."/_{$date}_processlog.txt", $log);
}


function pushStepLog($log) {
    $date = date('Y_m_d');
    pushRawFileLog(__DIR__."/logs/".UniqueConst::$serverID."/_{$date}_steplog.txt", $log);
}

function pushLockLog($log) {
    $date = date('Y_m_d');
    pushRawFileLog(__DIR__."/logs/".UniqueConst::$serverID."/_{$date}_locklog.txt", $log);
}

function pushAdminLog($log) {
    pushRawFileLog(__DIR__."/logs/".UniqueConst::$serverID."/_adminlog.txt", $log);
}

function pushAuctionLog($log) {
    pushRawFileLog(__DIR__."/logs/".UniqueConst::$serverID."/_auctionlog.txt", $log);
}

function getAuctionLogRecent(int $count) {
    return join('<br>', array_reverse(getFormattedFileLogRecent(__DIR__."/logs/".UniqueConst::$serverID."/_auctionlog.txt", $count, 300)));
}

//DB-based
function formatHistoryToHTML(array $history):string{
    $result = [];
    foreach($history as $item){
        $result[] = ConvertLog($item);
    }
    return join('<br>', $result);
}

function pushGeneralActionLog(int $generalID, ?array $history, ?int $year=null, ?int $month=null) {
    if(!$history){
        return;
    }
    $db = DB::db();

    if($year === null || $month === null){
        $gameStor = KVStorage::getStorage($db, 'game_env');
        list($year, $month) = $gameStor->getValuesAsArray(['year', 'month']);
    }
    $request = array_map(function($text) use ($year, $month, $generalID) {
        return [
            'general_id'=>$generalID,
            'log_type'=>'action',
            'year'=>$year,
            'month'=>$month,
            'text'=>$text
        ];
    }, array_values($history));
    $db->insert('general_record', $request);
}

function getGeneralActionLogRecent(int $generalID, int $count):array{
    $db = DB::db();

    return $db->queryFirstColumn(
        'SELECT `text` from general_record WHERE general_id = %i AND log_type = "action" order by id desc LIMIT %i',
        $generalID, $count
    );
}

function pushBattleResultLog(int $generalID, array $history, ?int $year=null, ?int $month=null) {
    if(!$history){
        return;
    }
    $db = DB::db();

    if($year === null || $month === null){
        $gameStor = KVStorage::getStorage($db, 'game_env');
        list($year, $month) = $gameStor->getValuesAsArray(['year', 'month']);
    }
    $request = array_map(function($text) use ($year, $month, $generalID) {
        return [
            'general_id'=>$generalID,
            'log_type'=>'battle_brief',
            'year'=>$year,
            'month'=>$month,
            'text'=>$text
        ];
    }, array_values($history));
    $db->insert('general_record', $request);
}

function getBattleResultRecent(int $generalID, int $count):array {
    $db = DB::db();

    return $db->queryFirstColumn(
        'SELECT `text` from general_record WHERE general_id = %i AND log_type = "battle_brief" order by id desc LIMIT %i',
        $generalID, $count
    );
}

function pushBattleDetailLog(int $generalID, array $history, ?int $year=null, ?int $month=null) {
    if(!$history){
        return;
    }
    $db = DB::db();

    if($year === null || $month === null){
        $gameStor = KVStorage::getStorage($db, 'game_env');
        list($year, $month) = $gameStor->getValuesAsArray(['year', 'month']);
    }
    $request = array_map(function($text) use ($year, $month, $generalID) {
        return [
            'general_id'=>$generalID,
            'log_type'=>'battle',
            'year'=>$year,
            'month'=>$month,
            'text'=>$text
        ];
    }, array_values($history));
    $db->insert('general_record', $request);
}

function getBattleDetailLogRecent(int $generalID, int $count):array {
    $db = DB::db();

    return $db->queryFirstColumn(
        'SELECT `text` from general_record WHERE general_id = %i AND log_type = "battle" order by id desc LIMIT %i',
        $generalID, $count
    );
}


function pushGeneralHistoryLog(int $generalID, ?array $history, $year=null, $month=null) {
    if(!$history){
        return;
    }
    $db = DB::db();
    
    if($year === null || $month === null){
        $gameStor = KVStorage::getStorage($db, 'game_env');
        list($year, $month) = $gameStor->getValuesAsArray(['year', 'month']);
    }
    $request = array_map(function($text) use ($year, $month, $generalID) {
        return [
            'general_id'=>$generalID,
            'log_type'=>'history',
            'year'=>$year,
            'month'=>$month,
            'text'=>$text
        ];
    }, array_values($history));
    $db->insert('general_record', $request);

}

function getGeneralHistoryLogAll(int $generalID):array {
    $db = DB::db();

    return $db->queryFirstColumn(
        'SELECT `text` from general_record WHERE general_id = %i AND log_type = "history" order by id desc',
        $generalID
    );
}


function pushNationHistoryLog(int $nationID, ?array $history, ?int $year=null, ?int $month=null) {
    if(!$history){
        return;
    }
    if(!$nationID){
        return;
    }
    $db = DB::db();
    
    if($year === null || $month === null){
        $gameStor = KVStorage::getStorage($db, 'game_env');
        list($year, $month) = $gameStor->getValuesAsArray(['year', 'month']);
    }
    $request = array_map(function($text) use ($year, $month, $nationID) {
        return ['nation_id'=>$nationID, 'year'=>$year, 'month'=>$month, 'text'=>$text];
    }, array_values($history));
    $db->insert('world_history', $request);
}

function getNationHistoryLogAll(int $nationID):array {
    $db = DB::db();

    return $db->queryFirstColumn(
        'SELECT `text` from world_history WHERE nation_id = %i order by id desc',
        $nationID
    );
}


function pushGlobalHistoryLog(?array $history, $year=null, $month=null) {
    if(!$history){
        return;
    }
    $db = DB::db();
    
    if($year === null || $month === null){
        $gameStor = KVStorage::getStorage($db, 'game_env');
        list($year, $month) = $gameStor->getValuesAsArray(['year', 'month']);
    }
    $request = array_map(function($text) use ($year, $month) {
        return ['nation_id'=>0, 'year'=>$year, 'month'=>$month, 'text'=>$text];
    }, array_values($history));
    $db->insert('world_history', $request);
}

function getGlobalHistoryLogRecent(int $count):array {
    $db = DB::db();

    return $db->queryFirstColumn('SELECT `text` from world_history WHERE nation_id = 0 order by id desc limit %i', $count);
}

function getGlobalHistoryLogWithDate(int $year, int $month):array {
    $db = DB::db();

    $texts = $db->queryFirstColumn(
        'SELECT `text` from world_history where nation_id = 0 AND year = %i and month = %i order by id desc', 
        $year, 
        $month
    );

    if(!$texts){
        return ["<C>●</>{$year}년 {$month}월: 기록 없음"];
    }
    return $texts;
}


function pushGlobalActionLog(?array $history, ?int $year=null, ?int $month=null) {
    if(!$history){
        return;
    }
    $db = DB::db();
    
    if($year === null || $month === null){
        $gameStor = KVStorage::getStorage($db, 'game_env');
        list($year, $month) = $gameStor->getValuesAsArray(['year', 'month']);
    }
    $request = array_map(function($text) use ($year, $month) {
        return ['general_id'=>0, 'log_type'=>'history', 'year'=>$year, 'month'=>$month, 'text'=>$text];
    }, array_values($history));
    $db->insert('general_record', $request);
}

function getGlobalActionLogRecent(int $count):array {
    $db = DB::db();

    return $db->queryFirstColumn(
        'SELECT `text` from general_record WHERE general_id = 0 AND log_type = "history" order by id desc limit %i',
        $count
    );
}

function getGlobalActionLogWithDate(int $year, int $month):array {
    $db = DB::db();

    $texts = $db->queryFirstColumn(
        'SELECT `text` from general_record where general_id = 0 AND log_type = "history" AND year = %i and month = %i order by id desc', 
        $year, 
        $month
    );

    if(!$texts){
        return ["<C>●</>{$month}월: 기록 없음"];
    }
    return $texts;
}

function LogHistory($isFirst=0) {
    if(STEP_LOG) pushStepLog(TimeUtil::now().', LogHistory Start');

    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');
    $obj = $gameStor->getValues(['startyear', 'year', 'month']);

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

    $year = $map['year'];
    $month = $map['month'];

    
    $globalHistory = getGlobalHistoryLogWithDate($year, $month);
    $globalAction = getGlobalActionLogWithDate($year, $month);
    
    $nations = getAllNationStaticInfo();
    $nations[0] = getNationStaticInfo(0);

    foreach($db->query('SELECT name, nation FROM city') as $city){
        $cityNationID = $city['nation'];
        if(!key_exists('cities', $nations[$cityNationID])){
            $nations[$cityNationID]['cities'] = [];
        }
        $nations[$cityNationID]['cities'][] = $city['name'];
    }

    usort($nations, function(array $lhs, array $rhs){
        return -($lhs['power']<=>$rhs['power']);
    });

    if(STEP_LOG) pushStepLog(TimeUtil::now().', contents collected');
    
    $db->insert('ng_history', [
        'server_id' => UniqueConst::$serverID,
        'year' => $year,
        'month' => $month,
        'map' => Json::encode($map),
        'global_history' => Json::encode($globalHistory),
        'global_action' => Json::encode($globalAction),
        'nations' => Json::encode($nations),
    ]);

    if(STEP_LOG) pushStepLog(TimeUtil::now().', LogHistory Finish');
    return true;
}
