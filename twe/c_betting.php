<?php
// $btn0~15, $gold0~15

include "lib.php";
include "func.php";
//로그인 검사
CheckLogin();
$connect = dbConn();
increaseRefresh($connect, "베팅", 1);

$query = "select tournament,phase,tnmt_type,develcost from game where no='1'";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$admin = MYDB_fetch_array($result);

if($admin['tournament'] != 6) {
    //echo "<script>location.replace('b_betting.php');</script>";
    echo 'b_betting.php';//TODO:debug all and replace
    exit();
}

$query = "select gold,bet0,bet1,bet2,bet3,bet4,bet5,bet6,bet7,bet8,bet9,bet10,bet11,bet12,bet13,bet14,bet15,bet0+bet1+bet2+bet3+bet4+bet5+bet6+bet7+bet8+bet9+bet10+bet11+bet12+bet13+bet14+bet15 as bet from general where user_id='{$_SESSION['p_id']}'";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$me = MYDB_fetch_array($result);

for($i=0; $i < 16; $i++) {
    if(${"btn{$i}"} == "베팅!") {
        $gold = ${"gold{$i}"};
        $mebet = $me["bet{$i}"];
        if($gold >= 10 && $gold <= 1000) {
            if($gold + 500 <= $me['gold'] && $gold + $mebet <= 1000 && $gold + $me['bet'] <= 1000) {
                $query = "update general set gold=gold-'$gold',bet{$i}=bet{$i}+'$gold',betgold=betgold+'$gold' where user_id='{$_SESSION['p_id']}'";
                MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
                $query = "update game set bet{$i}=bet{$i}+'$gold' where no='1'";
                MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            }
        }
    }
}

?>

<!--<script>location.replace('b_betting.php');</script>//TODO:debug all and replace-->
b_betting.php
