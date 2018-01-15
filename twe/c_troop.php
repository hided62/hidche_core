<?php
// $btn, $name, $troop

include "lib.php";
include "func.php";
//로그인 검사
CheckLogin();
$connect = dbConn();

$query = "select no,nation,troop from general where user_id='{$_SESSION['p_id']}'";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$me = MYDB_fetch_array($result);

$name = trim($name);
$name = addslashes(SQ2DQ($name));
if($btn == "부 대 창 설" && $name != "" && $me['troop'] == 0) {
    $query = "insert into troop (name,nation,no) values ('$name','{$me['nation']}','{$me['no']}')";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

    $query = "select troop from troop where no='{$me['no']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $troop = MYDB_fetch_array($result);

    $query = "update general set troop='{$troop['troop']}' where no='{$me['no']}'";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
} elseif($btn == "부 대 변 경" && $name != "") {
    $query = "update troop set name='$name' where no='{$me['no']}'";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
} elseif($btn == "부 대 추 방" && $gen != 0) {
    $query = "update general set troop='0' where no='$gen'";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
} elseif($btn == "부 대 가 입" && $troop != 0) {
    $query = "update general set troop='$troop' where no='{$me['no']}'";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
} elseif($btn == "부 대 탈 퇴") {
    $query = "select no from troop where troop='{$me['troop']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $troop = MYDB_fetch_array($result);

    //부대장일 경우
    if($troop['no'] == $me['no']) {
        // 모두 탈퇴
        $query = "update general set troop='0' where troop='{$me['troop']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        // 부대 삭제
        $query = "delete from troop where troop='{$me['troop']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    } else {
        $query = "update general set troop='0' where no='{$me['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    }
}

echo "<script>location.replace('b_troop.php');</script>";

MYDB_close($connect);
