<?php

namespace sammo;

include "lib.php";
include "func.php";

$v = new Validator($_POST + $_GET);
$v->rule('required', 'gen')
    ->rule('integer', 'gen');

$btn = Util::getReq('btn');
$gen = Util::getReq('gen', 'int', 0);
$reqQueryType = Util::getReq('query_type', 'string', null);

// $queryTypeText, $reqArgType(0=>None, 1=>AdditionalColumn, 2=>rankVal, 3=>aux), $comp
$queryMap = [
    'turntime' => ['최근턴', 0, function ($lhs, $rhs) {
        return - ($lhs['turntime'] <=> $rhs['turntime']);
    }],
    'recent_war' => ['최근전투', 1, function ($lhs, $rhs) {
        return - ($lhs['recent_war'] <=> $rhs['recent_war']);
    }],
    'name' => ['장수명', 0, function ($lhs, $rhs) {
        if ($lhs['npc'] !== $rhs['npc']) {
            return $lhs['npc'] <=> $rhs['npc'];
        }
        return $lhs['name'] <=> $rhs['name'];
    }],
    'warnum' => ['전투수', 2, function ($lhs, $rhs) {
        return - ($lhs['warnum'] <=> $rhs['warnum']);
    }]
];

if ($reqQueryType === null || !key_exists($reqQueryType, $queryMap)) {
    $reqQueryType = Util::array_first_key($queryMap);
}


//로그인 검사
$session = Session::requireGameLogin()->setReadOnly();
$userID = Session::getUserID();

$db = DB::db();
$gameStor = KVStorage::getStorage($db, 'game_env');

increaseRefresh("감찰부", 2);
//전투 추진을 위해 갱신
TurnExecutionHelper::executeAllCommand();
$gameStor->resetCache();

$testGeneralNationID = $db->queryFirstField('SELECT nation FROM general WHERE no = %i', $gen);

$me = $db->queryFirstRow('SELECT no,nation,officer_level,con,turntime,belong,permission,penalty from general where owner=%i', $userID);
$nationID = $me['nation'];

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

//잘못된 접근
if ($testGeneralNationID != $nationID) {
    $gen = 0;
}

if ($btn == '정렬') {
    $gen = 0;
}


[$queryTypeText, $reqArgType, $comp] = $queryMap[$reqQueryType];
if ($reqArgType === 0) {
    $generalBasicList = $db->query('SELECT no, name, npc, turntime, officer_level FROM general WHERE nation = %i', $nationID);
} else if ($reqArgType === 1) {
    $generalBasicList = $db->query('SELECT no, name, npc, turntime, officer_level, %b FROM general WHERE nation = %i', $reqQueryType, $nationID);
} else if ($reqArgType === 2) {
    $generalBasicList = $db->query(
        'SELECT no, name, npc, turntime, officer_level, value as %b
        FROM general LEFT JOIN rank_data
        ON general.no = rank_data.general_id
        WHERE rank_data.type = %s AND general.nation = %i',
        $reqQueryType,
        $reqQueryType,
        $nationID
    );
} else if ($reqArgType === 3) {
    $generalBasicList = array_map(function ($arr) {
        $arr['aux'] = Json::decode($arr['aux']);
        return $arr;
    }, $db->query('SELECT no, name, npc, turntime, officer_level, aux FROM general WHERE nation = %i', $nationID));
} else {
    throw new \sammo\MustNotBeReachedException();
}


usort($generalBasicList, $comp);

if (!$gen) {
    $gen = $generalBasicList[0]['no'];
}
$showGeneral = General::createGeneralObjFromDB($gen);
?>
<!DOCTYPE html>
<html>

<head>
    <title><?= UniqueConst::$serverName ?>: 감찰부</title>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=500" />
    <?= WebUtil::printJS('../d_shared/common_path.js') ?>
    <?= WebUtil::printCSS('../d_shared/common.css') ?>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/gh/orioncactus/pretendard/dist/web/static/pretendard.css" />
    <?= WebUtil::printDist('ts', ['common', 'battleCenter']) ?>
</head>

<body>
    <div id="container" class="bg0">
        <div class="row gx-0">
            <div class="col">감 찰 부<br><?= closeButton() ?></div>
        </div>
        <div class="row gx-0">
            <div class="col-12" style="border-left: solid 1px gray; border-right:solid 1px gray;">
                <form name=form1 method=get>
                    정렬순서 :
                    <select name='query_type' size=1>
                        <?php foreach ($queryMap as $queryType => [$queryTypeText,]) : ?>
                            <option <?= $queryType == $reqQueryType ? 'selected' : '' ?> value='<?= $queryType ?>'><?= $queryTypeText ?></option>
                        <?php endforeach; ?>
                    </select>
                    <input type=submit name=btn value='정렬'>
                    대상장수 :
                    <select name=gen size=1>
                        <?php foreach ($generalBasicList as $general) : ?>
                            <option <?= $gen == $general['no'] ? 'selected' : '' ?> value='<?= $general['no'] ?>'><?= $general['officer_level'] > 4 ? "*{$general['name']}*" : $general['name'] ?> (<?= substr($general['turntime'], 14, 5) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                    <input type=submit name=btn value='조회'>
                </form>
            </div>
            <div class="col col-12 col-md-6">
                <div class="row mx-0">
                    <div class="col bg1 header-cell" style="color:skyblue">장수 정보</div>
                </div>
                <div class="row">
                    <div class="col"><?php generalInfo($showGeneral); ?><?php generalInfo2($showGeneral); ?></div>
                </div>
            </div>

            <div class="col col-12 col-md-6">
                <div class="row mx-0">
                    <div class="col bg1 header-cell">장수 열전</div>
                </div>
                <div class="row">
                    <div class="col"><?= formatHistoryToHTML(getGeneralHistoryLogAll($gen)) ?></div>
                </div>
            </div>

            <div class="col col-12 col-md-6">
                <div class="row mx-0">
                    <div class="col bg1 header-cell">전투 기록</div>
                </div>
                <div class="row">
                    <div class="col"><?= formatHistoryToHTML(getBattleDetailLogRecent($gen, 24)) ?></div>
                </div>
            </div>

            <div class="col col-12 col-md-6">
                <div class="row mx-0">
                    <div class="col bg1 header-cell">전투 결과</div>
                </div>
                <div class="row">
                    <div class="col"><?= formatHistoryToHTML(getBattleResultRecent($gen, 24)) ?></div>
                </div>
            </div>

            <?php if ($showGeneral->getNPCType() > 1 || $permission >= 2) : ?>
                <div class="col col-12 col-md-6">
                    <div class="row mx-0">
                        <div class="col bg1 header-cell">개인 기록</div>
                    </div>
                    <div class="row">
                        <div class="col"><?= formatHistoryToHTML(getGeneralActionLogRecent($gen, 24)) ?></div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <div class="row gx-0">
            <div class="col "><?= backButton() ?></div>
        </div>
        <div class="row bg0 gx-0">
            <div class="col bg0"><?= banner() ?></div>
        </div>
    </div>
</body>

</html>