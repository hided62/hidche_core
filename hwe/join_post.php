<?php
namespace sammo;

include "lib.php";
include "func.php";

$v = new Validator($_POST + $_GET);
$v
->rule('required', [
    'name',
    'leader',
    'power',
    'intel'
])
->rule('integer', [
    'leader',
    'power',
    'intel',
    'character',
])
->rule('stringWidthBetween', 'name', 1, 18)
->rule('min', [
    'leader',
    'power',
    'intel'
], GameConst::$defaultStatMin)
->rule('max', [
    'leader',
    'power',
    'intel'
], GameConst::$defaultStatMax)
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
$name       = htmlspecialchars($name);
$name       = StringUtil::removeSpecialCharacter($name);
$name       = WebUtil::htmlPurify($name);
$name       = StringUtil::textStrip($name);
$pic        = (int)Util::getReq('pic', 'bool', 0);
$character  = Util::getReq('character', 'int', 0);

$leader = Util::getReq('leader', 'int', 50);
$power = Util::getReq('power', 'int', 50);
$intel = Util::getReq('intel', 'int', 50);

$join = Util::getReq('join'); //쓸모 없음

$rootDB = RootDB::db();
//회원 테이블에서 정보확인
$member = $rootDB->queryFirstRow('SELECT `no`, id, picture, grade, `name`, imgsvr FROM member WHERE no=%i', $userID);

if (!$member) {
    MessageBox("잘못된 접근입니다!!!");
    echo "<script>history.go(-1);</script>";
    exit(1);
}

$db = DB::db();
$gameStor = KVStorage::getStorage($db, 'game_env');
$gameStor->cacheValues(['year','month','maxgeneral','scenario','show_img_level','turnterm','genius','npcmode']);
########## 동일 정보 존재여부 확인. ##########

$gencount = $db->queryFirstField('SELECT count(`no`) FROM general WHERE npc<2');
$oldGeneral = $db->queryFirstField('SELECT `no` FROM general WHERE `owner`=%i', $userID);
$oldName = $db->queryFirstField('SELECT `no` FROM general WHERE `name`=%s', $name);

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
if ($gameStor->maxgeneral <= $gencount) {
    echo("<script>
      window.alert('더이상 등록할 수 없습니다!')
      history.go(-1)
      </script>");
    exit;
}
if ($name == '') {
    echo("<script>
      window.alert('이름이 짧습니다. 다시 가입해주세요!')
      history.go(-1)
      </script>");
    exit;
}
if (mb_strwidth($name) > 18) {
    echo("<script>
      window.alert('이름이 유효하지 않습니다. 다시 가입해주세요!')
      history.go(-1)
      </script>");
    exit;
}
if ($leader + $power + $intel > GameConst::$defaultStatTotal) {
    echo("<script>
      window.alert('능력치가 ".GameConst::$defaultStatTotal."을 넘어섰습니다. 다시 가입해주세요!')
      history.go(-1)
      </script>");
    exit;
}

$genius = Util::randBool(0.01);
// 현재 1%
if ($genius && $gameStor->genius > 0) {
    $gameStor->genius = $gameStor->genius-1;
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
    switch (Util::choiceRandomUsingWeight([$leader, $power, $intel])) {
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

$admin = $gameStor->getValues(['scenario', 'turnterm', 'show_img_level', 'startyear', 'year']);
$relYear = Util::valueFit($admin['year'] - $admin['startyear'], 0);

$age = 20 + ($pleader + $ppower + $pintel) * 2 - (mt_rand(0, 1));
// 아직 남았고 천재등록상태이면 특기 부여
if ($genius) {
    $specage2 = $age;
    $special2 = getSpecial2($leader, $power, $intel);
} else {
    $specage2 = Util::valueFit(Util::round((80 - $age)/4 - $relYear / 2), 3) + $age;
    $special2 = 0;
}
//내특
$specage = Util::valueFit(Util::round((80 - $age)/12 - $relYear / 2), 3) + $age;
$special = GameConst::$defaultSpecialDomestic;

if ($admin['scenario'] >= 1000) {
    $specage2 = $age + 3;
    $specage = $age + 3;
}

if($relYear < 3){
    $experience = 0;
}
else{
    $expGenCount = $db->queryFirstField('SELECT count(*) FROM general WHERE nation != 0 AND npc < 5');
    $targetGenOrder = Util::round($expGenCount * 0.2);
    $experience = $db->queryFirstField(
        'SELECT experience FROM general WHERE nation != 0 AND npc < 5 ORDER BY experience ASC LIMIT %i, 1', 
        $targetGenOrder - 1
    );
    $experience *= 0.8;
}

$turntime = getRandTurn($admin['turnterm']);

$lastconnect = TimeUtil::now();
if ($lastconnect >= $turntime) {
    $turntime = addTurn($turntime, $admin['turnterm']);
}

//특회 전콘
if ($admin['show_img_level'] >= 1 && $member['grade'] >= 1 && $member['picture'] != "" && $pic) {
    $face = $member['picture'];
    $imgsvr = $member['imgsvr'];
} else {
    $face = "default.jpg";
    $imgsvr = 0;
}

//성격 랜덤시
if (!in_array($character, GameConst::$availablePersonality)){
    $character = Util::choiceRandom(GameConst::$availablePersonality);
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
    'experience' => $experience,
    'dedication' => 0,
    'gold' => GameConst::$defaultGold,
    'rice' => GameConst::$defaultRice,
    'crew' => 0,
    'train' => 0,
    'atmos' => 0,
    'level' => 0,
    'turntime' => $turntime,
    'killturn' => 6,
    'lastconnect' => $lastconnect,
    'crewtype'=>GameUnitConst::DEFAULT_CREWTYPE,
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
$turnRows = [];
foreach(range(0, GameConst::$maxTurn - 1) as $turnIdx){
    $turnRows[] = [
        'general_id'=>$generalID,
        'turn_idx'=>$turnIdx,
        'action'=>'휴식',
        'arg'=>null,
    ];
}
$db->insert('general_turn', $turnRows);
$cityname = CityConst::byID($city)->name;

$me = [
    'no'=>$generalID
];

$log = [];
$mylog = [];

$josaRa = JosaUtil::pick($name, '라');
if ($genius) {
    $log[0] = "<C>●</>{$gameStor->month}월:<G><b>{$cityname}</b></>에서 <Y>{$name}</>{$josaRa}는 기재가 천하에 이름을 알립니다.";
    $log[1] = "<C>●</>{$gameStor->month}월:<C>".getGenSpecial($special2)."</> 특기를 가진 <C>천재</>의 등장으로 온 천하가 떠들썩합니다.";

    pushWorldHistory(["<C>●</>{$gameStor->year}년 {$gameStor->month}월:<L><b>【천재】</b></><G><b>{$cityname}</b></>에 천재가 등장했습니다."], $gameStor->year, $gameStor->month);
} else {
    $log[0] = "<C>●</>{$gameStor->month}월:<G><b>{$cityname}</b></>에서 <Y>{$name}</>{$josaRa}는 호걸이 천하에 이름을 알립니다.";
}
pushGeneralHistory($me, "<C>●</>{$gameStor->year}년 {$gameStor->month}월:<Y>{$name}</>, <G>{$cityname}</>에서 큰 뜻을 품다.");
$mylog[] = "<C>●</>삼국지 모의전투 PHP의 세계에 오신 것을 환영합니다 ^o^";
$mylog[] = "<C>●</>처음 하시는 경우에는 <D>도움말</>을 참고하시고,";
$mylog[] = "<C>●</>문의사항이 있으시면 게시판에 글을 남겨주시면 되겠네요~";
$mylog[] = "<C>●</>부디 즐거운 삼모전 되시길 바랍니다 ^^";
$mylog[] = "<C>●</>통솔 <C>$pleader</> 무력 <C>$ppower</> 지력 <C>$pintel</> 의 보너스를 받으셨습니다.";
$mylog[] = "<C>●</>연령은 <C>$age</>세로 시작합니다.";
if ($genius) {
    $mylog[] = "<C>●</>축하합니다! 천재로 태어나 처음부터 <C>".getGenSpecial($special2)."</> 특기를 가지게 됩니다!";
    pushGeneralHistory($me, "<C>●</>{$gameStor->year}년 {$gameStor->month}월:<C>".getGenSpecial($special2)."</> 특기를 가진 천재로 탄생.");
}
pushGenLog($me, $mylog);
pushGeneralPublicRecord($log, $gameStor->year, $gameStor->month);

pushAdminLog(["가입 : {$userID} // {$name} // {$generalID}".getenv("REMOTE_ADDR")]);

$rootDB->insert('member_log', [
    'member_no' => $userID,
    'date'=>TimeUtil::now(),
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
window.alert('정상적으로 회원 가입되었습니다. 장수명 : <?=$name?> \n위키와 팁/강좌 게시판을 꼭 읽어보세요!');
</script>
<script>location.replace('./');</script>


