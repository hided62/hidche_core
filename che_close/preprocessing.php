<?
include "lib.php";
include "func.php";
//로그인 검사
CheckLogin(1);
$connect = dbConn();
increaseRefresh($connect, "턴입력", 1);

$query = "select conlimit from game where no=1";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$admin = MYDB_fetch_array($result);

$query = "select no,name,nation,userlevel,con from general where user_id='$_SESSION[p_id]'";
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$me = MYDB_fetch_array($result);

$con = checkLimit($me[userlevel], $me[con], $admin[conlimit]);
if($con >= 2) { echo "<script>window.top.main.location.replace('main.php');</script>"; exit(); }

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
    case  0: command_Single($connect, $turn, 0); break; //휴식
    case  1: command_Single($connect, $turn, 1); break; //농업
    case  2: command_Single($connect, $turn, 2); break; //상업
    case  3: command_Single($connect, $turn, 3); break; //기술
    case  4: command_Single($connect, $turn, 4); break; //선정
    case  5: command_Single($connect, $turn, 5); break; //수비
    case  6: command_Single($connect, $turn, 6); break; //성벽
    case  7: command_Single($connect, $turn, 7); break; //정착 장려
    case  8: command_Single($connect, $turn, 8); break; //치안 강화
    case  9: command_Single($connect, $turn, 9); break; //자금 조달

//    case 11: command_11($connect,     $turn, 11); break; //징병
//    case 12: command_12($connect,     $turn, 12); break; //모병
    case 13: command_Single($connect, $turn, 13); break; //훈련
    case 14: command_Single($connect, $turn, 14); break; //사기진작
    case 15: command_Single($connect, $turn, 0); break; //전투태세
//    case 16: command_16($connect,     $turn, 16); break; //전쟁
    case 17: command_Single($connect, $turn, 17); break; //소집해제

//    case 21: command_21($connect,     $turn, 21); break; //이동
//    case 22: command_22($connect,     $turn, 22); break; //등용
//    case 23: command_23($connect,     $turn, 23); break; //포상
//    case 24: command_24($connect,     $turn, 24); break; //몰수
//    case 25: command_25($connect,     $turn, 25); break; //임관
    case 26: command_Single($connect, $turn, 26); break; //집합
//    case 27: command_27($connect,     $turn, 27); break; //발령
    case 28: command_Single($connect, $turn, 28); break; //귀환
    case 29: command_Single($connect, $turn, 29); break; //인재탐색
//    case 30: command_30($connect,     $turn, 30); break; //강행
    
//    case 31: command_31($connect, $turn, 31); break; //첩보
//    case 32: command_32($connect, $turn, 32); break; //화계
//    case 33: command_33($connect, $turn, 33); break; //탈취
//    case 34: command_34($connect, $turn, 34); break; //파괴
//    case 35: command_35($connect, $turn, 35); break; //선동
//    case 36: command_36($connect, $turn, 36); break; //기습

    case 41: command_Single($connect, $turn, 41); break; //단련
    case 42: command_Single($connect, $turn, 42); break; //견문
//    case 43: command_43($connect,     $turn, 43); break; //증여
//    case 44: command_44($connect,     $turn, 44); break; //헌납
    case 45: command_Single($connect, $turn, 45); break; //하야
//    case 46: command_46($connect,     $turn, 46); break; //건국
    case 47: command_Single($connect, $turn, 47); break; //방랑
//    case 48: command_48($connect,     $turn, 48); break; //장비구입
//    case 49: command_49($connect,     $turn, 49); break; //군량매매
    case 50: command_Single($connect, $turn, 50); break; //요양

//    case 51: command_51($connect, $turn, 51); break; //항복권고
//    case 52: command_52($connect, $turn, 52); break; //원조
//    case 53: command_53($connect, $turn, 53); break; //통합제의
//    case 54: command_54($connect, $turn, 54); break; //선양
    case 55: command_Single($connect, $turn, 55); break; //거병
    case 56: command_Single($connect, $turn, 56); break; //해산
    case 57: command_Single($connect, $turn, 57); break; //모반 시도

//    case 61: command_61($connect, $turn, 61); break; //불가침
//    case 62: command_62($connect, $turn, 62); break; //선포
//    case 63: command_63($connect, $turn, 63); break; //종전
//    case 64: command_64($connect, $turn, 64); break; //파기
//    case 65: command_65($connect, $turn, 65); break; //초토화
//    case 66: command_66($connect, $turn, 66); break; //천도
//    case 67: command_67($connect, $turn, 67); break; //증축
//    case 68: command_68($connect, $turn, 68); break; //감축

    case 71: command_Chief($connect, $turn, 71); break; //필사즉생
//    case 72: command_72($connect, $turn, 72); break; //백성동원
//    case 73: command_73($connect, $turn, 73); break; //수몰
//    case 74: command_74($connect, $turn, 74); break; //허보
//    case 75: command_75($connect, $turn, 75); break; //피장파장
    case 76: command_Chief($connect, $turn, 76); break; //의병모집
//    case 77: command_77($connect, $turn, 77); break; //이호경식
//    case 78: command_78($connect, $turn, 78); break; //급습

//    case 81: command_81($connect, $turn, 81); break; //국기변경

//    case 99: command_99($connect, $turn); break; //수뇌부 후식
    default: command_Other($connect, $turn, $commandtype); break;
}

?>

