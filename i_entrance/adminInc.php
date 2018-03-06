<?php
// 외부 파라미터

require_once('_common.php');

?>

    <script type="text/javascript">
ImportStyle("<?=ROOT;?>"+W+I+ENTRANCE+W+ADMIN+STYLE);
ImportAction("<?=ROOT;?>"+W+I+ENTRANCE+W+ADMIN+ACTION);
Entrance_AdminImport();
Entrance_AdminInit();
Entrance_AdminUpdate();
    </script>

    <div id="Entrance_0002" class="bg0">
        <div id="Entrance_000200" class="bg2">회 원 관 리</div>
        <input id="Entrance_000202" type="button" value="회 원 관 리">
        <input id="Entrance_000203" type="text" value="<?=$system['NOTICE'];?>">
        <input id="Entrance_000204" type="button" value="공 지 변 경">
    </div>
    <div id="Entrance_0003" class="bg0">
        <div id="Entrance_000300" class="bg2">서 버 관 리</div>
        <div id="Entrance_000301">
            <div id="Entrance_00030000" class="bg1">서버(접속)</div>
            <div id="Entrance_00030001" class="bg1">상 태</div>
            <div id="Entrance_00030002" class="bg1">선 택</div>
        </div>
        <div id="Entrance_000302">
<?php
$i = 0;
foreach($severList as $server) {

    $settingObj = $server[2];

    $serverPath = $settingObj->getBasePath();
    $serverDir = $settingObj->getShortName();
    //TODO: .htaccess가 서버 오픈에도 사용될 수 있으니 별도의 방법이 필요함
    if(!is_dir($serverPath)){
        $state = '상태이상, 01';
    }
    else if(!file_exists($serverPath.'/index.php')){
        $state = '상태이상, 02';
    }
    else if(file_exists($serverPath.'/.htaccess')) {
        // 폐쇄중
        if($settingObj->isExist()) {
            // 폐쇄중, 설정있음
            $state = '폐쇄중, 설정있음';
        } else {
            // 폐쇄중, 설정없음
            $state = '폐쇄중, 설정없음';
        }
    } else{
        // 오픈중
        if($settingObj->isExist()) {
            // 서비스중
            $state = '서비스중';
        } else {
            // 오픈중, 설정없음
            $state = '오픈중, 설정없음';
        }
    } 
    
?>
            <div class="Entrance_ServerAdminList">
                <div class="Entrance_ServerAdminListServer"><?=$serverDir;?></div>
                <div class="Entrance_ServerAdminListState"><?=$state;?></div>
                <div class="Entrance_ServerAdminListSelect">
                    <input type="button" class="Entrance_ServerAdminListSelectButton1" value="폐쇄" onclick="Entrance_AdminPost(<?=$i;?>, 0);">
                    <input type="button" class="Entrance_ServerAdminListSelectButton1" value="리셋" onclick="Entrance_AdminPost(<?=$i;?>, 1);">
                    <input type="button" class="Entrance_ServerAdminListSelectButton1" value="오픈" onclick="Entrance_AdminPost(<?=$i;?>, 2);">
                    <input type="button" class="Entrance_ServerAdminListSelectButton2" value="N로그인" onclick="Entrance_AdminNPCLogin('../<?=$serverDir;?>');">
                    <input type="button" class="Entrance_ServerAdminListSelectButton2" value="N생성" onclick="Entrance_AdminNPCCreate('../<?=$serverDir;?>');">
                    <input type="button" class="Entrance_ServerAdminListSelectButton3" value="폐쇄중로그인" onclick="Entrance_AdminClosedLogin('../<?=$serverDir;?>');">
                    <input type="button" class="Entrance_ServerAdminListSelectButton2" value="119" onclick="Entrance_AdminOpen119('../<?=$serverDir;?>');">
                </div>
            </div>
<?php
}
?>
        </div>
    </div>
