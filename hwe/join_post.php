<?php
namespace sammo;

include "lib.php";
include "func.php";

$v = new Validator($_POST + $_GET);
$v
->rule('required', [
    'name',
    'pic',
    'leader',
    'power',
    'intel'
])
->rule('integer', [
    'leader',
    'power',
    'intel',
    'character',
    'pic'
])
->rule('lengthBetween', 'name', [1, 6])
->rule('min', [
    'leader',
    'power',
    'intel'
], 10)
->rule('max', [
    'leader',
    'power',
    'intel'
], 75)
->rule('min', 'character', 0)
->rule('max', 'character', 11);

if (!$v->validate()) {
    MessageBox($v->errorStr());
    echo "<script>history.go(-1);</script>";
    exit(1);
}

$session = Session::requireLogin()->setReadOnly();
$userID = Session::getUserID();
//NOTE: 이 페이지에서는 세션에 데이터를 등록하지 않음. 로그인은 이후에.

$name       = Util::getReq('name');
$name       = StringUtil::removeSpecialCharacter($name);
$pic        = Util::getReq('pic', 'int', 0);
$character  = Util::getReq('character', 'int', 0);

$leader = Util::getReq('leader', 'int', 50);
$power = Util::getReq('power', 'int', 50);
$intel = Util::getReq('intel', 'int', 50);

$mylog = [];

$rootDB = RootDB::db();
//회원 테이블에서 정보확인
$member = $rootDB->queryFirstRow('SELECT `no`, id, picture, grade, `name` FROM MEMBER WHERE no=%i', $userID);

if (!$member) {
    MessageBox("잘못된 접근입니다!!!");
    echo "<script>history.go(-1);</script>";
    exit(1);
}

$db = DB::db();
########## 동일 정보 존재여부 확인. ##########

$admin = $db->queryFirstRow('SELECT year,month,maxgeneral,turnterm,genius,npcmode from game limit 1');
$gencount = $db->queryFirstField('SELECT count(`no`) FROM general WHERE noc<2');
$oldGeneral = $db->queryFirstField('SELECT `no` FROM general WHERE `owner`=%i', $userID);
$oldName = $db->queryFirstField('SELECT `no` FROM general WHERE `name`=%i', $name);

if ($oldGeneral) {
    echo("<script>
      window.alert('이미 등록하셨습니다!')
      history.go(-1)
      </script>");
    exit;
}
if ($oldName) {
    echo("<script>
      window.alert('이미 있는 장수입니다. 다른 이름으로 등록해 주세요!')
      history.go(-1)
      </script>");
    exit;
}
if ($admin['maxgeneral'] <= $gencount) {
    echo("<script>
      window.alert('더이상 등록할 수 없습니다!')
      history.go(-1)
      </script>");
    exit;
}
if (mb_strlen($name) < 1) {
    echo("<script>
      window.alert('이름이 짧습니다. 다시 가입해주세요!')
      history.go(-1)
      </script>");
    exit;
}
if (mb_strlen($name) > 6) {
    echo("<script>
      window.alert('이름이 유효하지 않습니다. 다시 가입해주세요!')
      history.go(-1)
      </script>");
    exit;
}
if ($leader + $power + $intel > 150) {
    echo("<script>
      window.alert('능력치가 150을 넘어섰습니다. 다시 가입해주세요!')
      history.go(-1)
      </script>");
    exit;
}

$genius = Util::randBool(0.01);
// 현재 1%
if ($genius && $admin['genius'] > 0) {
    $db->update('game', [
        'genius'=>$db->sqleval('genius-1')
    ], true);
} else {
    $genius = false;
}

// 공백지에서만 태어나게
$city = $db->queryFirstField("select city from city where level>=5 and level<=6 and nation=0 order by rand() limit 0,1");
if (!$city) {
    $city = $db->queryFirstField("select city from city where level>=5 and level<=6 order by rand() limit 0,1");
}

$pleader = 0;
$ppower = 0;
$pintel = 0;
for ($statBonusCnt = 3 + mt_rand(0, 2); $statBonusCnt > 0; $statBonusCnt--) {
    switch (Util::choiceRandomUsingWeight(array($leader, $power, $intel))) {
    case 0:
        $pleader++;
        break;
    case 1:
        $ppower++;
        break;
    case 2:
        $pintel++;
        break;
    }
}

$leader = $leader + $pleader;
$power = $power + $ppower;
$intel = $intel + $pintel;

$age = 20 + ($pleader + $ppower + $pintel) * 2 - (mt_rand(0, 1));
// 아직 남았고 천재등록상태이면 특기 부여
if ($genius) {
    $specage2 = $age;
    $special2 = getSpecial2($leader, $power, $intel);
} else {
    $specage2 = round((80 - $age)/3) + $age;
    $special2 = 0;
}
//내특
$specage = round((80 - $age)/12) + $age;
$special = 0;

if ($admin['scenario'] > 0) {
    $specage2 = $age + 3;
    $specage = $age + 3;
}

$turntime = getRandTurn($admin['turnterm']);

$lastconnect = date('Y-m-d H:i:s');
if ($lastconnect >= $turntime) {
    $turntime = addTurn($turntime, $admin['turnterm']);
}

//특회 전콘
if ($admin['show_img_level'] >= 1 && $member['grade'] >= 1 && $member['picture'] != "" && $pic == 1) {
    $face = $member['picture'];
    $imgsvr = $member['imgsvr'];
} else {
    $face = "default.jpg";
    $imgsvr = 0;
}

//성격 랜덤시
if ($character == 11) {
    $character = rand()%10;
}
//상성 랜덤
$affinity = rand()%150 + 1;

########## 회원정보 테이블에 입력값을 등록한다. ##########
$db->insert('general', [
    'owner' => $userID,
    'name' => $name,
    'picture' => $face,
    'imgsvr' => $imgsvr,
    'nation' => 0,
    'city' => $city,
    'troop' => 0,
    'affinity' => $affinity,
    'leader' => $leader,
    'power' => $power,
    'intel' => $intel,
    'experience' => 0,
    'dedication' => 0,
    'gold' => 1000,
    'rice' => 1000,
    'crew' => 0,
    'train' => 0,
    'atmos' => 0,
    'level' => 0,
    'turntime' => $turntime,
    'killturn' => 6,
    'lastconnect' => $lastconnect,
    'makelimit' => 0,
    'age' => $age,
    'startage' => $age,
    'personal' => $character,
    'specage' => $specage,
    'special' => $special,
    'specage2' => $specage2,
    'special2' => $special2
]);
$generalID = $db->insertId();

$cityname = CityConst::byID($city)->name;

$me = [
    'no'=>$generalID
];

if ($genius) {
    $log[0] = "<C>●</>{$admin['month']}월:<G><b>{$cityname}</b></>에서 <Y>{$name}</>(이)라는 기재가 천하에 이름을 알립니다.";
    $log[1] = "<C>●</>{$admin['month']}월:<C>".getGenSpecial($special2)."</> 특기를 가진 <C>천재</>의 등장으로 온 천하가 떠들썩합니다.";

    $history[0] = "<C>●</>{$admin['year']}년 {$admin['month']}월:<L><b>【천재】</b></><G><b>{$cityname}</b></>에 천재가 등장했습니다.";
    pushWorldHistory($history, $admin['year'], $admin['month']);
} else {
    $log[0] = "<C>●</>{$admin['month']}월:<G><b>{$cityname}</b></>에서 <Y>{$name}</>(이)라는 호걸이 천하에 이름을 알립니다.";
}
pushGeneralHistory($me, "<C>●</>{$admin['year']}년 {$admin['month']}월:<Y>{$name}</>, <G>{$cityname}</>에서 큰 뜻을 품다.");
$mylog[] = "<C>●</>삼국지 모의전투 PHP의 세계에 오신 것을 환영합니다 ^o^";
$mylog[] = "<C>●</>처음 하시는 경우에는 <D>도움말</>을 참고하시고,";
$mylog[] = "<C>●</>문의사항이 있으시면 게시판에 글을 남겨주시면 되겠네요~";
$mylog[] = "<C>●</>부디 즐거운 삼모전 되시길 바랍니다 ^^";
$mylog[] = "<C>●</>통솔 <C>$pleader</> 무력 <C>$ppower</> 지력 <C>$pintel</> 의 보너스를 받으셨습니다.";
$mylog[] = "<C>●</>연령은 <C>$age</>세로 시작합니다.";
if ($genius) {
    $mylog[] = "<C>●</>축하합니다! 천재로 태어나 처음부터 <C>".getGenSpecial($special2)."</> 특기를 가지게 됩니다!";
    pushGeneralHistory($me, "<C>●</>{$admin['year']}년 {$admin['month']}월:<C>".getGenSpecial($special2)."</> 특기를 가진 천재로 탄생.");
}
pushGenLog($me, $mylog);
pushGeneralPublicRecord($log, $admin['year'], $admin['month']);

$adminLog[0] = "가입 : {$name} // {$name} // {$id} // ".getenv("REMOTE_ADDR");
pushAdminLog($adminLog);

$rootDB->insert('member_log', [
    'member_no' => $userID,
    'date'=>date('Y-m-d H:i:s'),
    'action_type'=>'make_general',
    'action'=>Json::encode([
        'server'=>DB::prefix(),
        'type'=>'general',
        'generalID'=>$generalID,
        'generalName'=>$name
    ])
]);

?>
<script>
window.alert('정상적으로 회원 가입되었습니다. ID : <?=$id?> \n튜토리얼을 꼭 읽어보세요!');
</script>
<script>location.replace('index.php');</script>


