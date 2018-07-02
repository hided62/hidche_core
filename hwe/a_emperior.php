<?php
namespace sammo;

include "lib.php";
include "func.php";

$select = Util::getReq('select', 'int', 0);

$db = DB::db();

$templates = new \League\Plates\Engine('templates');
$templates->registerFunction('ConvertLog', '\sammo\ConvertLog');
$templates->registerFunction('newColor', '\sammo\newColor');
increaseRefresh("왕조일람", 1);
?>
<!DOCTYPE html>
<html>

<head>
<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=1024" />
<title><?=UniqueConst::$serverName?>: 왕조일람</title>
<?=WebUtil::printCSS('../d_shared/common.css')?>
<?=WebUtil::printCSS('css/common.css')?>

</head>

<body>
<table align=center width=1000 class="tb_layout bg0">
    <tr><td>역 대 왕 조<br>
        <button onclick=window.close()>창 닫기</button><br>
    </td></tr>
</table>

<?php

if ($select == 0) {
    foreach($db->query('SELECT * FROM emperior ORDER BY `no` DESC') as $emperior){

?>

<table align=center width=1000 class='tb_layout bg0'>
    <tr>
        <td bgcolor=skyblue align=center colspan=8>
            <font size=5><?=$emperior['phase']?></font>
            
            <a href="a_emperior.php?select=<?=$emperior['no']?>"><button>자세히</button></a>
            
            <?php if($emperior['server_id']): ?>
            <a href="a_history.php?serverID=<?=$emperior['server_id']?>"><button>역사 보기</button></a>
            <?php endif ?>
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
$serverID = $emperior['server_id']??($emperior['serverID']??null);

?>
<table align=center width=1000 class='tb_layout bg0'>
    <tr>
        <td bgcolor=skyblue align=center colspan=6>
            <font size=5><?=$emperior['phase']?></font>
            <a href="a_emperior.php"><button>전체보기</button></a>
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

<?php
if($serverID){
    $nations = $db->query('SELECT * FROM ng_old_nations WHERE server_id=%s ORDER BY date DESC', $serverID);
    foreach($nations as $nation){
        if(!$nation['nation']??null){
            continue;
        }
        $nation += Json::decode($nation['data']);

        $nation['typeName'] = getNationType($nation['type']);
        $nation['levelName'] = getNationLevel($nation['level']);
        if($nation['generals']){
            $generals = $db->query('SELECT `general_no`, `name` FROM ng_old_generals WHERE server_id=%s AND general_no IN %li', $serverID, $nation['generals']);
            $nation['generalsFull'] = $generals;
        }
        else{
            $nation['generalsFull'] = [];
        }
        
        
        echo $templates->render('oldNation', $nation);
    }
}
?>

<table align=center width=1000 class='tb_layout bg0'>
    <tr><td><?=closeButton()?></td></tr>
    <tr><td><?=banner()?></td></tr>
</table>
</body>
</html>