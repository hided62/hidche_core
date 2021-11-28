<?php

namespace sammo;


/**
 * 템플릿 생성과 관련된 함수들을 모아두는 파일
 *
 * NOTE: 아직 converter와 명확한 구분이 되어있지 않음.
 * TODO: legacy template들을 전부 template 폴더로 모아둘 필요 있음
 * TODO: side effect를 제거
 */

 /**
  * 관리자 권한이 필요함을 출력.
  * @return void
  */
function requireAdminPermissionHTML()
{
    ob_start();
?>
    <!DOCTYPE html>
    <html>

    <head>
        <title>관리메뉴</title>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=1024" />
        <?= WebUtil::printCSS('../d_shared/common.css') ?>
        <?= WebUtil::printCSS('css/common.css') ?>
    </head>

    <body>
        관리자가 아닙니다.<br>
        <?= banner() ?>
    </body>

    </html>
<?php
    return ob_get_clean();
}

function chiefTurnTable()
{
    $turnList = [];
    $turnList[] = "<option selected value='0'>1턴</option>";
    foreach (Util::range(1, GameConst::$maxChiefTurn) as $turnIdx) {
        $turnText = $turnIdx + 1;
        $turnList[] = "<option value='{$turnIdx}'>{$turnText}턴</option>";
    }

    $turnText = join("\n", $turnList);

    return "
<select id='chiefTurnSelector' name=turn[] size=6 multiple>
{$turnText}
</select>
";
}

function displayiActionObjInfo(?iAction $action)
{
    if ($action === null) {
        $info = '';
        $text = '-';
    } else {
        $info = $action->getInfo();
        $text = $action->getName();
    }

    $templates = new \League\Plates\Engine(__DIR__ . '/templates');

    return $templates->render('tooltip', [
        'text' => $text,
        'info' => $info,
    ]);
}
function displayCharInfo(string $type): string
{
    $class = buildPersonalityClass($type);
    $info = $class->getInfo();
    $text = $class->getName();

    $templates = new \League\Plates\Engine(__DIR__ . '/templates');

    return $templates->render('tooltip', [
        'text' => $text,
        'info' => $info,
    ]);
}

function displaySpecialWarInfo(?string $type): string
{
    $class = buildGeneralSpecialWarClass($type);
    $info = $class->getInfo();
    $name = $class->getName();

    $templates = new \League\Plates\Engine(__DIR__ . '/templates');

    return $templates->render('tooltip', [
        'text' => $name,
        'info' => $info,
    ]);
}

function displaySpecialDomesticInfo(?string $type): string
{
    $class = buildGeneralSpecialDomesticClass($type);
    $info = $class->getInfo();
    $name = $class->getName();

    $templates = new \League\Plates\Engine(__DIR__ . '/templates');

    return $templates->render('tooltip', [
        'text' => $name,
        'info' => $info,
    ]);
}

function allButton(bool $seizeNPCMode)
{
    if ($seizeNPCMode) {
        $site = "a_npcList.php";
        $call = "빙의일람";
    } else {
        $site = "a_vote.php";
        $call = "설문조사";
    }

    if (\file_exists(__DIR__ . "/d_setting/templates/allButton.php")) {
        $templates = new \League\Plates\Engine(__DIR__ . '/d_setting/templates');
    } else {
        $templates = new \League\Plates\Engine(__DIR__ . '/templates');
    }


    return $templates->render('allButton', [
        'call' => $call,
        'site' => $site
    ]);
}


function commandButton()
{
    $session = Session::getInstance();
    $userID = Session::getUserID();
    if (!$session->isGameLoggedIn()) {
        return '';
    }

    $db = DB::db();
    $me = $db->queryFirstRow("select no,nation,officer_level,belong,permission,penalty from general where owner=%i", $userID);

    $nation = $db->queryFirstRow("select nation,level,color,secretlimit from nation where nation=%i", $me['nation']) ?? [
        'nation' => 0,
        'level' => 0,
        'secretlimit' => 99,
        'color' => '#000000'
    ];

    $bgColor = Util::array_get($nation['color']) ?: '#000000';
    $fgColor = newColor($bgColor);

    $templates = new \League\Plates\Engine(__DIR__ . '/templates');
    $showSecret = false;
    $permission = checkSecretPermission($me);
    if ($permission >= 1) {
        $showSecret = true;
    } else if ($me['officer_level'] == 0) {
        $showSecret = false;
    }

    return $templates->render('commandButton', [
        'bgColor' => $bgColor,
        'fgColor' => $fgColor,
        'meLevel' => $me['officer_level'],
        'nationLevel' => $nation['level'],
        'showSecret' => $showSecret,
        'permission' => $permission,
    ]);
}

function formatWounded(int $value, int $wound): string
{
    if ($wound == 0) {
        return "$value";
    }
    $woundedValue = intdiv($value * (100 - $wound), 100);
    return "<font color=red>$woundedValue</font>";
}

function formatDefenceTrain(int $value): string
{
    if ($value === 999) {
        return "×";
    } else if ($value >= 90){
        return "☆";
    } else if ($value >= 80) {
        return "◎";
    } else if ($value >= 60) {
        return "○";
    } else{
        return "△";
    }
}

function formatLeadershipBonus(int $value): string
{
    if ($value == 0) {
        return '';
    }
    return "<font color=cyan>+{$value}</font>";
}

function formatName(string $name, int $npc): string
{
    if ($npc == 1) {
        $name = "<font color='skyblue'>$name</font>";
    } else if ($npc > 1) {
        $name = "<font color='cyan'>$name</font>";
    }
    return $name;
}

function getMapHtml(?string $mapTheme = null)
{
    $templates = new \League\Plates\Engine(__DIR__ . '/templates');

    if ($mapTheme === null) {
        $db = DB::db();
        $gameStor = KVStorage::getStorage($db, 'game_env');
        $mapTheme = $gameStor->map_theme ?? 'che';
    }

    return $templates->render('map', [
        'mapTheme' => $mapTheme
    ]);
}

function getInvitationList(array $nationList)
{
    $templates = new \League\Plates\Engine(__DIR__ . '/templates');

    foreach ($nationList as &$nation) {
        $nation['textColor'] = newColor($nation['color']);
    }
    return $templates->render('invitationList', [
        'nationList' => $nationList
    ]);
}
