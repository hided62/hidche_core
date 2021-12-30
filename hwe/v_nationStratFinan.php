<?php

namespace sammo;

include "lib.php";
include "func.php";
//로그인 검사
$session = Session::requireGameLogin()->setReadOnly();
$userID = Session::getUserID();

$db = DB::db();
$gameStor = KVStorage::getStorage($db, 'game_env');

increaseRefresh("내무부", 1);

$me = $db->queryFirstRow('SELECT no, nation, officer_level, con, turntime, belong, permission, penalty FROM general WHERE owner=%i', $userID);

$con = checkLimit($me['con']);
if ($con >= 2) {
    printLimitMsg($me['turntime']);
    exit();
}

$permission = checkSecretPermission($me);
if ($permission < 0) {
    echo '국가에 소속되어있지 않습니다.';
    die();
} else if ($permission < 1) {
    echo "권한이 부족합니다. 수뇌부가 아니거나 사관년도가 부족합니다.";
    die();
}


$nationID = $me['nation'];
$nation = $db->queryFirstRow('SELECT nation,level,name,color,type,gold,rice,bill,rate,scout,war,secretlimit,capital FROM nation WHERE nation = %i', $nationID);

$nationStor = KVStorage::getStorage($db, $nationID, 'nation_env');

[$year, $month] = $gameStor->getValuesAsArray(['year', 'month']);
$nationStor->cacheValues(['notice', 'scout_msg', 'available_war_setting_cnt']);

$cityCntList = Util::convertPairArrayToDict($db->queryAllLists('SELECT nation, count(city) FROM city GROUP BY nation'));
$dipStateList = Util::convertArrayToDict($db->query('SELECT you,state,term FROM diplomacy WHERE me = %i', $nationID), 'you');

$nationsList = getAllNationStaticInfo();

$cityList = $db->query('SELECT * FROM city WHERE nation=%i', $nationID);
$dedicationList = $db->query('SELECT dedication FROM general WHERE nation=%i AND npc!=5', $nationID);


foreach ($nationsList as &$nationItem) {

    $staticNationID = $nationItem['nation'];
    //속령수
    $nationItem['cityCnt'] = $cityCntList[$staticNationID] ?? 0;
    if ($staticNationID !== $nationID) {
        $diplomacyItem = $dipStateList[$staticNationID];

        $nationItem['diplomacy'] = [
            'state' => $diplomacyItem['state'],
            'term' => $diplomacyItem['term'],
        ];
    } else {
        $nationItem['diplomacy'] = [
            'state' => 7,
            'term' => null,
        ];
    }
}



// 수입 연산
$goldIncome  = getGoldIncome(
    $nation['nation'],
    $nation['level'],
    $nation['rate'],
    $nation['capital'],
    $nation['type'],
    $cityList
);
$warIncome  = getWarGoldIncome($nation['type'], $cityList);

$riceIncome = getRiceIncome(
    $nation['nation'],
    $nation['level'],
    $nation['rate'],
    $nation['capital'],
    $nation['type'],
    $cityList
);
$wallIncome = getWallIncome(
    $nation['nation'],
    $nation['level'],
    $nation['rate'],
    $nation['capital'],
    $nation['type'],
    $cityList
);

$incomes = [
    'gold' => [
        'city' => $goldIncome,
        'war' => $warIncome,
    ],
    'rice' => [
        'city' => $riceIncome,
        'wall' => $wallIncome,
    ],
];

$outcome = getOutcome(100, $dedicationList);
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=500" />
    <title><?= UniqueConst::$serverName ?>: 내무부</title>
    <?= WebUtil::printStaticValues([
        'staticValues' => [
            'editable' => ($me['officer_level'] >= 5 || $permission == 4),
            'nationMsg' => $nationStor->notice ?? '',
            'scoutMsg' => $nationStor->scout_msg ?? '',
            'nationID' => $nationID,
            'officerLevel' => $me['officer_level'],
            'year' => $year,
            'month' => $month,
            'nationsList' => $nationsList,

            'gold' => $nation['gold'],
            'rice' => $nation['rice'],
            'income' => $incomeList,
            'outcome' => $outcome,

            'policy' => [
                'rate' => $nation['rate'],
                'bill' => $nation['bill'],
                'secretLimit' => $nation['secretlimit'],
                'blockScout' => $nation['scout']!=0,
                'blockWar' => $nation['war']!=0,
            ],
            'warSettingCnt' => [
                'remain' => $nationStor->getValue('available_war_setting_cnt'),
                'inc' => GameConst::$incAvailableWarSettingCnt,
                'max' => GameConst::$maxAvailableWarSettingCnt
            ],
        ]
    ]) ?>
    <?= WebUtil::printJS('../d_shared/common_path.js') ?>
    <?= WebUtil::printCSS('../d_shared/common.css') ?>
    <link rel="stylesheet" type="text/css"
        href="https://cdn.jsdelivr.net/gh/orioncactus/pretendard/dist/web/static/pretendard.css" />
    <?= WebUtil::printDist('vue', 'v_nationStratFinan', true) ?>
</head>

<body>
    <div id="app"></div>
</body>

</html>