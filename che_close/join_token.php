<?
include "lib.php";
include "func.php";

$id = $_POST[id];
$pw = $_POST[pw];
$type = $_POST[type];

$pwTemp = substr($pw, 0, 32);

$connect = dbConn("sammo");

//회원 테이블에서 정보확인
$query = "select no,id,picture,imgsvr,grade from MEMBER where id='$id' and pw='$pwTemp'";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$member = MYDB_fetch_array($result);

$connect = dbConn();

if(!$member) {
    $response['result'] = "error";
    echo json_encode($response);
    exit;
}

if($type == "rand") {
    $response['result'] = "rand";
    
    $abil = abilityRand();
} elseif($type == "leadpow") {
    $response['result'] = "leadpow";
    
    $abil = abilityLeadpow();
} elseif($type == "leadint") {
    $response['result'] = "leadint";

    $abil = abilityLeadint();
} elseif($type == "powint") {
    $response['result'] = "powint";

    $abil = abilityPowint();
} else {
    $response['result'] = "error";
    echo json_encode($response);
    exit;
}

$query = "update token set leader={$abil['leader']},power={$abil['power']},intel={$abil['intel']} where id='{$id}'";
MYDB_query($query, $connect) or Error("join_post ".MYDB_error($connect),"");

$response['leader'] = $abil['leader'];
$response['power'] = $abil['power'];
$response['intel'] = $abil['intel'];

usleep(100*1000);
echo json_encode($response);

?>