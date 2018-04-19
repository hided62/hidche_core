<?php
namespace sammo;


/**
 * 템플릿 생성과 관련된 함수들을 모아두는 파일
 * 
 * NOTE: 아직 converter와 명확한 구분이 되어있지 않음.
 * TODO: legacy template들을 전부 template 폴더로 모아둘 필요 있음
 * TODO: side effect를 제거
 */


function turnTable() {
    return "
<select name=turn[] size=11 multiple style=width:50px;color:white;background-color:black;font-size:13px;>
    <option value=100>전체</option>
    <option value=99>홀턴</option>
    <option value=98>짝턴</option>
    <option selected value=0> 1턴</option>
    <option value=1> 2턴</option>
    <option value=2> 3턴</option>
    <option value=3> 4턴</option>
    <option value=4> 5턴</option>
    <option value=5> 6턴</option>
    <option value=6> 7턴</option>
    <option value=7> 8턴</option>
    <option value=8> 9턴</option>
    <option value=9>10턴</option>
    <option value=10>11턴</option>
    <option value=11>12턴</option>
    <option value=12>13턴</option>
    <option value=13>14턴</option>
    <option value=14>15턴</option>
    <option value=15>16턴</option>
    <option value=16>17턴</option>
    <option value=17>18턴</option>
    <option value=18>19턴</option>
    <option value=19>20턴</option>
    <option value=20>21턴</option>
    <option value=21>22턴</option>
    <option value=22>23턴</option>
    <option value=23>24턴</option>
</select>
";
}

function CoreTurnTable() {
    return "
<select name=turn[] size=3 multiple style=color:white;background-color:black;font-size:13px;>
    <option selected value=0> 1턴</option>
    <option value=1> 2턴</option>
    <option value=2> 3턴</option>
    <option value=3> 4턴</option>
    <option value=4> 5턴</option>
    <option value=5> 6턴</option>
    <option value=6> 7턴</option>
    <option value=7> 8턴</option>
    <option value=8> 9턴</option>
    <option value=9>10턴</option>
    <option value=10>11턴</option>
    <option value=11>12턴</option>
</select>
";
}


function allButton() {
    $npcmode = DB::db()->queryFirstField("select npcmode from game limit 1");
    if($npcmode == 1) {
        $site = "a_npcList.php";
        $call = "빙의일람";
    } else {
        $site = "a_vote.php";
        $call = "설문조사";
    }

    if(\file_exists("d_setting/templates/allButton.php")){
        $templates = new \League\Plates\Engine('d_setting/templates');
    }
    else{
        $templates = new \League\Plates\Engine('templates');
    }
    

    return $templates->render('allButton', [
        'call' => $call,
        'site' => $site
    ]);
}


function commandButton() {
    $session = Session::getInstance();
    $userID = Session::getUserID();
    $generalID = $session->generalID;
    
    if($generalID === null){
        return '';
    }
    $db = DB::db();
    $me = $db->queryFirstRow("select no,nation,level,belong from general where owner=%i", $userID);

    $nation = $db->queryFirstRow("select nation,color,secretlimit from nation where nation=%i",$me['nation']);

    if($nation['color'] == "") { $nation['color'] = "#000000"; }


    $bgColor = Util::array_get($nation['color'])?:'#000000';
    $fgColor = newColor($bgColor);

    $templates = new \League\Plates\Engine('templates');
    $showSecret = false;
    if($me['level'] >= 2){
        $showSecret = true;
    }
    else if($me['level']== 0){
        $showSecret = false;
    }
    else if($me['belong'] >= $nation['secretlimit']){
        $showSecret = true;
    }
    
    return $templates->render('commandButton', [
        'bgColor'=>$bgColor,
        'fgColor'=>$fgColor,
        'meLevel'=>$me['level'],
        'showSecret'=>$showSecret
    ]);
}



function getMapHtml(){
    //NOTE: 필요한가?
    $templates = new \League\Plates\Engine('templates');

    return $templates->render('map');
}