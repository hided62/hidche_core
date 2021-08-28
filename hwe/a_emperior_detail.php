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

$emperior = $db->queryFirstRow('SELECT * FROM emperior WHERE `no`=%i',$select);
$serverID = $emperior['server_id']??($emperior['serverID']??null);

?>

<!DOCTYPE html>
<html>

<head>
<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=1024" />
<title><?=UniqueConst::$serverName?>: 왕조일람</title>
<?=WebUtil::printCSS('../e_lib/bootstrap.min.css')?>
<?=WebUtil::printCSS('../d_shared/common.css')?>
<?=WebUtil::printCSS('css/common.css')?>

</head>

<body>
<table align=center width=1000 class="tb_layout bg0">
    <tr><td>역 대 왕 조<br>
        <button onclick=window.close()>창 닫기</button>
        <div style="float:right;"><a href="a_emperior.php"><button type='button'>전체보기</button></a><div>
    </td></tr>

</table>

<?php

if($emperior):
?>


<table align=center width=1000 class='tb_layout bg0'>
    <tr>
        <td bgcolor=skyblue align=center colspan=6>
            <font size=5><?=$emperior['phase']?></font>
        </td>
    </tr>
    <tr>
        <td class='bg1' width=98 align=center>국 가 수<br>(최종 / 최대)</td>
        <td align=center width=398 colspan=2><?=$emperior['nation_count']?></td>
        <td class='bg1' width=98 align=center>장 수 수<br>(최종 / 최대)</td>
        <td align=center width=398 colspan=2><?=$emperior['gen_count']?></td>
    </tr>
    <tr>
        <td class='bg1' align=center>등 장 국 가</td>
        <td colspan=5><?=$emperior['nation_name']?></td>
    </tr>
    <tr>
        <td class='bg1' align=center>국가별 성향</td>
        <td colspan=5><?=$emperior['nation_hist']?></td>
    </tr>
    <tr>
        <td class='bg1' align=center>장 수 성 격</td>
        <td colspan=5><?=$emperior['personal_hist']?></td>
    </tr>
    <tr>
        <td class='bg1' align=center>장 수 특 기</td>
        <td colspan=5><?=$emperior['special_hist']?></td>
    </tr>
    <tr>
        <td align=center style=color:<?=newColor($emperior['color'])?>; bgcolor=<?=$emperior['color']?> colspan=6>
            <font size=5><?=$emperior['name']?> (<?=$emperior['year']?>年 <?=$emperior['month']?>月)</font>
        </td>
    </tr>
    <tr>
        <td class='bg1' width=98 align=center>국 력</td>
        <td align=center width=398 colspan=2><?=$emperior['power']?></td>
        <td class='bg1' width=98 align=center>성 향</td>
        <td align=center width=398 colspan=2><?=$emperior['type']?></td>
    </tr>
    <tr>
        <td class='bg1' width=98 align=center>장 수</td>
        <td align=center width=398 colspan=2><?=$emperior['gennum']?></td>
        <td class='bg1' width=98 align=center>속 령</td>
        <td align=center width=398 colspan=2><?=$emperior['citynum']?></td>
    </tr>
    <tr>
        <td class='bg1' align=center>총 인 구</td>
        <td align=center colspan=2><?=$emperior['pop']?></td>
        <td class='bg1' align=center>인 구 율</td>
        <td align=center colspan=2><?=$emperior['poprate']?></td>
    </tr>
    <tr>
        <td class='bg1' align=center>국 고</td>
        <td align=center colspan=2><?=$emperior['gold']?></td>
        <td class='bg1' align=center>병 량</td>
        <td align=center colspan=2><?=$emperior['rice']?></td>
    </tr>
    <tr>
        <td class='bg1' align=center>황 제</td>
        <td width=64>&nbsp;</td>
        <td width=332><?=$emperior['l12name']?></td>
        <td class='bg1' align=center>승 상</td>
        <td width=64>&nbsp;</td>
        <td width=332><?=$emperior['l11name']?></td>
    </tr>
    <tr>
        <td class='bg1' align=center>위 장 군</td>
        <td width=64>&nbsp;</td>
        <td width=332><?=$emperior['l10name']?></td>
        <td class='bg1' align=center>사 공</td>
        <td width=64>&nbsp;</td>
        <td width=332><?=$emperior['l9name']?></td>
    </tr>
    <tr>
        <td class='bg1' align=center>표 기 장 군</td>
        <td width=64>&nbsp;</td>
        <td width=332><?=$emperior['l8name']?></td>
        <td class='bg1' align=center>태 위</td>
        <td width=64>&nbsp;</td>
        <td width=332><?=$emperior['l7name']?></td>
    </tr>
    <tr>
        <td class='bg1' align=center>거 기 장 군</td>
        <td width=64>&nbsp;</td>
        <td width=332><?=$emperior['l6name']?></td>
        <td class='bg1' align=center>사 도</td>
        <td width=64>&nbsp;</td>
        <td width=332><?=$emperior['l5name']?></td>
    </tr>
    <tr>
        <td class='bg1' align=center>오 호 장 군</td>
        <td colspan=5><?=$emperior['tiger']?></td>
    </tr>
    <tr>
        <td class='bg1' align=center>건 안 칠 자</td>
        <td colspan=5><?=$emperior['eagle']?></td>
    </tr>
    <tr>
        <td class='bg1' align=center>장 수 들<br>(공헌도 순서)</td>
        <td colspan=5><?=$emperior['gen']?></td>
    </tr>
    <tr>
        <td class='bg1' align=center>역 사 기 록</td>
        <td colspan=5><?=formatHistoryToHTML(Json::decode($emperior['history']))?></td>
    </tr>
</table>

<?php
endif;

$showServers = $emperior;

if(!$serverID){
    $serverID = UniqueConst::$serverID;
}

if($showServers){
    $nations = $db->query('SELECT * FROM ng_old_nations WHERE server_id=%s ORDER BY date DESC', $serverID);
    foreach($nations as $nation){
        if(!$nation['nation']??null){
            continue;
        }
        $nation += Json::decode($nation['data']);

        $nation['typeName'] = getNationType($nation['type']);
        $nation['levelName'] = getNationLevel($nation['level']);
        /** @var int[]|null $nationGenerals */
        $nationGenerals = $nation['generals'];

        if($nationGenerals){
            $generals = $db->query('SELECT `general_no`, `name`, `last_yearmonth` FROM ng_old_generals WHERE server_id=%s AND general_no IN %li', $serverID, $nationGenerals);

            if(count($generals) != count($nationGenerals) && $serverID == UniqueConst::$serverID){
                $liveGenerals = $nationGenerals;
                foreach($generals as $general){
                    if(in_array($general['general_no'], $nationGenerals)){
                        unset($nationGenerals[$general['general_no']]);
                    }
                }
                $liveGenerals = $db->query('SELECT `no`as`general_no`, `name` FROM general WHERE no IN %li', $liveGenerals);
                $nation['generalsFull'] = array_merge($liveGenerals, $generals);
            }
            else{
                $nation['generalsFull'] = $generals;
            }
        }
        else{
            $nation['generalsFull'] = [];
        }

        if(key_exists('aux', $nation) && !is_array($nation['aux'])){
            $nation += Json::decode($nation['aux']);
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