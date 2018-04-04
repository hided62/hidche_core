<?php
namespace sammo;

include "lib.php";
include "func.php";
//로그인 검사
$session = Session::requireGameLogin()->setReadOnly();

$db = DB::db();
$connect=$db->get();

increaseRefresh("턴입력", 1);

$query = "select conlimit from game limit 1";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$admin = MYDB_fetch_array($result);

$query = "select no,name,nation,con from general where owner='{$session->userID}'";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$me = MYDB_fetch_array($result);

$con = checkLimit($me['con'], $admin['conlimit']);
if($con >= 2) { 
    //echo "<script>window.top.main.location.replace('index.php');</script>"; 
    echo 'index.php';//TODO:debug all and replace
    exit();
 }

$count = sizeof($turn);
for($i=0; $i < $count; $i++) {
    if($turn[$i] == 100 || $turn[$i] == 99 || $turn[$i] == 98) {
    } elseif($turn[$i] >= 0 && $turn[$i] <= 23) {
    } else {
        unset($turn);
        $turn[0] = 100;
        break;
    }
}

if($turn[0] == 100) {
    unset($turn);
    for($i=0; $i < 24; $i++) $turn[$i] = $i;
} elseif($turn[0] == 99) {
    unset($turn);
    for($i=0, $j=0; $i < 24; $i+=2, $j++) $turn[$j] = $i;
} elseif($turn[0] == 98) {
    unset($turn);
    for($i=1, $j=0; $i < 24; $i+=2, $j++) $turn[$j] = $i;
}

switch($commandtype) {
    case  0: command_Single($turn, 0); break; //휴식
    case  1: command_Single($turn, 1); break; //농업
    case  2: command_Single($turn, 2); break; //상업
    case  3: command_Single($turn, 3); break; //기술
    case  4: command_Single($turn, 4); break; //선정
    case  5: command_Single($turn, 5); break; //수비
    case  6: command_Single($turn, 6); break; //성벽
    case  7: command_Single($turn, 7); break; //정착 장려
    case  8: command_Single($turn, 8); break; //치안 강화
    case  9: command_Single($turn, 9); break; //자금 조달

//    case 11: command_11(    $turn, 11); break; //징병
//    case 12: command_12(    $turn, 12); break; //모병
    case 13: command_Single($turn, 13); break; //훈련
    case 14: command_Single($turn, 14); break; //사기진작
    case 15: command_Single($turn, 0); break; //전투태세
//    case 16: command_16(    $turn, 16); break; //전쟁
    case 17: command_Single($turn, 17); break; //소집해제

//    case 21: command_21(    $turn, 21); break; //이동
//    case 22: command_22(    $turn, 22); break; //등용
//    case 23: command_23(    $turn, 23); break; //포상
//    case 24: command_24(    $turn, 24); break; //몰수
//    case 25: command_25(    $turn, 25); break; //임관
    case 26: command_Single($turn, 26); break; //집합
//    case 27: command_27(    $turn, 27); break; //발령
    case 28: command_Single($turn, 28); break; //귀환
    case 29: command_Single($turn, 29); break; //인재탐색
//    case 30: command_30(    $turn, 30); break; //강행
    
//    case 31: command_31($turn, 31); break; //첩보
//    case 32: command_32($turn, 32); break; //화계
//    case 33: command_33($turn, 33); break; //탈취
//    case 34: command_34($turn, 34); break; //파괴
//    case 35: command_35($turn, 35); break; //선동
//    case 36: command_36($turn, 36); break; //기습

    case 41: command_Single($turn, 41); break; //단련
    case 42: command_Single($turn, 42); break; //견문
//    case 43: command_43(    $turn, 43); break; //증여
//    case 44: command_44(    $turn, 44); break; //헌납
    case 45: command_Single($turn, 45); break; //하야
//    case 46: command_46(    $turn, 46); break; //건국
    case 47: command_Single($turn, 47); break; //방랑
//    case 48: command_48(    $turn, 48); break; //장비구입
//    case 49: command_49(    $turn, 49); break; //군량매매
    case 50: command_Single($turn, 50); break; //요양

//    case 51: command_51($turn, 51); break; //항복권고
//    case 52: command_52($turn, 52); break; //원조
//    case 53: command_53($turn, 53); break; //통합제의
//    case 54: command_54($turn, 54); break; //선양
    case 55: command_Single($turn, 55); break; //거병
    case 56: command_Single($turn, 56); break; //해산
    case 57: command_Single($turn, 57); break; //모반 시도

//    case 61: command_61($turn, 61); break; //불가침
//    case 62: command_62($turn, 62); break; //선포
//    case 63: command_63($turn, 63); break; //종전
//    case 64: command_64($turn, 64); break; //파기
//    case 65: command_65($turn, 65); break; //초토화
//    case 66: command_66($turn, 66); break; //천도
//    case 67: command_67($turn, 67); break; //증축
//    case 68: command_68($turn, 68); break; //감축

    case 71: command_Chief($turn, 71); break; //필사즉생
//    case 72: command_72($turn, 72); break; //백성동원
//    case 73: command_73($turn, 73); break; //수몰
//    case 74: command_74($turn, 74); break; //허보
//    case 75: command_75($turn, 75); break; //피장파장
    case 76: command_Chief($turn, 76); break; //의병모집
//    case 77: command_77($turn, 77); break; //이호경식
//    case 78: command_78($turn, 78); break; //급습

//    case 81: command_81($turn, 81); break; //국기변경

//    case 99: command_99($turn); break; //수뇌부 후식
    default: command_Other($turn, $commandtype); break;
}

