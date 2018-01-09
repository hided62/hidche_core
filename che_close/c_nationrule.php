<?
// $msg

include "lib.php";
include "func.php";
//로그인 검사
CheckLogin();
$connect = dbConn();

$query = "select no,nation from general where user_id='$_SESSION[p_id]'";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$me = MYDB_fetch_array($result);

$msg = addslashes(SQ2DQ($msg));

$query = "update nation set rule='$msg' where nation='$me[nation]'";
MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

echo "<script>location.replace('b_nationrule.php');</script>";

?>

