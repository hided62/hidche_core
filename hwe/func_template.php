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
        <meta name="color-scheme" content="dark">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=1024" />
        <?= WebUtil::printCSS('../d_shared/common.css') ?>
        <?= WebUtil::printDist('ts', 'common', true) ?>
        <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/gh/orioncactus/pretendard/dist/web/static/pretendard.css" />
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

function templateLimitMsg(string $turntime): string
{
    return "이미 너무 많은 접속을 하셨습니다. 다음 턴에 다시 시도해주세요. (턴시간: {$turntime})";
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

function allButton(bool $seizeNPCMode, array $opts = [])
{
    if ($seizeNPCMode) {
        $site = "a_npcList.php";
        $call = "빙의일람";
    } else {
        $site = "v_vote.php";
        $call = "설문조사";
    }

    if (\file_exists(__DIR__ . "/d_setting/templates/allButton.php")) {
        $templates = new \League\Plates\Engine(__DIR__ . '/d_setting/templates');
    } else {
        $templates = new \League\Plates\Engine(__DIR__ . '/templates');
    }


    return $templates->render('allButton', array_merge([
        'call' => $call,
        'site' => $site
    ], $opts));
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
    } else if ($value >= 90) {
        return "☆";
    } else if ($value >= 80) {
        return "◎";
    } else if ($value >= 60) {
        return "○";
    } else {
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



function getMapTheme(): string
{
    return GameConst::$mapName;
}

function getMapHtml(?string $mapName = null)
{
    $templates = new \League\Plates\Engine(__DIR__ . '/templates');

    if ($mapName === null) {
        $mapName = GameConst::$mapName;
    }

    return $templates->render('map', [
        'mapName' => $mapName
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


function getAutorunInfo($autorunOption)
{
    $templates = new \League\Plates\Engine(__DIR__ . '/templates');
    $auto_info = [];
    foreach ($autorunOption['options'] as $auto_option => $value) {
        assert($value);
        switch ($auto_option) {
            case 'develop':
                $auto_info['내정'] = '내정';
                break;
            case 'warp':
                $auto_info['순간이동'] = '순간이동';
                break;
            case 'recruit':
                $auto_info['징병'] = $auto_info['징병'] ?? '징병';
                break;
            case 'recruit_high':
                $auto_info['징병'] = '모병';
                break;
            case 'train':
                $auto_info['훈사'] = '훈련/사기진작';
                break;
            case 'battle':
                $auto_info['출병'] = '출병';
                break;
            case 'chief':
                $auto_info['사령턴'] = '사령턴';
                break;
        }
    }
    $limit = Util::toInt($autorunOption['limit_minutes']);
    if ($limit >= 43200) {
        $auto_info['제한'] = '항상 유효';
    } else if ($limit % 60 == 0) {
        $auto_info['제한'] = ($limit / 60) . '시간 유효';
    } else {
        $auto_info['제한'] = ($limit) . '분 유효';
    }
    $auto_info = join(', ', array_values($auto_info));
    return $templates->render('tooltip', [
        'text' => '자율행동',
        'info' => $auto_info,
        'style' => 'text-decoration:underline',
        'copyable_info' => true
    ]);
}
