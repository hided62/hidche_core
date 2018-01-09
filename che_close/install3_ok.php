<?
include "lib.php";
include "func.php";

$connect=dbConn();

LogHistory($connect, 1);

echo "<script>location.replace('index.php');</script>";
?>
