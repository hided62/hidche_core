<?php

namespace sammo;

include "lib.php";
include "func.php";
//로그인 검사
$session = Session::requireGameLogin()->setReadOnly();
$userID = Session::getUserID();

$db = DB::db();
$gameStor = KVStorage::getStorage($db, 'game_env');

increaseRefresh("세력도", 2);
TurnExecutionHelper::executeAllCommand();

$mapTheme = $gameStor->map_theme ?? 'che';

$me = $db->queryFirstRow('SELECT con,turntime from general where owner=%i', $userID);

$con = checkLimit($me['con']);
if ($con >= 2) {
    printLimitMsg($me['turntime']);
    exit();
}
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=1024" />
    <title><?= UniqueConst::$serverName ?>: 세력도</title>
    <?= WebUtil::printCSS('../d_shared/common.css') ?>
    <?= WebUtil::printCSS('css/normalize.css') ?>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/gh/orioncactus/pretendard/dist/web/static/pretendard.css" />
    <?= WebUtil::printCSS('css/map.css') ?>
    <?= WebUtil::printJS('../d_shared/common_path.js') ?>
    <?= WebUtil::printJS('d_shared/base_map.js') ?>
    <?= WebUtil::printDist('ts', ['common', 'map']) ?>
    <script>
        window.serverNick = '<?= DB::prefix() ?>';
        window.serverID = '<?= UniqueConst::$serverID ?>';
        $(function() {

            reloadWorldMap({
                neutralView: true,
                showMe: true,
                useCachedMap: true
            });

        });
    </script>
</head>

<body>
    <table align=center width=1200 class='tb_layout bg0'>
        <tr>
            <td>세 력 도<br><?= closeButton() ?></td>
        </tr>
    </table>
    <table align=center width=1200 height=520 class='tb_layout bg0'>
        <tr height=520>
            <td width=498 valign=top>
                <?= formatHistoryToHTML(getGlobalActionLogRecent(34)) ?>
            </td>
            <td width=698>
                <?= getMapHtml($mapTheme) ?>
            </td>
        </tr>
        <tr>
            <td colspan=2 valign=top>
                <?= formatHistoryToHTML(getGlobalHistoryLogRecent(34)) ?>
            </td>
        </tr>
    </table>
    <table align=center width=1200 class='tb_layout bg0'>
        <tr>
            <td><?= closeButton() ?></td>
        </tr>
        <tr>
            <td><?= banner() ?> </td>
        </tr>
    </table>
</body>

</html>