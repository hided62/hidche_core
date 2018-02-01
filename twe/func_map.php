<?php

class MapRequest{
    public $year;
    public $month;
    public $aux;
    public $neutralView;
    public $showMe;
    function __construct($obj){
        $this->year = $obj['year'];
        $this->month = $obj['month'];
        $this->aux = $obj['aux'];
        $this->neutralView = $obj['neutralView'];
        $this->showMe = $obj['showMe'];
    }
}


/**
 * @param int $year
 * @param int $month
 * @return mixed
 */
function getHistoryMap($year, $month){
    if(!$year || !$month){
        return ['result'=>false, 'reason'=>'연 월이 지정되지 않음'];
    }

    $map = getDB()->queryFirstField('select map from history where year=%i and month=%i',
        $year, 
        $month);

    if(!$map){
        return ['result'=>false, 'reason'=>'연감이 저장되지 않음'];
    }

    return json_decode($map, true);//까짓거 json_decode, json_encode 두번하지 뭐.
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
        return getHistoryMap($req->year, $req->month);
    }

    $generalID = getGeneralID();

    $db = getDB();

    list($startYear, $year, $month) = $db->queryFirstRow('select `startyear`, `year`, `month` from `game` where `no`=1');

    if($generalID && ($req->showMe || $req->neutralView)){
        list($myCity, $myNation) 
            = $db->queryFirstRow(
                'select `city`, `nation` from `general` where `user_id`=%i',
                 $generalID);
        if(!$req->showMe){
            $myCity = null;
        }
        if(!$req->neutralView){
            $myNation = null;
        }
    }
    else{
        $myCity = null;
        $myNation = null;
    }

    if($myNation){
        $spyList = $db->queryFirstField('select `spy` from `nation` where `nation`=%i', 
            $myNation);
        $spyList = array_map('intval', explode("|", $spyList));
    }
    else{
        $spyList = [];
    }

    $nationList = [];
    foreach($db->query('select `nation`, `name`, `color`, `capital` from `nation`') as $row){
        $nationList[] = [$row['nation'], $row['name'], $row['color'], $row['capital']];
    }

    if($myNation){
        //굳이 타국 도시에 있는 아국 장수 리스트를 뽑을 이유가 없음. 일단 다 뽑자.
        $shownByGeneralList = 
            $db->queryFirstRow('select distinct `city` from `general` where `nation` = %i',
                $myNation);
    }
    else{
        $shownByGeneralList = [];
    }

    $cityList = [];
    foreach($db->query('select `city`, `level`, `state`, `nation`, `region`, `supply` from `city`') as $r){
        $cityList[] = [$r['city'], $r['level'], $r['state'], $r['nation'], $r['region'], $r['supply']];
    }

    return [
        'startYear' => $startYear,
        'year' => $year,
        'month' => $month,
        'cityList' => $cityList,
        'nationList' => $nationList,
        'spyList' => $spyList,
        'shownByGeneralList' => $shownByGeneralList,
        'myCity' => $myCity,
        'myNation' => $myNation
    ];
}