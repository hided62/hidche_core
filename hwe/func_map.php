<?php
namespace sammo;
class MapRequest{
    public $serverID;
    public $year;
    public $month;
    public $aux;
    public $neutralView;
    public $showMe;
    function __construct($obj){
        $this->serverID = $obj['serverID'] ?? null;
        $this->year = $obj['year'] ?? null;
        $this->month = $obj['month'] ?? null;
        $this->aux = $obj['aux'] ?? [];
        $this->neutralView = $obj['neutralView'] ?? false;
        $this->showMe = $obj['showMe'];
    }
}


/**
 * @param int $year
 * @param int $month
 * @param string|null $serverID
 * @return mixed
 */
function getHistoryMap($year, $month, ?string $serverID=null){
    if(!$year || !$month){
        return ['result'=>false, 'reason'=>'연 월이 지정되지 않음'];
    }

    if($serverID === null){
        $serverID = UniqueConst::$serverID;
    }

    $map = DB::db()->queryFirstField('SELECT map FROM ng_history WHERE server_id = %s AND year=%i and month=%i',
        $serverID,
        $year,
        $month);

    if(!$map){
        return ['result'=>false, 'reason'=>'연감이 저장되지 않음'];
    }

    return Json::decode($map);//까짓거 json_decode, json_encode 두번하지 뭐.
}

/**
 * @param MapRequest|array $req
 * @return mixed
 */
function getWorldMap($req){
    if(is_array($req)){
        $req = new MapRequest($req);
    }

    if($req->year && $req->month){
        return getHistoryMap($req->year, $req->month, $req->serverID??null);
    }

    $session = Session::getInstance();
    $userID = $session->userID;
    $userGrade = $session->userGrade;

    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');

    list($startYear, $year, $month) = $gameStor->getValuesAsArray(['startyear', 'year', 'month']);
    $startYear = Util::toInt($startYear);
    $year = Util::toInt($year);
    $month = Util::toInt($month);

    $general = $db->queryFirstRow(
        'select `no`, `city`, `nation` from `general` where `owner`=%i',
         $userID);


    if($general && ($req->showMe || !$req->neutralView)){
        if($session->generalID !== $general['no']){
            $session->logoutGame()->loginGame();
        }

        $myCity = Util::toInt($general['city']);
        $myNation = Util::toInt($general['nation']);

        if(!$req->showMe){
            $myCity = null;
        }
        if($req->neutralView){
            $myNation = null;
        }
    }
    else{
        $myCity = null;
        $myNation = null;
    }

    $spyInfo = (object)null;

    if($myNation){
        $rawSpy = $db->queryFirstField('select `spy` from `nation` where `nation`=%i',
            $myNation);

        if(strpos($rawSpy, '|') !== false || is_numeric($rawSpy)){
            //NOTE: 0.8 이전 데이터가 남아있으므로, 0.8버전으로 마이그레이션 이후에도 이곳은 삭제하면 안됨
            $spyInfo = [];
            foreach(explode('|', $rawSpy) as $value){
                $value = intval($value);
                $cityNo = intdiv($value, 10);
                $remainMonth = $value % 10;
                $spyInfo[$cityNo] = $remainMonth;
            }
        }
        else if($rawSpy != ''){
            $spyInfo = Json::decode($rawSpy);
        }
    }

    if(!$spyInfo){
        $spyInfo = (object)null;
    }

    $nationList = [];
    foreach($db->query('select `nation`, `name`, `color`, `capital` from `nation`') as $row){
        $nationList[] = [
            Util::toInt($row['nation']),
            $row['name'],
            $row['color'],
            Util::toInt($row['capital'])
        ];
    }

    if($myNation){
        //굳이 타국 도시에 있는 아국 장수 리스트를 뽑을 이유가 없음. 일단 다 뽑자.
        $shownByGeneralList =
            array_map('\\sammo\\Util::toInt',
                $db->queryFirstColumn('select distinct `city` from `general` where `nation` = %i',
                    $myNation));
    }
    else{
        $shownByGeneralList = [];
    }

    $cityList = [];
    foreach($db->query('select `city`, `level`, `state`, `nation`, `region`, `supply` from `city`') as $r){
        $cityList[] =
            array_map('\\sammo\\Util::toInt', [$r['city'], $r['level'], $r['state'], $r['nation'], $r['region'], $r['supply']]);
    }

    if(($req->showMe || !$req->neutralView) && $userGrade >= 5){
        $spyInfo = [];
        foreach($cityList as $tmpCity){
            $spyInfo[$tmpCity[0]] = 1;
        }
    }

    return [
        'startYear' => $startYear,
        'year' => $year,
        'month' => $month,
        'cityList' => $cityList,
        'nationList' => $nationList,
        'spyList' => $spyInfo,
        'shownByGeneralList' => $shownByGeneralList,
        'myCity' => $myCity,
        'myNation' => $myNation,
        'version' => 0,
        'result' => true
    ];
}