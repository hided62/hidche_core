<?php
include "lib.php";
include "func.php";

$id   = $_POST['id'];
$pw   = $_POST['pw'];
$face = $_POST['face'];

$pwTemp = substr($pw, 0, 32);

$connect = dbConn("sammo");

//회원 테이블에서 정보확인
$query = "select no,id,picture,grade,name from MEMBER where id='$id' and pw='$pwTemp'";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$member = MYDB_fetch_array($result);

if(!$member) {
    MessageBox("잘못된 접근입니다!!!");
    echo "<script>history.go(-1);</script>";
    exit(1);
}

$date = date('Y-m-d H:i:s');
//등록정보
$query = "update MEMBER set reg_num=reg_num+1,reg_date='$date' where no='{$member['no']}'";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

$connect = dbConn();

$npcid = "gen{$face}";
$query = "select no,npc,level from general where user_id='$npcid'";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$npc = MYDB_fetch_array($result);

########## 동일 정보 존재여부 확인. ##########

$query = "select year,month,maxgeneral,turnterm,genius,npcmode from game where no='1'";
$result = MYDB_query($query, $connect) or Error("join_post ".MYDB_error($connect),"");
$admin = MYDB_fetch_array($result);

$query  = "select no from general where npc<2";
$result = MYDB_query($query,$connect) or Error(__LINE__.MYDB_error($connect),"");
$gencount = MYDB_num_rows($result);

$query  = "select no from general where user_id='{$member['id']}'";
$result = MYDB_query($query,$connect) or Error(__LINE__.MYDB_error($connect),"");
$id_num = MYDB_num_rows($result);

if($admin['npcmode'] != 1) {
    echo "<script>alert('잘못된 접근입니다!');</script>";
    echo "<script>history.go(-1);</script>";
    exit();
} elseif($id_num) {
    echo("<script>
      window.alert('이미 등록하셨습니다!')
      history.go(-1)
      </script>");
    exit;
} elseif($admin['maxgeneral'] <= $gencount) {
    echo("<script>
      window.alert('더이상 등록할 수 없습니다!')
      history.go(-1)
      </script>");
    exit;
} elseif($npc['npc'] < 2) {
    echo("<script>
      window.alert('이미 선택된 장수입니다!')
      history.go(-1)
      </script>");
    exit;
} elseif($npc['npc'] != 2) {
    echo("<script>
      window.alert('선택할 수 없는 NPC입니다!')
      history.go(-1)
      </script>");
    exit;
/*
} elseif($npc['level'] >= 5) {
    echo("<script>
      window.alert('수뇌부는 선택할 수 없습니다!')
      history.go(-1)
      </script>");
    exit;
*/
} else {
    //특회
    $userlevel = $member['grade'];

    $query = "
        update general set
            user_id='{$id}',
            password='{$pwTemp}',
            name2='{$member['name']}',
            conmsg='',
            npc=1,
            killturn=6,
            skin=1,
            mode=2,
            map=0,
            userlevel='{$userlevel}'
        where user_id='{$npcid}'
    ";
    MYDB_query($query, $connect) or Error("join_post ".MYDB_error($connect),"");

    $query = "select no,name,history from general where user_id='$id'";
    $result = MYDB_query($query, $connect) or Error("join_post ".MYDB_error($connect),"");
    $me = MYDB_fetch_array($result);

    $log[0] = "<C>●</>{$admin['month']}월:<Y>{$me['name']}</>의 육체에 <Y>{$member['name']}</>(이)가 <S>빙의</>됩니다!";
    $me = addHistory($connect, $me, "<C>●</>$admin['year']년 $admin['month']월:<Y>{$me['name']}</>의 육체에 <Y>{$member['name']}</>(이)가 빙의되다.");
    pushGenLog($me, $mylog);
    pushAllLog($log);

    $adminLog[0] = "가입 : {$me['name']} // {$id} // ".getenv("REMOTE_ADDR");
    pushAdminLog($connect, $adminLog);

    MYDB_close($connect);

    echo("<script>
        window.alert('정상적으로 회원 가입되었습니다. ID : $id');
        </script>");
    echo("<script>window.open('../i_other/help.php');</script>");
    echo("<script>location.replace('index.php');</script>");
}

