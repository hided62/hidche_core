<?php
//NOTE: 전역 로그아웃 외에 게임 로그아웃이 의미가 있나?
include "lib.php";
include "func.php";

unset($_SESSION['p_id']);
unset($_SESSION['p_ip']);
unset($_SESSION[getServPrefix().'p_no']);
unset($_SESSION[getServPrefix().'p_name']);

header('Location:../');