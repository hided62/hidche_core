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

?>
<!DOCTYPE html>
<html>

<head>
    <title>회원관리</title>
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
            <td>회 원 관 리<br><?= backButton() ?></td>
        </tr>
    </table>
    <form name=form1 method=post action=_admin2_submit.php>
        <table align=center width=1000 class='tb_layout bg0'>
            <tr>
                <td width=80 align=center>접속제한</td>
                <td width=713 align=center><input type=submit name=btn value='전체 접속허용'><input type=submit name=btn value='전체 접속제한'></td>
            </tr>
        </table>
        <table align=center width=1000 class='tb_layout bg0'>
            <tr>
                <td width=80 align=center rowspan=12>회원선택<br><br>
                    <font color=cyan>NPC</font><br>
                    <font color=skyblue>NPC유저</font><br>
                    <font color=red>접속제한</font><br><b style=background-color:red;>블럭회원</b>
                </td>
                <td width=105 rowspan=12>
                    <select name=genlist[] size=20 multiple style='color:white;background-color:black;font-size:14px'>
                        <?php
                        $generalList = $db->query('SELECT `no`, `name`, npc, `block` FROM general ORDER BY npc, binary(`name`)');

                        foreach ($generalList as $general) {
                            $style = "style=;";
                            if ($general['block']         > 0) {
                                $style .= "background-color:red;";
                            }

                            $npcColor = getNPCColor($general['npc']);
                            if ($npcColor !== null) {
                                $style .= "color:{$npcColor};";
                            }

                            echo "<option value={$general['no']} $style>{$general['name']}</option>";
                        }
                        ?>
                    </select>
                </td>
            </tr>
            <tr>
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
                <td align=center>이벤트2</td>
                <td><input type=submit name=btn value='보숙10000'><input type=submit name=btn value='궁숙10000'><input type=submit name=btn value='기숙10000'><input type=submit name=btn value='귀숙10000'><input type=submit name=btn value='차숙10000'></td>
            </tr>
            <tr>
                <td align=center>접속제한</td>
                <td><input type=submit name=btn value='접속 허용'><input type=submit name=btn value='접속 제한'></td>
            </tr>
            <tr>
                <td align=center>명령 설정</td>
                <td><input type=submit name=btn value='하야입력'><input type=submit name=btn value='방랑해산'></td>
            </tr>
            <tr>
                <td align=center>메세지 전달</td>
                <td><input type=textarea size=60 maxlength=255 name=msg style=background-color:black;color:white;><input type=submit name=btn value='메세지 전달'></td>
            </tr>
        </table>
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