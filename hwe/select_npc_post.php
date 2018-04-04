<?php
namespace sammo;

include "lib.php";
include "func.php";

$session = Session::requireLogin()->setReadOnly();

$face = $_POST['face'];//TODO: face를 user_id에서 general.no 값을 이용하도록 변경

$userID = $session->userID;
$rootDB = RootDB::db();

//회원 테이블에서 정보확인
$member = $rootDB->queryFirstRow('SELECT `no`, id, picture, grade, `name` FROM MEMBER WHERE no=%i', $userID);

if(!$member) {
    MessageBox("잘못된 접근입니다!!!");
    echo "<script>history.go(-1);</script>";
    exit(1);
}

$db = DB::db();

$npc = $db->queryFirstRow('SELECT `no`, `name`, `npc`, `level` FROM general WHERE `no`=%i', $face);
if(!$npc){
    echo "<script>alert('선택한 장수가 없습니다!');</script>";
    echo "<script>history.go(-1);</script>";
    exit();
}

$npcID = $npc['no'];
########## 동일 정보 존재여부 확인. ##########

$admin = $db->queryFirstRow('SELECT year,month,maxgeneral,turnterm,genius,npcmode from game limit 1');
$gencount = $db->queryFirstField('SELECT count(`no`) FROM general WHERE noc<2');
$oldGeneral = $db->queryFirstField('SELECT `no` FROM general WHERE `owner`=%i', $userID);

if($admin['npcmode'] != 1) {
    echo "<script>alert('잘못된 접근입니다!');</script>";
    echo "<script>history.go(-1);</script>";
    exit();
}
if($oldGeneral) {
    echo("<script>
      window.alert('이미 등록하셨습니다!')
      history.go(-1)
      </script>");
    exit;
}
if($admin['maxgeneral'] <= $gencount) {
    echo("<script>
      window.alert('더이상 등록할 수 없습니다!')
      history.go(-1)
      </script>");
    exit;
}
if($npc['npc'] < 2) {
    echo("<script>
      window.alert('이미 선택된 장수입니다!')
      history.go(-1)
      </script>");
    exit;
}
if($npc['npc'] != 2) {
    echo("<script>
      window.alert('선택할 수 없는 NPC입니다!')
      history.go(-1)
      </script>");
    exit;
} 
/*if($npc['level'] >= 5) {
    echo("<script>
      window.alert('수뇌부는 선택할 수 없습니다!')
      history.go(-1)
      </script>");
    exit;

} */

$db->update('general', [
    'name2'=>$session->userName,
    'npc'=>1,
    'killturn'=>6,
    'mode'=>2,
    'map'=>0,
    'owner'=>$userID
], 'no=%i and npc=2', $npcID);
$affected = $db->affectedRows();
if(!$affected){
    echo("<script>
    window.alert('이미 선택된 장수입니다!')
    history.go(-1)
    </script>");
    exit;
}

$me = [
    'no'=>$npcID
];

$log[0] = "<C>●</>{$admin['month']}월:<Y>{$npc['name']}</>의 육체에 <Y>{$session->userName}</>(이)가 <S>빙의</>됩니다!";
pushGeneralHistory($me, "<C>●</>{$admin['year']}년 {$admin['month']}월:<Y>{$npc['name']}</>의 육체에 <Y>{$session->userName}</>(이)가 빙의되다.");
pushGenLog($me, $mylog);
pushGeneralPublicRecord($log, $admin['year'], $admin['month']);

$adminLog[0] = "가입 : {$session->userName} // {$id} // ".getenv("REMOTE_ADDR");
pushAdminLog($adminLog);

$rootDB->insert('member_log', [
    'member_no' => $userID,
    'date'=>date('Y-m-d H:i:s'),
    'action_type'=>'make_general',
    'action'=>Json::encode([
        'server'=>DB::prefix(),
        'type'=>'npc',
        'generalID'=>$me['no'],
        'generalName'=>$me['name']
    ])
]);

?>
<script>
    window.alert('정상적으로 회원 가입되었습니다. ID : <?=$id?>');
</script>");
<script>window.open('../i_other/help.php');</script>
