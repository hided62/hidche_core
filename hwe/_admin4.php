<?php

namespace sammo;

include "lib.php";
include "func.php";
//로그인 검사
$session = Session::requireGameLogin()->setReadOnly();

if ($session->userGrade < 5) {
    die(requireAdminPermissionHTML());
}

$db = DB::db();
$gameStor = KVStorage::getStorage($db, 'game_env');

$conlimit = $gameStor->conlimit;

$ipGroupList = Util::arrayGroupBy(
    $db->query(
        'SELECT name,ip,lastconnect,owner,block,substring_index(ip,".",3) as ip_c from general WHERE ip!="" and npc<2'
    ),
    'ip_c'
);

function colorBlockedName($general){
    if(!$general['blocked']){
        return $general['name'];
    }
    return "<span style='color:magenta;'>{$general['name']}</span>";
}

?>
<!DOCTYPE html>
<html>

<head>
    <title>멀티관리</title>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=1024" />
    <?= WebUtil::printCSS('../d_shared/common.css') ?>
    <?= WebUtil::printCSS('dist_css/common.css') ?>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/gh/orioncactus/pretendard/dist/web/static/pretendard.css" />
</head>

<body>
    <table align=center width=1000 class='tb_layout bg0'>
        <tr>
            <td>멀 티 관 리<br><?= backButton() ?></td>
        </tr>
    </table>
    <form name=form1 method=post action=_admin4_submit.php>
        <table align=center width=1000 class='tb_layout bg0'>
            <tr>
                <td width=80 align=center rowspan=3>회원선택<br><br>
                    <font color=cyan>NPC</font><br>
                    <font color=skyblue>NPC유저</font><br>
                    <font color=red>접속제한</font><br><b style=background-color:red;>블럭회원</b>
                </td>
                <td width=105 rowspan=3>
                    <?php

                    echo "
            <select name=genlist[] size=20 multiple style='color:white;background-color:black;font-size:14px'>";

                    foreach ($db->query('SELECT no,name,npc,block,con from general where ip!=\'\' order by npc,ip') as $general) {
                        $style = "style=;";
                        if ($general['block']         > 0) {
                            $style .= "background-color:red;";
                        }
                        if ($general['npc']          >= 2) {
                            $style .= "color:cyan;";
                        } elseif ($general['npc']      == 1) {
                            $style .= "color:skyblue;";
                        }
                        if ($general['con'] > $conlimit) {
                            $style .= "color:red;";
                        }

                        echo "
                <option value={$general['no']} $style>{$general['name']}</option>";
                    }

                    echo "
            </select>";
                    ?>
                </td>
                <td width=100 align=center>블럭</td>
                <td width=504>
                    <input type=submit name=btn value='블럭 해제'><input type=submit name=btn value='1단계 블럭'><input type=submit name=btn value='2단계 블럭'><input type=submit name=btn value='3단계 블럭'><input type=submit name=btn value='무한삭턴'><br>
                    1단계:발언권, 2단계:턴블럭
                </td>
            </tr>
            <tr>
                <td align=center>강제 사망</td>
                <td><input type=submit name=btn value='강제 사망'></td>
            </tr>
            <tr>
                <td align=center>메세지 전달</td>
                <td><input type=textarea size=60 maxlength=255 name=msg style=background-color:black;color:white;><input type=submit name=btn value='메세지 전달'></td>
            </tr>
        </table>
        <table align=center width=1000 class='tb_layout bg0'>
            <tr>
                <td align=center width=100>장수명</td>
                <td align=center width=180>최근로그인</td>
                <td align=center width=129>IP</td>
                <td align=center width=100>ID</td>
            </tr>
<?php foreach($ipGroupList as $ipGroupC=>$users): ?>
    <tr>
        <td><?=join('<br>',array_map('\sammo\colorBlockedName', $users))?></td>
        <td><?=join('<br>',array_column($users, 'lastconnect'))?></td>
        <td><?=join('<br>',array_column($users, 'ip'))?></td>
        <td><?=join('<br>',array_column($users, 'owner'))?></td>
    </tr>
<?php endforeach; ?>
        </table>
        <?php
        //NOTE: password의 md5 해시가 같은지 확인하는 방식으로는 앞으로 잡아낼 수 없다. 폐기
        ?>
    </form>
    <table align=center width=1000 class='tb_layout bg0'>
        <tr>
            <td><?= backButton() ?></td>
        </tr>
        <tr>
            <td><?= banner() ?> </td>
        </tr>
    </table>
</body>

</html>