<?php
namespace sammo;

include "lib.php";
include "func.php";

$session = Session::getInstance()->setReadOnly();

$db = DB::db();
$connect=$db->get();

increaseRefresh("명예의전당", 1);
?>
<!DOCTYPE html>
<html>

<head>
<meta HTTP-EQUIV='Content-Type' CONTENT='text/html; charset=utf-8'>
<title><?=UniqueConst::$serverName?>: 명예의 전당</title>
<?=WebUtil::printCSS('../d_shared/common.css')?>
<?=WebUtil::printCSS('css/common.css')?>
<?=WebUtil::printCSS('css/hallOfFrame.css')?>
</head>

<body>
<table align=center width=1100 class='tb_layout bg0'>
    <tr><td>명 예 의 전 당<br><?=closeButton()?></td></tr>
</table>
<div style="margin:auto;width=1100px;">
<?php
$types = array(
    "명 성",
    "계 급",
    "계 략 성 공",
    "전 투 횟 수",
    "승 리",
    "승 률",
    "사 살",
    "살 상 률",
    "보 병 숙 련 도",
    "궁 병 숙 련 도",
    "기 병 숙 련 도",
    "귀 병 숙 련 도",
    "차 병 숙 련 도",
    "전 력 전 승 률",
    "통 솔 전 승 률",
    "일 기 토 승 률",
    "설 전 승 률",
    "베 팅 투 자 액",
    "베 팅 당 첨",
    "베 팅 수 익 금",
    "베 팅 수 익 률"
);

$templates = new \League\Plates\Engine('templates');

foreach($types as $idx=>$typeName) {
    $hallResult = $db->query('SELECT * FROM ng_hall WHERE server_id = %s AND `type`=%i ORDER BY `value` DESC LIMIT 10', UniqueConst::$serverID, $idx);

    $hallResult = array_map(function($general){
        $aux = Json::decode($general['aux']);
        $general += $aux;
        if(!key_exists('color', $general)){
            $general['color'] = GameConst::$basecolor4;
            $general['fgColor'] = newColor($general['color']);
        }
        if(key_exists('picture', $general)){
            $imageTemp = GetImageURL($general['imgsvr']);
            $general['pictureFullPath'] = "$imageTemp/{$general['picture']}";
        }
        else{
            $general['pictureFullPath'] = GetImageURL(0)."/default.jpg";
        }
        return $general;
    }, $hallResult);

    echo $templates->render('hallOfFrame', [
        'typeName'=>$typeName,
        'generals'=>$hallResult
    ]);
}
?>
</div>
<table align=center width=1100 class='tb_layout bg0'>
    <tr><td><?=closeButton()?></td></tr>
    <tr><td><?=banner()?> </td></tr>
</table>
</body>
</html>

