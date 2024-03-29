<?php

namespace sammo;

use sammo\Enums\GeneralQueryMode;

include "lib.php";
include "func.php";

$btn = Util::getPost('btn');
$gen = Util::getPost('gen', 'int', 0);
$reqQueryType = Util::getPost('query_type', 'string', null);

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

if ($session->userGrade < 5) {
    die(requireAdminPermissionHTML());
}

$db = DB::db();

if ($btn == '정렬하기') {
    $gen = 0;
}

[$queryTypeText, $reqArgType, $comp] = $queryMap[$reqQueryType];
if ($reqArgType === 0) {
    $generalBasicList = $db->query('SELECT no, name, nation, npc, turntime FROM general');
} else if ($reqArgType === 1) {
    $generalBasicList = $db->query('SELECT no, name, nation, npc, turntime, %b FROM general', $reqQueryType);
} else if ($reqArgType === 2) {
    $generalBasicList = $db->query(
        'SELECT no, name, nation, npc, turntime, value as %b
        FROM general LEFT JOIN rank_data
        ON general.no = rank_data.general_id
        WHERE rank_data.type = %s',
        $reqQueryType,
        $reqQueryType
    );
} else if ($reqArgType === 3) {
    $generalBasicList = array_map(function ($arr) {
        $arr['aux'] = Json::decode($arr['aux']);
        return $arr;
    }, $db->query('SELECT no, name, nation, npc, turntime, aux FROM general'));
} else {
    throw new \sammo\MustNotBeReachedException();
}

usort($generalBasicList, $comp);

if (!$gen) {
    $gen = $generalBasicList[0]['no'];
}

$generalObj = General::createObjFromDB($gen, null, GeneralQueryMode::FullWithAccessLog);

?>
<!DOCTYPE html>
<html>

<head>
    <title>로그정보</title>
    <meta charset="UTF-8">
    <meta name="color-scheme" content="dark">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=1024" />
    <?= WebUtil::printCSS('../d_shared/common.css') ?>
    <?= WebUtil::printDist('ts', 'common', true) ?>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/gh/orioncactus/pretendard/dist/web/static/pretendard.css" />
</head>

<body>
    <table align=center width=1000 class='tb_layout bg0'>
        <tr>
            <td>로 그 정 보<br><?= closeButton() ?></td>
        </tr>
        <tr>
            <td>
                <form name=form1 method=post>
                    정렬순서 :
                    <select name='query_type' size=1>
                        <?php foreach ($queryMap as $queryType => [$queryTypeText,]) : ?>
                            <option <?= $queryType == $reqQueryType ? 'selected' : '' ?> value='<?= $queryType ?>'><?= $queryTypeText ?></option>
                        <?php endforeach; ?>
                    </select>
                    <input type=submit name=btn value='정렬하기'>
                    대상장수 :
                    <select name=gen size=1>
                        <?php foreach ($generalBasicList as $general) : ?>
                            <option <?= $gen == $general['no'] ? 'selected' : '' ?> value='<?= $general['no'] ?>'><?= $general['name'] ?> (<?= substr($general['turntime'], 14, 5) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                    <input type=submit name=btn value='조회하기'>
                </form>
            </td>
        </tr>
    </table>
    <table width=1000 align=center class='tb_layout bg0'>
        <tr>
            <td width=50% align=center class='bg1'>
                <font color=skyblue size=3>장 수 정 보</font>
            </td>
            <td width=50% align=center class='bg1'>
                <font color=orange size=3>-</font>
            </td>
        </tr>
        <tr>
            <td valign=top>
                <?php generalInfo($generalObj);
                generalInfo2($generalObj); ?>
            </td>
            <td valign=top>&nbsp;
            </td>
        </tr>
        <tr>
            <td align=center class='bg1'>
                <font color=skyblue size=3>개인 기록</font>
            </td>
            <td align=center class='bg1'>
                <font color=orange size=3>전투 기록</font>
            </td>
        </tr>
        <tr>
            <td valign=top>
                <?= formatHistoryToHTML(getGeneralActionLogRecent($gen, 24)) ?>
            </td>
            <td valign=top>
                <?= formatHistoryToHTML(getBattleDetailLogRecent($gen, 24)) ?>
            </td>
        </tr>
        <tr>
            <td align=center class='bg1'>
                <font color=skyblue size=3>장수 열전</font>
            </td>
            <td align=center class='bg1'>
                <font color=orange size=3>전투 결과</font>
            </td>
        </tr>
        <tr>
            <td valign=top>
                <?= formatHistoryToHTML(getGeneralHistoryLogAll($gen)) ?>
            </td>
            <td valign=top>
                <?= formatHistoryToHTML(getBattleResultRecent($gen, 24)) ?>
            </td>
        </tr>
    </table>
    <table align=center width=1000 class='tb_layout bg0'>
        <tr>
            <td><?= closeButton() ?></td>
        </tr>
        <tr>
            <td><?= banner() ?> </td>
        </tr>
    </table>
</body>

</html>