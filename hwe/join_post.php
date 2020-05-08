<?php
namespace sammo;

include "lib.php";
include "func.php";

$v = new Validator($_POST);
$v
->rule('required', [
    'name',
    'leadership',
    'strength',
    'intel'
])
->rule('integer', [
    'leadership',
    'strength',
    'intel',
])
->rule('stringWidthBetween', 'name', 1, 18)
->rule('min', [
    'leadership',
    'strength',
    'intel'
], GameConst::$defaultStatMin)
->rule('max', [
    'leadership',
    'strength',
    'intel'
], GameConst::$defaultStatMax)
->rule('in', 'character', array_merge(GameConst::$availablePersonality, ['Random']));

if (!$v->validate()) {
    MessageBox($v->errorStr());
    echo "<script>history.go(-1);</script>";
    exit(1);
}

$session = Session::requireLogin()->setReadOnly();
$userID = Session::getUserID();
//NOTE: 이 페이지에서는 세션에 데이터를 등록하지 않음. 로그인은 이후에.

$name       = Util::getPost('name');
$name       = htmlspecialchars($name);
$name       = StringUtil::removeSpecialCharacter($name);
$name       = WebUtil::htmlPurify($name);
$name       = StringUtil::textStrip($name);
$pic        = (int)Util::getPost('pic', 'bool', 0);
$character  = Util::getPost('character');

$leadership = Util::getPost('leadership', 'int', 50);
$strength = Util::getPost('strength', 'int', 50);
$intel = Util::getPost('intel', 'int', 50);

$join = Util::getPost('join'); //쓸모 없음

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
$gameStor->cacheValues(['year','month','maxgeneral','scenario','show_img_level','turnterm','turntime','genius','npcmode']);
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
if ($leadership + $strength + $intel > GameConst::$defaultStatTotal) {
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

$pleadership = 0;
$pstrength = 0;
$pintel = 0;
for ($statBonusCnt = 3 + mt_rand(0, 2); $statBonusCnt > 0; $statBonusCnt--) {
    switch (Util::choiceRandomUsingWeight([$leadership, $strength, $intel])) {
    case 0:
        $pleadership++;
        break;
    case 1:
        $pstrength++;
        break;
    case 2:
        $pintel++;
        break;
    }
}

$leadership = $leadership + $pleadership;
$strength = $strength + $pstrength;
$intel = $intel + $pintel;

$admin = $gameStor->getValues(['scenario', 'turnterm', 'turntime', 'show_img_level', 'startyear', 'year']);
$relYear = Util::valueFit($admin['year'] - $admin['startyear'], 0);

$age = 20 + ($pleadership + $pstrength + $pintel) * 2 - (mt_rand(0, 1));
// 아직 남았고 천재등록상태이면 특기 부여
if ($genius) {
    $specage2 = $age;
    $special2 = SpecialityHelper::pickSpecialWar([
        'leadership'=>$leadership,
        'strength'=>$strength,
        'intel'=>$intel,
        'dex1'=>0,
        'dex2'=>0,
        'dex3'=>0,
        'dex4'=>0,
        'dex5'=>0
    ]);
} else {
    $specage2 = Util::valueFit(Util::round((GameConst::$retirementYear - $age)/4 - $relYear / 2), 3) + $age;
    $special2 = GameConst::$defaultSpecialWar;
}
//내특
$specage = Util::valueFit(Util::round((GameConst::$retirementYear - $age)/12 - $relYear / 2), 3) + $age;
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

$turntime = getRandTurn($admin['turnterm'], new \DateTimeImmutable($admin['turntime']));

$now = date('Y-m-d H:i:s');
if ($now >= $turntime) {
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
    'owner_name' => $member['name'],
    'picture' => $face,
    'imgsvr' => $imgsvr,
    'nation' => 0,
    'city' => $city,
    'troop' => 0,
    'affinity' => $affinity,
    'leadership' => $leadership,
    'strength' => $strength,
    'intel' => $intel,
    'experience' => $experience,
    'dedication' => 0,
    'gold' => GameConst::$defaultGold,
    'rice' => GameConst::$defaultRice,
    'crew' => 0,
    'train' => 0,
    'atmos' => 0,
    'officer_level' => 0,
    'turntime' => $turntime,
    'killturn' => 6,
    'lastconnect' => $now,
    'lastrefresh' => $now,
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
foreach(Util::range(GameConst::$maxTurn) as $turnIdx){
    $turnRows[] = [
        'general_id'=>$generalID,
        'turn_idx'=>$turnIdx,
        'action'=>'휴식',
        'arg'=>null,
        'brief'=>'휴식'
    ];
}
$db->insert('general_turn', $turnRows);

$rank_data = [];
foreach(array_keys(General::RANK_COLUMN) as $rankColumn){
    $rank_data[] = [
        'general_id'=>$generalID,
        'nation_id'=>0,
        'type'=>$rankColumn,
        'value'=>0
    ];
}
$db->insert('rank_data', $rank_data);
$db->insert('betting', [
    'general_id'=>$generalID,
]);
$cityname = CityConst::byID($city)->name;

$me = [
    'no'=>$generalID
];

$log = [];
$mylog = [];

$logger = new ActionLogger($generalID, 0, $gameStor->year, $gameStor->month);

$josaRa = JosaUtil::pick($name, '라');
$speicalText = getGeneralSpecialWarName($special2);
if ($genius) {
    
    $logger->pushGlobalActionLog("<G><b>{$cityname}</b></>에서 <Y>{$name}</>{$josaRa}는 기재가 천하에 이름을 알립니다.");
    $logger->pushGlobalActionLog("<C>{$speicalText}</> 특기를 가진 <C>천재</>의 등장으로 온 천하가 떠들썩합니다.");
    $logger->pushGlobalHistoryLog("<L><b>【천재】</b></><G><b>{$cityname}</b></>에 천재가 등장했습니다.");
} else {
    $logger->pushGlobalActionLog("<G><b>{$cityname}</b></>에서 <Y>{$name}</>{$josaRa}는 호걸이 천하에 이름을 알립니다.");
}

$logger->pushGeneralHistoryLog("<Y>{$name}</>, <G>{$cityname}</>에서 큰 뜻을 품다.");
$logger->pushGeneralActionLog("삼국지 모의전투 PHP의 세계에 오신 것을 환영합니다 ^o^", ActionLogger::PLAIN);
$logger->pushGeneralActionLog("처음 하시는 경우에는 <D>도움말</>을 참고하시고,", ActionLogger::PLAIN);
$logger->pushGeneralActionLog("문의사항이 있으시면 게시판에 글을 남겨주시면 되겠네요~", ActionLogger::PLAIN);
$logger->pushGeneralActionLog("부디 즐거운 삼모전 되시길 바랍니다 ^^", ActionLogger::PLAIN);
$logger->pushGeneralActionLog("통솔 <C>$pleadership</> 무력 <C>$pstrength</> 지력 <C>$pintel</> 의 보너스를 받으셨습니다.", ActionLogger::PLAIN);
$logger->pushGeneralActionLog("연령은 <C>$age</>세로 시작합니다.", ActionLogger::PLAIN);

if ($genius) {
    $logger->pushGeneralActionLog("축하합니다! 천재로 태어나 처음부터 <C>{$speicalText}</> 특기를 가지게 됩니다!", ActionLogger::PLAIN);
    $logger->pushGeneralHistoryLog("<C>{$speicalText}</> 특기를 가진 천재로 탄생.");
}

$logger->flush();

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


