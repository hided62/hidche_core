<?php
namespace sammo;

include "lib.php";
include "func.php";

$select = Util::getReq('select', 'int', 0);

extractMissingPostToGlobals();

$db = DB::db();
$connect=$db->get();

increaseRefresh("왕조일람", 1);
?>
<!DOCTYPE html>
<html>

<head>
<meta HTTP-EQUIV='Content-Type' CONTENT='text/html; charset=utf-8'>
<title><?=UniqueConst::$serverName?>: 왕조일람</title>
<?=WebUtil::printCSS('../d_shared/common.css')?>
<?=WebUtil::printCSS('css/common.css')?>

</head>

<body>
<table align=center width=1000 class="tb_layout bg0">
    <tr><td>역 대 왕 조<br>
        <input type=button value='창 닫기' onclick=window.close()><br>
    </td></tr>
</table>

<?php

if ($select == 0) {
    $query = "select * from emperior order by no desc";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect), "");
    $empcount = MYDB_num_rows($result);

    for ($i=0; $i < $empcount; $i++) {
        $emperior = MYDB_fetch_array($result);

?>

<table align=center width=1000 class='tb_layout bg0'>
    <tr>
        <td bgcolor=skyblue align=center colspan=8>
        <form action=a_emperior.php method=get>
            <font size=5><?=$emperior['phase']?></font>
            
            <input type=submit value='자세히'>
            <input type=hidden name=select value='<?=$emperior['no']?>'>
            
            <?php if($emperior['server_id']): ?>
            <a href="a_history.php?server_id=<?=$emperior['server_id']?>"><button type="button">역사 보기</button></a>
            <?php endif ?>
        </form>
        </td>
    </tr>
    <tr>
        <td align=center style=color:".newColor($emperior['color'])."; bgcolor=<?=$emperior['color']?> colspan=8>
            <font size=5><?=$emperior['name']?> (<?=$emperior['year']?>年 <?=$emperior['month']?>月)</font>
        </td>
    </tr>
    <tr>
        <td id=bg1 align=center width=80>국 력</td>
        <td align=center width=170><?=$emperior['power']?></td>
        <td id=bg1 align=center width=80>장 수</td>
        <td align=center width=170><?=$emperior['gennum']?></td>
        <td id=bg1 align=center width=80>속 령</td>
        <td align=center width=170><?=$emperior['citynum']?></td>
        <td id=bg1 align=center width=80>성 향</td>
        <td align=center width=170><?=$emperior['type']?></td>
    </tr>
    <tr>
        <td id=bg1 align=center>황 제</td>
        <td align=center><?=$emperior['l12name']?></td>
        <td id=bg1 align=center>승 상</td>
        <td align=center><?=$emperior['l11name']?></td>
        <td id=bg1 align=center>위 장 군</td>
        <td align=center><?=$emperior['l10name']?></td>
        <td id=bg1 align=center>사 공</td>
        <td align=center><?=$emperior['l9name']?></td>
    </tr>
</table>



<?php
    }
?>
<table align=center width=1000 class='tb_layout bg0'>
    <tr><td><?=closeButton()?></td></tr>
    <tr><td><?=banner()?></td></tr>
</table>
</body>
</html>
<?php
    die();
}

$emperior = $db->queryFirstRow('SELECT * FROM emperior WHERE `no`=%i',$select);

?>
<table align=center width=1000 class='tb_layout bg0'>
    <tr>
        <td bgcolor=skyblue align=center colspan=6>
            <form action=a_emperior.php method=get>
            <font size=5><?=$emperior['phase']?></font>
            <input type=submit value='전체보기'>
            </form>
        </td>
    </tr>
    <tr>
        <td id=bg1 width=98 align=center>국 가 수<br>(최종 / 최대)</td>
        <td align=center width=398 colspan=2><?=$emperior['nation_count']?></td>
        <td id=bg1 width=98 align=center>장 수 수<br>(최종 / 최대)</td>
        <td align=center width=398 colspan=2><?=$emperior['gen_count']?></td>
    </tr>
    <tr>
        <td id=bg1 align=center>등 장 국 가</td>
        <td colspan=5><?=$emperior['nation_name']?></td>
    </tr>
    <tr>
        <td id=bg1 align=center>국가별 성향</td>
        <td colspan=5><?=$emperior['nation_hist']?></td>
    </tr>
    <tr>
        <td id=bg1 align=center>장 수 성 격</td>
        <td colspan=5><?=$emperior['personal_hist']?></td>
    </tr>
    <tr>
        <td id=bg1 align=center>장 수 특 기</td>
        <td colspan=5><?=$emperior['special_hist']?></td>
    </tr>
    <tr>
        <td align=center style=color:<?=newColor($emperior['color'])?>; bgcolor=<?=$emperior['color']?> colspan=6>
            <font size=5><?=$emperior['name']?> (<?=$emperior['year']?>年 <?=$emperior['month']?>月)</font>
        </td>
    </tr>
    <tr>
        <td id=bg1 width=98 align=center>국 력</td>
        <td align=center width=398 colspan=2><?=$emperior['power']?></td>
        <td id=bg1 width=98 align=center>성 향</td>
        <td align=center width=398 colspan=2><?=$emperior['type']?></td>
    </tr>
    <tr>
        <td id=bg1 width=98 align=center>장 수</td>
        <td align=center width=398 colspan=2><?=$emperior['gennum']?></td>
        <td id=bg1 width=98 align=center>속 령</td>
        <td align=center width=398 colspan=2><?=$emperior['citynum']?></td>
    </tr>
    <tr>
        <td id=bg1 align=center>총 인 구</td>
        <td align=center colspan=2><?=$emperior['pop']?></td>
        <td id=bg1 align=center>인 구 율</td>
        <td align=center colspan=2><?=$emperior['poprate']?></td>
    </tr>
    <tr>
        <td id=bg1 align=center>국 고</td>
        <td align=center colspan=2><?=$emperior['gold']?></td>
        <td id=bg1 align=center>병 량</td>
        <td align=center colspan=2><?=$emperior['rice']?></td>
    </tr>
    <tr>
        <td id=bg1 align=center>황 제</td>
        <td width=64>&nbsp;</td>
        <td width=332><?=$emperior['l12name']?></td>
        <td id=bg1 align=center>승 상</td>
        <td width=64>&nbsp;</td>
        <td width=332><?=$emperior['l11name']?></td>
    </tr>
    <tr>
        <td id=bg1 align=center>위 장 군</td>
        <td width=64>&nbsp;</td>
        <td width=332><?=$emperior['l10name']?></td>
        <td id=bg1 align=center>사 공</td>
        <td width=64>&nbsp;</td>
        <td width=332><?=$emperior['l9name']?></td>
    </tr>
    <tr>
        <td id=bg1 align=center>표 기 장 군</td>
        <td width=64>&nbsp;</td>
        <td width=332><?=$emperior['l8name']?></td>
        <td id=bg1 align=center>태 위</td>
        <td width=64>&nbsp;</td>
        <td width=332><?=$emperior['l7name']?></td>
    </tr>
    <tr>
        <td id=bg1 align=center>거 기 장 군</td>
        <td width=64>&nbsp;</td>
        <td width=332><?=$emperior['l6name']?></td>
        <td id=bg1 align=center>사 도</td>
        <td width=64>&nbsp;</td>
        <td width=332><?=$emperior['l5name']?></td>
    </tr>
    <tr>
        <td id=bg1 align=center>오 호 장 군</td>
        <td colspan=5><?=$emperior['tiger']?></td>
    </tr>
    <tr>
        <td id=bg1 align=center>건 안 칠 자</td>
        <td colspan=5><?=$emperior['eagle']?></td>
    </tr>
    <tr>
        <td id=bg1 align=center>장 수 들<br>(공헌도 순서)</td>
        <td colspan=5><?=$emperior['gen']?></td>
    </tr>
    <tr>
        <td id=bg1 align=center>역 사 기 록</td>
        <td colspan=5><?=ConvertLog($emperior['history'], 1)?></td>
    </tr>
</table>

<table align=center width=1000 class='tb_layout bg0'>
    <tr><td><?=closeButton()?></td></tr>
    <tr><td><?=banner()?></td></tr>
</table>
</body>
</html>