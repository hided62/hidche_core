<?php
include "process_war.php";
include "func_process.php";
include "func_npc.php";
include "func_tournament.php";
include "func_auction.php";
include "func_string.php";
include "func_history.php";

// 37.5 ~ 75
function abilityRand() {
    $total  = 150;
    $leader = (rand()%100 + 1) / 100.0 + 1.0;
    $power  = (rand()%100 + 1) / 100.0 + 1.0;
    $intel  = (rand()%100 + 1) / 100.0 + 1.0;
    $rate = $leader + $power + $intel;
    $leader = floor($leader / $rate * $total);
    $power  = floor($power  / $rate * $total);
    $intel  = floor($intel  / $rate * $total);

    while($leader+$power+$intel < 150) {
        $leader++;
    }

    return array('leader' => $leader, 'power' => $power, 'intel' => $intel);
}

// 14 ~ 75
function abilityLeadpow() {
    $total  = 150;
    $leader = (rand()%100 + 1) / 100.0 + 6.0;
    $power  = (rand()%100 + 1) / 100.0 + 6.0;
    $intel  = (rand()%100 + 1) / 100.0 + 1.0;
    $rate = $leader + $power + $intel;
    $leader = floor($leader / $rate * $total);
    $power  = floor($power  / $rate * $total);
    $intel  = floor($intel  / $rate * $total);

    while($leader+$power+$intel < 150) {
        $leader++;
    }

    return array('leader' => $leader, 'power' => $power, 'intel' => $intel);
}

function abilityLeadint() {
    $total  = 150;
    $leader = (rand()%100 + 1) / 100.0 + 6.0;
    $power  = (rand()%100 + 1) / 100.0 + 1.0;
    $intel  = (rand()%100 + 1) / 100.0 + 6.0;
    $rate = $leader + $power + $intel;
    $leader = floor($leader / $rate * $total);
    $power  = floor($power  / $rate * $total);
    $intel  = floor($intel  / $rate * $total);

    while($leader+$power+$intel < 150) {
        $leader++;
    }

    return array('leader' => $leader, 'power' => $power, 'intel' => $intel);
}

function abilityPowint() {
    $total  = 150;
    $leader = (rand()%100 + 1) / 100.0 + 1.0;
    $power  = (rand()%100 + 1) / 100.0 + 6.0;
    $intel  = (rand()%100 + 1) / 100.0 + 6.0;
    $rate = $leader + $power + $intel;
    $leader = floor($leader / $rate * $total);
    $power  = floor($power  / $rate * $total);
    $intel  = floor($intel  / $rate * $total);

    while($leader+$power+$intel < 150) {
        $leader++;
    }

    return array('leader' => $leader, 'power' => $power, 'intel' => $intel);
}

function delInDir($dir) {
    $handle = opendir($dir);
    if($handle !== false){
        while(false !== ($FolderOrFile = readdir($handle))) {
            if ($FolderOrFile == "." || $FolderOrFile == "..") {
                continue;
            }

            $filepath = sprintf('%s/%s', $dir, $FolderOrFile);
            if (is_dir($filepath)) {
                delInDir($filepath);
            } // recursive
            else {
                @unlink($filepath);
            }
        }
    }
    closedir($handle);
//    if(rmdir($dir)) {
//        $success = true;
//    }
    return true;
}

function GetImageURL($imgsvr) {
    global $image, $image1;
    if($imgsvr == 0) {
        return $image;
    } else {
        return $image1;
    }
}

function CheckLogin($type=0) {
    if($_SESSION['p_id'] == "") {
        if($type == 0) { echo "<script>location.replace('start.php');</script>"; }
        else           { echo "<script>window.top.main.location.replace('main.php');</script>"; }
        exit();
    }
}

function checkLimit($userlevel, $con, $conlimit) {
    //운영자
    if($userlevel >= 5) { return 0; }
    //특회이면 3배
    if($userlevel >= 3) { $conlimit *= 3; }
    //접속률 이상이면 제한
    if($con > $conlimit) {
        return 2;
    //접속제한 90%이면 경고문구
    } elseif($con > $conlimit * 0.9) {
        return 1;
    } else {
        return 0;
    }
}

function bar($per, $skin=1, $h=7) {
    global $images;
    if($h == 7) { $bd = 0; $h =  7; $h2 =  5; }
    else        { $bd = 1; $h = 12; $h2 =  8; }

    $per = round($per, 1);
    if($per < 1 || $per > 99) { $per = round($per); }
    $str1 = "<td width={$per}% background={$images}/pb{$h2}.gif>&nbsp;</td>";
    $str2 = "<td width=*% background={$images}/pr{$h2}.gif>&nbsp;</td>";
    if($per <= 0) { $str1 = ""; }
    elseif($per >= 100) { $str2 = ""; }
    if($skin == 0) {
        $str = "-";
    } else {
        $str = "
        <table width=100% height={$h} border={$bd} cellspacing=0 cellpadding=0 bordercolordark=gray bordercolorlight=black style=font-size:1;>
            <tr>{$str1}{$str2}</tr>
        </table>";
    }
    return $str;
}

function printLimitMsg($turntime) {
    echo "
<html>
<head>
<title>접속제한</title>
<meta HTTP-EQUIV='Content-Type' CONTENT='text/html; charset=utf-8'>
<link rel=stylesheet href=stylesheet.php type=text/css>
";
require('analytics.php');
echo "
</head>
<body oncontextmenu='return false'>
<font size=4><b>
접속 제한중입니다. 1턴 이내에 너무 많은 갱신을 하셨습니다. (다음 접속 가능 시각 : {$turntime})<br>
(자신의 턴이 되면 다시 접속 가능합니다. 당신의 건강을 위해 잠시 쉬어보시는 것은 어떨까요? ^^)<br>
</b></font>
</body>
</html>
";
}
// (자신의 턴이 되면 다시 접속 가능합니다. <font color=orange size=4>제한량을 늘리기 위해 참여해주세요!</font> <font color=magenta size=4>참여게시판 참고.</font>)<br>

function getScenario($connect) {
    $query = "select scenario from game where no='1'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $admin = MYDB_fetch_array($result);

    switch($admin['scenario']) {
    case  0: $str = "공백지모드"; break;
    case  1: $str = "역사모드1 : 184년 황건적의 난"; break;
    case  2: $str = "역사모드2 : 190년 반동탁연합"; break;
    case  3: $str = "역사모드3 : 194년 군웅할거"; break;
    case  4: $str = "역사모드4 : 196년 황제는 허도로"; break;
    case  5: $str = "역사모드5 : 200년 관도대전"; break;
    case  6: $str = "역사모드6 : 202년 원가의 분열"; break;
    case  7: $str = "역사모드7 : 207년 적벽대전"; break;
    case  8: $str = "역사모드8 : 213년 익주 공방전"; break;
    case  9: $str = "역사모드9 : 219년 삼국정립"; break;
    case 10: $str = "역사모드10 : 225년 칠종칠금"; break;
    case 11: $str = "역사모드11 : 228년 출사표"; break;

    case 12: $str = "IF모드1 : 191년 백마장군의 위세"; break;

    case 20: $str = "가상모드1 : 180년 영웅 난무"; break;
    case 21: $str = "가상모드1 : 180년 영웅 집결"; break;
    case 22: $str = "가상모드2 : 179년 훼신 집결"; break;
    case 23: $str = "가상모드3 : 180년 영웅 시대"; break;
    case 24: $str = "가상모드4 : 180년 결사항전"; break;
    case 25: $str = "가상모드5 : 180년 영웅독존"; break;
    case 26: $str = "가상모드6 : 180년 무풍지대"; break;
    case 27: $str = "가상모드7 : 180년 가요대잔치"; break;
    case 28: $str = "가상모드8 : 180년 확산성 밀리언 아서"; break;
    default: $str = "시나리오?"; break;
    }
    return $str;
}

function CheckBlock($connect) {
    $query = "select block from general where user_id='{$_SESSION['p_id']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $me = MYDB_fetch_array($result);
    return $me['block'];
}

function NationCharCall($call) {
    switch($call) {
        case "명가":    $type =13; break;
        case "음양가":  $type =12; break;
        case "종횡가":  $type =11; break;
        case "불가":    $type =10; break;
        case "도적":    $type = 9; break;
        case "오두미도":$type = 8; break;
        case "태평도":  $type = 7; break;
        case "도가":    $type = 6; break;
        case "묵가":    $type = 5; break;
        case "덕가":    $type = 4; break;
        case "병가":    $type = 3; break;
        case "유가":    $type = 2; break;
        case "법가":    $type = 1; break;
        default:        $type = 0; break;
    }
    return $type;
}

function getRandGenName() {
    $first = array('가', '간', '감', '강', '고', '공', '공손', '곽', '관', '괴', '교', '금', '노', '뇌', '능', '도', '동', '두',
        '등', '마', '맹', '문', '미', '반', '방', '부', '비', '사', '사마', '서', '설', '성', '소', '손', '송', '순', '신', '심',
        '악', '안', '양', '엄', '여', '염', '오', '왕', '요', '우', '원', '위', '유', '육', '윤', '이', '장', '저', '전', '정',
        '제갈', '조', '종', '주', '진', '채', '태사', '하', '하후', '학', '한', '향', '허', '호', '화', '황',
        '공손', '손', '왕', '유', '장', '조');
    $last = array('가', '간', '강', '거', '건', '검', '견', '경', '공', '광', '권', '규', '녕', '단', '대', '도', '등', '람',
        '량', '례', '로', '료', '모', '민', '박', '범', '보', '비', '사', '상', '색', '서', '소', '속', '송', '수', '순', '습',
        '승', '양', '연', '영', '온', '옹', '완', '우', '웅', '월', '위', '유', '윤', '융', '이', '익', '임', '정', '제', '조',
        '주', '준', '지', '찬', '책', '충', '탁', '택', '통', '패', '평', '포', '합', '해', '혁', '현', '화', '환', '회', '횡',
        '후', '훈', '휴', '흠', '흥');

    $firstname = $first[rand()%count($first)];
    $lastname = $last[rand()%count($last)];

    $fullname = "{$firstname}{$lastname}";
    return $fullname;
}

function CityNameArray() {
    $cityNames = array(
        "","업","허창","낙양","장안","성도","양양","건업","북평","남피","완","수춘","서주","강릉","장사",
        "시상","위례","계","복양","진류","여남","하비","서량","하내","한중","상용","덕양","강주","건녕",
        "남해","계양","오","평양","사비","계림","진양","평원","북해","초","패","천수","안정","홍농",
        "하변","자동","영안","귀양","주시","운남","남영","교지","신야","강하","무릉","영릉","상동","여강",
        "회계","고창","대","안평","졸본","이도","강","저","흉노","남만","산월","오환","왜","호관",
        "호로","사곡","함곡","사수","양평","가맹","역경","계교","동황","관도","정도","합비","광릉","적도",
        "가정","기산","면죽","이릉","장판","백랑","적벽","파양","탐라","유구"
    );
    return $cityNames;
}

function CityCall($call) {
    switch($call) {
        case "업":      $type =  1; break;      case "허창":    $type =  2; break;
        case "낙양":    $type =  3; break;      case "장안":    $type =  4; break;
        case "성도":    $type =  5; break;      case "양양":    $type =  6; break;
        case "건업":    $type =  7; break;      case "북평":    $type =  8; break;
        case "남피":    $type =  9; break;      case "완":      $type = 10; break;
        case "수춘":    $type = 11; break;      case "서주":    $type = 12; break;
        case "강릉":    $type = 13; break;      case "장사":    $type = 14; break;
        case "시상":    $type = 15; break;      case "위례":    $type = 16; break;
        case "계":      $type = 17; break;      case "복양":    $type = 18; break;
        case "진류":    $type = 19; break;      case "여남":    $type = 20; break;
        case "하비":    $type = 21; break;      case "서량":    $type = 22; break;
        case "하내":    $type = 23; break;      case "한중":    $type = 24; break;
        case "상용":    $type = 25; break;      case "덕양":    $type = 26; break;
        case "강주":    $type = 27; break;      case "건녕":    $type = 28; break;
        case "남해":    $type = 29; break;      case "계양":    $type = 30; break;
        case "오":      $type = 31; break;      case "평양":    $type = 32; break;
        case "사비":    $type = 33; break;      case "계림":    $type = 34; break;
        case "진양":    $type = 35; break;      case "평원":    $type = 36; break;
        case "북해":    $type = 37; break;      case "초":      $type = 38; break;
        case "패":      $type = 39; break;      case "천수":    $type = 40; break;
        case "안정":    $type = 41; break;      case "홍농":    $type = 42; break;
        case "하변":    $type = 43; break;      case "자동":    $type = 44; break;
        case "영안":    $type = 45; break;      case "귀양":    $type = 46; break;
        case "주시":    $type = 47; break;      case "운남":    $type = 48; break;
        case "남영":    $type = 49; break;      case "교지":    $type = 50; break;
        case "신야":    $type = 51; break;      case "강하":    $type = 52; break;
        case "무릉":    $type = 53; break;      case "영릉":    $type = 54; break;
        case "상동":    $type = 55; break;      case "여강":    $type = 56; break;
        case "회계":    $type = 57; break;      case "고창":    $type = 58; break;
        case "대":      $type = 59; break;      case "안평":    $type = 60; break;
        case "졸본":    $type = 61; break;      case "이도":    $type = 62; break;
        case "강":      $type = 63; break;      case "저":      $type = 64; break;
        case "흉노":    $type = 65; break;      case "남만":    $type = 66; break;
        case "산월":    $type = 67; break;      case "오환":    $type = 68; break;
        case "왜":      $type = 69; break;      case "호관":    $type = 70; break;
        case "호로":    $type = 71; break;      case "사곡":    $type = 72; break;
        case "함곡":    $type = 73; break;      case "사수":    $type = 74; break;
        case "양평":    $type = 75; break;      case "가맹":    $type = 76; break;
        case "역경":    $type = 77; break;      case "계교":    $type = 78; break;
        case "동황":    $type = 79; break;      case "관도":    $type = 80; break;
        case "정도":    $type = 81; break;      case "합비":    $type = 82; break;
        case "광릉":    $type = 83; break;      case "적도":    $type = 84; break;
        case "가정":    $type = 85; break;      case "기산":    $type = 86; break;
        case "면죽":    $type = 87; break;      case "이릉":    $type = 88; break;
        case "장판":    $type = 89; break;      case "백랑":    $type = 90; break;
        case "적벽":    $type = 91; break;      case "파양":    $type = 92; break;
        case "탐라":    $type = 93; break;      case "유구":    $type = 94; break;
    }
    return $type;
}

function CityNum($num) {
    switch($num) {
        case  1:    $call = "업"  ; break;      case  2:    $call = "허창"; break;
        case  3:    $call = "낙양"; break;      case  4:    $call = "장안"; break;
        case  5:    $call = "성도"; break;      case  6:    $call = "양양"; break;
        case  7:    $call = "건업"; break;      case  8:    $call = "북평"; break;
        case  9:    $call = "남피"; break;      case 10:    $call = "완"  ; break;
        case 11:    $call = "수춘"; break;      case 12:    $call = "서주"; break;
        case 13:    $call = "강릉"; break;      case 14:    $call = "장사"; break;
        case 15:    $call = "시상"; break;      case 16:    $call = "위례"; break;
        case 17:    $call = "계"  ; break;      case 18:    $call = "복양"; break;
        case 19:    $call = "진류"; break;      case 20:    $call = "여남"; break;
        case 21:    $call = "하비"; break;      case 22:    $call = "서량"; break;
        case 23:    $call = "하내"; break;      case 24:    $call = "한중"; break;
        case 25:    $call = "상용"; break;      case 26:    $call = "덕양"; break;
        case 27:    $call = "강주"; break;      case 28:    $call = "건녕"; break;
        case 29:    $call = "남해"; break;      case 30:    $call = "계양"; break;
        case 31:    $call = "오"  ; break;      case 32:    $call = "평양"; break;
        case 33:    $call = "사비"; break;      case 34:    $call = "계림"; break;
        case 35:    $call = "진양"; break;      case 36:    $call = "평원"; break;
        case 37:    $call = "북해"; break;      case 38:    $call = "초"  ; break;
        case 39:    $call = "패"  ; break;      case 40:    $call = "천수"; break;
        case 41:    $call = "안정"; break;      case 42:    $call = "홍농"; break;
        case 43:    $call = "하변"; break;      case 44:    $call = "자동"; break;
        case 45:    $call = "영안"; break;      case 46:    $call = "귀양"; break;
        case 47:    $call = "주시"; break;      case 48:    $call = "운남"; break;
        case 49:    $call = "남영"; break;      case 50:    $call = "교지"; break;
        case 51:    $call = "신야"; break;      case 52:    $call = "강하"; break;
        case 53:    $call = "무릉"; break;      case 54:    $call = "영릉"; break;
        case 55:    $call = "상동"; break;      case 56:    $call = "여강"; break;
        case 57:    $call = "회계"; break;      case 58:    $call = "고창"; break;
        case 59:    $call = "대"  ; break;      case 60:    $call = "안평"; break;
        case 61:    $call = "졸본"; break;      case 62:    $call = "이도"; break;
        case 63:    $call = "강"  ; break;      case 64:    $call = "저"  ; break;
        case 65:    $call = "흉노"; break;      case 66:    $call = "남만"; break;
        case 67:    $call = "산월"; break;      case 68:    $call = "오환"; break;
        case 69:    $call = "왜"  ; break;      case 70:    $call = "호관"; break;
        case 71:    $call = "호로"; break;      case 72:    $call = "사곡"; break;
        case 73:    $call = "함곡"; break;      case 74:    $call = "사수"; break;
        case 75:    $call = "양평"; break;      case 76:    $call = "가맹"; break;
        case 77:    $call = "역경"; break;      case 78:    $call = "계교"; break;
        case 79:    $call = "동황"; break;      case 80:    $call = "관도"; break;
        case 81:    $call = "정도"; break;      case 82:    $call = "합비"; break;
        case 83:    $call = "광릉"; break;      case 84:    $call = "적도"; break;
        case 85:    $call = "가정"; break;      case 86:    $call = "기산"; break;
        case 87:    $call = "면죽"; break;      case 88:    $call = "이릉"; break;
        case 89:    $call = "장판"; break;      case 90:    $call = "백랑"; break;
        case 91:    $call = "적벽"; break;      case 92:    $call = "파양"; break;
        case 93:    $call = "탐라"; break;      case 94:    $call = "유구"; break;
    }
    return $call;
}

function CharCall($call) {
    switch($call) {
        case "은둔":    $type =10; break;
        case "안전";    $type = 9; break;
        case "유지";    $type = 8; break;
        case "재간";    $type = 7; break;
        case "출세";    $type = 6; break;
        case "할거";    $type = 5; break;
        case "정복";    $type = 4; break;
        case "패권";    $type = 3; break;
        case "의협";    $type = 2; break;
        case "대의";    $type = 1; break;
        case "왕좌";    $type = 0; break;
    }
    return $type;
}

function SpecCall($call) {
    switch($call) {
        case "-":       $type =  0; break;
        case "경작":    $type =  1; break;
        case "상재":    $type =  2; break;
        case "발명":    $type =  3; break;

        case "축성":    $type = 10; break;
        case "수비":    $type = 11; break;
        case "통찰":    $type = 12; break;

        case "인덕":    $type = 20; break;

        case "거상":    $type = 30; break;
        case "귀모":    $type = 31; break;

        case "귀병":    $type = 40; break;
        case "신산":    $type = 41; break;
        case "환술":    $type = 42; break;
        case "집중":    $type = 43; break;
        case "신중":    $type = 44; break;
        case "반계":    $type = 45; break;

        case "보병":    $type = 50; break;
        case "궁병":    $type = 51; break;
        case "기병":    $type = 52; break;
        case "공성":    $type = 53; break;

        case "돌격":    $type = 60; break;
        case "무쌍":    $type = 61; break;
        case "견고":    $type = 62; break;
        case "위압":    $type = 63; break;

        case "저격":    $type = 70; break;
        case "필살":    $type = 71; break;
        case "징병":    $type = 72; break;
        case "의술":    $type = 73; break;
        case "격노":    $type = 74; break;
        case "척사":    $type = 75; break;
    }
    return $type;
}

//       0     1     2     3     4     5     6     7
//  0    -, 경작, 상재, 발명                         = 3 지력내정
// 10 축성, 수비, 통찰                               = 3 무력내정
// 20 인덕                                           = 1 통솔내정
// 30 거상, 귀모                                     = 2 공통내정

function getSpecial($connect, $leader, $power, $intel) {
    //통장
    if($leader*0.9 > $power && $leader*0.9 > $intel) {
        $type = array(20, 30, 31);
        $special = $type[rand()%3];
        // 거상, 귀모는 33% * 6% = 2%
        if(($special == 30 || $special == 31) && rand()%100 > 6) {
            $type = array(20, 20);
            $special = $type[rand()%2];
        }
    //무장
    } elseif($power >= $intel) {
        $type = array(10, 11, 12, 30, 31);
        $special = $type[rand()%5];
        // 거상, 귀모는 그중에 20% * 10% = 2%
        if(($special == 30 || $special == 31) && rand()%100 > 10) {
            $type = array(10, 11, 12);
            $special = $type[rand()%3];
        }
    //지장
    } elseif($intel > $power) {
        $type = array(1, 2, 3, 30, 31);
        $special = $type[rand()%5];
        // 거상, 귀모는 그중에 20% * 10% = 2%
        if(($special == 30 || $special == 31) && rand()%100 > 10) {
            $type = array(1, 2, 3);
            $special = $type[rand()%3];
        }
    } else {
        $type = array(30, 31);
        $special = $type[rand()%2];
    }
    return $special;
}

//       0     1     2     3     4     5     6     7
// 40 귀병, 신산, 환술, 집중, 신중, 반계             = 6 지력전투
// 50 보병, 궁병, 기병, 공성                         = 4 무력전투
// 60 돌격, 무쌍, 견고, 위압                         = 4 무장전투
// 70 저격, 필살, 징병, 의술, 격노, 척사             = 6 공통전투

function getSpecial2($connect, $leader, $power, $intel, $nodex=1, $dex0=0, $dex10=0, $dex20=0, $dex30=0, $dex40=0) {
    $special2 = 70;
    // 숙련 10,000: 25%, 40,000: 50%, 100,000: 79%, 160,000: 100%
    $dex = sqrt($dex0 + $dex10 + $dex20 + $dex30 + $dex40);
    $dex = round($dex / 4);
    // 숙련 10,000: 75%, 40,000: 50%, 100,000: 21%, 160,000: 0%
    // 그중 20%만
    if($nodex == 0 && rand()%100 < 20 && rand()%100 > $dex) {
        if(max($dex0, $dex10, $dex20, $dex30, $dex40) == $dex0) {
            $special2 = 50;
            // 숙련이 아얘 없을시 재분배
            if($dex0 <= 0) {
                if($power >= $intel) {
                    $special2 = 50 + rand()%4;
                } else {
                    $special2 = 40;
                }
            }
        } elseif(max($dex0, $dex10, $dex20, $dex30, $dex40) == $dex10) {
            $special2 = 51;
        } elseif(max($dex0, $dex10, $dex20, $dex30, $dex40) == $dex20) {
            $special2 = 52;
        } elseif(max($dex0, $dex10, $dex20, $dex30, $dex40) == $dex30) {
            $special2 = 40;
        } elseif(max($dex0, $dex10, $dex20, $dex30, $dex40) == $dex40) {
            $special2 = 53;
        }
    //무장
    } elseif($power >= $intel) {
        $type = array(60, 61, 62, 63, 70, 71, 72, 73, 74, 75);
        $special2 = $type[rand()%10];
        // 의술은 그중에 10% * 20% = 2%
        if(($special2 == 73) && rand()%100 > 20) {
            $type = array(60, 61, 62, 63, 70, 71, 72, 74, 75);
            $special2 = $type[rand()%9];
        }
    //지장
    } elseif($intel > $power) {
        $type = array(41, 42, 43, 44, 45, 70, 71, 72, 73, 74, 75);
        $special2 = $type[rand()%11];
        // 환술은 그중에 9% * 50% = 4.5%
        if(($special2 == 42) && rand()%100 > 50) {
            $type = array(41, 43, 44, 45, 70, 71, 72, 74, 75);
            $special2 = $type[rand()%9];
        }
        // 의술은 그중에 9% * 20% = 1.8%
        if(($special2 == 73) && rand()%100 > 20) {
            $type = array(41, 42, 43, 44, 45, 70, 71, 72, 74, 75);
            $special2 = $type[rand()%10];
        }
    } else {
        $type = array(70, 71, 72, 73, 74, 75);
        $special2 = $type[rand()%6];
    }
    return $special2;
}

function getConnect($con) {
    if($con < 50)        $conname = "안함";
    elseif($con <   100) $conname = "무관심";
    elseif($con <   200) $conname = "가끔";
    elseif($con <   400) $conname = "보통";
    elseif($con <   800) $conname = "자주";
    elseif($con <  1600) $conname = "열심";
    elseif($con <  3200) $conname = "중독";
    elseif($con <  6400) $conname = "폐인";
    elseif($con < 12800) $conname = "경고";
    else $conname = "헐...";

    return $conname;
}

function getCityLevel($level) {
    switch($level) {
        case 8: $call = "특"; break;
        case 7: $call = "대"; break;
        case 6: $call = "중"; break;
        case 5: $call = "소"; break;
        case 4: $call = "이"; break;
        case 3: $call = "관"; break;
        case 2: $call = "진"; break;
        case 1: $call = "수"; break;
        default:$call = "?"; break;
    }
    return $call;
}

function getRegion($region) {
    switch($region) {
        case 8: $call = "동이"; break;
        case 7: $call = "오월"; break;
        case 6: $call = "초"; break;
        case 5: $call = "남중"; break;
        case 4: $call = "서촉"; break;
        case 3: $call = "서북"; break;
        case 2: $call = "중원"; break;
        case 1: $call = "하북"; break;
        default:$call = "?"; break;
    }
    return $call;
}

function getNationChiefLevel($level) {
    switch($level) {
        case 7: $lv = 5; break;
        case 6: $lv = 5; break;
        case 5: $lv = 7; break;
        case 4: $lv = 7; break;
        case 3: $lv = 9; break;
        case 2: $lv = 9; break;
        case 1: $lv = 11; break;
        case 0: $lv = 11; break;
    }
    return $lv;
}

function getNationLevel($level) {
    switch($level) {
        case 7: $call = "황제"; break;
        case 6: $call = "왕"; break;
        case 5: $call = "공"; break;
        case 4: $call = "주목"; break;
        case 3: $call = "주자사"; break;
        case 2: $call = "군벌"; break;
        case 1: $call = "호족"; break;
        case 0: $call = "방랑군"; break;
    }
    return $call;
}

function getGenChar($type) {
    switch($type) {
        case 10: $call = "은둔"; break;
        case  9: $call = "안전"; break;
        case  8: $call = "유지"; break;
        case  7: $call = "재간"; break;
        case  6: $call = "출세"; break;
        case  5: $call = "할거"; break;
        case  4: $call = "정복"; break;
        case  3: $call = "패권"; break;
        case  2: $call = "의협"; break;
        case  1: $call = "대의"; break;
        case  0: $call = "왕좌"; break;
    }
    return $call;
}

function getGenSpecial($type) {
    switch($type) {
        case  0: $call = "-"; break;
        case  1: $call = "경작"; break;
        case  2: $call = "상재"; break;
        case  3: $call = "발명"; break;

        case 10: $call = "축성"; break;
        case 11: $call = "수비"; break;
        case 12: $call = "통찰"; break;

        case 20: $call = "인덕"; break;

        case 30: $call = "거상"; break;
        case 31: $call = "귀모"; break;

        case 40: $call = "귀병"; break;
        case 41: $call = "신산"; break;
        case 42: $call = "환술"; break;
        case 43: $call = "집중"; break;
        case 44: $call = "신중"; break;
        case 45: $call = "반계"; break;

        case 50: $call = "보병"; break;
        case 51: $call = "궁병"; break;
        case 52: $call = "기병"; break;
        case 53: $call = "공성"; break;

        case 60: $call = "돌격"; break;
        case 61: $call = "무쌍"; break;
        case 62: $call = "견고"; break;
        case 63: $call = "위압"; break;

        case 70: $call = "저격"; break;
        case 71: $call = "필살"; break;
        case 72: $call = "징병"; break;
        case 73: $call = "의술"; break;
        case 74: $call = "격노"; break;
        case 75: $call = "척사"; break;
    }
    return $call;
}

function getNationType($type) {
    switch($type) {
        case 13: $call = "명 가"; break;
        case 12: $call = "음 양 가"; break;
        case 11: $call = "종 횡 가"; break;
        case 10: $call = "불 가"; break;
        case 9: $call = "도 적"; break;
        case 8: $call = "오 두 미 도"; break;
        case 7: $call = "태 평 도"; break;
        case 6: $call = "도 가"; break;
        case 5: $call = "묵 가"; break;
        case 4: $call = "덕 가"; break;
        case 3: $call = "병 가"; break;
        case 2: $call = "유 가"; break;
        case 1: $call = "법 가"; break;
        case 0: $call = "-"; break;
    }
    return $call;
}

function getNationType2($type, $skin) {
    switch($type) {
        case 13: $call = "<font color=cyan>기술↑ 인구↑</font> <font color=magenta>쌀수입↓ 수성↓</font>"; break;
        case 12: $call = "<font color=cyan>내정↑ 인구↑</font> <font color=magenta>기술↓ 전략↓</font>"; break;
        case 11: $call = "<font color=cyan>전략↑ 수성↑</font> <font color=magenta>금수입↓ 내정↓</font>"; break;
        case 10: $call = "<font color=cyan>민심↑ 수성↑</font> <font color=magenta>금수입↓</font>"; break;
        case 9: $call = "<font color=cyan>계략↑</font> <font color=magenta>금수입↓ 치안↓ 민심↓</font>"; break;
        case 8: $call = "<font color=cyan>쌀수입↑ 인구↑</font> <font color=magenta>기술↓ 수성↓ 내정↓</font>"; break;
        case 7: $call = "<font color=cyan>인구↑ 민심↑</font> <font color=magenta>기술↓ 수성↓</font>"; break;
        case 6: $call = "<font color=cyan>인구↑</font> <font color=magenta>기술↓ 치안↓</font>"; break;
        case 5: $call = "<font color=cyan>수성↑</font> <font color=magenta>기술↓</font>"; break;
        case 4: $call = "<font color=cyan>치안↑인구↑ 민심↑</font> <font color=magenta>쌀수입↓ 수성↓</font>"; break;
        case 3: $call = "<font color=cyan>기술↑ 수성↑</font> <font color=magenta>인구↓ 민심↓</font>"; break;
        case 2: $call = "<font color=cyan>내정↑ 민심↑</font> <font color=magenta>쌀수입↓</font>"; break;
        case 1: $call = "<font color=cyan>금수입↑ 치안↑</font> <font color=magenta>인구↓ 민심↓</font>"; break;
        case 0: $call = "-"; break;
    }
    if($skin == 0) {
        $call = str_replace("<font color=cyan>","", $call);
        $call = str_replace("<font color=magenta>","", $call);
        $call = str_replace("</font>","", $call);
    }
    return $call;
}

function getLevel($level, $nlevel=8) {
    if($level >= 0 && $level <= 4) { $nlevel = 0; }
    $code = $nlevel * 100 + $level;
    switch($code) {
        case 812: $call =     "군주"; break;
        case 811: $call =     "참모"; break;
        case 810: $call =  "제1장군"; break;
        case 809: $call =  "제1모사"; break;
        case 808: $call =  "제2장군"; break;
        case 807: $call =  "제2모사"; break;
        case 806: $call =  "제3장군"; break;
        case 805: $call =  "제3모사"; break;

        case 712: $call =     "황제"; break;    case 612: $call =       "왕"; break;
        case 711: $call =     "승상"; break;    case 611: $call =   "광록훈"; break;
        case 710: $call =   "위장군"; break;    case 610: $call =   "전장군"; break;
        case 709: $call =     "사공"; break;    case 609: $call =   "상서령"; break;
        case 708: $call = "표기장군"; break;    case 608: $call =   "좌장군"; break;
        case 707: $call =     "태위"; break;    case 607: $call =   "중서령"; break;
        case 706: $call = "거기장군"; break;    case 606: $call =   "우장군"; break;
        case 705: $call =     "사도"; break;    case 605: $call =   "비서령"; break;

        case 512: $call =       "공"; break;    case 412: $call =     "주목"; break;
        case 511: $call = "광록대부"; break;    case 411: $call =   "태사령"; break;
        case 510: $call = "안국장군"; break;    case 410: $call = "아문장군"; break;
        case 509: $call =   "집금오"; break;    case 409: $call =     "낭중"; break;
        case 508: $call = "파로장군"; break;    case 408: $call =     "호군"; break;
        case 507: $call =     "소부"; break;    case 407: $call = "종사중랑"; break;

        case 312: $call =   "주자사"; break;    case 212: $call =     "군벌"; break;
        case 311: $call =     "주부"; break;    case 211: $call =     "참모"; break;
        case 310: $call =   "편장군"; break;    case 210: $call =   "비장군"; break;
        case 309: $call = "간의대부"; break;    case 209: $call =   "부참모"; break;

        case 112: $call =     "영주"; break;    case  12: $call =     "두목"; break;
        case 111: $call =     "참모"; break;    case  11: $call =   "부두목"; break;

        case   4: $call =     "태수"; break;
        case   3: $call =     "군사"; break;
        case   2: $call =     "시중"; break;
        case   1: $call =     "일반"; break;
        case   0: $call =     "재야"; break;
        default:  $call =        "-"; break;
    }
    return $call;
}

function getCall($leader, $power, $intel) {
    global $_goodgenleader, $_goodgenpower, $_goodgenintel;

    $call = "평범";
    if($leader >= $_goodgenleader && $power >= $_goodgenpower && $intel >= $_goodgenintel) {
        $call = "만능";
    } elseif($leader >= $_goodgenleader && $power >= $_goodgenpower) {
        $call = "용장";
    } elseif($leader >= $_goodgenleader && $intel >= $_goodgenintel) {
        $call = "지장";
    } elseif($power >= $_goodgenpower && $intel >= $_goodgenintel) {
        $call = "명장";
    } elseif($leader >= $_goodgenleader) {
        $call = "명사";
    } elseif($power >= $_goodgenpower) {
        $call = "용맹";
    } elseif($intel >= $_goodgenintel) {
        $call = "현명";
    }
    return $call;
}

function getDed($dedication) {
    if($dedication < 1 ) $level2 = "무품관";
    elseif($dedication < 10*10) $level2 = "30품관";
    elseif($dedication < 20*20) $level2 = "29품관";
    elseif($dedication < 30*30) $level2 = "28품관";
    elseif($dedication < 40*40) $level2 = "27품관";
    elseif($dedication < 50*50) $level2 = "26품관";
    elseif($dedication < 60*60) $level2 = "25품관";
    elseif($dedication < 70*70) $level2 = "24품관";
    elseif($dedication < 80*80) $level2 = "23품관";
    elseif($dedication < 90*90) $level2 = "22품관";
    elseif($dedication < 100*100) $level2 = "21품관";
    elseif($dedication < 110*110) $level2 = "20품관";
    elseif($dedication < 120*120) $level2 = "19품관";
    elseif($dedication < 130*130) $level2 = "18품관";
    elseif($dedication < 140*140) $level2 = "17품관";
    elseif($dedication < 150*150) $level2 = "16품관";
    elseif($dedication < 160*160) $level2 = "15품관";
    elseif($dedication < 170*170) $level2 = "14품관";
    elseif($dedication < 180*180) $level2 = "13품관";
    elseif($dedication < 190*190) $level2 = "12품관";
    elseif($dedication < 200*200) $level2 = "11품관"; // 40000
    elseif($dedication < 210*210) $level2 = "10품관"; // 44100
    elseif($dedication < 220*220) $level2 =  "9품관"; // 48400
    elseif($dedication < 230*230) $level2 =  "8품관"; // 52900
    elseif($dedication < 240*240) $level2 =  "7품관"; // 57600
    elseif($dedication < 250*250) $level2 =  "6품관"; // 62500
    elseif($dedication < 260*260) $level2 =  "5품관"; // 67600
    elseif($dedication < 270*270) $level2 =  "4품관"; // 72900
    elseif($dedication < 280*280) $level2 =  "3품관"; // 78400
    elseif($dedication < 290*290) $level2 =  "2품관"; // 84100
    else {
        $level2 = "1품관";
    }

    return $level2;
}

function getExpLevel($experience) {
    if($experience < 1000) {
        $level = floor($experience / 100);
    } else {
        for($level = 0; $experience > (($level+1)*($level+1)*10); $level++) {
        }
    }

    return $level;
}

function getDedLevel($dedication) {
    for($level = 0; $dedication > (($level+1)*($level+1)*100); $level++) {
    }

    return $level;
}

function expStatus($exp) {
    global $_upgradeLimit;
    return $exp / $_upgradeLimit * 100;
}

function getLevelPer($exp, $level) {
    if($exp < 100)      { $per = $exp; }
    elseif($exp < 1000) { $per = $exp - ($level)*100; }
    else                { $per = ($exp - 10*$level*$level) / (2*$level+1) * 10; }
    return $per;
}

function getBill($dedication) {
    for($level = 0; $dedication > (($level+1)*($level+1)*100); $level++) {
    }

    return ($level * 200 + 400);
}

function getHonor($experience) {
    if($experience < 640 ) $honor = "전무";
    elseif($experience < 2560) $honor = "무명";
    elseif($experience < 5760) $honor = "신동";
    elseif($experience < 10240) $honor = "약간";
    elseif($experience < 16000) $honor = "평범";
    elseif($experience < 23040) $honor = "지역적";
    elseif($experience < 31360) $honor = "전국적";
    elseif($experience < 40960) $honor = "세계적";
    elseif($experience < 45000) $honor = "유명";
    elseif($experience < 51840) $honor = "명사";
    elseif($experience < 55000) $honor = "호걸";
    elseif($experience < 64000) $honor = "효웅";
    elseif($experience < 77440) $honor = "영웅";
    else $honor = "구세주";

    return $honor;
}

function getTypename($type) {
    switch($type) {
        case  0: $typename =     "보병"; break;
        case  1: $typename =   "청주병"; break;
        case  2: $typename =     "수병"; break;
        case  3: $typename =   "자객병"; break;
        case  4: $typename =   "근위병"; break;
        case  5: $typename =   "등갑병"; break;

        case 10: $typename =     "궁병"; break;
        case 11: $typename =   "궁기병"; break;
        case 12: $typename =   "연노병"; break;
        case 13: $typename =   "강궁병"; break;
        case 14: $typename =   "석궁병"; break;

        case 20: $typename =     "기병"; break;
        case 21: $typename =   "백마병"; break;
        case 22: $typename = "중장기병"; break;
        case 23: $typename = "돌격기병"; break;
        case 24: $typename =   "철기병"; break;
        case 25: $typename = "수렵기병"; break;
        case 26: $typename =   "맹수병"; break;
        case 27: $typename = "호표기병"; break;

        case 30: $typename =     "귀병"; break;
        case 31: $typename =   "신귀병"; break;
        case 32: $typename =   "백귀병"; break;
        case 33: $typename =   "흑귀병"; break;
        case 34: $typename =   "악귀병"; break;
        case 35: $typename =   "남귀병"; break;
        case 36: $typename =   "황귀병"; break;
        case 37: $typename =   "천귀병"; break;
        case 38: $typename =   "마귀병"; break;

        case 40: $typename =     "정란"; break;
        case 41: $typename =     "충차"; break;
        case 42: $typename =   "벽력거"; break;
        case 43: $typename =     "목우"; break;
    }
    return $typename;
}

function getCost($connect, $armtype) {
    $query = "select cst{$armtype} from game where no='1'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $admin = MYDB_fetch_array($result);

    return $admin["cst{$armtype}"];
}

function TechLimit($startyear, $year, $tech) {
    $limit = 0;
    if($year < $startyear+ 5 && $tech >=  1000) { $limit = 1; }
    if($year < $startyear+10 && $tech >=  2000) { $limit = 1; }
    if($year < $startyear+15 && $tech >=  3000) { $limit = 1; }
    if($year < $startyear+20 && $tech >=  4000) { $limit = 1; }
    if($year < $startyear+25 && $tech >=  5000) { $limit = 1; }
    if($year < $startyear+30 && $tech >=  6000) { $limit = 1; }
    if($year < $startyear+35 && $tech >=  7000) { $limit = 1; }
    if($year < $startyear+40 && $tech >=  8000) { $limit = 1; }
    if($year < $startyear+45 && $tech >=  9000) { $limit = 1; }
    if($year < $startyear+50 && $tech >= 10000) { $limit = 1; }
    if($year < $startyear+55 && $tech >= 11000) { $limit = 1; }
    if($year < $startyear+60 && $tech >= 12000) { $limit = 1; }
    return $limit;
}

function getTechAbil($tech) {
    if($tech < 1000)      { $abil =   0; }
    elseif($tech < 2000)  { $abil =  25; }
    elseif($tech < 3000)  { $abil =  50; }
    elseif($tech < 4000)  { $abil =  75; }
    elseif($tech < 5000)  { $abil = 100; }
    elseif($tech < 6000)  { $abil = 125; }
    elseif($tech < 7000)  { $abil = 150; }
    elseif($tech < 8000)  { $abil = 175; }
    elseif($tech < 9000)  { $abil = 200; }
    elseif($tech < 10000) { $abil = 225; }
    elseif($tech < 11000) { $abil = 250; }
    elseif($tech < 12000) { $abil = 275; }
    else                  { $abil = 300; }
    return $abil;
}

function getTechCost($tech) {
    if($tech < 1000)      { $cost = 1.00; }
    elseif($tech < 2000)  { $cost = 1.15; }
    elseif($tech < 3000)  { $cost = 1.30; }
    elseif($tech < 4000)  { $cost = 1.45; }
    elseif($tech < 5000)  { $cost = 1.60; }
    elseif($tech < 6000)  { $cost = 1.75; }
    elseif($tech < 7000)  { $cost = 1.90; }
    elseif($tech < 8000)  { $cost = 2.05; }
    elseif($tech < 9000)  { $cost = 2.20; }
    elseif($tech < 10000) { $cost = 2.35; }
    elseif($tech < 11000) { $cost = 2.50; }
    elseif($tech < 12000) { $cost = 2.65; }
    else                  { $cost = 2.80; }
    return $cost;
}

function getTechCall($tech) {
    if($tech < 1000)      { $str = "0등급"; }
    elseif($tech < 2000)  { $str = "1등급"; }
    elseif($tech < 3000)  { $str = "2등급"; }
    elseif($tech < 4000)  { $str = "3등급"; }
    elseif($tech < 5000)  { $str = "4등급"; }
    elseif($tech < 6000)  { $str = "5등급"; }
    elseif($tech < 7000)  { $str = "6등급"; }
    elseif($tech < 8000)  { $str = "7등급"; }
    elseif($tech < 9000)  { $str = "8등급"; }
    elseif($tech < 10000) { $str = "9등급"; }
    elseif($tech < 11000) { $str = "10등급"; }
    elseif($tech < 12000) { $str = "11등급"; }
    else                  { $str = "12등급"; }
    return $str;
}

function getDexCall($dex) {
    if($dex < 2500)        { $str = "<font color=navy>F-</font>"; }
    elseif($dex <    7500) { $str = "<font color=navy>F</font>"; }
    elseif($dex <   15000) { $str = "<font color=navy>F+</font>"; }
    elseif($dex <   25000) { $str = "<font color=skyblue>E-</font>"; }
    elseif($dex <   37500) { $str = "<font color=skyblue>E</font>"; }
    elseif($dex <   52500) { $str = "<font color=skyblue>E+</font>"; }
    elseif($dex <   70000) { $str = "<font color=seagreen>D-</font>"; }
    elseif($dex <   90000) { $str = "<font color=seagreen>D</font>"; }
    elseif($dex <  112500) { $str = "<font color=seagreen>D+</font>"; }
    elseif($dex <  137500) { $str = "<font color=teal>C-</font>"; }
    elseif($dex <  165000) { $str = "<font color=teal>C</font>"; }
    elseif($dex <  195000) { $str = "<font color=teal>C+</font>"; }
    elseif($dex <  227500) { $str = "<font color=limegreen>B-</font>"; }
    elseif($dex <  262500) { $str = "<font color=limegreen>B</font>"; }
    elseif($dex <  300000) { $str = "<font color=limegreen>B+</font>"; }
    elseif($dex <  340000) { $str = "<font color=gold>A-</font>"; }
    elseif($dex <  382500) { $str = "<font color=gold>A</font>"; }
    elseif($dex <  427500) { $str = "<font color=gold>A+</font>"; }
    elseif($dex <  475000) { $str = "<font color=darkorange>S-</font>"; }
    elseif($dex <  525000) { $str = "<font color=darkorange>S</font>"; }
    elseif($dex <  577500) { $str = "<font color=darkorange>S+</font>"; }
    elseif($dex <  632500) { $str = "<font color=tomato>SS-</font>"; }
    elseif($dex <  690000) { $str = "<font color=tomato>SS</font>"; }
    elseif($dex <  750000) { $str = "<font color=tomato>SS+</font>"; }
    elseif($dex <  812500) { $str = "<font color=red>SSS-</font>"; }
    elseif($dex <  877500) { $str = "<font color=red>SSS</font>"; }
    elseif($dex <  945000) { $str = "<font color=red>SSS+</font>"; }
    elseif($dex < 1015000) { $str = "<font color=darkviolet>Z-</font>"; }
    elseif($dex < 1087500) { $str = "<font color=darkviolet>Z</font>"; }
    elseif($dex < 1162500) { $str = "<font color=darkviolet>Z+</font>"; }
    else                   { $str = "<font color=white>?</font>"; }
    return $str;
}

function getDexLevel($dex) {
    if($dex < 2500)        { $lvl =  0; }
    elseif($dex <    7500) { $lvl =  1; }
    elseif($dex <   15000) { $lvl =  2; }
    elseif($dex <   25000) { $lvl =  3; }
    elseif($dex <   37500) { $lvl =  4; }
    elseif($dex <   52500) { $lvl =  5; }
    elseif($dex <   70000) { $lvl =  6; }
    elseif($dex <   90000) { $lvl =  7; }
    elseif($dex <  112500) { $lvl =  8; }
    elseif($dex <  137500) { $lvl =  9; }
    elseif($dex <  165000) { $lvl = 10; }
    elseif($dex <  195000) { $lvl = 11; }
    elseif($dex <  227500) { $lvl = 12; }
    elseif($dex <  262500) { $lvl = 13; }
    elseif($dex <  300000) { $lvl = 14; }
    elseif($dex <  340000) { $lvl = 15; }
    elseif($dex <  382500) { $lvl = 16; }
    elseif($dex <  427500) { $lvl = 17; }
    elseif($dex <  475000) { $lvl = 18; }
    elseif($dex <  525000) { $lvl = 19; }
    elseif($dex <  577500) { $lvl = 20; }
    elseif($dex <  632500) { $lvl = 21; }
    elseif($dex <  690000) { $lvl = 22; }
    elseif($dex <  750000) { $lvl = 23; }
    elseif($dex <  812500) { $lvl = 24; }
    elseif($dex <  877500) { $lvl = 25; }
    elseif($dex <  945000) { $lvl = 26; }
    elseif($dex < 1015000) { $lvl = 27; }
    elseif($dex < 1087500) { $lvl = 28; }
    elseif($dex < 1162500) { $lvl = 29; }
    else                   { $lvl = 30; }
    return $lvl;
}

function getDexLog($dex1, $dex2) {
    $ratio = (getDexLevel($dex1) - getDexLevel($dex2)) / 50 + 1;
    return $ratio;
}

function getGenDex($general, $type) {
    $type = floor($type / 10) * 10;
    return $general["dex{$type}"];
}

function addGenDex($connect, $no, $type, $exp) {
    $type = floor($type / 10) * 10;
    $dexType = "dex{$type}";
    if($type == 30) { $exp = round($exp * 0.90); }     //귀병은 90%효율
    elseif($type == 40) { $exp = round($exp * 0.90); } //차병은 90%효율

    $query = "update general set {$dexType}={$dexType}+{$exp} where no='$no'";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
}

function getWeapName($weap) {
    switch($weap) {
        case  0: $weapname = "-"; break;
        case  1: $weapname = "단도(+1)"; break;
        case  2: $weapname = "단궁(+2)"; break;
        case  3: $weapname = "단극(+3)"; break;
        case  4: $weapname = "목검(+4)"; break;
        case  5: $weapname = "죽창(+5)"; break;
        case  6: $weapname = "소부(+6)"; break;

        case  7: $weapname = "동추(+7)"; break;
        case  8: $weapname = "철편(+7)"; break;
        case  9: $weapname = "철쇄(+7)"; break;
        case 10: $weapname = "맥궁(+7)"; break;
        case 11: $weapname = "유성추(+8)"; break;
        case 12: $weapname = "철질여골(+8)"; break;
        case 13: $weapname = "쌍철극(+9)"; break;
        case 14: $weapname = "동호비궁(+9)"; break;
        case 15: $weapname = "삼첨도(+10)"; break;
        case 16: $weapname = "대부(+10)"; break;
        case 17: $weapname = "고정도(+11)"; break;
        case 18: $weapname = "이광궁(+11)"; break;
        case 19: $weapname = "철척사모(+12)"; break;
        case 20: $weapname = "칠성검(+12)"; break;
        case 21: $weapname = "사모(+13)"; break;
        case 22: $weapname = "양유기궁(+13)"; break;
        case 23: $weapname = "언월도(+14)"; break;
        case 24: $weapname = "방천화극(+14)"; break;
        case 25: $weapname = "청홍검(+15)"; break;
        case 26: $weapname = "의천검(+15)"; break;
    }
    return $weapname;
}

function getWeapEff($weap) {
    switch($weap) {
        case  7: $weap =  7; break;
        case  8: $weap =  7; break;
        case  9: $weap =  7; break;
        case 10: $weap =  7; break;
        case 11: $weap =  8; break;
        case 12: $weap =  8; break;
        case 13: $weap =  9; break;
        case 14: $weap =  9; break;
        case 15: $weap = 10; break;
        case 16: $weap = 10; break;
        case 17: $weap = 11; break;
        case 18: $weap = 11; break;
        case 19: $weap = 12; break;
        case 20: $weap = 12; break;
        case 21: $weap = 13; break;
        case 22: $weap = 13; break;
        case 23: $weap = 14; break;
        case 24: $weap = 14; break;
        case 25: $weap = 15; break;
        case 26: $weap = 15; break;
        default: break;
    }
    return $weap;
}

function getBookName($book) {
    switch($book) {
        case  0: $bookname = "-"; break;
        case  1: $bookname = "효경전(+1)"; break;
        case  2: $bookname = "회남자(+2)"; break;
        case  3: $bookname = "변도론(+3)"; break;
        case  4: $bookname = "건상역주(+4)"; break;
        case  5: $bookname = "여씨춘추(+5)"; break;
        case  6: $bookname = "사민월령(+6)"; break;

        case  7: $bookname = "위료자(+7)"; break;
        case  8: $bookname = "사마법(+7)"; break;
        case  9: $bookname = "한서(+7)"; break;
        case 10: $bookname = "논어(+7)"; break;
        case 11: $bookname = "전론(+8)"; break;
        case 12: $bookname = "사기(+8)"; break;
        case 13: $bookname = "장자(+9)"; break;
        case 14: $bookname = "역경(+9)"; break;
        case 15: $bookname = "시경(+10)"; break;
        case 16: $bookname = "구국론(+10)"; break;
        case 17: $bookname = "상군서(+11)"; break;
        case 18: $bookname = "춘추전(+11)"; break;
        case 19: $bookname = "산해경(+12)"; break;
        case 20: $bookname = "맹덕신서(+12)"; break;
        case 21: $bookname = "관자(+13)"; break;
        case 22: $bookname = "병법24편(+13)"; break;
        case 23: $bookname = "한비자(+14)"; break;
        case 24: $bookname = "오자병법(+14)"; break;
        case 25: $bookname = "노자(+15)"; break;
        case 26: $bookname = "손자병법(+15)"; break;
    }
    return $bookname;
}

function getBookEff($book) {
    switch($book) {
        case  7: $book =  7; break;
        case  8: $book =  7; break;
        case  9: $book =  7; break;
        case 10: $book =  7; break;
        case 11: $book =  8; break;
        case 12: $book =  8; break;
        case 13: $book =  9; break;
        case 14: $book =  9; break;
        case 15: $book = 10; break;
        case 16: $book = 10; break;
        case 17: $book = 11; break;
        case 18: $book = 11; break;
        case 19: $book = 12; break;
        case 20: $book = 12; break;
        case 21: $book = 13; break;
        case 22: $book = 13; break;
        case 23: $book = 14; break;
        case 24: $book = 14; break;
        case 25: $book = 15; break;
        case 26: $book = 15; break;
        default: break;
    }
    return $book;
}

function getHorseName($horse) {
    switch($horse) {
        case  0: $horsename = "-"; break;
        case  1: $horsename = "노기(+1)"; break;
        case  2: $horsename = "조랑(+2)"; break;
        case  3: $horsename = "노새(+3)"; break;
        case  4: $horsename = "나귀(+4)"; break;
        case  5: $horsename = "갈색마(+5)"; break;
        case  6: $horsename = "흑색마(+6)"; break;

        case  7: $horsename = "백마(+7)"; break;
        case  8: $horsename = "백마(+7)"; break;
        case  9: $horsename = "기주마(+7)"; break;
        case 10: $horsename = "기주마(+7)"; break;
        case 11: $horsename = "양주마(+8)"; break;
        case 12: $horsename = "양주마(+8)"; break;
        case 13: $horsename = "과하마(+9)"; break;
        case 14: $horsename = "과하마(+9)"; break;
        case 15: $horsename = "대완마(+10)"; break;
        case 16: $horsename = "대완마(+10)"; break;
        case 17: $horsename = "서량마(+11)"; break;
        case 18: $horsename = "서량마(+11)"; break;
        case 19: $horsename = "사륜거(+12)"; break;
        case 20: $horsename = "사륜거(+12)"; break;
        case 21: $horsename = "절영(+13)"; break;
        case 22: $horsename = "적로(+13)"; break;
        case 23: $horsename = "적란마(+14)"; break;
        case 24: $horsename = "조황비전(+14)"; break;
        case 25: $horsename = "한혈마(+15)"; break;
        case 26: $horsename = "적토마(+15)"; break;
    }
    return $horsename;
}

function getHorseEff($horse) {
    switch($horse) {
        case  7: $horse =  7; break;
        case  8: $horse =  7; break;
        case  9: $horse =  7; break;
        case 10: $horse =  7; break;
        case 11: $horse =  8; break;
        case 12: $horse =  8; break;
        case 13: $horse =  9; break;
        case 14: $horse =  9; break;
        case 15: $horse = 10; break;
        case 16: $horse = 10; break;
        case 17: $horse = 11; break;
        case 18: $horse = 11; break;
        case 19: $horse = 12; break;
        case 20: $horse = 12; break;
        case 21: $horse = 13; break;
        case 22: $horse = 13; break;
        case 23: $horse = 14; break;
        case 24: $horse = 14; break;
        case 25: $horse = 15; break;
        case 26: $horse = 15; break;
        default: break;
    }
    return $horse;
}

function getItemName($item) {
    switch($item) {
        case  0: $itemname = "-"; break;
        case  1: $itemname = "환약(치료)"; break;
        case  2: $itemname = "수극(저격)"; break;
        case  3: $itemname = "탁주(사기)"; break;
        case  4: $itemname = "청주(훈련)"; break;
        case  5: $itemname = "이추(계략)"; break;
        case  6: $itemname = "향낭(계략)"; break;

        case  7: $itemname = "오석산(치료)"; break;
        case  8: $itemname = "무후행군(치료)"; break;
        case  9: $itemname = "도소연명(치료)"; break;
        case 10: $itemname = "칠엽청점(치료)"; break;
        case 11: $itemname = "정력견혈(치료)"; break;
        case 12: $itemname = "과실주(훈련)"; break;
        case 13: $itemname = "이강주(훈련)"; break;
        case 14: $itemname = "의적주(사기)"; break;
        case 15: $itemname = "두강주(사기)"; break;
        case 16: $itemname = "보령압주(사기)"; break;
        case 17: $itemname = "철벽서(훈련)"; break;
        case 18: $itemname = "단결도(훈련)"; break;
        case 19: $itemname = "춘화첩(사기)"; break;
        case 20: $itemname = "초선화(사기)"; break;
        case 21: $itemname = "육도(계략)"; break;
        case 22: $itemname = "삼략(계략)"; break;
        case 23: $itemname = "청낭서(의술)"; break;
        case 24: $itemname = "태평청령(의술)"; break;
        case 25: $itemname = "태평요술(회피)"; break;
        case 26: $itemname = "둔갑천서(회피)"; break;
    }
    return $itemname;
}

function getItemCost2($weap) {
    switch($weap) {
        case  0: $weapcost = 0; break;
        case  1: $weapcost = 100; break;
        case  2: $weapcost = 1000; break;
        case  3: $weapcost = 1000; break;
        case  4: $weapcost = 1000; break;
        case  5: $weapcost = 1000; break;
        case  6: $weapcost = 3000; break;
        default: $weapcost = 200; break;
    }
    return $weapcost;
}

function getItemCost($weap) {
    switch($weap) {
        case  0: $weapcost = 0; break;
        case  1: $weapcost = 1000; break;
        case  2: $weapcost = 3000; break;
        case  3: $weapcost = 6000; break;
        case  4: $weapcost = 10000; break;
        case  5: $weapcost = 15000; break;
        case  6: $weapcost = 21000; break;
        default: $weapcost = 200; break;
    }
    return $weapcost;
}

function getTurn($connect, $general, $type, $font=1) {
    $turn[0] = $general["turn0"];

    if($type >= 1) {
        $turn[1] = $general["turn1"];
        $turn[2] = $general["turn2"];
        $turn[3] = $general["turn3"];
        $turn[4] = $general["turn4"];
        //$turn[5] = $general["turn5"];
    }
    if($type >= 2) {
        $turn[6] = $general["turn6"];
        $turn[7] = $general["turn7"];
        $turn[8] = $general["turn8"];
        $turn[9] = $general["turn9"];
        $turn[10] = $general["turn10"];
        $turn[11] = $general["turn11"];
        $turn[12] = $general["turn12"];
        $turn[13] = $general["turn13"];
        $turn[14] = $general["turn14"];
        $turn[15] = $general["turn15"];
        $turn[16] = $general["turn16"];
        $turn[17] = $general["turn17"];
        $turn[18] = $general["turn18"];
        $turn[19] = $general["turn19"];
        $turn[20] = $general["turn20"];
        $turn[21] = $general["turn21"];
        $turn[22] = $general["turn22"];
        $turn[23] = $general["turn23"];
    }

    if($type == 0) { $count = 1; }
    elseif($type == 1) { $count = 5; }
    elseif($type == 2) { $count = 24; }

    for($i=0; $i < $count; $i++) {
        $command = DecodeCommand($turn[$i]);

        switch($command[0]) {
            case 0:  $str[$i] = "휴식"; break; //휴식
            case 1:  $str[$i] = "농지 개간"; break; //농업
            case 2:  $str[$i] = "상업 투자"; break; //상업
            case 3:  $str[$i] = "기술 연구"; break; //기술
            case 4:  $str[$i] = "주민 선정"; break; //선정
            case 5:  $str[$i] = "수비 강화"; break; //수비
            case 6:  $str[$i] = "성벽 보수"; break; //성벽
            case 7:  $str[$i] = "정착 장려"; break; //정착 장려
            case 8:  $str[$i] = "치안 강화"; break; //치안
            case 9:  $str[$i] = "물자 조달"; break; //조달

            case 11: //징병
                $third = getTypename($command[2]);
                $double = $command[1];
                $str[$i] = "【{$third}】 {$double}00명 징병";
                break;
            case 12: //모병
                $third = getTypename($command[2]);
                $double = $command[1];
                $str[$i] = "【{$third}】 {$double}00명 모병";
                break;
            case 13: //훈련
                $str[$i] = "훈련";
                break;
            case 14: //사기진작
                $str[$i] = "사기진작";
                break;
            case 15: //전투태세
                $str[$i] = "전투태세";
                break;
            case 16: //전쟁
                $double = $command[1];
                $city = getCity($connect, $double, "name");
                $str[$i] = "【{$city['name']}】(으)로 출병";
                break;
            case 17: //소집해제
                $str[$i] = "소집 해제";
                break;

            case 21: //이동
                $double = $command[1];
                $city = getCity($connect, $double, "name");
                $str[$i] = "【{$city['name']}】(으)로 이동";
                break;
            case 22: //등용
                $double = $command[1];

                $query = "select name from general where no='$double'";
                $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
                $general = MYDB_fetch_array($result);

                $str[$i] = "【{$general['name']}】(을)를 등용";
                break;
            case 25: //임관
                $double = $command[1];

                $query = "select name from nation where nation='$double'";
                $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
                $nation = MYDB_fetch_array($result);

                if(!$nation['name']) { $nation['name'] = '????'; }

                $str[$i] = "【{$nation['name']}】(으)로 임관";
                break;
            case 26: //집합
                $str[$i] = "집합";
                break;
            case 28: //귀환
                $str[$i] = "담당 도시로 귀환";
                break;
            case 29: //인재탐색
                $str[$i] = "인재 탐색";
                break;
            case 30: //강행
                $double = $command[1];
                $city = getCity($connect, $double, "name");
                $str[$i] = "【{$city['name']}】(으)로 강행";
                break;
                
            case 31: //첩보
                $double = $command[1];
                $city= getCity($connect, $double, "name");
                $str[$i] = "【{$city['name']}】에 첩보 실행";
                break;
            case 32: //화계
                $double = $command[1];
                $city= getCity($connect, $double, "name");
                $str[$i] = "【{$city['name']}】에 화계 실행";
                break;
            case 33: //탈취
                $double = $command[1];
                $city= getCity($connect, $double, "name");
                $str[$i] = "【{$city['name']}】에 탈취 실행";
                break;
            case 34: //파괴
                $double = $command[1];
                $city= getCity($connect, $double, "name");
                $str[$i] = "【{$city['name']}】에 파괴 실행";
                break;
            case 35: //선동
                $double = $command[1];
                $city= getCity($connect, $double, "name");
                $str[$i] = "【{$city['name']}】에 선동 실행";
                break;
            case 36: //기습
                $double = $command[1];
                $city= getCity($connect, $double, "name");
                $str[$i] = "【{$city['name']}】에 기습 실행";
                break;

            case 41: //단련
                $str[$i] = "숙련도를 단련";
                break;
            case 42: //견문
                $str[$i] = "견문";
                break;
            case 43: //증여
                $fourth = $command[3];
                $third = $command[2];
                $double = $command[1];

                $query = "select name from general where no='$third'";
                $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
                $general = MYDB_fetch_array($result);

                if($fourth == 1) { $str[$i] = "【{$general['name']}】에게 금 {$double}00을 증여"; }
                else { $str[$i] = "【{$general['name']}】에게 쌀 {$double}00을 증여"; }
                break;
            case 44: //헌납
                $third = $command[2];
                $double = $command[1];

                if($third == 1) { $str[$i] = "금 {$double}00을 헌납"; }
                else { $str[$i] = "쌀 {$double}00을 헌납"; }
                break;
            case 45: //하야
                $str[$i] = "하야";
                break;
            case 46: //건국
                $query = "select makenation from general where no='{$general['no']}'";
                $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
                $general = MYDB_fetch_array($result);

                $str[$i] = "【{$general['makenation']}】(을)를 건국";
                break;
            case 47: //방랑
                $str[$i] = "방랑";
                break;
            case 48: //장비 구입
                $double = $command[1];
                if($double == 0) {
                    $str[$i] = "무기를 판매";
                } elseif($double == 100) {
                    $str[$i] = "서적을 판매";
                } elseif($double == 200) {
                    $str[$i] = "명마를 판매";
                } elseif($double == 300) {
                    $str[$i] = "도구를 판매";
                } elseif($double < 100) {
                    $str[$i] = "【".getWeapName($double)."】(을)를 구입";
                } elseif($double < 200) {
                    $str[$i] = "【".getBookName($double-100)."】(을)를 구입";
                } elseif($double < 300) {
                    $str[$i] = "【".getHorseName($double-200)."】(을)를 구입";
                } elseif($double < 400) {
                    $str[$i] = "【".getItemName($double-300)."】(을)를 구입";
                }
                break;
            case 49: //군량 매매
                $third = $command[2];
                $double = $command[1];

                if($third == 1) { $str[$i] = "군량 {$double}00을 판매"; }
                else { $str[$i] = "군량 {$double}00을 구입"; }
                break;
            case 50: //요양
                $str[$i] = "요양";
                break;

            case 54: //선양
                $double = $command[1];

                $query = "select name from general where no='$double'";
                $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
                $general = MYDB_fetch_array($result);

                $str[$i] = "【{$general['name']}】에게 선양";
                break;
            case 55: //거병
                $str[$i] = "방랑군 결성";
                break;
            case 56: //해산
                $str[$i] = "방랑군 해산";
                break;
            case 57: //모반 시도
                $str[$i] = "모반 시도";
                break;

            default:
                $str[$i] = "????";
                break;
        }
    }

    if($font == 1) {
        for($i=0; $i < count($str); $i++) {
            $str[$i] = getFont($str[$i]);
        }
    }
    return $str;
}

function getCoreTurn($connect, $nation, $level) {
    $turn[0] = $nation["l{$level}turn0"];
    $turn[1] = $nation["l{$level}turn1"];
    $turn[2] = $nation["l{$level}turn2"];
    $turn[3] = $nation["l{$level}turn3"];
    $turn[4] = $nation["l{$level}turn4"];
    $turn[5] = $nation["l{$level}turn5"];
    $turn[6] = $nation["l{$level}turn6"];
    $turn[7] = $nation["l{$level}turn7"];
    $turn[8] = $nation["l{$level}turn8"];
    $turn[9] = $nation["l{$level}turn9"];
    $turn[10] = $nation["l{$level}turn10"];
    $turn[11] = $nation["l{$level}turn11"];

    $count = 12;
    for($i=0; $i < $count; $i++) {
        $command = DecodeCommand($turn[$i]);

        switch($command[0]) {
            case 99: //휴식
                $str[$i] = "휴식";
                break;
            case 23: //포상
                $fourth = $command[3];
                $third = $command[2];
                $double = $command[1];

                $query = "select name from general where no='$third'";
                $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
                $general = MYDB_fetch_array($result);

                if($fourth == 1) { $str[$i] = "【{$general['name']}】에게 금 {$double}00을 포상"; }
                else { $str[$i] = "【{$general['name']}】에게 쌀 {$double}00을 포상"; }
                break;
            case 24: //몰수
                $fourth = $command[3];
                $third = $command[2];
                $double = $command[1];

                $query = "select name from general where no='$third'";
                $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
                $general = MYDB_fetch_array($result);

                if($fourth == 1) { $str[$i] = "【{$general['name']}】에게서 금 {$double}00을 몰수"; }
                else { $str[$i] = "【{$general['name']}】에게서 쌀 {$double}00을 몰수"; }
                break;
            case 27: //발령
                $third = $command[2];
                $double = $command[1];

                $query = "select name from general where no='$third'";
                $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
                $general = MYDB_fetch_array($result);
                $city = getCity($connect, $double, "name");

                $str[$i] = "【{$general['name']}】【{$city['name']}】(으)로 발령";
                break;
            case 51: //항복권고
                $double = $command[1];

                $query = "select name from nation where nation='$double'";
                $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
                $nation = MYDB_fetch_array($result);

                $str[$i] = "【{$nation['name']}】에게 항복 권고";
                break;
            case 52: //원조
                $fourth = $command[3];
                $third = $command[2];
                $double = $command[1];

                $query = "select name from nation where nation='$double'";
                $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
                $nation = MYDB_fetch_array($result);

                $fourth *= 1000;
                $third *= 1000;
                $str[$i] = "【{$nation['name']}】에게 국고 {$third} 병량 {$fourth} 원조";
                break;
            case 53: //통합제의
                $query = "select makenation from general where level='$level' and nation='{$nation['nation']}'";
                $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
                $general = MYDB_fetch_array($result);

                $double = $command[1];

                $query = "select name from nation where nation='$double'";
                $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
                $nation = MYDB_fetch_array($result);

                $str[$i] = "【{$nation['name']}】에 【{$general['makenation']}】(으)로 통합 제의";
                break;
            case 61: //불가침제의
                $third = $command[2];
                $double = $command[1];

                $query = "select name from nation where nation='$double'";
                $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
                $nation = MYDB_fetch_array($result);

                $str[$i] = "【{$nation['name']}】에 {$third}년 불가침 제의";
                break;
            case 62: //선전 포고
                $double = $command[1];

                $query = "select name from nation where nation='$double'";
                $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
                $nation = MYDB_fetch_array($result);

                $str[$i] = "【{$nation['name']}】에 선전 포고";
                break;
            case 63: //종전 제의
                $double = $command[1];

                $query = "select name from nation where nation='$double'";
                $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
                $nation = MYDB_fetch_array($result);

                $str[$i] = "【{$nation['name']}】에 종전 제의";
                break;
            case 64: //파기 제의
                $double = $command[1];

                $query = "select name from nation where nation='$double'";
                $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
                $nation = MYDB_fetch_array($result);

                $str[$i] = "【{$nation['name']}】에 파기 제의";
                break;
            case 65: //초토
                $double = $command[1];
                $city = getCity($connect, $double, "name");
                $str[$i] = "【{$city['name']}】(을)를 초토화";
                break;
            case 66: //천도
                $double = $command[1];
                $city = getCity($connect, $double, "name");
                $str[$i] = "【{$city['name']}】(으)로 천도";
                break;
            case 67: //증축
                $double = $command[1];
                $city = getCity($connect, $double, "name");
                $str[$i] = "【{$city['name']}】(을)를 증축";
                break;
            case 68: //감축
                $double = $command[1];
                $city = getCity($connect, $double, "name");
                $str[$i] = "【{$city['name']}】(을)를 감축";
                break;
            case 71: //필사즉생
                $str[$i] = "필사즉생";
                break;
            case 72: //백성동원
                $double = $command[1];
                $city = getCity($connect, $double, "name");
                $str[$i] = "【{$city['name']}】에 백성동원";
                break;
            case 73: //수몰
                $double = $command[1];
                $city = getCity($connect, $double, "name");
                $str[$i] = "【{$city['name']}】(을)를 수몰";
                break;
            case 74: //허보
                $double = $command[1];
                $city = getCity($connect, $double, "name");
                $str[$i] = "【{$city['name']}】에 허보";
                break;
            case 75: //피장파장
                $double = $command[1];

                $query = "select name from nation where nation='$double'";
                $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
                $nation = MYDB_fetch_array($result);

                $str[$i] = "【{$nation['name']}】에 피장파장";
                break;
            case 76: //의병모집
                $str[$i] = "의병모집";
                break;
            case 77: //이호경식
                $double = $command[1];

                $query = "select name from nation where nation='$double'";
                $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
                $nation = MYDB_fetch_array($result);

                $str[$i] = "【{$nation['name']}】에 이호경식";
                break;
            case 78: //급습
                $double = $command[1];

                $query = "select name from nation where nation='$double'";
                $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
                $nation = MYDB_fetch_array($result);

                $str[$i] = "【{$nation['name']}】에 급습";
                break;
            case 81: //국기변경
                $double = $command[1];
                $colors = GetNationColors();
                $color = $colors[$double];

                $str[$i] = "【<font color={$color}>국기</font>】를 변경";
                break;
            default:
                $str[$i] = "????";
                break;
        }
    }

    for($i=0; $i < count($str); $i++) {
        $str[$i] = getFont($str[$i]);
    }

    return $str;
}

function turnTable() {
    echo "
<select name=turn[] size=11 multiple style=width:50px;color:white;background-color:black;font-size:13;>
    <option value=100>전체</option>
    <option value=99>홀턴</option>
    <option value=98>짝턴</option>
    <option selected value=0> 1턴</option>
    <option value=1> 2턴</option>
    <option value=2> 3턴</option>
    <option value=3> 4턴</option>
    <option value=4> 5턴</option>
    <option value=5> 6턴</option>
    <option value=6> 7턴</option>
    <option value=7> 8턴</option>
    <option value=8> 9턴</option>
    <option value=9>10턴</option>
    <option value=10>11턴</option>
    <option value=11>12턴</option>
    <option value=12>13턴</option>
    <option value=13>14턴</option>
    <option value=14>15턴</option>
    <option value=15>16턴</option>
    <option value=16>17턴</option>
    <option value=17>18턴</option>
    <option value=18>19턴</option>
    <option value=19>20턴</option>
    <option value=20>21턴</option>
    <option value=21>22턴</option>
    <option value=22>23턴</option>
    <option value=23>24턴</option>
</select>
";
}

function CoreTurnTable() {
    echo "
<select name=turn[] size=3 multiple style=color:white;background-color:black;font-size:13;>
    <option selected value=0> 1턴</option>
    <option value=1> 2턴</option>
    <option value=2> 3턴</option>
    <option value=3> 4턴</option>
    <option value=4> 5턴</option>
    <option value=5> 6턴</option>
    <option value=6> 7턴</option>
    <option value=7> 8턴</option>
    <option value=8> 9턴</option>
    <option value=9>10턴</option>
    <option value=10>11턴</option>
    <option value=11>12턴</option>
</select>
";
}

function cityInfo($connect) {
    global $_basecolor, $_basecolor2, $images;

    $query = "select no,city,skin from general where user_id='{$_SESSION['p_id']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $me = MYDB_fetch_array($result);

    // 도시 정보
    $city = getCity($connect, $me['city']);

    $query = "select name,color from nation where nation='{$city['nation']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $nation = MYDB_fetch_array($result);

    $pop  = $city['pop'] / $city['pop2'] * 100;
    $rate = $city['rate'];
    $agri = $city['agri'] / $city['agri2'] * 100;
    $comm = $city['comm'] / $city['comm2'] * 100;
    $secu = $city['secu'] / $city['secu2'] * 100;
    $def  = $city['def'] / $city['def2'] * 100;
    $wall = $city['wall'] / $city['wall2'] * 100;
    if($city['trade'] == 0) {
        $trade = 0;
        $tradeStr = "상인없음";
    } else {
        $trade = ($city['trade']-95) * 10;
        $tradeStr = $city['trade'] . "%";
    }

    if($nation['color'] == "" || $me['skin'] < 1) { $nation['color'] = "000000"; }
    echo "<table width=640 border=1 cellspacing=0 cellpadding=0 bordercolordark=gray bordercolorlight=black style=font-size:13;word-break:break-all; id=bg2>
    <tr><td colspan=8 align=center style=height:20;color:".newColor($nation['color']).";background-color:{$nation['color']};font-weight:bold;font-size:13;>【 ".getRegion($city['region'])." | ".getCityLevel($city['level'])." 】 {$city['name']}</td></tr>
    <tr><td colspan=8 align=center style=height:20;color:".newColor($nation['color']).";background-color:{$nation['color']}><b>";

    if($city['nation'] == 0) {
        echo "공 백 지";
    } else {
        echo "지배 국가 【 {$nation['name']} 】";
    }

    if($city['gen1'] > 0) {
        $query = "select name from general where no='$city[gen1]'";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $gen1 = MYDB_fetch_array($result);
    } else {
        $gen1['name'] = '-';
    }

    if($city['gen2'] > 0) {
        $query = "select name from general where no='$city[gen2]'";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $gen2 = MYDB_fetch_array($result);
    } else {
        $gen2['name'] = '-';
    }

    if($city['gen3'] > 0) {
        $query = "select name from general where no='$city[gen3]'";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $gen3 = MYDB_fetch_array($result);
    } else {
        $gen3['name'] = '-';
    }

    echo "
        </b></td>
    </tr>
    <tr>
        <td rowspan=2 align=center id=bg1><b>주민</b></td>
        <td height=7 colspan=3>".bar($pop, $me['skin'])."</td>
        <td rowspan=2 align=center id=bg1><b>민심</b></td>
        <td height=7>".bar($rate, $me['skin'])."</td>
        <td rowspan=2 align=center id=bg1><b>태수</b></td>
        <td rowspan=2 align=center>{$gen1['name']}</td>
    </tr>
    <tr>
        <td colspan=3 align=center>{$city['pop']}/{$city['pop2']}</td>
        <td align=center>{$city['rate']}</td>
    </tr>
    <tr>
        <td width=50  rowspan=2 align=center id=bg1><b>농업</b></td>
        <td width=100 height=7>".bar($agri, $me['skin'])."</td>
        <td width=50  rowspan=2 align=center id=bg1><b>상업</b></td>
        <td width=100 height=7>".bar($comm, $me['skin'])."</td>
        <td width=50  rowspan=2 align=center id=bg1><b>치안</b></td>
        <td width=100 height=7>".bar($secu, $me['skin'])."</td>
        <td width=50  rowspan=2 align=center id=bg1><b>군사</b></td>
        <td rowspan=2 align=center>{$gen2['name']}</td>
    </tr>
    <tr>
        <td align=center>{$city['agri']}/{$city['agri2']}</td>
        <td align=center>{$city['comm']}/{$city['comm2']}</td>
        <td align=center>{$city['secu']}/{$city['secu2']}</td>
    </tr>
    <tr>
        <td rowspan=2 align=center id=bg1><b>수비</b></td>
        <td height=7>".bar($def, $me['skin'])."</td>
        <td rowspan=2 align=center id=bg1><b>성벽</b></td>
        <td height=7>".bar($wall, $me['skin'])."</td>
        <td rowspan=2 align=center id=bg1><b>시세</b></td>
        <td height=7>".bar($trade, $me['skin'])."</td>
        <td rowspan=2 align=center id=bg1><b>시중</b></td>
        <td rowspan=2 align=center>{$gen3['name']}</td>
    </tr>
    <tr>
        <td align=center>{$city['def']}/{$city['def2']}</td>
        <td align=center>{$city['wall']}/{$city['wall2']}</td>
        <td align=center>{$tradeStr}</td>
    </tr>
</table>
";
}

function myNationInfo($connect) {
    global $_basecolor, $_basecolor2, $images;

    $query = "select startyear,year from game where no='1'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $admin = MYDB_fetch_array($result);

    $query = "select skin,no,nation from general where user_id='{$_SESSION['p_id']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $me = MYDB_fetch_array($result);

    $query = "select nation,name,color,power,msg,gold,rice,bill,rate,scout,war,tricklimit,surlimit,tech,totaltech,level,type from nation where nation='{$me['nation']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $nation = MYDB_fetch_array($result);

    $query = "select COUNT(*) as cnt, SUM(pop) as totpop, SUM(pop2) as maxpop from city where nation='{$nation['nation']}'"; // 도시 이름 목록
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $city = MYDB_fetch_array($result);

    $query = "select COUNT(*) as cnt, SUM(crew) as totcrew,SUM(leader)*100 as maxcrew from general where nation='{$nation['nation']}'";    // 장수 목록
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $general = MYDB_fetch_array($result);

    $query = "select name from general where nation='{$nation['nation']}' and level='12'";
    $genresult = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $level12 = MYDB_fetch_array($genresult);

    $query = "select name from general where nation='{$nation['nation']}' and level='11'";
    $genresult = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $level11 = MYDB_fetch_array($genresult);

    echo "<table width=498 height=190 border=1 cellspacing=0 cellpadding=0 bordercolordark=gray bordercolorlight=black style=font-size:13;word-break:break-all; id=bg2>
    <tr>
        <td colspan=4 align=center ";

    if($me['skin'] < 1) {
        if($me['nation'] == 0) { echo "style=font-weight:bold;font-size:13;>【재 야】"; }
        else { echo "style=font-weight:bold;font-size:13;>국가【 {$nation['name']} 】"; }
    } else {
        if($me['nation'] == 0) { echo "style=color:white;background-color:000000;font-weight:bold;font-size:13;>【재 야】"; }
        else { echo "style=color:".newColor($nation['color']).";background-color:{$nation['color']};font-weight:bold;font-size:13;>국가【 {$nation['name']} 】"; }
    }

    echo "
        </td>
    </tr>
    <tr>
        <td align=center id=bg1><b>성 향</b></td>
        <td align=center colspan=3><font color="; echo $me['skin']>0?"yellow":"white"; echo ">".getNationType($nation['type'])."</font> (".getNationType2($nation['type'], $me['skin']).")</td>
        </td>
    </tr>
    <tr>
        <td width=68  align=center id=bg1><b>".getLevel(12, $nation['level'])."</b></td>
        <td width=178 align=center>";echo $level12==''?"-":"{$level12['name']}"; echo "</td>
        <td width=68  align=center id=bg1><b>".getLevel(11, $nation['level'])."</b></td>
        <td width=178 align=center>";echo $level11==''?"-":"{$level11['name']}"; echo "</td>
    </tr>
    <tr>
        <td align=center id=bg1><b>총주민</b></td>
        <td align=center>";echo $me['nation']==0?"해당 없음":"{$city['totpop']}/{$city['maxpop']}";echo "</td>
        <td align=center id=bg1><b>총병사</b></td>
        <td align=center>";echo $me['nation']==0?"해당 없음":"{$general['totcrew']}/{$general['maxcrew']}"; echo "</td>
        </td>
    </tr>
    <tr>
        <td align=center id=bg1><b>국 고</b></td>
        <td align=center>";echo $me['nation']==0?"해당 없음":"{$nation['gold']}";echo "</td>
        <td align=center id=bg1><b>병 량</b></td>
        <td align=center>";echo $me['nation']==0?"해당 없음":"{$nation['rice']}";echo "</td>
    </tr>
    <tr>
        <td align=center id=bg1><b>지급율</b></td>
        <td align=center>";
    if($me['nation'] == 0) {
        echo "해당 없음";
    } else {
        echo $nation['bill']==0?"0 %":"{$nation['bill']} %";
    }
    echo "
        </td>
        <td align=center id=bg1><b>세 율</b></td>
        <td align=center>";
    if($me['nation'] == 0) {
        echo "해당 없음";
    } else {
        echo $nation['rate']==0?"0 %":"{$nation['rate']} %";
    }

    $techCall = getTechCall($nation['tech']);

    if(TechLimit($admin['startyear'], $admin['year'], $nation['tech'])) { $nation['tech'] = "<font color=magenta>{$nation['tech']}</font>"; }
    else { $nation['tech'] = "<font color=limegreen>{$nation['tech']}</font>"; }

    $nation['tech'] = "$techCall / {$nation['tech']}";
    
    if($me['nation']==0){
        $nation['tricklimit'] = "<font color=white>해당 없음</font>";
        $nation['surlimit'] = "<font color=white>해당 없음</font>";
        $nation['scout'] = "<font color=white>해당 없음</font>";
        $nation['war'] = "<font color=white>해당 없음</font>";
        $nation['power'] = "<font color=white>해당 없음</font>";
    } else {
        if($nation['tricklimit'] != 0) { $nation['tricklimit'] = "<font color=red>{$nation['tricklimit']}턴</font>"; }
        else { $nation['tricklimit'] = "<font color=limegreen>가 능</font>"; }
    
        if($nation['surlimit'] != 0) { $nation['surlimit'] = "<font color=red>{$nation['surlimit']}턴</font>"; }
        else { $nation['surlimit'] = "<font color=limegreen>가 능</font>"; }
    
        if($nation['scout'] != 0) { $nation['scout'] = "<font color=red>금 지</font>"; }
        else { $nation['scout'] = "<font color=limegreen>허 가</font>"; }
    
        if($nation['war'] != 0) { $nation['war'] = "<font color=red>금 지</font>"; }
        else { $nation['war'] = "<font color=limegreen>허 가</font>"; }
    
        
    }

    if($me['skin'] == 0) {
        $nation['tech'] = unfont($nation['tech']);
        $nation['tricklimit'] = unfont($nation['tricklimit']);
        $nation['surlimit'] = unfont($nation['surlimit']);
        $nation['scout'] = unfont($nation['scout']);
        $nation['war'] = unfont($nation['war']);
    }

    echo "
        </td>
    </tr>
    <tr>
        <td align=center id=bg1><b>속 령</b></td>
        <td align=center>";echo $me['nation']==0?"-":"{$city['cnt']}"; echo "</td>
        <td align=center id=bg1><b>장 수</b></td>
        <td align=center>";echo $me['nation']==0?"-":"{$general['cnt']}"; echo "</td>
    </tr>
    <tr>
        <td align=center id=bg1><b>국 력</b></td>
        <td align=center>{$nation['power']}</td>
        <td align=center id=bg1><b>기술력</b></td>
        <td align=center>";echo $me['nation']==0?"-":"{$nation['tech']}"; echo "</td>
    </tr>
    <tr>
        <td align=center id=bg1><b>전 략</b></td>
        <td align=center>{$nation['tricklimit']}</td>
        <td align=center id=bg1><b>외 교</b></td>
        <td align=center>{$nation['surlimit']}</td>
    </tr>
    <tr>
        <td align=center id=bg1><b>임 관</b></td>
        <td align=center>{$nation['scout']}</td>
        <td align=center id=bg1><b>전 쟁</b></td>
        <td align=center>{$nation['war']}</td>
    </tr>
</table>
";
}

function addCommand($typename, $value, $valid = 1, $color=0) {
    if($valid == 1) {
        switch($color) {
            case 0:
                echo "
    <option style=color:white;background-color:black; value={$value}>{$typename}</option>";
                break;
            case 1:
                echo "
    <option style=color:skyblue;background-color:black; value={$value}>{$typename}</option>";
                break;
            case 2:
                echo "
    <option style=color:orange;background-color:black; value={$value}>{$typename}</option>";
                break;
        }
    } else {
        echo "
    <option style=color:white;background-color:red; value={$value}>{$typename}</option>";
    }
}

function commandGroup($typename, $type=0) {
    if($type == 0) {
        echo "
    <optgroup label='{$typename}' style=color:skyblue;background-color:black;>";
    } else {
        echo "
    </optgroup>";
    }
}

function commandTable($connect) {
    $query = "select startyear,year,develcost,scenario from game where no='1'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $admin = MYDB_fetch_array($result);

    $query = "select no,npc,troop,city,nation,level,crew,makelimit,special from general where user_id='{$_SESSION['p_id']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $me = MYDB_fetch_array($result);

    $troop = getTroop($connect, $me['troop']);
    $city = getCity($connect, $me['city']);

    $query = "select nation from nation";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $nationcount = MYDB_num_rows($result);

    $query = "select city from city where nation='{$me['nation']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $citycount = MYDB_num_rows($result);

    $query = "select no from general where nation='{$me['nation']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $gencount = MYDB_num_rows($result);

    $query = "select type,level from nation where nation='{$me['nation']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $nation = MYDB_fetch_array($result);

    $develcost = $admin['develcost'];
    $develcostA = $admin['develcost'];    $colorA = 0;
    $develcostB = $admin['develcost'];    $colorB = 0;
    $develcostC = $admin['develcost'];    $colorC = 0;
    $develcostD = $admin['develcost'];    $colorD = 0;
    $develcostE = $admin['develcost']*2;  $colorE = 0;
    $develcost3 = $admin['develcost']*3;
    $develcost5 = $admin['develcost']*5;

    // 농상 국가보정
    if($nation['type'] == 2 || $nation['type'] == 12)                                             { $develcostA *= 0.8;   $colorA = 1; }
    if($nation['type'] == 8 || $nation['type'] == 11)                                                                   { $develcostA *= 1.2;   $colorA = 2; }
    // 기술 국가보정
    if($nation['type'] == 3 || $nation['type'] == 13)                                                                   { $develcostB *= 0.8;   $colorB = 1; }
    if($nation['type'] == 5 || $nation['type'] == 6 || $nation['type'] == 7 || $nation['type'] == 8 || $nation['type'] == 12) { $develcostB *= 1.2;   $colorB = 2; }
    // 수성 국가보정
    if($nation['type'] == 3 || $nation['type'] == 5 || $nation['type'] == 10 || $nation['type'] == 11)                      { $develcostC *= 0.8;   $colorC = 1; }
    if($nation['type'] == 4 || $nation['type'] == 7 || $nation['type'] == 8  || $nation['type'] == 13)                      { $develcostC *= 1.2;   $colorC = 2; }
    // 치안 국가보정
    if($nation['type'] == 1 || $nation['type'] == 4)                                                                    { $develcostD *= 0.8;   $colorD = 1; }
    if($nation['type'] == 6 || $nation['type'] == 9)                                                                    { $develcostD *= 1.2;   $colorD = 2; }
    // 민심,정착장려 국가보정
    if($nation['type'] == 2 || $nation['type'] == 4 || $nation['type'] == 7 || $nation['type'] == 10) { $develcostE *= 0.8;   $colorE = 1; }
    if($nation['type'] == 1 || $nation['type'] == 3 || $nation['type'] == 9)                                                                    { $develcostE *= 1.2;   $colorE = 2; }

    $develcostA = round($develcostA);
    $develcostB = round($develcostB);
    $develcostC = round($develcostC);
    $develcostD = round($develcostD);
    $develcostE = round($develcostE);

    echo "
<select name=commandtype size=1 style=width:260px;color:white;background-color:black;font-size:12;>";
    addCommand("휴 식", 0);
    addCommand("요 양", 50);
    commandGroup("========= 내 정 ==========");
    if($me['level'] >= 1 && ($citycount != 0 || $admin['year'] >= $admin['startyear']+3) && $city['supply'] != 0) {
        addCommand("농지개간(지력경험, 자금$develcostA)", 1, 1, $colorA);
        addCommand("상업투자(지력경험, 자금$develcostA)", 2, 1, $colorA);
        addCommand("기술연구(지력경험, 자금$develcostB)", 3, 1, $colorB);
        addCommand("수비강화(무력경험, 자금$develcostC)", 5, 1, $colorC);
        addCommand("성벽보수(무력경험, 자금$develcostC)", 6, 1, $colorC);
        addCommand("치안강화(무력경험, 자금$develcostD)", 8, 1, $colorD);
        addCommand("정착장려(통솔경험, 군량$develcostE)", 7, 1, $colorE);
        addCommand("주민선정(통솔경험, 군량$develcostE)", 4, 1, $colorE);
    } else {
        addCommand("농지개간(지력경험, 자금$develcostA)", 1, 0);
        addCommand("상업투자(지력경험, 자금$develcostA)", 2, 0);
        addCommand("기술연구(지력경험, 자금$develcostB)", 3, 0);
        addCommand("수비강화(무력경험, 자금$develcostC)", 5, 0);
        addCommand("성벽보수(무력경험, 자금$develcostC)", 6, 0);
        addCommand("치안강화(무력경험, 자금$develcostD)", 8, 0);
        addCommand("정착장려(통솔경험, 군량$develcostE)", 7, 0);
        addCommand("주민선정(통솔경험, 군량$develcostE)", 4, 0);
    }
    if($me['level'] >= 1 && (($nation['level'] > 0 && $city['nation'] == $me['nation'] && $city['supply'] != 0) || $nation['level'] == 0)) {
        addCommand("물자조달(랜덤경험)", 9, 1);
    } else {
        addCommand("물자조달(랜덤경험)", 9, 0);
    }
    commandGroup("", 1);
    commandGroup("========= 군 사 ==========");
    if($me['level'] >= 1 && $citycount > 0) {
        addCommand("첩보(통솔경험, 자금$develcost3, 군량$develcost3)", 31);
        addCommand("징병(통솔경험)", 11);
        addCommand("모병(통솔경험, 자금x2)", 12);
        addCommand("훈련(통솔경험, 사기↓)", 13);
        addCommand("사기진작(통솔경험, 자금↓)", 14);
        //addCommand("전투태세/3턴(통솔경험, 자금↓)", 15);
        addCommand("출병", 16);
    } else {
        addCommand("첩보(통솔경험, 자금$develcost3, 군량$develcost3)", 31, 0);
        addCommand("징병(통솔경험)", 11, 0);
        addCommand("모병(통솔경험, 자금x2)", 12, 0);
        addCommand("훈련(통솔경험, 사기↓)", 13, 0);
        addCommand("사기진작(통솔경험, 자금↓)", 14, 0);
        //addCommand("전투태세/3턴(통솔경험, 자금↓)", 15, 0);
        addCommand("출병", 16, 0);
    }
    if($me['crew'] > 0) {
        addCommand("소집해제(병사↓, 주민↑)", 17);
    } else {
        addCommand("소집해제(병사↓, 주민↑)", 17, 0);
    }

    commandGroup("", 1);
    commandGroup("========= 인 사 ==========");
    addCommand("이동(통솔경험, 자금$develcost, 사기↓)", 21);
    addCommand("강행(통솔경험, 자금$develcost5, 병력/사기/훈련↓)", 30);
    
    if($nation['level'] > 0 && $me['level'] >= 1) {
        addCommand("인재탐색(랜덤경험, 자금$develcost)", 29);
    } else {
        addCommand("인재탐색(랜덤경험, 자금$develcost)", 29, 0);
    }
    if($me['level'] >= 1 && $city['supply'] != 0) {
        addCommand("등용(자금{$develcost5}+장수가치)", 22);
    } else {
        addCommand("등용(자금{$develcost5}+장수가치)", 22, 0);
    }
    if($me['no'] == $troop['no'] && $citycount > 0 && $city['supply'] != 0 && $city['nation'] == $me['nation']) {
        addCommand("집합(통솔경험)", 26);
    } else {
        addCommand("집합(통솔경험)", 26, 0);
    }
    if($me['level'] >= 1 && $me['level'] <= 12 && $nation['level'] > 0) {
        addCommand("귀환(통솔경험)", 28);
    } else {
        addCommand("귀환(통솔경험)", 28, 0);
    }
    if($me['level'] == 0 && $nationcount != 0 && $me['makelimit'] == 0) {
        addCommand("임관", 25);
    } else {
        addCommand("임관", 25, 0);
    }
    commandGroup("", 1);
    commandGroup("========= 계 략 ==========");
    if($me['level'] >= 1 && (($nation['level'] > 0 && $city['nation'] == $me['nation'] && $city['supply'] != 0) || $nation['level'] == 0)) {
        addCommand("화계(지력경험, 자금$develcost5, 군량$develcost5)", 32);
        addCommand("탈취(무력경험, 자금$develcost5, 군량$develcost5)", 33);
        addCommand("파괴(무력경험, 자금$develcost5, 군량$develcost5)", 34);
        addCommand("선동(통솔경험, 자금$develcost5, 군량$develcost5)", 35);
    //    addCommand("기습(통, 무, 지, 자금$develcost10, 군량$develcost10)", 36);
    } else {
        addCommand("화계(지력경험, 자금$develcost5, 군량$develcost5)", 32, 0);
        addCommand("탈취(무력경험, 자금$develcost5, 군량$develcost5)", 33, 0);
        addCommand("파괴(무력경험, 자금$develcost5, 군량$develcost5)", 34, 0);
        addCommand("선동(통솔경험, 자금$develcost5, 군량$develcost5)", 35, 0);
    //    addCommand("기습(통, 무, 지, 자금500, 군량500)", 36, 0);
    }
    commandGroup("", 1);
    commandGroup("========= 개 인 ==========");
    if($me['level'] >= 1) {
        addCommand("단련(자금$develcost, 군량$develcost)", 41);
    } else {
        addCommand("단련(자금$develcost, 군량$develcost)", 41, 0);
    }
    addCommand("견문(자금?, 군량?, 경험치?)", 42);
    if($city['trade'] > 0 || $me['special'] == 30) {
        addCommand("장비매매", 48);
        addCommand("군량매매", 49);
    } else {
        addCommand("장비매매", 48, 0);
        addCommand("군량매매", 49, 0);
    }
    if($city['supply'] != 0 && $city['nation'] == $me['nation']) {
        addCommand("증여(통솔경험)", 43);
    } else {
        addCommand("증여(통솔경험)", 43, 0);
    }

    if($me['level'] >= 1 && $city['supply'] != 0 && $city['nation'] == $me['nation']) {
        addCommand("헌납(통솔경험)", 44);
    } else {
        addCommand("헌납(통솔경험)", 44, 0);
    }
    if($me['npc'] == 0) {
        if($me['level'] >= 1 && $me['level'] < 12) {
            addCommand("하야", 45);
        } else {
            addCommand("하야", 45, 0);
        }
    }
    if($me['level'] == 0) {
        addCommand("거병", 55);
    } else {
        addCommand("거병", 55, 0);
    }
    if($me['level'] == 12 &&
        ($city['level'] == 5 || $city['level'] == 6) &&
        $city['nation'] == 0 &&
        $me['makelimit'] == 0 &&
        $gencount >= 2 &&
        $citycount == 0 &&
        $admin['year'] < $admin['startyear']+2
    ) {
        addCommand("건국", 46);
    } else {
        addCommand("건국", 46, 0);
    }
    if($me['level'] == 12) {
        addCommand("선양", 54);
        if($citycount != 0) {
            addCommand("방랑", 47);
            addCommand("해산", 56, 0);
        } else {
            addCommand("방랑", 47, 0);
            addCommand("해산", 56);
        }
    } else {
        addCommand("선양", 54, 0);
        addCommand("방랑", 47, 0);
        addCommand("해산", 56, 0);
    }
    if($me['level'] > 1 && $me['level'] < 12) {
        addCommand("모반시도", 57);
    } else {
        addCommand("모반시도", 57, 0);
    }
    commandGroup("", 1);

    echo "
</select>
";
}

function CoreCommandTable($connect) {
    $query = "select develcost from game where no='1'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $admin = MYDB_fetch_array($result);

    $query = "select no,nation,city,level from general where user_id='{$_SESSION['p_id']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $me = MYDB_fetch_array($result);

    $query = "select level,colset from nation where nation='{$me['nation']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $nation = MYDB_fetch_array($result);

    $query = "select no from general where nation='{$me['nation']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $genCount = MYDB_num_rows($result);

    $query = "select supply from city where city='{$me['city']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $city = MYDB_fetch_array($result);

    if($nation['level'] > 0) { $valid = 1; }
    else { $valid = 0; }
    if($city['supply'] == 0) { $valid = 0; }

    echo "
<select name=commandtype size=1 style=color:white;background-color:black;font-size:13>";
    addCommand("휴 식", 99);
    commandGroup("", 1);
    commandGroup("====== 인 사 ======");
    addCommand("발령", 27, $valid);
    addCommand("포상", 23, $valid);
    addCommand("몰수", 24, $valid);
    commandGroup("", 1);
    commandGroup("====== 외 교 ======");
    if($citycount <= 4) {
        addCommand("통합 제의", 53, $valid);
    } else {
        addCommand("통합 제의", 53, 0);
    }

    addCommand("항복 권고", 51, $valid);
    if($nation['level'] >= 2) {
        addCommand("물자 원조", 52, $valid);
    } else {
        addCommand("물자 원조", 52, 0);
    }
    addCommand("불가침 제의", 61, $valid);
    addCommand("선전 포고", 62, $valid);
    addCommand("종전 제의", 63, $valid);
    addCommand("파기 제의", 64, $valid);
    commandGroup("", 1);
    commandGroup("====== 특 수 ======");
    if($citycount >= 5) {
        addCommand("초토화", 65, $valid);
    } else {
        addCommand("초토화", 65, 0);
    }
    addCommand("천도/3턴(금쌀{$admin['develcost']}0)", 66, $valid);
    $cost = $admin['develcost'] * 500 + 60000;   // 7만~13만
    addCommand("증축/6턴(금쌀{$cost})", 67, $valid);
    addCommand("감축/6턴", 68, $valid);
    commandGroup("", 1);
    commandGroup("====== 전 략 ======");
    $term = round(sqrt($genCount*8)*10);
    addCommand("필사즉생/3턴(전략{$term})", 71, $valid);
    $term = round(sqrt($genCount*4)*10);
    addCommand("백성동원/1턴(전략{$term})", 72, $valid);
    $term = round(sqrt($genCount*4)*10);
    addCommand("수몰/3턴(전략{$term})", 73, $valid);
    $term = round(sqrt($genCount*4)*10);
    addCommand("허보/2턴(전략{$term})", 74, $valid);
    $term = round(sqrt($genCount*2)*10);
    if($term < 72) { $term = 72; }
    addCommand("피장파장/3턴(전략{$term})", 75, $valid);
    $term = round(sqrt($genCount*10)*10);
    addCommand("의병모집/3턴(전략{$term})", 76, $valid);
    $term = round(sqrt($genCount*16)*10);
    addCommand("이호경식/1턴(전략{$term})", 77, $valid);
    $term = round(sqrt($genCount*16)*10);
    addCommand("급습/1턴(전략{$term})", 78, $valid);
    commandGroup("", 1);
    commandGroup("====== 기 타 ======");
    if($nation['colset'] > 0) {
        addCommand("국기 변경", 81, 1);
    } else {
        addCommand("국기 변경", 81, 0);
    }
    commandGroup("", 1);
    echo "
</select>
";
}

function commandButton($connect) {
    global $_basecolor, $_basecolor2;

    $query = "select skin,no,nation,level,belong from general where user_id='{$_SESSION['p_id']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $me = MYDB_fetch_array($result);

    $query = "select nation,color,secretlimit from nation where nation='{$me['nation']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $nation = MYDB_fetch_array($result);

    if($nation['color'] == "" || $me['skin'] < 1) { $nation['color'] = "000000"; }

    echo "
<table align=center border=0 cellspacing=0 cellpadding=0 style=font-size:13;word-break:break-all; id=bg2>
    <tr>";

    if($me['level'] >= 1) { echo "<td width=111 height=30 align=center><input style=width:111;height:30;background-color:{$nation['color']};color:".newColor($nation['color']).";font-weight:bold; type=button value='회 의 실' onclick='refreshing(1,1)'></td>"; }
    else {                     echo "<td width=111 height=30 align=center><font size=2 color=gray>【회 의 실】</font></td>"; }
    if($me['level'] >= 5) { echo "<td width=111 height=30 align=center><input style=width:111;height:30;background-color:{$nation['color']};color:".newColor($nation['color']).";font-weight:bold; type=button value='기 밀 실' onclick='refreshing(1,4)'></td>"; }
    else {                     echo "<td width=111 height=30 align=center><font size=2 color=gray>【기 밀 실】</font></td>"; }
    if($me['level'] >= 1) { echo "<td width=111 height=30 align=center><input style=width:111;height:30;background-color:{$nation['color']};color:".newColor($nation['color']).";font-weight:bold; type=button value='부대 편성' onclick='refreshing(1,2)'></td>"; }
    else {                     echo "<td width=111 height=30 align=center><font size=2 color=gray>【부대 편성】</font></td>"; }
    if($me['level'] >= 1) { echo "<td width=111 height=30 align=center><input style=width:111;height:30;background-color:{$nation['color']};color:".newColor($nation['color']).";font-weight:bold; type=button value='인 사 부' onclick='refreshing(1,10)'></td>"; }
    else {                     echo "<td width=111 height=30 align=center><font size=2 color=gray>【인 사 부】</font></td>"; }
    if($me['level'] >= 2 || ($me['level'] == 1 && $me['belong'] >= $nation['secretlimit'])) { echo "<td width=111 height=30 align=center><input style=width:111;height:30;background-color:{$nation['color']};color:".newColor($nation['color']).";font-weight:bold; type=button value='내 무 부' onclick='refreshing(1,13)'></td>"; }
    else {                     echo "<td width=111 height=30 align=center><font size=2 color=gray>【내 무 부】</font></td>"; }
    if($me['level'] >= 2 || ($me['level'] == 1 && $me['belong'] >= $nation['secretlimit'])) { echo "<td width=111 height=30 align=center><input style=width:111;height:30;background-color:{$nation['color']};color:".newColor($nation['color']).";font-weight:bold; type=button value='사 령 부' onclick='refreshing(1,5)'></td>"; }
    else {                     echo "<td width=111 height=30 align=center><font size=2 color=gray>【사 령 부】</font></td>"; }
    if($me['level'] >= 2 || ($me['level'] == 1 && $me['belong'] >= $nation['secretlimit'])) { echo "<td width=111 height=30 align=center><input style=width:111;height:30;background-color:{$nation['color']};color:".newColor($nation['color']).";font-weight:bold; type=button value='암 행 부' onclick='refreshing(1,6)'></td>"; }
    else {                     echo "<td width=111 height=30 align=center><font size=2 color=gray>【암 행 부】</font></td>"; }
    echo "<td width=111 height=30 align=center><input style=width:111;height:30;background-color:{$nation['color']};color:".newColor($nation['color']).";font-weight:bold; type=button value='토 너 먼 트' onclick='refreshing(1,15)'></td>";
    echo "<td width=111 height=30 align=center><input style=width:111;height:30;background-color:{$nation['color']};color:".newColor($nation['color']).";font-weight:bold; type=button value='베 팅 장' onclick='refreshing(1,16)'></td>";
    echo "
    </tr>
</table>";

    echo "
<table align=center border=0 cellspacing=0 cellpadding=0 style=font-size:13;word-break:break-all; id=bg2>
    <tr>";

    if($me['level'] >= 1) { echo "<td width=111 height=30 align=center><input style=width:111;height:30;background-color:{$nation['color']};color:".newColor($nation['color']).";font-weight:bold; type=button value='세력 정보' onclick='refreshing(1,7)'></td>"; }
    else {                     echo "<td width=111 height=30 align=center><font size=2 color=gray>【세력 정보】</font></td>"; }
    if($me['level'] >= 1) { echo "<td width=111 height=30 align=center><input style=width:111;height:30;background-color:{$nation['color']};color:".newColor($nation['color']).";font-weight:bold; type=button value='세력 도시' onclick='refreshing(1,8)'></td>"; }
    else {                     echo "<td width=111 height=30 align=center><font size=2 color=gray>【세력 도시】</font></td>"; }
    if($me['level'] >= 1) { echo "<td width=111 height=30 align=center><input style=width:111;height:30;background-color:{$nation['color']};color:".newColor($nation['color']).";font-weight:bold; type=button value='세력 장수' onclick='refreshing(1,9)'></td>"; }
    else {                     echo "<td width=111 height=30 align=center><font size=2 color=gray>【세력 장수】</font></td>"; }
    if($me['level'] >= 1) { echo "<td width=111 height=30 align=center><input style=width:111;height:30;background-color:{$nation['color']};color:".newColor($nation['color']).";font-weight:bold; type=button value='국 법' onclick='refreshing(1,3)'></td>"; }
    else {                     echo "<td width=111 height=30 align=center><font size=2 color=gray>【국 법】</font></td>"; }
    echo "<td width=111 height=30 align=center><input style=width:111;height:30;background-color:{$nation['color']};color:".newColor($nation['color']).";font-weight:bold; type=button value='중원 정보' onclick='refreshing(1,14)'></td>";
    echo "<td width=111 height=30 align=center><input style=width:111;height:30;background-color:{$nation['color']};color:".newColor($nation['color']).";font-weight:bold; type=button value='현재 도시' onclick='refreshing(1,11)'></td>";
    if($me['level'] >= 2 || ($me['level'] == 1 && $me['belong'] >= $nation['secretlimit'])) { echo "<td width=111 height=30 align=center><input style=width:111;height:30;background-color:{$nation['color']};color:".newColor($nation['color']).";font-weight:bold; type=button value='감 찰 부' onclick='refreshing(1,18)'></td>"; }
    else {                     echo "<td width=111 height=30 align=center><font size=2 color=gray>【감 찰 부】</font></td>"; }
    echo "<td width=111 height=30 align=center><input style=width:111;height:30;background-color:{$nation['color']};color:".newColor($nation['color']).";font-weight:bold; type=button value='내 정보 & 설정' onclick='refreshing(1,12)'></td>";
    echo "<td width=111 height=30 align=center><input style=width:111;height:30;background-color:{$nation['color']};color:".newColor($nation['color']).";font-weight:bold; type=button value='거 래 장' onclick='refreshing(1,17)'></td>";
    echo "
    </tr>
</table>";
}

function myInfo($connect) {
    $query = "select no,skin from general where user_id='{$_SESSION['p_id']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $me = MYDB_fetch_array($result);

    generalInfo($connect, $me['no'], $me['skin']);
}

function generalInfo($connect, $no, $skin) {
    global $_basecolor, $_basecolor2, $image, $images;

    $query = "select img from game where no='1'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $admin = MYDB_fetch_array($result);

    $query = "select skin from general where user_id='{$_SESSION['p_id']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $me = MYDB_fetch_array($result);

    $query = "select block,userlevel,no,name,picture,imgsvr,injury,nation,city,troop,leader,leader2,power,power2,intel,intel2,explevel,experience,level,gold,rice,crew,crewtype,train,atmos,weap,book,horse,item,turntime,killturn,age,personal,special,specage,special2,specage2,mode,con,connect from general where no='$no'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $general = MYDB_fetch_array($result);

    $query = "select nation,level,color from nation where nation='{$general['nation']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $nation = MYDB_fetch_array($result);

    if($general['level'] == 12) {
        $lbonus = $nation['level'] * 2;
    } elseif($general['level'] >= 5) {
        $lbonus = $nation['level'];
    } else {
        $lbonus = 0;
    }
    if($lbonus > 0) {
        $lbonus = "<font color=cyan>+{$lbonus}</font>";
    } else {
        $lbonus = "";
    }

    $troop = getTroop($connect, $general['troop']);

    $level = getLevel($general['level'], $nation['level']);
    if($general['level'] == 2)     {
        $query = "select name from city where gen3='{$general['no']}'";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $city = MYDB_fetch_array($result);
        $level = $city['name']." ".$level;
    } elseif($general['level'] == 3) {
        $query = "select name from city where gen2='{$general['no']}'";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $city = MYDB_fetch_array($result);
        $level = $city['name']." ".$level;
    } elseif($general['level'] == 4) {
        $query = "select name from city where gen1='{$general['no']}'";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $city = MYDB_fetch_array($result);
        $level = $city['name']." ".$level;
    }
    $call = getCall($general['leader'], $general['power'], $general['intel']);
    $typename = getTypename($general['crewtype']);
    $weapname = getWeapName($general['weap']);
    $bookname = getBookName($general['book']);
    $horsename = getHorseName($general['horse']);
    $itemname = getItemName($general['item']);
    if($general['injury'] > 0) {
        $leader = floor($general['leader'] * (100 - $general['injury'])/100);
        $power = floor($general['power'] * (100 - $general['injury'])/100);
        $intel = floor($general['intel'] * (100 - $general['injury'])/100);
    } else {
        $leader = $general['leader'];
        $power = $general['power'];
        $intel = $general['intel'];
    }
    if($general['injury'] > 60)     { $color = "<font color=red>";     $injury = "위독"; }
    elseif($general['injury'] > 40) { $color = "<font color=magenta>"; $injury = "심각"; }
    elseif($general['injury'] > 20) { $color = "<font color=orange>";  $injury = "중상"; }
    elseif($general['injury'] > 0)  { $color = "<font color=yellow>";  $injury = "경상"; }
    else                     { $color = "<font color=white>";   $injury = "건강"; }

    $remaining = substr($general['turntime'], 14, 2) - date('i');
    if($remaining < 0) { $remaining = 60 + $remaining; }

    if($general['userlevel'] > 2) { $specUser = "<font color=cyan><b>특별</b></font>"; }
    else                   { $specUser = "<font color=gray><b>일반</b></font>"; }
    if($general['block'] > 0)     { $specUser = "<font color=red><b>블럭</b></font>"; }
    $specUser = '';

    if($nation['color'] == "" || $skin < 1) { $nation['color'] = "000000"; }

    if($general['age'] < 60)     { $general['age'] = "<font color=limegreen>{$general['age']} 세</font>"; }
    elseif($general['age'] < 80) { $general['age'] = "<font color=yellow>{$general['age']} 세</font>"; }
    else                  { $general['age'] = "<font color=red>{$general['age']} 세</font>"; }

    $general['connect'] = round($general['connect'] / 10, 0) * 10;
    $special = $general['special'] == 0 ? "{$general['specage']}세" : "<font color=limegreen>".getGenSpecial($general['special'])."</font>";
    $special2 = $general['special2'] == 0 ? "{$general['specage2']}세" : "<font color=limegreen>".getGenSpecial($general['special2'])."</font>";

    switch($general['personal']) {
        case  2:    case  4:
            $atmos = "<font color=cyan>{$general['atmos']} (+5)</font>"; break;
        case  0:    case  9:    case 10:
            $atmos = "<font color=magenta>{$general['atmos']} (-5)</font>"; break;
        default:
            $atmos = "{$general['atmos']}"; break;
    }
    switch($general['personal']) {
        case  3:    case  5:
            $train = "<font color=cyan>{$general['train']} (+5)</font>"; break;
        case  1:    case  8:    case 10:
            $train = "<font color=magenta>{$general['train']} (-5)</font>"; break;
        default:
            $train = "{$general['train']}"; break;
    }
    if($general['troop'] == 0)    { $troop['name'] = "-"; }
    if($general['mode'] == 2)     { $general['mode'] = "<font color=limegreen>수비 함(훈사80)</font>"; }
    elseif($general['mode'] == 1) { $general['mode'] = "<font color=limegreen>수비 함(훈사60)</font>"; }
    else                        { $general['mode'] = "<font color=red>수비 안함</font>"; }

    if($skin == 0) {
        $general['age'] = unfont($general['age']);
        $special = unfont($special);
        $special2 = unfont($special2);
        $atmos = unfont($atmos);
        $train = unfont($train);
        $general['mode'] = unfont($general['mode']);
    }

    $weapImage = "{$images}/weap{$general['crewtype']}.jpg";
    if($admin['img'] < 2) { $weapImage = "{$image}/default.jpg"; };
    $imageTemp = GetImageURL($general['imgsvr']);
    echo "<table width=498 border=1 cellspacing=0 cellpadding=0 bordercolordark=gray bordercolorlight=black style=font-size:13;word-break:break-all; id=bg2>
    <tr>
        <td width=64 height=64 align=center rowspan=3"; echo $skin>0?" background={$imageTemp}/{$general['picture']}":""; echo ">&nbsp;</td>
        <td align=center colspan=9 height=16 style=color:".newColor($nation['color']).";background-color:{$nation['color']};font-weight:bold;font-size:13;>{$specUser} {$general['name']} 【 {$level} | {$call} | {$color}{$injury}</font> 】 ".substr($general['turntime'], 11)."</td>
    </tr>
    <tr height=16>
        <td align=center id=bg1><b>통솔</b></td>
        <td align=center>&nbsp;{$color}{$leader}</font>{$lbonus}&nbsp;</td>
        <td align=center width=45>".bar(expStatus($general['leader2']), $skin, 20)."</td>
        <td align=center id=bg1><b>무력</b></td>
        <td align=center>&nbsp;{$color}{$power}</font>&nbsp;</td>
        <td align=center width=45>".bar(expStatus($general['power2']), $skin, 20)."</td>
        <td align=center id=bg1><b>지력</b></td>
        <td align=center>&nbsp;{$color}{$intel}</font>&nbsp;</td>
        <td align=center width=45>".bar(expStatus($general['intel2']), $skin, 20)."</td>
    </tr>
    <tr>
        <td align=center id=bg1><b>명마</b></td>
        <td align=center colspan=2><font size=1>$horsename</font></td>
        <td align=center id=bg1><b>무기</b></td>
        <td align=center colspan=2><font size=1>$weapname</font></td>
        <td align=center id=bg1><b>서적</b></td>
        <td align=center colspan=2><font size=1>$bookname</font></td>
    </tr>
    <tr>
        <td align=center height=64 rowspan=3"; echo $skin>0?" background={$weapImage}":""; echo ">&nbsp;</td>
        <td align=center id=bg1><b>자금</b></td>
        <td align=center colspan=2>{$general['gold']}</td>
        <td align=center id=bg1><b>군량</b></td>
        <td align=center colspan=2>{$general['rice']}</td>
        <td align=center id=bg1><b>도구</b></td>
        <td align=center colspan=2><font size=1>$itemname</font></td>
    </tr>
    <tr>
        <td align=center id=bg1><b>병종</b></td>
        <td align=center colspan=2>$typename</td>
        <td align=center id=bg1><b>병사</b></td>
        <td align=center colspan=2>{$general['crew']}</td>
        <td align=center id=bg1><b>성격</b></td>
        <td align=center colspan=2>".getGenChar($general['personal'])."</td>
    </tr>
    <tr>
        <td align=center id=bg1><b>훈련</b></td>
        <td align=center colspan=2>$train</td>
        <td align=center id=bg1><b>사기</b></td>
        <td align=center colspan=2>$atmos</td>
        <td align=center id=bg1><b>특기</b></td>
        <td align=center colspan=2>$special / $special2</td>
    </tr>
    <tr height=20>
        <td align=center id=bg1><b>Lv</b></td>
        <td align=center>&nbsp;{$general['explevel']}&nbsp;</td>
        <td align=center colspan=5>".bar(getLevelPer($general['experience'], $general['explevel']), $skin, 20)."</td>
        <td align=center id=bg1><b>연령</b></td>
        <td align=center colspan=2>{$general['age']}</td>
    </tr>
    <tr height=20>
        <td align=center id=bg1><b>수비</b></td>
        <td align=center colspan=3>{$general['mode']}</td>
        <td align=center id=bg1><b>삭턴</b></td>
        <td align=center colspan=2>{$general['killturn']} 턴</td>
        <td align=center id=bg1><b>실행</b></td>
        <td align=center colspan=2>$remaining 분 남음</td>
    </tr>
    <tr height=20>
        <td align=center id=bg1><b>부대</b></td>
        <td align=center colspan=3>{$troop['name']}</td>
        <td align=center id=bg1><b>벌점</b></td>
        <td align=center colspan=5>".getConnect($general['connect'])." {$general['connect']}({$general['con']})</td>
    </tr>
</table>";
}

function myInfo2($connect) {
    $query = "select no,skin from general where user_id='{$_SESSION['p_id']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $me = MYDB_fetch_array($result);

    generalInfo2($connect, $me['no'], $me['skin']);
}

function generalInfo2($connect, $no, $skin) {
    global $_basecolor, $_basecolor2, $image, $images, $_dexLimit;

    $query = "select personal,experience,dedication,firenum,warnum,killnum,deathnum,killcrew,deathcrew,belong,killnum*100/warnum as winrate,killcrew/deathcrew*100 as killrate,dex0,dex10,dex20,dex30,dex40 from general where no='$no'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $general = MYDB_fetch_array($result);

    $general['winrate'] = round($general['winrate'], 2);
    $general['killrate'] = round($general['killrate'], 2);

    switch($general['personal']) {
        case  0:    case  1;    case  6:
            $experience = "<font color=cyan>".getHonor($general['experience'])." ({$general['experience']})</font>"; break;
        case  4:    case  5:    case  7:    case 10:
            $experience = "<font color=magenta>".getHonor($general['experience'])." ({$general['experience']})</font>"; break;
        default:
            $experience = getHonor($general['experience'])." ({$general['experience']})"; break;
    }
    switch($general['personal']) {
        case 10:
            $dedication = "<font color=magenta>".getDed($general['dedication'])." ({$general['dedication']})</font>"; break;
        default:
            $dedication = getDed($general['dedication'])." ({$general['dedication']})"; break;
    }

    if($skin == 0) {
        $experience = unfont($experience);
        $dedication = unfont($dedication);
    }

    $dex0  = $general['dex0']  / $_dexLimit * 100;
    $dex10 = $general['dex10'] / $_dexLimit * 100;
    $dex20 = $general['dex20'] / $_dexLimit * 100;
    $dex30 = $general['dex30'] / $_dexLimit * 100;
    $dex40 = $general['dex40'] / $_dexLimit * 100;

    if($dex0 > 100) { $dex0 = 100; }
    if($dex10 > 100) { $dex10 = 100; }
    if($dex20 > 100) { $dex20 = 100; }
    if($dex30 > 100) { $dex30 = 100; }
    if($dex40 > 100) { $dex40 = 100; }

    $general['dex0']  = getDexCall($general['dex0']);
    $general['dex10'] = getDexCall($general['dex10']);
    $general['dex20'] = getDexCall($general['dex20']);
    $general['dex30'] = getDexCall($general['dex30']);
    $general['dex40'] = getDexCall($general['dex40']);

    echo "<table width=498 border=1 cellspacing=0 cellpadding=0 bordercolordark=gray bordercolorlight=black style=font-size:13;word-break:break-all; id=bg2>
    <tr><td align=center colspan=6 id=bg1><b>추 가 정 보</b></td></tr>
    <tr>
        <td align=center id=bg1><b>명성</b></td>
        <td align=center>$experience</td>
        <td align=center id=bg1><b>계급</b></td>
        <td align=center colspan=3>$dedication</td>
    </tr>
    <tr>
        <td width=64 align=center id=bg1><b>전투</b></td>
        <td width=132 align=center>{$general['warnum']}</td>
        <td width=48 align=center id=bg1><b>계략</b></td>
        <td width=98 align=center>{$general['firenum']}</td>
        <td width=48 align=center id=bg1><b>사관</b></td>
        <td width=98 align=center>{$general['belong']}년</td>
    </tr>
    <tr>
        <td align=center id=bg1><b>승률</b></td>
        <td align=center>{$general['winrate']} %</td>
        <td align=center id=bg1><b>승리</b></td>
        <td align=center>{$general['killnum']}</td>
        <td align=center id=bg1><b>패배</b></td>
        <td align=center>{$general['deathnum']}</td>
    </tr>
    <tr>
        <td align=center id=bg1><b>살상률</b></td>
        <td align=center>{$general['killrate']} %</td>
        <td align=center id=bg1><b>사살</b></td>
        <td align=center>{$general['killcrew']}</td>
        <td align=center id=bg1><b>피살</b></td>
        <td align=center>{$general['deathcrew']}</td>
    </tr>
</table>
<table width=498 border=1 cellspacing=0 cellpadding=0 bordercolordark=gray bordercolorlight=black style=font-size:13;word-break:break-all; id=bg2>
    <tr><td align=center colspan=3 id=bg1><b>숙 련 도</b></td></tr>
    <tr height=16>
        <td width=64 align=center id=bg1><b>보병</b></td>
        <td width=64>　　$general[dex0]</td>
        <td width=366 align=center>".bar($dex0, $skin, 16)."</td>
    </tr>
    <tr height=16>
        <td align=center id=bg1><b>궁병</b></td>
        <td>　　$general[dex10]</td>
        <td align=center>".bar($dex10, $skin, 16)."</td>
    </tr>
    <tr height=16>
        <td align=center id=bg1><b>기병</b></td>
        <td>　　$general[dex20]</td>
        <td align=center>".bar($dex20, $skin, 16)."</td>
    </tr>
    <tr height=16>
        <td align=center id=bg1><b>귀병</b></td>
        <td>　　$general[dex30]</td>
        <td align=center>".bar($dex30, $skin, 16)."</td>
    </tr>
    <tr height=16>
        <td align=center id=bg1><b>차병</b></td>
        <td>　　$general[dex40]</td>
        <td align=center>".bar($dex40, $skin, 16)."</td>
    </tr>
</table>";
}

function pushTrickLog($connect, $log) {
    $size = count($log);
    if($size > 0) {
        $fp = fopen("logs/_tricklog.txt", "a");
        for($i=0; $i < $size; $i++) {
            fwrite($fp, $log[$i]."\r\n");
        }
        fclose($fp);
    }
}

function pushProcessLog($connect, $log) {
    $size = count($log);
    if($size > 0) {
        $date = date('Y_m_d');
        $fp = fopen("logs/_{$date}_processlog.txt", "a");
        for($i=0; $i < $size; $i++) {
            fwrite($fp, $log[$i]."\r\n");
        }
        fclose($fp);
    }
}

function delStepLog() {
    $date = date('Y_m_d');
    @unlink("logs/_{$date}_steplog.txt");
}

function pushStepLog($log) {
    $date = date('Y_m_d');
    $fp = fopen("logs/_{$date}_steplog.txt", "a");
    fwrite($fp, $log."\r\n");
    fclose($fp);
}

function pushLockLog($connect, $log) {
    $size = count($log);
    if($size > 0) {
        $date = date('Y_m_d');
        $fp = fopen("logs/_{$date}_locklog.txt", "a");
        for($i=0; $i < $size; $i++) {
            fwrite($fp, $log[$i]."\r\n");
        }
        fclose($fp);
    }
}

function pushAdminLog($connect, $log) {
    $size = count($log);
    if($size > 0) {
        $fp = fopen("logs/_adminlog.txt", "a");
        for($i=0; $i < $size; $i++) {
            fwrite($fp, $log[$i]."\r\n");
        }
        fclose($fp);
    }
}

function pushAuctionLog($connect, $log) {
    $size = count($log);
    if($size > 0) {
        $fp = fopen("logs/_auctionlog.txt", "a");
        for($i=0; $i < $size; $i++) {
            fwrite($fp, $log[$i]."\r\n");
        }
        fclose($fp);
    }
}

function pushGenLog($general, $log) {
    $size = count($log);
    if($size > 0) {
        $fp = fopen("logs/gen{$general['no']}.txt", "a");
        for($i=0; $i < $size; $i++) {
            fwrite($fp, $log[$i]."\r\n");
        }
        fclose($fp);
    }
}

function pushBatRes($general, $log) {
    $size = count($log);
    if($size > 0) {
        $fp = fopen("logs/batres{$general['no']}.txt", "a");
        for($i=0; $i < $size; $i++) {
            fwrite($fp, $log[$i]."\r\n");
        }
        fclose($fp);
    }
}

function pushBatLog($general, $log) {
    $size = count($log);
    if($size > 0) {
        $fp = fopen("logs/batlog{$general['no']}.txt", "a");
        for($i=0; $i < $size; $i++) {
            fwrite($fp, $log[$i]."\r\n");
        }
        fclose($fp);
    }
}

function pushAllLog($log) {
    $size = count($log);
    if($size > 0) {
        $fp = fopen("logs/_alllog.txt", "a");
        for($i=0; $i < $size; $i++) {
            fwrite($fp, $log[$i]."\r\n");
        }
        fclose($fp);
    }
}

function pushHistory($connect, $history) {
    $size = count($history);
    if($size > 0) {
        $fp = fopen("logs/_history.txt", "a");
        for($i=0; $i < $size; $i++) {
            fwrite($fp, $history[$i]."\r\n");
        }
        fclose($fp);
    }
}

function TrickLog($count, $skin) {
    $fp = @fopen("logs/_tricklog.txt", "r");
    @fseek($fp, -$count*150, SEEK_END);
    $file = @fread($fp, $count*150);
    @fclose($fp);
    $log = explode("\r\n",$file);
    $str = "";
    for($i=0; $i < $count; $i++) { $str .= ConvertLog($log[count($log)-2-$i], $skin)."<br>"; }
    echo $str;
}

function AllLog($count, $skin) {
    $fp = @fopen("logs/_alllog.txt", "r");
    @fseek($fp, -$count*300, SEEK_END);
    $file = @fread($fp, $count*300);
    @fclose($fp);
    $log = explode("\r\n",$file);
    $str = "";
    for($i=0; $i < $count; $i++) {
    	 $str .= isset($log[count($log)-2-$i]) ? ConvertLog($log[count($log)-2-$i], $skin)."<br>" : "<br>"; 
  	}
    echo $str;
}

function AuctionLog($count, $skin) {
    $fp = @fopen("logs/_auctionlog.txt", "r");
    @fseek($fp, -$count*300, SEEK_END);
    $file = @fread($fp, $count*300);
    @fclose($fp);
    $log = explode("\r\n",$file);
    $str = "";
    for($i=0; $i < $count; $i++) { $str .= ConvertLog($log[count($log)-2-$i], $skin)."<br>"; }
    echo $str;
}

function History($count, $skin) {
    $fp = @fopen("logs/_history.txt", "r");
    @fseek($fp, -$count*300, SEEK_END);
    $file = @fread($fp, $count*300);
    @fclose($fp);
    $log = explode("\r\n",$file);
    $str = "";
    for($i=0; $i < $count; $i++) {
    	 $str .= isset($log[count($log)-2-$i]) ? ConvertLog($log[count($log)-2-$i], $skin)."<br>" : "<br>"; 
	}
    echo $str;
}

function MyLog($no, $count, $skin) {
    $fp = @fopen("logs/gen{$no}.txt", "r");
    @fseek($fp, -$count*300, SEEK_END);
    $file = @fread($fp, $count*300);
    @fclose($fp);
    $log = explode("\r\n",$file);
    $str = "";
    for($i=0; $i < $count; $i++) {
    	 $str .= isset($log[count($log)-2-$i]) ? ConvertLog($log[count($log)-2-$i], $skin)."<br>" : "<br>"; 
	}
    echo $str;
}

function MyBatRes($no, $count, $skin) {
    $fp = @fopen("logs/batres{$no}.txt", "r");
    @fseek($fp, -$count*300, SEEK_END);
    $file = @fread($fp, $count*300);
    @fclose($fp);
    $log = explode("\r\n",$file);
    $str = "";
    for($i=0; $i < $count; $i++) {
         $str .= isset($log[count($log)-2-$i]) ?  ConvertLog($log[count($log)-2-$i], $skin)."<br>" : "<br>"; 
    }
    echo $str;
}

function MyBatLog($no, $count, $skin) {
    $fp = @fopen("logs/batlog{$no}.txt", "r");
    @fseek($fp, -$count*300, SEEK_END);
    $file = @fread($fp, $count*300);
    @fclose($fp);
    $log = explode("\r\n",$file);
    $str = "";
    for($i=0; $i < $count; $i++) {
         $str .= isset($log[count($log)-2-$i]) ?  ConvertLog($log[count($log)-2-$i], $skin)."<br>" : "<br>"; 
    }
    echo $str;
}

function MyHistory($connect, $no, $skin) {
    $query = "select history from general where no='$no'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $general = MYDB_fetch_array($result);
    echo ConvertLog($general['history'], $skin);
}

function addHistory($connect, $me, $history) {
    $me['history'] = "{$history}<br>{$me['history']}";
    $query = "update general set history='{$me['history']}' where no='{$me['no']}'";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

    return $me;
}

function addNationHistory($connect, $nation, $history) {
    $nation['history'] = "{$nation['history']}{$history}<br>";
    $query = "update nation set history='{$nation['history']}' where nation='{$nation['nation']}'";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

    return $nation;
}

function adminMsg($connect, $skin=1) {
    $query = "select msg from game where no='1'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $admin = MYDB_fetch_array($result);

    echo "운영자 메세지 : <font color="; echo $skin>0?"yellow":"white"; echo ">";
    echo $admin['msg']."</font>";
}

function allButton($connect) {
    global $_basecolor2;
    $query = "select npcmode from game where no='1'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $admin = MYDB_fetch_array($result);
    if($admin['npcmode'] == 1) {
        $site = "a_npcList.php";
        $call = "빙의일람";
    } else {
        $site = "a_vote.php";
        $call = "설문조사";
    }

    echo "
<table align=center border=0 cellspacing=0 cellpadding=0 bordercolordark=gray bordercolorlight=black style=font-size:13;word-break:break-all; id=bg1>
    <tr>
        <td align=center><input type=button style=background-color:$_basecolor2;color:white;width:125;height:30;font-weight:bold;font-size:13; value='세력도' onclick=window.open('a_status.php')></td>
        <td align=center><input type=button style=background-color:$_basecolor2;color:white;width:125;height:30;font-weight:bold;font-size:13; value='세력일람' onclick=window.open('a_kingdomList.php')></td>
        <td align=center><input type=button style=background-color:$_basecolor2;color:white;width:125;height:30;font-weight:bold;font-size:13; value='장수일람' onclick=window.open('a_genList.php')></td>
        <td align=center><input type=button style=background-color:$_basecolor2;color:white;width:125;height:30;font-weight:bold;font-size:13; value='명장일람' onclick=window.open('a_bestGeneral.php')></td>
        <td align=center><input type=button style=background-color:$_basecolor2;color:white;width:125;height:30;font-weight:bold;font-size:13; value='연감' onclick=window.open('a_history.php')></td>
        <td align=center><input type=button style=background-color:$_basecolor2;color:white;width:125;height:30;font-weight:bold;font-size:13; value='명예의전당' onclick=window.open('a_hallOfFame.php')></td>
        <td align=center><input type=button style=background-color:$_basecolor2;color:white;width:125;height:30;font-weight:bold;font-size:13; value='왕조일람' onclick=window.open('a_emperior.php')></td>
        <td align=center><input type=button style=background-color:$_basecolor2;color:white;width:125;height:30;font-weight:bold;font-size:13; value='접속량정보' onclick=window.open('a_traffic.php')></td>
    </tr>
    <tr>
        <td align=center><input type=button style=background-color:$_basecolor2;color:magenta;width:125;height:30;font-weight:bold;font-size:13; value='삼모게시판' onclick=window.open('/bbs/bbs/board.php?bo_table=0free')></td>
        <td align=center><input type=button style=background-color:$_basecolor2;color:white;width:125;height:30;font-weight:bold;font-size:13; value='삼국일보' onclick=window.open('/bbs/bbs/board.php?bo_table=1news')></td>
        <td align=center><input type=button style=background-color:$_basecolor2;color:white;width:125;height:30;font-weight:bold;font-size:13; value='레퍼런스' onclick=window.open('/bbs/bbs/board.php?bo_table=2reference')></td>
        <td align=center><input type=button style=background-color:$_basecolor2;color:white;width:125;height:30;font-weight:bold;font-size:13; value='패치게시판' onclick=window.open('/bbs/bbs/board.php?bo_table=3patch')></td>
        <td align=center><input type=button style=background-color:$_basecolor2;color:white;width:125;height:30;font-weight:bold;font-size:13; value='-'></td>
        <td align=center><input type=button style=background-color:$_basecolor2;color:white;width:125;height:30;font-weight:bold;font-size:13; value='참여게시판' onclick=window.open('/bbs/bbs/board.php?bo_table=4donation')></td>
        <td align=center><input type=button style=background-color:$_basecolor2;color:white;width:125;height:30;font-weight:bold;font-size:13; value='튜토리얼' onclick=window.open('../i_other/help.php')></td>
        <td align=center><input type=button style=background-color:$_basecolor2;color:white;width:125;height:30;font-weight:bold;font-size:13; value='{$call}' onclick=window.open('{$site}')></td>
    </tr>
</table>";
}

function onlinenum($connect) {
    $query = "select online from game where no='1'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $game = MYDB_fetch_array($result);
    return $game['online'];
}

function onlinegen($connect) {
    $onlinegen = "";
    if($_SESSION['p_nation'] == 0) {
        $query = "select onlinegen from game where no='1'";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $game = MYDB_fetch_array($result);

        $onlinegen = $game['onlinegen'];
    } else {
        $query = "select onlinegen from nation where nation='{$_SESSION['p_nation']}'";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $nation = MYDB_fetch_array($result);

        $onlinegen = $nation['onlinegen'];
    }
    return $onlinegen;
}

function onlineNation($connect) {
    $query = "select onlinenation from game where no='1'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $game = MYDB_fetch_array($result);
    return $game['onlinenation'];
}

function nationMsg($connect) {
    $query = "select no,nation,skin from general where user_id='{$_SESSION['p_id']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $me = MYDB_fetch_array($result);

    $query = "select msg from nation where nation='{$me['nation']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $nation = MYDB_fetch_array($result);

    echo "<font color="; echo $me['skin']>0?"orange":"white"; echo ">".$nation['msg']."</font>";
}

function genList($connect) {
    $query = "select no,nation,level,msgindex,userlevel from general where user_id='{$_SESSION['p_id']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $me = MYDB_fetch_array($result);
    $you = array();
    
    $query = "select msg{$me['msgindex']}_who as reply,msg{$me['msgindex']}_type as type from general where user_id='{$_SESSION['p_id']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $reply = MYDB_fetch_array($result);
    if($reply['type'] % 100 == 9) {
        $reply['reply'] %= 10000;
        $query = "select no,name from general where no={$reply['reply']}";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $you = MYDB_fetch_array($result);
    } elseif($reply['type'] % 100 == 10) {
        $reply['reply'] = floor($reply['reply']/10000);
        $query = "select no,name from general where no={$reply['reply']}";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $you = MYDB_fetch_array($result);
    }

    $query = "select nation,color,name from nation where nation='{$me['nation']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $nation = MYDB_fetch_array($result);

    echo "
<select name=genlist size=1 style=color:white;background-color:black;font-size:13>
    <optgroup label='즐겨찾기'>";
    if($me['nation'] != 0) {
        echo "
    <option selected style=color:".newColor($nation['color']).";background-color:{$nation['color']} value="; echo $nation['nation']+9000; echo ">【 아국 메세지 】</option>";
    } else {
        echo "
    <option selected style=color:".newColor($nation['color']).";background-color:{$nation['color']} value="; echo 9000; echo ">【 재야 】</option>";
    }
    echo "
    <option value=9999>【 전체&nbsp;&nbsp;&nbsp;메세지 】</option>";
    if($you) {
        echo "
    <option value={$you['no']}>{$you['name']}</option>";
    }
    echo "
    <option value=1>운영자</option>";

    if($me['level'] >= 5 || $me['userlevel'] >= 5) {
        echo "
    </optgroup>
    <optgroup label='국가메세지'>
    <option value=9000>【 재야 】</option>";

        $query = "select nation,name,color from nation order by binary(name)";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $count = MYDB_num_rows($result);
        for($i=1; $i <= $count; $i++) {
            $nation = MYDB_fetch_array($result);
            $nationNation[$nation['nation']] = $nation['nation'];
            $nationName[$nation['nation']]   = $nation['name'];
            $nationColor[$nation['nation']]  = $nation['color'];
            echo "
    <option style=color:".newColor($nation['color']).";background-color:{$nation['color']} value="; echo $nation['nation']+9000; echo ">【 {$nation['name']} 】</option>";
        }
        echo "
    </optgroup>";
    }

    echo "
    <optgroup label='개인메세지'>
    <optgroup label='재야'>";
    $query = "select no,name,npc from general where nation=0 and user_id!='{$_SESSION['p_id']}' and npc<2 order by npc,binary(name)";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $gencount = MYDB_num_rows($result);
    for($i=0; $i < $gencount; $i++) {
        $general = MYDB_fetch_array($result);
        if($general['npc'] >= 2)     { $color = "cyan"; }
        elseif($general['npc'] == 1) { $color = "skyblue"; }
        else                       { $color = "white"; }
        echo "
    <option value={$general['no']} style=color:{$color};background-color:black;>{$general['name']}</option>";
    }
    echo "
    </optgroup>";

    $query = "select nation,name,color from nation order by binary(name)";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $count = MYDB_num_rows($result);
    for($i=1; $i <= $count; $i++) {
        $nation = MYDB_fetch_array($result);
        echo "
    <optgroup label='【{$nation['name']}】' style=color:".newColor($nation['color']).";background-color:{$nation['color']};>";

        $query = "select no,name,npc,level from general where nation='{$nation['nation']}' and user_id!='{$_SESSION['p_id']}' and npc<2 order by npc,binary(name)";
        $genresult = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $gencount = MYDB_num_rows($genresult);
        for($j=0; $j < $gencount; $j++) {
            $general = MYDB_fetch_array($genresult);
            if($general['level'] >= 12) { $general['name'] = "*{$general['name']}*"; }
            if($general['npc'] >= 2)     { $color = "cyan"; }
            elseif($general['npc'] == 1) { $color = "skyblue"; }
            else                       { $color = "white"; }
            echo "
    <option value={$general['no']} style=color:{$color};background-color:black;>{$general['name']}</option>";
        }
        echo "
    </optgroup>";
    }
    echo "
</select>
";
}

function moveMsg($connect, $table, $msgtype, $msgnum, $msg, $type, $who, $when, $column, $value) {
    $query = "update {$table} set {$msgtype}{$msgnum}='$msg',{$msgtype}{$msgnum}_type='$type',{$msgtype}{$msgnum}_who='$who',{$msgtype}{$msgnum}_when='$when' where {$column}='$value'";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
}

function MsgMe($connect, $bg) {
    $query = "select no,nation,msgindex,
        msg0,msg1,msg2,msg3,msg4,msg5,msg6,msg7,msg8,msg9,
        msg0_type,msg1_type,msg2_type,msg3_type,msg4_type,msg5_type,msg6_type,msg7_type,msg8_type,msg9_type,
        msg0_who,msg1_who,msg2_who,msg3_who,msg4_who,msg5_who,msg6_who,msg7_who,msg8_who,msg9_who,
        msg0_when,msg1_when,msg2_when,msg3_when,msg4_when,msg5_when,msg6_when,msg7_when,msg8_when,msg9_when
        from general where user_id='{$_SESSION['p_id']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $me = MYDB_fetch_array($result);

    $index = $me['msgindex'];
    for($i=0; $i < 10; $i++) {
        if($me["msg{$index}"]) { echo "\n"; DecodeMsg($connect, $me["msg{$index}"], $me["msg{$index}_type"], $me["msg{$index}_who"], $me["msg{$index}_when"], $bg, $index); }
        $index--;
        if($index < 0) { $index = 9; }
    }
}

function MsgDip($connect, $bg) {
    $query = "select no,nation from general where user_id='{$_SESSION['p_id']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $me = MYDB_fetch_array($result);

    $query = "select dip0,dip1,dip2,dip3,dip4,dip0_who,dip1_who,dip2_who,dip3_who,dip4_who,dip0_when,dip1_when,dip2_when,dip3_when,dip4_when,dip0_type,dip1_type,dip2_type,dip3_type,dip4_type from nation where nation='{$me['nation']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $nation = MYDB_fetch_array($result);

    if($nation['dip0']) { echo "\n"; DecodeMsg($connect, $nation['dip0'], $nation['dip0_type'], $nation['dip0_who'], $nation['dip0_when'], $bg, 0); }
    if($nation['dip1']) { echo "\n"; DecodeMsg($connect, $nation['dip1'], $nation['dip1_type'], $nation['dip1_who'], $nation['dip1_when'], $bg, 1); }
    if($nation['dip2']) { echo "\n"; DecodeMsg($connect, $nation['dip2'], $nation['dip2_type'], $nation['dip2_who'], $nation['dip2_when'], $bg, 2); }
    if($nation['dip3']) { echo "\n"; DecodeMsg($connect, $nation['dip3'], $nation['dip3_type'], $nation['dip3_who'], $nation['dip3_when'], $bg, 3); }
    if($nation['dip4']) { echo "\n"; DecodeMsg($connect, $nation['dip4'], $nation['dip4_type'], $nation['dip4_who'], $nation['dip4_when'], $bg, 4); }
}

// type : xx,xx(불가침기간,타입)
// who : xxxx,xxxx(발신인, 수신인)
function DecodeMsg($connect, $msg, $type, $who, $date, $bg, $num=0) {
    $query = "select skin,no,nation,name,picture,level from general where user_id='{$_SESSION['p_id']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $me = MYDB_fetch_array($result);

    // 1 : 전메, 2 : 아국->타국, 3 : 타국->아국
    // 4 : 합병, 5 : 통합, 6 : 불가침, 7 : 종전, 8 : 파기
    // 9 : 자신->타인, 10 : 타인 -> 자신, 11 : 등용
    $category = $type % 100;
    $term = floor($type / 100);
    $from = floor($who / 10000);
    $to = $who % 10000;

    $query = "select name,picture,imgsvr,nation from general where no='$from'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $sndr = MYDB_fetch_array($result);

    if($sndr['nation'] == 0) {
        $sndrnation['name'] = '재야';
        $sndrnation['color'] = 'FFFFFF';
    } else {
        $query = "select name,color from nation where nation='{$sndr['nation']}'";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $sndrnation = MYDB_fetch_array($result);
    }

    switch($bg) {
        case 2:
        case 4: $bgcolor = "CC6600"; break;
    }

    if($category == 6) {
        $query = "select reserved from diplomacy where me='{$sndr['nation']}' and you='{$me['nation']}'";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $dip = MYDB_fetch_array($result);

        ShowMsg($me['skin'], $bgcolor, $category, $sndr['picture'], $sndr['imgsvr'], "{$sndr['name']}:{$sndrnation['name']}▶", $sndrnation['color'], $sndr['name'], $sndrnation['color'], $msg, $date, $num, $from, $term, $me['level'], $dip['reserved']);
    } elseif($category <= 8) {
        $query = "select name,color from nation where nation='$to'";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $rcvrnation = MYDB_fetch_array($result);

        ShowMsg($me['skin'], $bgcolor, $category, $sndr['picture'], $sndr['imgsvr'], "{$sndr['name']}:{$sndrnation['name']}▶", $sndrnation['color'], $rcvrnation['name'], $rcvrnation['color'], $msg, $date, $num, $from, $term, $me['level']);
    } elseif($category <= 11) {
        $query = "select name,picture,nation from general where no='$to'";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $rcvr = MYDB_fetch_array($result);

        if($rcvr['nation'] == 0) {
            $rcvrnation['name'] = '재야';
            $rcvrnation['color'] = 'FFFFFF';
        } else {
            $query = "select name,color from nation where nation='{$rcvr['nation']}'";
            $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            $rcvrnation = MYDB_fetch_array($result);
        }
        ShowMsg($me['skin'], $bgcolor, $category, $sndr['picture'], $sndr['imgsvr'], "{$sndr['name']}:{$sndrnation['name']}▶", $sndrnation['color'], "{$rcvr['name']}:{$rcvrnation['name']}", $rcvrnation['color'], $msg, $date, $num, $from, $term);
    }
}

function PushMsg($type, $nation, $picture, $imgsvr, $from, $fromcolor, $to, $tocolor, $msg) {
    if($nation == 0) { $nation = 0; }
    if($fromcolor == "") { $fromcolor = "FFFFFF"; }
    if($to == "") { $to = "재야"; }
    if($tocolor == "") { $tocolor = "FFFFFF"; }

    $date = date('Y-m-d H:i:s');
    if($type == 1) { $file = "_all_msg.txt"; }
    else { $file = "_nation_msg{$nation}.txt"; }
    $fp = fopen("logs/{$file}", "a");
    //로그 파일에 기록
    $str = "{$type}|"._String::Fill($from,12," ")."|"._String::Fill($to,12," ")."|".$date."|".$msg."|".$fromcolor."|".$tocolor."|".$picture."|".$imgsvr;
    fwrite($fp, "{$str}\r\n");
    fclose($fp);
}

function MsgFile($skin, $bg, $nation=0, $level=0) {
    switch($bg) {
        case 1: $bgcolor = "000055"; $count = 10; $fl = "_all_msg.txt"; break;
        case 3: $bgcolor = "336600"; $count = 20; $fl = "_nation_msg{$nation}.txt"; break;
    }

    $fp = @fopen("logs/{$fl}", "r");
    @fseek($fp, -$count*300, SEEK_END);
    $file = @fread($fp, $count*300);
    @fclose($fp);
    $code = explode("\r\n",$file);
    for($i=0; $i < $count; $i++) {
        $msg = isset($code[count($code)-2-$i]) ? explode("|", $code[count($code)-2-$i]) : array();

        $cnt = count($msg);
		if(!empty($cnt)){
	        for($k=0; $k < $cnt; $k++) {
	        	 $msg[$k] = trim($msg[$k]); 
			}
		}
        if(!empty($msg)){
            ShowMsg($skin, $bgcolor, $msg[0], $msg[7], $msg[8], $msg[1], $msg[5], $msg[2], $msg[6], $msg[4], $msg[3]);
        }
    }
}

function ShowMsg($skin, $bgcolor, $type, $picture, $imgsvr, $me, $mycolor, $you, $youcolor, $msg, $date, $num=0, $who=0, $when=0, $level=0, $note="") {
    if($msg == "") return;

    $msg = Tag2Code($msg);

    $site = ""; $form = ""; $form2 = "";
    if($type == 11 || ($type >= 4 && $type <= 8 && $level >= 5)) {
        $corebutton = "&nbsp;<input type=submit name=ok value=수락 onclick='return confirm(\"정말 수락하시겠습니까?\")'><input type=submit name=ok value=거절 onclick='return confirm(\"정말 거절하시겠습니까?\")'>";
    } else {
        $corebutton = "&nbsp;【수락】【거절】";
    }
    if($type == 6) {
        $corebutton .= "<br>비고: {$note}";
    }
    switch($type) {
    case  1: $sign = ""; $corebutton = ""; break;
    case  2: $sign = ""; $corebutton = ""; break;
    case  3: $sign = ""; $corebutton = ""; break;
    case  4: $sign = ""; $site = "d_surrender.php"; break;
    case  5: $sign = ""; $site = "d_merge.php";     break;
    case  6: $sign = ""; $site = "d_ally.php";      break;
    case  7: $sign = ""; $site = "d_cease.php";     break;
    case  8: $sign = ""; $site = "d_cancel.php";    break;
    case  9: $sign = ""; $corebutton = ""; break;
    case 10: $sign = ""; $corebutton = ""; break;
    case 11: $sign = ""; $site = "d_scout.php"; break;
    }
    if($skin == 0) {
        $bgcolor = "000000"; $picture = "";
        $naming = "[{$me}{$sign}{$you}]";
    } else {
        $imageTemp = GetImageURL($imgsvr);
        $naming = "[<font color=$mycolor>$me</font>{$sign}<font color=$youcolor>$you</font>]";
        $picture = "<img src={$imageTemp}/{$picture}>";
    }
    if($site != "") {
        $form = "<form name=scout method=post action={$site}>";
        $form2 = "</form>";
    }
    if($num >= 0) { $num = "<input type=hidden name=num value=$num>"; }
    else { $num = ""; }
    if($who > 0) { $who = "<input type=hidden name=gen value=$who>"; }
    else { $who = ""; }
    if($when > 0) { $when = "<input type=hidden name=when value=$when>"; }
    else { $when = ""; }
    echo "
        <table width=498 border=1  bordercolordark=gray bordercolorlight=black cellpadding=0 cellspacing=0 bgcolor='$bgcolor' style=font-size:13;table-layout:fixed;word-break:break-all;>
            <tr>
                <td width=64 height=64>$picture</td>
                $form
                <td width=434 valign=top><b>$naming</b><font size=1><$date></font> <br>{$msg}{$corebutton}</td>
                $num
                $who
                $when
                $form2
            </tr>
        </table>
    ";
}

function msgprint($connect, $msg, $name, $picture, $imgsvr, $when, $num, $type) {
    global $_basecolor2, $_basecolor4, $images;

    $message = explode("|", $msg);
    $count = (count($message) - 2)/2;
    $message[0] = Tag2Code($message[0]);
    $message[1] = Tag2Code($message[1]);
//    $message[0] = str_replace("\n", "<br>", $message[0]);
//    $message[1] = str_replace("\n", "<br>", $message[1]);

    if($type == 0) { $board = "c_nationboard.php"; }
    else { $board = "c_chiefboard.php"; }

    $imageTemp = GetImageURL($imgsvr);
    echo "
<table align=center width=1000 border=1 cellspacing=0 cellpadding=0 bordercolordark=gray bordercolorlight=black style=font-size:13;word-break:break-all; id=bg0>
    <tr>
        <td width=64 align=center id=bg1><font size=1>$name</font></td>
        <td width=772 align=center id=bg1><font size=4><b>$message[0]</b></font></td>
        <td width=148 align=center id=bg1>$when</td>
    </tr>
    <tr>
        <td width=64 height=64 valign=top><img src={$imageTemp}/{$picture} width=64 height=64 border=0></td>
        <td width=932 colspan=2>$message[1]</td>
    </tr>";
    for($i=0; $i < $count; $i++) {
        $who = Tag2Code($message[2+$i*2]);
        $reply = Tag2Code($message[3+$i*2]);
        $query = "select name from general where no='$who'";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $regen = MYDB_fetch_array($result);
        echo "
    <tr>
        <td width=64 align=center><font size=1>{$regen['name']}</font></td>
        <td width=932 colspan=2>$reply</td>
    </tr>";
    }
    echo "
    <tr>
        <form name=reply_form{$num} method=post action=$board>
        <td width=64 align=center>댓글달기</td>
        <td width=932 colspan=2>
            <input type=textarea name=reply maxlength=250 style=color:white;background-color:black;width:830;>
            <input type=submit value=댓글달기>
            <input type=hidden name=num value=$num>
        </td>
        </form>
    </tr>
</table>
<br>";
}

function banner() {
    global $_version, $_banner, $_helper;
    echo "<font size=2>$_version / $_banner <br> $_helper</font>";
}

function addTurn($date, $turnterm=1) {
    $lastday = array(0,31,28,31,30,31,30,31,31,30,31,30,31);

    $year  = $date[0].$date[1].$date[2].$date[3];
    $month = floor($date[5].$date[6]);
    $day   = $date[8].$date[9];
    $hour  = $date[11].$date[12];
    $min   = $date[14].$date[15];
    $sec   = $date[17].$date[18];

    //윤년계산
    if((($year % 4 == 0) && ($year % 100 != 0)) || ($year % 400 == 0)) { $lastday[2] = 29; }

    if($turnterm == 0)     { $hour += 2; }
    elseif($turnterm == 1) { $hour += 1; }
    elseif($turnterm == 2) { $min += 30; }
    elseif($turnterm == 3) { $min += 20; }
    elseif($turnterm == 4) { $min += 10; }
    elseif($turnterm == 5) { $min +=  5; }
    elseif($turnterm == 6) { $min +=  2; }
    elseif($turnterm == 7) { $min +=  1; }

    if($min >= 60) { $min %= 60; $hour++; }
    if($hour >= 24) { $hour %= 24; $day++; }
    if($day > $lastday[$month]) { $month++; $day = 1; }
    if($month >= 13) { $year++; $month = 1; }

    $month = _String::Fill2($month, 2, "0");
    $day   = _String::Fill2($day,   2, "0");
    $hour  = _String::Fill2($hour,  2, "0");
    $min   = _String::Fill2($min,   2, "0");
    $sec   = _String::Fill2($sec,   2, "0");

    return "{$year}-{$month}-{$day} {$hour}:{$min}:{$sec}";
}

function add12Turn($date, $turnterm=1) {
    $lastday = array(0,31,28,31,30,31,30,31,31,30,31,30,31);

    $year  = $date[0].$date[1].$date[2].$date[3];
    $month = floor($date[5].$date[6]);
    $day   = $date[8].$date[9];
    $hour  = $date[11].$date[12];
    $min   = $date[14].$date[15];
    $sec   = $date[17].$date[18];

    //윤년계산
    if((($year % 4 == 0) && ($year % 100 != 0)) || ($year % 400 == 0)) { $lastday[2] = 29; }

    if($turnterm == 0)     { $day  +=  1; }
    elseif($turnterm == 1) { $hour += 12; }
    elseif($turnterm == 2) { $hour +=  6; }
    elseif($turnterm == 3) { $hour +=  4; }
    elseif($turnterm == 4) { $hour +=  2; }
    elseif($turnterm == 5) { $hour +=  1; }
    elseif($turnterm == 6) { $min  += 24; }
    elseif($turnterm == 7) { $min  += 12; }

    if($min >= 60) { $min %= 60; $hour++; }
    if($hour >= 24) { $hour %= 24; $day++; }
    if($day > $lastday[$month]) { $month++; $day = 1; }
    if($month >= 13) { $year++; $month = 1; }

    $month = _String::Fill2($month, 2, "0");
    $day   = _String::Fill2($day,   2, "0");
    $hour  = _String::Fill2($hour,  2, "0");
    $min   = _String::Fill2($min,   2, "0");
    $sec   = _String::Fill2($sec,   2, "0");

    return "{$year}-{$month}-{$day} {$hour}:{$min}:{$sec}";
}

function subTurn($date, $turnterm=1) {
    $lastday = array(0,31,28,31,30,31,30,31,31,30,31,30,31);

    $year  = $date[0].$date[1].$date[2].$date[3];
    $month = floor($date[5].$date[6]);
    $day   = $date[8].$date[9];
    $hour  = $date[11].$date[12];
    $min   = $date[14].$date[15];
    $sec   = $date[17].$date[18];

    if($turnterm == 0)     { $hour -= 2; }
    elseif($turnterm == 1) { $hour -= 1; }
    elseif($turnterm == 2) { $min -= 30; }
    elseif($turnterm == 3) { $min -= 20; }
    elseif($turnterm == 4) { $min -= 10; }
    elseif($turnterm == 5) { $min -=  5; }
    elseif($turnterm == 6) { $min -=  2; }
    elseif($turnterm == 7) { $min -=  1; }

    //윤년계산
    if((($year % 4 == 0) && ($year % 100 != 0)) || ($year % 400 == 0)) { $lastday[2] = 29; }

    if($min < 0) { $min = 60 + $min; $hour--; }
    if($hour < 0) { $hour = 24 + $hour; $day--; }
    if($day <= 0) { $month--; $day = $lastday[$month]; }
    if($month <= 0) { $year--; $month = 12; }

    $year  = _String::Fill2($year,  2, "0");
    $month = _String::Fill2($month, 2, "0");
    $day   = _String::Fill2($day,   2, "0");
    $hour  = _String::Fill2($hour,  2, "0");
    $min   = _String::Fill2($min,   2, "0");
    $sec   = _String::Fill2($sec,   2, "0");

    return "{$year}-{$month}-{$day} {$hour}:{$min}:{$sec}";
}

function cutTurn($date, $turnterm=1) {
    //          0123456789012345678
    // $date = "2000-01-01 00:00:00";
    // 0 : 120분, 1 : 60분, 2 : 30분, 3 : 10분, 4 : 5분
    switch($turnterm) {
        case 0:
            $hour = $date[11].$date[12];
            if($hour % 2 == 1) { $hour--; }
            $date[11] = floor($hour / 10);
            $date[12] = $hour % 10;
            $date[14] = "0";
            $date[15] = "0";
            $date[17] = "0";
            $date[18] = "0";
            break;
        case 1:
            $date[14] = "0";
            $date[15] = "0";
            $date[17] = "0";
            $date[18] = "0";
            break;
        case 2:
            $min = $date[14].$date[15];
            if($min < 30) { $min = 0; }
            else { $min = 30; }
            $date[14] = floor($min / 10);
            $date[15] = $min % 10;
            $date[17] = "0";
            $date[18] = "0";
            break;
        case 3:
            $min = $date[14].$date[15];
            if($min < 20) { $min = 0; }
            elseif($min < 40) { $min = 20; }
            else { $min = 40; }
            $date[14] = floor($min / 10);
            $date[15] = $min % 10;
            $date[17] = "0";
            $date[18] = "0";
            break;
        case 4:
            $min = $date[14].$date[15];
            if($min < 10) { $min = 0; }
            elseif($min < 20) { $min = 10; }
            elseif($min < 30) { $min = 20; }
            elseif($min < 40) { $min = 30; }
            elseif($min < 50) { $min = 40; }
            else { $min = 50; }
            $date[14] = floor($min / 10);
            $date[15] = $min % 10;
            $date[17] = "0";
            $date[18] = "0";
            break;
        case 5:
            $min = $date[14].$date[15];
            if($min < 5) { $min = 0; }
            elseif($min < 10) { $min = 5; }
            elseif($min < 15) { $min = 10; }
            elseif($min < 20) { $min = 15; }
            elseif($min < 25) { $min = 20; }
            elseif($min < 30) { $min = 25; }
            elseif($min < 35) { $min = 30; }
            elseif($min < 40) { $min = 35; }
            elseif($min < 45) { $min = 40; }
            elseif($min < 50) { $min = 45; }
            elseif($min < 55) { $min = 50; }
            else { $min = 55; }
            $date[14] = floor($min / 10);
            $date[15] = $min % 10;
            $date[17] = "0";
            $date[18] = "0";
            break;
        case 6:
            $min = $date[14].$date[15];
            $min = floor($min / 2) * 2;
            $date[14] = floor($min / 10);
            $date[15] = $min % 10;
            $date[17] = "0";
            $date[18] = "0";
            break;
        case 7:
            $date[17] = "0";
            $date[18] = "0";
            break;
    }

    return $date;
}

function CutDay($date) {
    $hour = $date[11].$date[12];
    if($hour >= 19) {
        $date[11] = "0";
        $date[12] = "1";

        $date = add12Turn($date, 1);
        $date = add12Turn($date, 1);
    } elseif($hour < 07) {
        $date[11] = "0";
        $date[12] = "1";
    } else {
        $date[11] = "1";
        $date[12] = "3";
    }
    $date[14] = "0";
    $date[15] = "0";
    $date[17] = "0";
    $date[18] = "0";
    return $date;
}

function increaseRefresh($connect, $type="", $cnt=1) {
    $date = date('Y-m-d H:i:s');

    $query = "update game set refresh=refresh+'$cnt' where no='1'";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

    if($_SESSION['p_id'] != "") {
        $query = sprintf("update general set lastrefresh='%s',con=con+'%d',connect=connect+'%d',refcnt=refcnt+'%d',refresh=refresh+'%d' where user_id='%s'",
        $date,$cnt,$cnt,$cnt,$cnt,$_SESSION['p_id']);
        
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    }

    $date = date('Y_m_d H:i:s');
    $date2 = substr($date, 0, 10);
    $online = onlinenum($connect);
    $fp = fopen("logs/_{$date2}_refresh.txt", "a");
    $msg = _String::Fill2($date,20," ")._String::Fill2($_SESSION['p_id'],13," ")._String::Fill2($_SESSION['p_name'],13," ")._String::Fill2($_SESSION['p_ip'],16," ")._String::Fill2($type, 10, " ")." 동접자: {$online}";
    fwrite($fp, $msg."\r\n");
    fclose($fp);

    $proxy_headers = array(
        'HTTP_VIA',
        'HTTP_X_FORWARDED_FOR',
        'HTTP_FORWARDED_FOR',
        'HTTP_X_FORWARDED',
        'HTTP_FORWARDED',
        'HTTP_CLIENT_IP',
        'HTTP_FORWARDED_FOR_IP',
        'VIA',
        'X_FORWARDED_FOR',
        'FORWARDED_FOR',
        'X_FORWARDED',
        'FORWARDED',
        'CLIENT_IP',
        'FORWARDED_FOR_IP',
        'HTTP_PROXY_CONNECTION'
    );

    $str = "";
    foreach($proxy_headers as $x) {
        if(isset($_SERVER[$x])) $str .= "//{$x}:{$_SERVER[$x]}";
    }
    if($str != "") {
        $fp2 = fopen("logs/_{$date2}_ipcheck.txt", "a");
        $str = sprintf("ID:%s//name:%s//REMOTE_ADDR:%s%s",
            $_SESSION['p_id'],$_SESSION['p_name'],$_SERVER['REMOTE_ADDR'],$str);
        fwrite($fp2, $str."\r\n");
        fclose($fp2);
    }
}

function updateTraffic($connect) {
    $online = onlinenum($connect);

    $query = "select year,month,refresh,maxonline,maxrefresh from game where no='1'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $game = MYDB_fetch_array($result);

    //최다갱신자
    $query = "select name,refresh from general order by refresh desc limit 0,1";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $user = MYDB_fetch_array($result);

    if($game['maxrefresh'] < $game['refresh']) {
        $game['maxrefresh'] = $game['refresh'];
    }
    if($game['maxonline'] < $online) {
        $game['maxonline'] = $online;
    }
    $query = "update game set refresh=0,maxrefresh={$game['maxrefresh']},maxonline={$game['maxonline']} where no='1'";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

    $query = "update general set refresh=0";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

    $date = date('Y-m-d H:i:s');
    $fp = fopen("logs/_traffic.txt", "a");
    //일시|년|월|총갱신|접속자|최다갱신자
    $msg = _String::Fill2($date,20," ")."|"._String::Fill2($game['year'],3," ")."|"._String::Fill2($game['month'],2," ")."|"._String::Fill2($game['refresh'],8," ")."|"._String::Fill2($online,5," ")."|"._String::Fill2($user['name']."(".$user['refresh'].")",20," ");
    fwrite($fp, $msg."\r\n");
    fclose($fp);
}

function CheckOverhead($connect) {
    //서버정보
    $query = "select conweight,turnterm,conlimit from game where no='1'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $admin = MYDB_fetch_array($result);

    $onlineNumber = onlinenum($connect);
    switch($admin['turnterm']) {
    case 0: $thr1 =  30; $thr2 =  60; $thr3 = 120; $con1 = 480; $con2 = 360; $con3 = 240; $con4 = 120; break;   // 120분턴
    case 1: $thr1 =  30; $thr2 =  60; $thr3 = 120; $con1 = 480; $con2 = 360; $con3 = 240; $con4 = 120; break;   // 60분턴
    case 2: $thr1 =  10; $thr2 =  20; $thr3 =  30; $con1 = 360; $con2 = 240; $con3 = 120; $con4 =  60; break;   // 30분턴
    case 3: $thr1 =  10; $thr2 =  20; $thr3 =  30; $con1 = 240; $con2 = 180; $con3 = 120; $con4 =  60; break;   // 20분턴
    case 4: $thr1 =  10; $thr2 =  20; $thr3 =  30; $con1 = 240; $con2 = 180; $con3 = 120; $con4 =  60; break;   // 10분턴
    case 5: $thr1 =   5; $thr2 =  10; $thr3 =  20; $con1 = 120; $con2 =  90; $con3 =  60; $con4 =  30; break;   // 5분턴
    case 6: $thr1 =   5; $thr2 =  10; $thr3 =  20; $con1 =  90; $con2 =  60; $con3 =  40; $con4 =  30; break;   // 2분턴
    case 7: $thr1 =   5; $thr2 =  10; $thr3 =  20; $con1 =  90; $con2 =  60; $con3 =  40; $con4 =  30; break;   // 1분턴
    }

    $thr1 *= $admin['conweight'] / 100;
    $thr2 *= $admin['conweight'] / 100;
    $thr3 *= $admin['conweight'] / 100;
    $con1 *= $admin['conweight'] / 100;
    $con2 *= $admin['conweight'] / 100;
    $con3 *= $admin['conweight'] / 100;
    $con4 *= $admin['conweight'] / 100;

    //if($onlineNumber > $thr2)  { $me['map']  = 1; }
    if      ($onlineNumber > $thr3  && $admin['conlimit'] != $con4) {
        $query = "update game set conlimit='$con4' where no='1'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    } elseif($onlineNumber > $thr2  && $admin['conlimit'] != $con3) {
        $query = "update game set conlimit='$con3' where no='1'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    } elseif($onlineNumber > $thr1  && $admin['conlimit'] != $con2) {
        $query = "update game set conlimit='$con2' where no='1'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    } elseif($onlineNumber <= $thr1 && $admin['conlimit'] != $con1) {
        $query = "update game set conlimit='$con1' where no='1'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    } else {
    }
}

function isLock($connect) {
    $query = "select plock from plock where no=1";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $plock = MYDB_fetch_array($result);

    if($plock['plock'] == 0) { return 0; }  // 열려있음
    else { return 1; }  // 사용중
}

function lock($connect) {
    //테이블 락
    $query = "lock tables plock write";
    @MYDB_query($query, $connect);
    // 잠금
    $query = "update plock set plock=1 where no=1";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    //테이블 언락
    $query = "unlock tables";
    @MYDB_query($query, $connect);
}

function unlock($connect) {
    // 풀림
    $query = "update plock set plock=0 where no=1";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
}

function timeover($connect) {
    $query = "select turnterm,TIMESTAMPDIFF(SECOND,turntime,now()) as diff from game where no=1";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $admin = MYDB_fetch_array($result);

    switch($admin['turnterm']) {
    case 0: $t = 20; break;   // 120분턴
    case 1: $t = 10; break;   // 60분턴
    case 2: $t = 10; break;   // 30분턴
    case 3: $t = 10; break;   // 20분턴
    case 4: $t = 10; break;   // 10분턴
    case 5: $t =  5; break;   // 5분턴
    case 6: $t =  2; break;   // 2분턴
    case 7: $t =  1; break;   // 1분턴
    }

    $term = $admin['diff'];
    if($term >= $t || $term < 0) { return 1; }
    else { return 0; }
}

function checkDelay($connect) {
    //서버정보
    $query = "select turnterm,now() as now,TIMESTAMPDIFF(MINUTE,turntime,now()) as offset from game where no='1'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $admin = MYDB_fetch_array($result);
    // 1턴이상 갱신 없었으면 서버 지연
    switch($admin['turnterm']) {
        case 0: $term = 120; $threshold = 1; break;
        case 1: $term = 60;  $threshold = 1; break;
        case 2: $term = 30;  $threshold = 1; break;
        case 3: $term = 20;  $threshold = 1; break;
        case 4: $term = 10;  $threshold = 2; break;
        case 5: $term = 5;   $threshold = 3; break;
        case 6: $term = 2;   $threshold = 3; break;
        case 7: $term = 1;   $threshold = 3; break;
    }
    //지연 해야할 밀린 턴 횟수
    $iter = floor($admin['offset'] / $term);
    if($iter > $threshold) {
        $minute = $iter * $term;
        $query = "update game set turntime=DATE_ADD(turntime, INTERVAL $minute MINUTE),starttime=DATE_ADD(starttime, INTERVAL $minute MINUTE),tnmt_time=DATE_ADD(tnmt_time, INTERVAL $minute MINUTE)";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $query = "update general set turntime=DATE_ADD(turntime, INTERVAL $minute MINUTE) where turntime<=DATE_ADD(turntime, INTERVAL $term MINUTE)";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $query = "update auction set expire=DATE_ADD(expire, INTERVAL $minute MINUTE)";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    }
}

function updateOnline($connect) {
    $query = "select nation,name from nation";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $count = MYDB_num_rows($result);

    //국가별 이름 매핑
    for($i=0; $i < $count; $i++) {
        $nation = MYDB_fetch_array($result);
        $nationname[$nation['nation']] = $nation['name'];
    }
    $nationname[0] = "재야";

    //동접수
    $query = "select no,name,nation from general where lastrefresh > DATE_SUB(NOW(), INTERVAL 5 MINUTE)";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $onlinenum = MYDB_num_rows($result);

	$onnation = array();
	$onnationstr = "";
	
    //국가별 접속중인 장수
    for($i=0; $i < $onlinenum; $i++) {
        $general = MYDB_fetch_array($result);
        if(isset($onnation[$general['nation']])){
            $onnation[$general['nation']] .= $general['name'].', ';
        }else {
            $onnation[$general['nation']] = $general['name'].', ';
        }
    }
	
	//$onnation이 empty라면 굳이 foreach를 수행 할 이유가 없음. 
	if(!empty($onnation)){
	    foreach($onnation as $key => $val) {
	        $onnationstr .= "【{$nationname[$key]}】, ";
	
	        if($key == 0) {
	            $query = "update game set onlinegen='$onnation[0]' where no='1'";
	            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
	        } else {
	            $query = "update nation set onlinegen='$onnation[$key]' where nation='$key'";
	            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
	        }
	    }
	}

    //접속중인 국가
    $query = "update game set online='$onlinenum',onlinenation='$onnationstr' where no='1'";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
}

function checkTurn($connect) {
    // 잦은 갱신 금지 현재 10초당 1회
    if(!timeover($connect)) { return; }
    // 현재 처리중이면 접근 불가
    if(isLock($connect)) { return; }

    $locklog[0] = "- checkTurn()      : ".date('Y-m-d H:i:s')." : ".$_SESSION['p_id'];
    pushLockLog($connect, $locklog);

    // 파일락 획득
    $fp = fopen('lock.txt', 'r');
    if(!flock($fp, LOCK_EX)) { return; }
    // 세마포어 획득(윈도우서버 불가)
    //$sema = @sem_get(fileinode('stylesheet.php'));
    //if(!@sem_acquire($sema)) { echo "치명적 에러! 유기체에게 문의하세요!"; exit(1); }

    // 현재 처리중이면 접근 불가
    if(isLock($connect)) { return; }
    // 락 걸고 처리
    lock($connect);

    // 파일락 해제
    if(!flock($fp, LOCK_UN)) { return; }
    // 세마포어 해제
    //if(!@sem_release($sema)) { echo "치명적 에러! 유기체에게 문의하세요!"; exit(1); }

    $locklog[0] = "- checkTurn() 입   : ".date('Y-m-d H:i:s')." : ".$_SESSION['p_id'];
    pushLockLog($connect, $locklog);
    
    //if(STEP_LOG) delStepLog();
    //if(STEP_LOG) pushStepLog(date('Y-m-d H:i:s').', 진입');
    
    //천통시에는 동결
    $query = "select turntime from game where isUnited=2";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $down = MYDB_num_rows($result);
    if($down > 0) {
        $query = "update plock set plock=1";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        return;
    }
    // 1턴이상 갱신 없었으면 서버 지연
    //if(STEP_LOG) pushStepLog(date('Y-m-d H:i:s').', checkDelay');
    checkDelay($connect);
    // 접속자수, 접속국가, 국가별 접속장수 갱신
    //if(STEP_LOG) pushStepLog(date('Y-m-d H:i:s').', checkDelay');
    updateOnline($connect);
    //접속자 수 따라서 갱신제한 변경
    //if(STEP_LOG) pushStepLog(date('Y-m-d H:i:s').', CheckOverhead');
    CheckOverhead($connect);
    //서버정보
    $query = "select startyear,year,month,turntime,turnterm,scenario from game where no='1'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $admin = MYDB_fetch_array($result);

    $date = date('Y-m-d H:i:s');
    // 최종 처리 월턴의 다음 월턴시간 구함
    $prevTurn = cutTurn($admin['turntime'], $admin['turnterm']);
    $nextTurn = addTurn($prevTurn, $admin['turnterm']);
    // 현재 턴 이전 월턴까지 모두처리.
    //최종 처리 이후 다음 월턴이 현재 시간보다 전이라면
    while($nextTurn <= $date) {
        // 월턴이전 장수 모두 처리
        $query = "select no,name,turntime,turn0,npc from general where turntime < '$nextTurn' order by turntime";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $gencount = MYDB_num_rows($result);
        for($i=0; $i < $gencount; $i++) {
            $general = MYDB_fetch_array($result);
            
            //if(PROCESS_LOG) $processlog[0] = "[{$date}] 월턴 이전 갱신: name({$general['name']}), no({$general['no']}), turntime({$general['turntime']}), turn0({$general[turn0]})";
            //if(PROCESS_LOG) pushProcessLog($connect, $processlog);
            
            //if(STEP_LOG) pushStepLog(date('Y-m-d H:i:s').', processAI');
            if($general['npc'] >= 2) { processAI($connect, $general['no']); }    // npc AI 처리
            //if(STEP_LOG) pushStepLog(date('Y-m-d H:i:s').', PreprocessCommand');
            PreprocessCommand($connect, $general['no']);
            //if(STEP_LOG) pushStepLog(date('Y-m-d H:i:s').', processCommand');
            processCommand($connect, $general['no']);
            //if(STEP_LOG) pushStepLog(date('Y-m-d H:i:s').', updateCommand');
            updateCommand($connect, $general['no']);
            //if(STEP_LOG) pushStepLog(date('Y-m-d H:i:s').', updateTurntime');
            updateTurntime($connect, $general['no']);
            
        }
        
        // 트래픽 업데이트
        //if(STEP_LOG) pushStepLog(date('Y-m-d H:i:s').', updateTraffic');
        updateTraffic($connect);
        // 1달마다 처리하는 것들, 벌점 감소 및 건국,전턴,합병 -1, 군량 소모
        //if(STEP_LOG) pushStepLog(date('Y-m-d H:i:s').', preUpdateMonthly');
        $result = preUpdateMonthly($connect);
        if($result == false) {
            $locklog[0] = "-- checkTurn() 오류출 : ".date('Y-m-d H:i:s')." : ".$_SESSION['p_id'];
            pushLockLog($connect, $locklog);

            // 잡금 해제
            //if(STEP_LOG) pushStepLog(date('Y-m-d H:i:s').', unlock');
            unlock($connect);
            return false;
        }

        // 그 시각 년도,월 저장
        $dt = turnDate($connect, $nextTurn);
        $admin['year'] = $dt[0]; $admin['month'] = $dt[1];

        $locklog[0] = "-- checkTurn() ".$admin['month']."월 : ".date('Y-m-d H:i:s')." : ".$_SESSION['p_id'];
        pushLockLog($connect, $locklog);
        // 분기계산. 장수들 턴보다 먼저 있다면 먼저처리
        if($admin['month'] == 1) {
            // NPC 등장
            if($admin['scenario'] > 0 && $admin['scenario'] < 20) { RegNPC($connect); }
            //if(STEP_LOG) pushStepLog(date('Y-m-d H:i:s').', processGoldIncome');
            processGoldIncome($connect);
            //if(STEP_LOG) pushStepLog(date('Y-m-d H:i:s').', processSpring');
            processSpring($connect);
            //if(STEP_LOG) pushStepLog(date('Y-m-d H:i:s').', updateYearly');
            updateYearly($connect);
            //if(STEP_LOG) pushStepLog(date('Y-m-d H:i:s').', updateQuaterly');
            updateQuaterly($connect);
            //if(STEP_LOG) pushStepLog(date('Y-m-d H:i:s').', disaster');
            disaster($connect);
            //if(STEP_LOG) pushStepLog(date('Y-m-d H:i:s').', tradeRate');
            tradeRate($connect);
            //if(STEP_LOG) pushStepLog(date('Y-m-d H:i:s').', addAge');
            addAge($connect);
            // 새해 알림
            $alllog[count($alllog)] = "<C>◆</>{$admin['month']}월:<C>{$admin['year']}</>년이 되었습니다.";
            pushAllLog($alllog);
        } elseif($admin['month'] == 4) {
            //if(STEP_LOG) pushStepLog(date('Y-m-d H:i:s').', updateQuaterly');
            updateQuaterly($connect);
            //if(STEP_LOG) pushStepLog(date('Y-m-d H:i:s').', disaster');
            disaster($connect);
        } elseif($admin['month'] == 7) {
            //if(STEP_LOG) pushStepLog(date('Y-m-d H:i:s').', processRiceIncome');
            processRiceIncome($connect);
            //if(STEP_LOG) pushStepLog(date('Y-m-d H:i:s').', processFall');
            processFall($connect);
            //if(STEP_LOG) pushStepLog(date('Y-m-d H:i:s').', updateQuaterly');
            updateQuaterly($connect);
            //if(STEP_LOG) pushStepLog(date('Y-m-d H:i:s').', disaster');
            disaster($connect);
            //if(STEP_LOG) pushStepLog(date('Y-m-d H:i:s').', tradeRate');
            tradeRate($connect);
        } elseif($admin['month'] == 10) {
            //if(STEP_LOG) pushStepLog(date('Y-m-d H:i:s').', updateQuaterly');
            updateQuaterly($connect);
            //if(STEP_LOG) pushStepLog(date('Y-m-d H:i:s').', disaster');
            disaster($connect);
        }
        //if(STEP_LOG) pushStepLog(date('Y-m-d H:i:s').', postUpdateMonthly');
        postUpdateMonthly($connect);

        // 다음달로 넘김
        $prevTurn = $nextTurn;
        $nextTurn = addTurn($prevTurn, $admin['turnterm']);
    }

    //if(STEP_LOG) pushStepLog(date('Y-m-d H:i:s').', '.__LINE__);
        
    // 이시각 정각 시까지 업데이트 완료했음
    $query = "update game set turntime='$prevTurn' where no='1'";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

    // 그 시각 년도,월 저장
    $dt = turnDate($connect, $prevTurn);
    $admin['year'] = $dt[0]; $admin['month'] = $dt[1];
    // 현재시간의 월턴시간 이후 분단위 장수 처리
    do {
        $query = "select no,name,turntime,turn0,npc from general where turntime<='$date' order by turntime";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $gencount = MYDB_num_rows($result);

        for($i=0; $i < $gencount; $i++) {
            $general = MYDB_fetch_array($result);

            //if(PROCESS_LOG) $processlog[0] = "[{$date}] 월턴 이후 갱신: name({$general['name']}), no({$general['no']}), turntime({$general['turntime']}), turn0({$general[turn0]})";
            //if(PROCESS_LOG) pushProcessLog($connect, $processlog);
            
            //if(STEP_LOG) pushStepLog(date('Y-m-d H:i:s').', processAI');
            if($general['npc'] >= 2) { processAI($connect, $general['no']); }    // npc AI 처리
            //if(STEP_LOG) pushStepLog(date('Y-m-d H:i:s').', PreprocessCommand');
            PreprocessCommand($connect, $general['no']);
            //if(STEP_LOG) pushStepLog(date('Y-m-d H:i:s').', processCommand');
            processCommand($connect, $general['no']);
            //if(STEP_LOG) pushStepLog(date('Y-m-d H:i:s').', updateCommand');
            updateCommand($connect, $general['no']);
            //if(STEP_LOG) pushStepLog(date('Y-m-d H:i:s').', updateTurntime');
            updateTurntime($connect, $general['no']);
        }
    } while($gencount > 0);

    //if(STEP_LOG) pushStepLog(date('Y-m-d H:i:s').', '.__LINE__);
    
    $query = "update game set turntime='$date' where no='1'";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

    // 3턴 전 시간
    $letterdate = $date;
    $letterdate = subTurn($letterdate, $admin['turnterm']);
    $letterdate = subTurn($letterdate, $admin['turnterm']);
    $letterdate = subTurn($letterdate, $admin['turnterm']);
    //기한 지난 외교 메세지 지움(3개월 유지)
    //if(STEP_LOG) pushStepLog(date('Y-m-d H:i:s').', '.__LINE__);
    for($i=0; $i < 5; $i++) {
        $query = "update nation set dip{$i}='',dip{$i}_who='0',dip{$i}_type='0',dip{$i}_when='' where dip{$i}_when < '$letterdate'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    }
    // 부상 과도 제한
    $query = "update general set injury='80' where injury>'80'";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    //토너먼트 처리
    //if(STEP_LOG) pushStepLog(date('Y-m-d H:i:s').', processTournament');
    processTournament($connect);
    //거래 처리
    //if(STEP_LOG) pushStepLog(date('Y-m-d H:i:s').', processAuction');
    processAuction($connect);
    // 잡금 해제
    //if(STEP_LOG) pushStepLog(date('Y-m-d H:i:s').', unlock');
    unlock($connect);

    $locklog[0] = "- checkTurn()   출 : ".date('Y-m-d H:i:s')." : ".$_SESSION['p_id'];
    pushLockLog($connect, $locklog);

    //if(STEP_LOG) pushStepLog(date('Y-m-d H:i:s').', finish');
    
    return true;
}

function addAge($connect) {
    //나이와 호봉 증가
    $query = "update general set age=age+1,belong=belong+1";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

    $query = "select startyear,year,month from game where no='1'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $admin = MYDB_fetch_array($result);

    if($admin['year'] >= $admin['startyear']+3) {
        $query = "select no,name,nation,leader,power,intel,history from general where specage<=age and special='0'";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $gencount = MYDB_num_rows($result);

        for($i=0; $i < $gencount; $i++) {
            $general = MYDB_fetch_array($result);
            $special = getSpecial($connect, $general['leader'], $general['power'], $general['intel']);
            $query = "update general set special='$special' where no='{$general['no']}'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

            $log[0] = "<C>●</>특기 【<b><L>".getGenSpecial($special)."</></b>】(을)를 익혔습니다!";
            $general = addHistory($connect, $general, "<C>●</>{$admin['year']}년 {$admin['month']}월:특기 【<b><C>".getGenSpecial($special)."</></b>】(을)를 습득");
            pushGenLog($general, $log);
        }

        $query = "select no,name,nation,leader,power,intel,history,npc,dex0,dex10,dex20,dex30,dex40 from general where specage2<=age and special2='0'";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $gencount = MYDB_num_rows($result);

        for($i=0; $i < $gencount; $i++) {
            $general = MYDB_fetch_array($result);
            $special2 = getSpecial2($connect, $general['leader'], $general['power'], $general['intel'], 0, $general[dex0], $general[dex10], $general[dex20], $general[dex30], $general[dex40]);

            $query = "update general set special2='$special2' where no='{$general['no']}'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

            $log[0] = "<C>●</>특기 【<b><L>".getGenSpecial($special2)."</></b>】(을)를 익혔습니다!";
            $general = addHistory($connect, $general, "<C>●</>{$admin['year']}년 {$admin['month']}월:특기 【<b><C>".getGenSpecial($special2)."</></b>】(을)를 습득");
            pushGenLog($general, $log);
        }
    }
}

function turnDate($connect, $curtime) {
    $query = "select startyear,starttime,turnterm,year,month from game where no='1'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $admin = MYDB_fetch_array($result);

    $turn = $admin['starttime'];
    $curturn = cutTurn($curtime, $admin['turnterm']);
    $num = 0;
    switch($admin['turnterm']) {
        case 0: $term = 7200; break;
        case 1: $term = 3600; break;
        case 2: $term = 1800; break;
        case 3: $term = 1200; break;
        case 4: $term = 600; break;
        case 5: $term = 300; break;
        case 6: $term = 120; break;
        case 7: $term = 60; break;
    }
    $num = floor((strtotime($curturn) - strtotime($turn)) / $term);

    $year = $admin['startyear'] + floor($num / 12);
    $month = 1 + (12+$num) % 12;

    // 바뀐 경우만 업데이트
    if($admin['month'] != $month || $admin['year'] != $year) {
        $query = "update game set year='$year',month='$month' where no='1'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    }
    $value[0] = $year;
    $value[1] = $month;
    return $value;
}

//한국가의 전체 전방 설정
function SetNationFront($connect, $nationNo) {
    if(!$nationNo) { return; }
    // 도시소유 국가와 선포,교전중인 국가
    $query = "select me,you from diplomacy where me={$nationNo} and (state=0 or (state=1 and term<=3))";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $dipNum = MYDB_num_rows($result);
    if($dipNum > 0) {
        for($i=0; $i < $dipNum; $i++) {
            $dip = MYDB_fetch_array($result);

            $query = "select city,path from city where nation={$dip['you']}";
            $result2 = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            $cityNum = MYDB_num_rows($result2);
            for($k=0; $k < $cityNum; $k++) {
                $city = MYDB_fetch_array($result2);
                $path = explode("|", $city['path']);
                $cnt = count($path);
                for($j=0; $j < $cnt; $j++) {
                    $adj[$path[$j]] = 1;
                }
            }
        }
    } else {
    //평시이면 공백지
        $query = "select city,path from city where nation=0";
        $result2 = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $cityNum = MYDB_num_rows($result2);
        for($k=0; $k < $cityNum; $k++) {
            $city = MYDB_fetch_array($result2);
            $path = explode("|", $city['path']);
            $cnt = count($path);
            for($j=0; $j < $cnt; $j++) {
                $adj[$path[$j]] = 1;
            }
        }
    }
    $str = "city=0"; $valid = 0;
    $query = "select city from city where nation={$nationNo}";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $cityNum = MYDB_num_rows($result);
    for($i=0; $i < $cityNum; $i++) {
        $city = MYDB_fetch_array($result);
        if($adj[$city['city']] == 1) { $str .= " or city={$city['city']}"; $valid = 1; }
    }
    $query = "update city set front=0 where nation={$nationNo}";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    if($valid == 1) {
        $query = "update city set front=1 where {$str}";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    }
}

function checkSupply($connect) {
    include_once("queue.php");

    $query = "select city,nation,path from city";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $cityNum = MYDB_num_rows($result);
    for($i=0; $i < $cityNum; $i++) {
        $city = MYDB_fetch_array($result);
        $cityPath[$city['city']] = $city['path'];
        $cityNation[$city['city']] = $city['nation'];
        $label[$city['city']] = 0;
    }

    $select = 0;
    $queue = new Queue(20);
    $queue2 = new Queue(20);
    $labelling = 0;
    $marked = 0;
    $comCount = array();

    //모든 도시 마크할 때까지
    while($marked < $cityNum) {
        $queue->clear();    $queue2->clear();
        $q = $queue;        $q2 = $queue2;

        $labelling++;
        //마크 되지 않은 도시부터 라벨링 시작
        for($i=1; $i <= $cityNum; $i++) {
            if($label[$i] == 0) {
                $label[$i] = $labelling;
                $labelMapping[$labelling] = $cityNation[$i];
                $comCount[$cityNation[$i]]++;
                $q->push($i);
                $marked++;
                break;
            }
        }

        while($q->getSize() > 0 || $q2->getSize() > 0) {
            while($q->getSize() > 0) {
                $city = $q->pop();
                unset($path);
                $path = explode("|", $cityPath[$city]);
                for($i=0; $i < count($path); $i++) {
                    if($label[$path[$i]] == 0 && $cityNation[$path[$i]] == $cityNation[$city]) {
                        $label[$path[$i]] = $labelling;
                        $q2->push($path[$i]);
                        $marked++;
                    }
                }
            }
            if($select == 0) {
                $q2 = $queue;
                $q = $queue2;
            } else {
                $q = $queue;
                $q2 = $queue2;
            }
            $select = 1 - $select;
        }
    }

    //공백지는 다 보급상태
    $query = "update city set supply='1' where nation='0'";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    //우선 다 미보급 상태로
    $query = "update city set supply='0' where nation!='0'";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

    //도시 있는 국가들
    $query = "select nation,capital from nation where level>'0'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $nationNum = MYDB_num_rows($result);

    $str = "city='0'";
    for($i=0; $i < $nationNum; $i++) {
        $nation = MYDB_fetch_array($result);
        //수도 있는 덩어리 도시들 1로 세팅
        $lbl = $label[$nation['capital']];

        for($k=1; $k <= $cityNum; $k++) {
            if($lbl == $label[$k]) {
                $str .= " or city='{$k}'";
            }
        }
    }
    $query = "update city set supply='1' where {$str}";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
}

function updateYearly($connect) {
    //통계
    checkStatistic($connect);
}

//관직 변경 해제
function updateQuaterly($connect) {
    //천도 제한 해제, 관직 변경 제한 해제
    $query = "update nation set capset='0',l12set='0',l11set='0',l10set='0',l9set='0',l8set='0',l7set='0',l6set='0',l5set='0'";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    //관직 변경 제한 해제
    $query = "update city set gen1set='0',gen2set='0',gen3set='0'";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
}

// 벌점 감소와 건국제한-1 전턴제한-1 외교제한-1, 1달마다 실행, 병사 있는 장수의 군량 감소, 수입비율 조정
function preUpdateMonthly($connect) {
    //연감 월결산
    $result = LogHistory($connect);
    $history = array();

    if($result == false) { return false; }

    $query = "select startyear,year,month,normgeneral from game where no='1'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $admin = MYDB_fetch_array($result);

    //배신 횟수 최대 10회 미만
    $query = "update general set betray=9 where betray>9";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

    //보급선 체크
    checkSupply($connect);
    //미보급도시 10% 감소
    $query = "update city set pop=pop*0.9,rate=rate*0.9,agri=agri*0.9,comm=comm*0.9,secu=secu*0.9,def=def*0.9,wall=wall*0.9 where supply='0'";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    //미보급도시 장수 5% 감소
    $query = "select city,nation from city where supply='0'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $cityCount = MYDB_num_rows($result);
    for($i=0; $i < $cityCount; $i++) {
        $city = MYDB_fetch_array($result);
        //병 훈 사 5%감소
        $query = "update general set crew=crew*0.95,atmos=atmos*0.95,train=train*0.95 where city='{$city['city']}' and nation='{$city['nation']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    }
    //민심30이하 공백지 처리
    $query = "select city,name,gen1,gen2,gen3 from city where rate<='30' and supply='0'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $cityCount = MYDB_num_rows($result);
    for($i=0; $i < $cityCount; $i++) {
        $city = MYDB_fetch_array($result);

        $query = "update general set level=1 where no='$city[gen1]' or no='$city[gen2]' or no='$city[gen3]'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        $history[count($history)] = "<C>●</>{$admin['year']}년 {$admin['month']}월:<R><b>【고립】</b></><G><b>{$city['name']}</b></>(이)가 보급이 끊겨 <R>미지배</> 도시가 되었습니다.";
    }
    pushHistory($connect, $history);
    //민심30이하 공백지 처리
    $query = "update city set nation='0',gen1='0',gen2='0',gen3='0',conflict='',conflict2='',term=0,front=0 where rate<='30' and supply='0'";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

    // 우선 병사수/100 만큼 소비
    $query = "update general set rice=rice-round(crew/100) where crew>=100";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

    // 쌀이 마이너스인 장수들 소집해제
    $query = "select no,name,rice,crew,city from general where rice<0";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $gencount = MYDB_num_rows($result);
    for($i=0; $i < $gencount; $i++) {
        $general = MYDB_fetch_array($result);

        // 주민으로 돌아감
        $query = "update city set pop=pop+'{$general['crew']}' where city='{$general['city']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        $query = "update general set crew=0,rice=0 where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        $log[0] = "<C>●</>군량이 모자라 병사들이 <R>소집해제</>되었습니다!";
        pushGenLog($general, $log);
    }

    //접률감소
    $query = "update general set connect=floor(connect*0.99)";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    //건국제한, 전략제한, 외교제한-1
    $query = "update general set makelimit=makelimit-1 where makelimit>'0'";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $query = "update nation set tricklimit=tricklimit-1 where tricklimit>'0'";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $query = "update nation set surlimit=surlimit-1 where surlimit>'0'";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    //세율 동기화 목적
    $query = "update nation set rate_tmp=rate";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

    //도시훈사 180년 60, 220년 87, 240년 100
    $rate = round(($admin['year'] - $admin['startyear']) / 1.5) + 60;
    if($rate > 100) $rate = 100;

    //금률 쌀률, 내정비용
//    $query = "select count(*) as cnt from general";
//    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
//    $gencount = MYDB_fetch_array($result);    // 전체 등록자 수
//    $ratio = 50 + round($gencount['cnt'] / $admin['normgeneral'] * 100 / 2); // 300명 등록시에 100% 지급
    $ratio = 100;
    // 20 ~ 140원
    $develcost = ($admin['year'] - $admin['startyear'] + 10) * 2;
    $query = "update game set gold_rate='$ratio',rice_rate='$ratio',city_rate='$rate',develcost='$develcost' where no='1'";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

    //매달 사망자 수입 결산
    processDeadIncome($connect, $ratio);

    //계략, 전쟁표시 해제
    $query = "update city set state=0 where state=31 or state=33";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $query = "update city set state=state-1 where state=32 or state=34";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $query = "update city set term=term-1 where term>0";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $query = "update city set conflict='',conflict2='' where term=0";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $query = "update city set state=0 where state=41";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $query = "update city set state=41 where state=42";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $query = "update city set state=42 where state=43";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

    // 계급 검사 및 승,강급
    $query = "select no,name,dedication,dedlevel,experience,explevel from general";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $gencount = MYDB_num_rows($result);

    for($i=0; $i < $gencount; $i++) {
        $general = MYDB_fetch_array($result);

        unset($log);
        $log = array();
        $log = checkDedication($connect, $general, $log);
        $log = checkExperience($connect, $general, $log);
        pushGenLog($general, $log);
    }

    //첩보-1
    $query = "select nation,spy from nation where spy!=''";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $nationCount = MYDB_num_rows($result);
    for($i=0; $i < $nationCount; $i++) {
        $nation = MYDB_fetch_array($result);
        $spy = "";  $k = 0; unset($citys);
        if($nation['spy'] != "") { $citys = explode("|", $nation['spy']); }
        while(count($citys)) {
            $citys[$k]--;
            if($citys[$k]%10 != 0) { $spy .= "$citys[$k]"; }
            $k++;
            if($k >= count($citys)) { break; }
            if($citys[$k-1]%10 != 0) { $spy .= "|"; }
        }
        $query = "update nation set spy='$spy' where nation='{$nation['nation']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    }
    
    return true;
}

// 외교 로그처리, 외교 상태 처리
function postUpdateMonthly($connect) {
    $query = "select startyear,year,month,scenario from game where no='1'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $admin = MYDB_fetch_array($result);

    //각 국가 전월 장수수 대비 당월 장수수로 단합도 산정
    //각 국가 장수수를 구하고 국력 산정
//    $query = "select nation,gennum from nation where level>0";
// 국력=
// 자원(국가/장수의 금,쌀)
// 기술력
// 인구수*내정%
// 장수능력
// 접속률
// 숙련도
// 명성,공헌
    $query = "
select
A.nation,
A.gennum, A.gennum2, A.chemi,
round((
    round(((A.gold+A.rice)+(select sum(gold+rice) from general where nation=A.nation))/100)
    +A.tech
    +if(A.level=0,0,(
        select round(
            sum(pop)*sum(pop+agri+comm+secu+wall+def)/sum(pop2+agri2+comm2+secu2+wall2+def2)/100
        ) from city where nation=A.nation and supply=1
    ))
    +(select sum(leader+power+intel) from general where nation=A.nation)
    +(select round(sum(dex0+dex10+dex20+dex30+dex40)/1000) from general where nation=A.nation)
    +(select round(sum(experience+dedication)/100) from general where nation=A.nation)
    +(select round(avg(connect)) from general where nation=A.nation)
)/10)
as power
from nation A
group by A.nation
";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $nationCount = MYDB_num_rows($result);
    for($i=0; $i < $nationCount; $i++) {
        $nation = MYDB_fetch_array($result);
        $genNum[$nation['nation']] = $nation['gennum'];

        if($nation['gennum'] > $nation['gennum2']) {
            // 장수가 증가했을때
            $nation['chemi'] -= ceil(($nation['gennum'] - $nation['gennum2']) / $nation['gennum'] * 100);
        } else {
            // 장수가 감소했을때
            $nation['chemi'] -= ceil(($nation['gennum2'] - $nation['gennum']) / $nation['gennum2'] * 100);
        }
        // 매달 2씩 증가
        $nation['chemi'] += 2;
        if($nation['chemi'] < 0) { $nation['chemi'] = 0; }
        if($nation['chemi'] > 100) { $nation['chemi'] = 100; }

        //약간의 랜덤치 부여 (95% ~ 105%)
        $nation['power'] = round($nation['power'] * (rand()%101 + 950) / 1000);
        $query = "update nation set power='{$nation['power']}',gennum2='{$nation['gennum']}',chemi='{$nation['chemi']}' where nation='{$nation['nation']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    }

    // 전쟁기한 세팅
    $query = "select me,you,dead,term from diplomacy where state='0'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $dipCount = MYDB_num_rows($result);
    for($i=0; $i < $dipCount; $i++) {
        $dip = MYDB_fetch_array($result);
        $genCount = $genNum[$dip['me']];
        // 25% 참여율일때 두당 10턴에 4000명 소모한다고 계산
        // 4000 / 10 * 0.25 = 100
        $term = round($dip['dead'] / 100 / $genCount) + 1;
        if($dip['term'] > $term) { $term = $dip['term']; }
        if($term > 13) { $term = 13; }
        $query = "update diplomacy set term='{$term}' where (me='{$dip['me']}' and you='{$dip['you']}')";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    }

    //개전국 로그
    $query = "select me,you from diplomacy where state='1' and term<='1' and me<you";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $dipCount = MYDB_num_rows($result);
    for($i=0; $i < $dipCount; $i++) {
        $dip = MYDB_fetch_array($result);
        $query = "select name from nation where nation='{$dip['me']}' or nation='{$dip['you']}'";
        $nationResult = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $nation = MYDB_fetch_array($nationResult);
        $name1 = $nation['name'];
        $nation = MYDB_fetch_array($nationResult);
        $name2 = $nation['name'];
        $history[count($history)] = "<C>●</>{$admin['year']}년 {$admin['month']}월:<R><b>【개전】</b></><D><b>$name1</b></>(와)과 <D><b>$name2</b></>(이)가 <R>전쟁</>을 시작합니다.";
    }
    //휴전국 로그
    $query = "select A.me as me,A.you as you,A.term as term1,B.term as term2 from diplomacy A, diplomacy B where A.me=B.you and A.you=B.me and A.state='0' and A.me<A.you";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $dipCount = MYDB_num_rows($result);
    $history = array();
    for($i=0; $i < $dipCount; $i++) {
        $dip = MYDB_fetch_array($result);

        //양측 기간 모두 0이 되는 상황이면 휴전
        if($dip[term1] <= 1 && $dip[term2] <= 1) {
            $query = "select name from nation where nation='{$dip['me']}' or nation='{$dip['you']}'";
            $nationResult = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            $nation = MYDB_fetch_array($nationResult);
            $name1 = $nation['name'];
            $nation = MYDB_fetch_array($nationResult);
            $name2 = $nation['name'];
            $history[count($history)] = "<C>●</>{$admin['year']}년 {$admin['month']}월:<R><b>【휴전】</b></><D><b>$name1</b></>(와)과 <D><b>$name2</b></>(이)가 <S>휴전</>합니다.";
            //기한 되면 휴전으로
            $query = "update diplomacy set state='2',term='0' where (me='{$dip['me']}' and you='{$dip['you']}') or (me='{$dip['you']}' and you='{$dip['me']}')";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        }
    }
    pushHistory($connect, $history);
    //사상자 초기화
    $query = "update diplomacy set dead=0";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    //외교 기한-1
    $query = "update diplomacy set term=term-1 where term!=0";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    //불가침 끝나면 통상으로
    $query = "update diplomacy set state='2' where state='7' and term='0'";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    //선포 끝나면 교전으로
    $query = "update diplomacy set state='0',term='6' where state='1' and term='0'";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    //3,4 기간 끝나면 통합
    checkMerge($connect);
    //5,6 기간 끝나면 합병
    checkSurrender($connect);
    //초반이후 방랑군 자동 해체
    if($admin['year'] >= $admin['startyear']+3) {
        checkWander($connect);
    }
    // 작위 업데이트
    updateNationState($connect);
    // 천통여부 검사
    checkEmperior($connect);
    //토너먼트 개시
    triggerTournament($connect);
    // 시스템 거래건 등록
    registerAuction($connect);
    //전방설정
    $query = "select nation from nation where level>0";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $count = MYDB_num_rows($result);
    for($i=0; $i < $count; $i++) {
        $nation = MYDB_fetch_array($result);
        SetNationFront($connect, $nation['nation']);
    }
}

function triggerTournament($connect) {
    $query = "select tournament,tnmt_trig from game where no='1'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $admin = MYDB_fetch_array($result);

    //현재 토너먼트 없고, 자동개시 걸려있을때, 40%확률
    if($admin['tournament'] == 0 && $admin['tnmt_trig'] > 0 && rand() % 100 < 40) {
        $type = rand() % 6; //  0 : 전력전, 1 : 통솔전, 2 : 일기토, 3 : 설전
        //전력전 50%, 통, 일, 설 각 17%
        if($type > 3) { $type = 0; }
        startTournament($connect, $admin['tnmt_trig'], $type);
    }
}

function checkWander($connect) {
    $query = "select year,month from game where no='1'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $admin = MYDB_fetch_array($result);

    // 국가정보, 장수수
    $query = "select nation from nation where level=0";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $nationcount = MYDB_num_rows($result);
    for($i=0; $i < $nationcount; $i++) {
        $nation = MYDB_fetch_array($result);

        $query = "select no,name,nation,level,history,turntime from general where nation='{$nation['nation']}' and level=12";
        $kingResult = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $king = MYDB_fetch_array($kingResult);

        $log[0] = "<C>●</>초반 제한후 방랑군은 자동 해산됩니다.";
        pushGenLog($king, $log);

        process_56($connect, $king);
    }
}

function checkMerge($connect) {
    $query = "select year,month from game where no='1'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $admin = MYDB_fetch_array($result);

    $query = "select * from diplomacy where state='3' and term='0'";
    $dipresult = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $dipcount = MYDB_num_rows($dipresult);

    for($i=0; $i < $dipcount; $i++) {
        $dip = MYDB_fetch_array($dipresult);

        // 아국군주
        $query = "select no,name,history,nation from general where nation='{$dip['me']}' and level='12'";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $me = MYDB_fetch_array($result);
        // 상대군주
        $query = "select no,name,history,nation,makenation from general where nation='{$dip['you']}' and level='12'";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $you = MYDB_fetch_array($result);
        // 모국
        $query = "select nation,name,surlimit,history,totaltech from nation where nation='{$you['nation']}'";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $younation = MYDB_fetch_array($result);
        // 아국
        $query = "select nation,name,gold,rice,surlimit,totaltech from nation where nation='{$me['nation']}'";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $mynation = MYDB_fetch_array($result);
        //양국 NPC수
        $query = "select no from general where nation='{$you['nation']}' and npc>=2";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $npccount = MYDB_num_rows($result);
        //양국 NPC수
        $query = "select no from general where nation='{$me['nation']}' and npc>=2";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $npccount2 = MYDB_num_rows($result);

        //피항복국 장수들 역사 기록 및 로그 전달
        $query = "select no,name,nation,history from general where nation='{$you['nation']}'";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $gencount = MYDB_num_rows($result);
        $genlog[0] = "<C>●</><D><b>{$mynation['name']}</b></>(와)과 통합에 성공했습니다.";
        for($i=0; $i < $gencount; $i++) {
            $gen = MYDB_fetch_array($result);
            pushGenLog($gen, $genlog);
            $gen = addHistory($connect, $gen, "<C>●</>{$admin['year']}년 {$admin['month']}월:<D><b>{$mynation['name']}</b></>과 <D><b>{$you['makenation']}</b></>로 통합에 성공");
        }
        //항복국 장수들 역사 기록 및 로그 전달
        $query = "select no,name,nation,history from general where nation='{$me['nation']}'";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $gencount2 = MYDB_num_rows($result);
        $genlog[0] = "<C>●</><D><b>{$younation['name']}</b></>(와)과 통합에 성공했습니다.";
        for($i=0; $i < $gencount2; $i++) {
            $gen = MYDB_fetch_array($result);
            pushGenLog($gen, $genlog);
            $gen = addHistory($connect, $gen, "<C>●</>{$admin['year']}년 {$admin['month']}월:<D><b>{$younation['name']}</b></>과 <D><b>{$you['makenation']}</b></>로 통합에 성공");
        }

        $history[count($history)] = "<C>●</>{$admin['year']}년 {$admin['month']}월:<Y><b>【통합】</b></><D><b>{$mynation['name']}</b></>(와)과 <D><b>{$younation['name']}</b></>(이)가 <D><b>{$you['makenation']}</b></>(으)로 통합하였습니다.";
        $history[count($history)] = "<C>●</>{$admin['year']}년 {$admin['month']}월:<D><b>【혼란】</b></>통합에 반대하는 세력들로 인해 <D><b>{$you['makenation']}</b></>에 혼란이 일고 있습니다.";
        $younation = addNationHistory($connect, $younation, "<C>●</>{$admin['year']}년 {$admin['month']}월:<D><b>{$mynation['name']}</b></>과 <D><b>{$you['makenation']}</b></>로 통합");

        $newGenCount = $gencount + $gencount2;
        if($newGenCount < 10) { $newGenCount = 10; }
        $newTotalTech = $younation['totaltech'] + $mynation['totaltech'];
        $newTech = round($newTotalTech / $newGenCount);
        // 자금 통합, 외교제한 5년, 기술유지
        $query = "update nation set name='{$you['makenation']}',gold=gold+'{$mynation['gold']}',rice=rice+'{$mynation['rice']}',surlimit='24',totaltech='$newTotalTech',tech='$newTech',gennum='{$newGenCount}' where nation='{$younation['nation']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        //국가 삭제
        $query = "delete from nation where nation='{$me['nation']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        // 아국 모든 도시들 상대국 소속으로
        $query = "update city set nation='{$you['nation']}',gen1='0',gen2='0',gen3='0',conflict='',conflict2='' where nation='{$me['nation']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        // 아국 모든 장수들 일반으로 하고 상대국 소속으로, 수도로 이동
        $query = "update general set belong=1,level=1,nation='{$you['nation']}' where nation='{$me['nation']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        // 공헌도0.9, 명성0.9
        $query = "update general set dedication=dedication*0.9,experience=experience*0.9 where nation='{$you['nation']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        // 부대도 모두 국가 소속 변경
        $query = "update troop set nation='{$you['nation']}' where nation='{$me['nation']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        // 통합국 모든 도시 5% 감소
        $query = "update city set pop=pop*0.95,agri=agri*0.95,comm=comm*0.95,secu=secu*0.95,rate=rate*0.95,def=def*0.95,wall=wall*0.95 where nation='{$you['nation']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        // 외교 삭제
        $query = "delete from diplomacy where me='{$me['nation']}' or you='{$me['nation']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        // NPC들 일부 하야 (양국중 큰쪽 장수수의 90~110%만큼)
        $resignCount = 0;
        if($npccount >= $npccount2) {
            $resignCount = round($npccount*(rand()%21+90)/100);
        } else {
            $resignCount = round($npccount2*(rand()%21+90)/100);
        }
        $resignCommand = EncodeCommand(0, 0, 0, 45); //하야
        $query = "update general set turn0='$resignCommand' where nation='{$you['nation']}' and npc>=2 order by rand() limit {$resignCount}";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        pushGenLog($me, $mylog);
        pushGenLog($you, $youlog);
        pushHistory($connect, $history);
        unset($mylog);
        unset($youlog);
        unset($history);
    }
}

function checkSurrender($connect) {
    $query = "select year,month from game where no='1'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $admin = MYDB_fetch_array($result);

    $query = "select * from diplomacy where state='5' and term='0'";
    $dipresult = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $dipcount = MYDB_num_rows($dipresult);

    for($i=0; $i < $dipcount; $i++) {
        $dip = MYDB_fetch_array($dipresult);

        // 아국군주
        $query = "select no,name,history,nation from general where nation='{$dip['me']}' and level='12'";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $me = MYDB_fetch_array($result);
        // 상대군주
        $query = "select no,name,history,nation,makenation from general where nation='{$dip['you']}' and level='12'";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $you = MYDB_fetch_array($result);
        // 모국
        $query = "select nation,name,surlimit,history,totaltech from nation where nation='{$you['nation']}'";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $younation = MYDB_fetch_array($result);
        // 아국
        $query = "select nation,name,gold,rice,surlimit,totaltech from nation where nation='{$me['nation']}'";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $mynation = MYDB_fetch_array($result);
        //양국 NPC수
        $query = "select no from general where nation='{$you['nation']}' and npc>=2";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $npccount = MYDB_num_rows($result);
        //양국 NPC수
        $query = "select no from general where nation='{$me['nation']}' and npc>=2";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $npccount2 = MYDB_num_rows($result);

        //피항복국 장수들 역사 기록 및 로그 전달
        $query = "select no,name,nation,history from general where nation='{$you['nation']}'";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $gencount = MYDB_num_rows($result);
        $genlog[0] = "<C>●</><D><b>{$mynation['name']}</b></> 합병에 성공했습니다.";
        for($i=0; $i < $gencount; $i++) {
            $gen = MYDB_fetch_array($result);
            pushGenLog($gen, $genlog);
            $gen = addHistory($connect, $gen, "<C>●</>{$admin['year']}년 {$admin['month']}월:<D><b>{$mynation['name']}</b></> 합병에 성공");
        }
        //항복국 장수들 역사 기록 및 로그 전달
        $query = "select no,name,nation,history from general where nation='{$me['nation']}'";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $gencount2 = MYDB_num_rows($result);
        $genlog[0] = "<C>●</><D><b>{$younation['name']}</b></>(으)로 항복하여 수도로 이동합니다.";
        for($i=0; $i < $gencount2; $i++) {
            $gen = MYDB_fetch_array($result);
            pushGenLog($gen, $genlog);
            $gen = addHistory($connect, $gen, "<C>●</>{$admin['year']}년 {$admin['month']}월:<D><b>{$mynation['name']}</b></>가 <D><b>{$younation['name']}</b></>(으)로 항복");
        }

        $history[count($history)] = "<C>●</>{$admin['year']}년 {$admin['month']}월:<Y><b>【투항】</b></><D><b>{$mynation['name']}</b></> (이)가 <D><b>{$younation['name']}</b></>(으)로 항복하였습니다.";
        $history[count($history)] = "<C>●</>{$admin['year']}년 {$admin['month']}월:<D><b>【혼란】</b></>통합에 반대하는 세력들로 인해 <D><b>{$younation['name']}</b></>에 혼란이 일고 있습니다.";
        $younation = addNationHistory($connect, $younation, "<C>●</>{$admin['year']}년 {$admin['month']}월:<D><b>{$mynation['name']}</b></>(와)과 합병");

        $newGenCount = $gencount + $gencount2;
        if($newGenCount < 10) { $newGenCount = 10; }
        $newTotalTech = $younation['totaltech'] + $mynation['totaltech'];
        $newTech = round($newTotalTech / $newGenCount);
        // 자금 통합, 외교제한 5년, 기술유지
        $query = "update nation set gold=gold+'{$mynation['gold']}',rice=rice+'{$mynation['rice']}',surlimit='24',totaltech='$newTotalTech',tech='$newTech',gennum='{$newGenCount}' where nation='{$younation['nation']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        //합병 당한국 모든 도시 10%감소
        $query = "update city set pop=pop*0.9,agri=agri*0.9,comm=comm*0.9,secu=secu*0.9,rate=rate*0.9,def=def*0.9,wall=wall*0.9 where nation='{$me['nation']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        //합병 시도국 모든 도시 5%감소
        $query = "update city set pop=pop*0.95,agri=agri*0.95,comm=comm*0.95,secu=secu*0.95,rate=rate*0.95,def=def*0.95,wall=wall*0.95 where nation='{$you['nation']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        //국가 삭제
        $query = "delete from nation where nation='{$me['nation']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        // 군주가 있는 위치 구함
        $query = "select city from general where nation='{$you['nation']}' and level='12'";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $king = MYDB_fetch_array($result);
        // 아국 모든 도시들 상대국 소속으로
        $query = "update city set nation='{$you['nation']}',gen1='0',gen2='0',gen3='0',conflict='',conflict2='' where nation='{$me['nation']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        // 제의국 모든 장수들 공헌도0.95, 명성0.95
        $query = "update general set dedication=dedication*0.95,experience=experience*0.95 where nation='{$you['nation']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        // 아국 모든 장수들 일반으로 하고 상대국 소속으로, 수도로 이동, 공헌도1.1, 명성0.9
        $query = "update general set belong=1,level=1,nation='{$you['nation']}',city='{$king['city']}',dedication=dedication*1.1,experience=experience*0.9 where nation='{$me['nation']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        // 부대도 모두 국가 소속 변경
        $query = "update troop set nation='{$you['nation']}' where nation='{$me['nation']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        // 외교 삭제
        $query = "delete from diplomacy where me='{$me['nation']}' or you='{$me['nation']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        // NPC들 일부 하야 (양국중 큰쪽 장수수의 90~110%만큼)
        $resignCount = 0;
        if($npccount >= $npccount2) {
            $resignCount = round($npccount*(rand()%21+90)/100);
        } else {
            $resignCount = round($npccount2*(rand()%21+90)/100);
        }
        $resignCommand = EncodeCommand(0, 0, 0, 45); //하야
        $query = "update general set turn0='$resignCommand' where nation='{$you['nation']}' and npc>=2 order by rand() limit {$resignCount}";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        pushGenLog($me, $mylog);
        pushGenLog($you, $youlog);
        pushHistory($connect, $history);
        unset($mylog);
        unset($youlog);
        unset($history);
    }
}

function updateNationState($connect) {
    $query = "select year,month from game where no='1'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $admin = MYDB_fetch_array($result);

    $query = "select nation,name,level,history from nation";
    $nationresult = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $nationcount = MYDB_num_rows($nationresult);

    for($i=0; $i < $nationcount; $i++) {
        $nation = MYDB_fetch_array($nationresult);

        $query = "select city,level,secu from city where nation='{$nation['nation']}' and level>=4";
        $cityresult = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $citycount = MYDB_num_rows($cityresult);

        $query = "select no from general where nation='{$nation['nation']}'";
        $genresult = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $gencount = MYDB_num_rows($genresult);

        if($citycount == 0) {
            $nationlevel = 0;   // 방랑군
        } elseif($citycount == 1) {
            $nationlevel = 1;   // 호족
        } elseif($citycount <= 4) {
            $nationlevel = 2;   // 군벌
        } elseif($citycount <= 7) {
            $nationlevel = 3;   // 주자사
        } elseif($citycount <= 10) {
            $nationlevel = 4;   // 주목
        } elseif($citycount <= 15) {
            $nationlevel = 5;   // 공
        } elseif($citycount <= 20) {
            $nationlevel = 6;   // 왕
        } else {
            $nationlevel = 7;   // 황제
        }

        if($nationlevel > $nation['level']) {
            $nation['level'] = $nationlevel;

            switch($nationlevel) {
                case 7:
                    $history[count($history)] = "<C>●</>{$admin['year']}년 {$admin['month']}월:<Y><b>【작위】</b></><D><b>{$nation['name']}</b></>의 군주가 <Y>".getNationLevel($nationlevel)."</>(을)를 자칭하였습니다.";
                    $nation = addNationHistory($connect, $nation, "<C>●</>{$admin['year']}년 {$admin['month']}월:<D><b>{$nation['name']}</b></>의 군주가 <Y>".getNationLevel($nationlevel)."</>(을)를 자칭");
                    break;
                case 6:
                    $history[count($history)] = "<C>●</>{$admin['year']}년 {$admin['month']}월:<Y><b>【작위】</b></><D><b>{$nation['name']}</b></>의 군주가 <Y>".getNationLevel($nationlevel)."</>에 등극하였습니다.";
                    $nation = addNationHistory($connect, $nation, "<C>●</>{$admin['year']}년 {$admin['month']}월:<D><b>{$nation['name']}</b></>의 군주가 <Y>".getNationLevel($nationlevel)."</>에 등극");
                    break;
                case 5:
                case 4:
                case 3:
                    $history[count($history)] = "<C>●</>{$admin['year']}년 {$admin['month']}월:<Y><b>【작위】</b></><D><b>{$nation['name']}</b></>의 군주가 <Y>".getNationLevel($nationlevel)."</>에 임명되었습니다.";
                    $nation = addNationHistory($connect, $nation, "<C>●</>{$admin['year']}년 {$admin['month']}월:<D><b>{$nation['name']}</b></>의 군주가 <Y>".getNationLevel($nationlevel)."</>에 임명됨");
                    break;
                case 2:
                    $history[count($history)] = "<C>●</>{$admin['year']}년 {$admin['month']}월:<Y><b>【작위】</b></><D><b>{$nation['name']}</b></>의 군주가 독립하여 <Y>".getNationLevel($nationlevel)."</>로 나섰습니다.";
                    $nation = addNationHistory($connect, $nation, "<C>●</>{$admin['year']}년 {$admin['month']}월:<D><b>{$nation['name']}</b></>의 군주가 <Y>".getNationLevel($nationlevel)."</>로 나서다");
                    break;
            }

            //작위 상승
            $query = "update nation set level='{$nation['level']}' where nation='{$nation['nation']}'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        }
        $gennum = $gencount;
        if($gencount < 10) $gencount = 10;
        //기술 및 변경횟수 업데이트
        $myset = $nation['level'] + 1;
        $query = "update nation set tech=totaltech/'$gencount',gennum='$gennum',myset='$myset' where nation='{$nation['nation']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    }
    pushHistory($connect, $history);
}

function checkStatistic($connect) {
    $query = "select year,month from game where no='1'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $admin = MYDB_fetch_array($result);

    $etc = '';

    $query = "select avg(gold) as avggold, avg(rice) as avgrice, avg(dex0+dex10+dex20+dex30) as avgdex, max(dex0+dex10+dex20+dex30) as maxdex, avg(experience+dedication) as avgexpded, max(experience+dedication) as maxexpded from general";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $general = MYDB_fetch_array($result);
    $general['avggold'] = round($general['avggold']);
    $general['avgrice'] = round($general['avgrice']);
    $general['avgdex'] = round($general['avgdex']);
    $general['avgexpded'] = round($general['avgexpded']);
    $etc .= "평균 금/쌀 ({$general['avggold']}/{$general['avgrice']}), 평균/최고 숙련({$general['avgdex']}/{$general['maxdex']}), 평균/최고 경험공헌({$general['avgexpded']}/{$general['maxexpded']}), ";

    $query = "select min(tech) as mintech, max(tech) as maxtech, avg(tech) as avgtech, min(power) as minpower, max(power) as maxpower, avg(power) as avgpower from nation where level>0";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $nation = MYDB_fetch_array($result);
    $nation['avgtech'] = round($nation['avgtech']);
    $nation['avgpower'] = round($nation['avgpower']);
    $etc .= "최저/평균/최고 기술({$nation['mintech']}/{$nation['avgtech']}/{$nation['maxtech']}), ";
    $etc .= "최저/평균/최고 국력({$nation['minpower']}/{$nation['avgpower']}/{$nation['maxpower']}), ";
    
    $nationName = '';
    $power_hist = '';

    $query = "select nation,name,type,power,gennum,round((gold+rice)/100) as goldrice from nation where level>0 order by power desc";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $nationCount = MYDB_num_rows($result);
    for($i=0; $i < $nationCount; $i++) {
        $nation = MYDB_fetch_array($result);

        $query = "select sum(leader+power+intel) as abil,round(sum(gold+rice)/100) as goldrice,round(sum(dex0+dex10+dex20+dex30)/1000) as dex,round(sum(experience+dedication)/100) as expded from general where nation='{$nation['nation']}'";
        $result2 = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $general = MYDB_fetch_array($result2);

        $query = "select count(*) as cnt,round(sum(pop)/100) as pop,round(sum(pop2)/100) as pop2 from city where nation='{$nation['nation']}'";
        $result2 = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $city = MYDB_fetch_array($result2);
        
        $nationName .= $nation['name'].'('.getNationType($nation['type']).'), ';
        $power_hist .= "{$nation['name']}({$nation['power']}/{$nation['gennum']}/{$city['cnt']}/{$city['pop']}/{$city['pop2']}/{$nation['goldrice']}/{$general['goldrice']}/{$general['abil']}/{$general['dex']}/{$general['expded']}), ";
        $nationHists[$nation['type']]++;
    }

    $nationHist = '';
    for($i=1; $i <= 13; $i++) {
        if(!$nationHists[$i]) { $nationHists[$i] = '-'; }
        $nationHist .= getNationType($i)."({$nationHists[$i]}), ";
    }

    $query = "select no from general where npc <= 1";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $genCount = MYDB_num_rows($result);

    $query = "select no from general where npc > 1";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $npcCount = MYDB_num_rows($result);

    $query = "select personal,special,special2 from general";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $generalCount = MYDB_num_rows($result);
    for($i=0; $i < $generalCount; $i++) {
        $general = MYDB_fetch_array($result);

        $personalHists[$general['personal']]++;
        $specialHists[$general['special']]++;
        $specialHists2[$general['special2']]++;
    }

    $generalCountStr = "{$generalCount}({$genCount}+{$npcCount})";

    $personalHist = '';
    for($i=0; $i < 11; $i++) {
        if(!$personalHists[$i]) { $personalHists[$i] = '-'; }
        $personalHist .= getGenChar($i)."({$personalHists[$i]}), ";
    }
    $specialHist = '';
    for($i=0; $i < 40; $i++) {
        $call = getGenSpecial($i);
        if($call) {
            if(!$specialHists[$i]) { $specialHists[$i] = '-'; }

            $specialHist .= $call."({$specialHists[$i]}), ";
        }
    }
    $specialHist .= '// ';
    $specialHist .= "-({$specialHists2[0]}), ";
    for($i=40; $i < 80; $i++) {
        $call = getGenSpecial($i);
        if($call) {
            if(!$specialHists2[$i]) { $specialHists2[$i] = '-'; }

            $specialHist .= $call."({$specialHists2[$i]}), ";
        }
    }

    $crewtype = '';
    $types = array(0, 1, 2, 3, 4, 5, 10, 11, 12, 13, 14, 20, 21, 22, 23, 24, 25, 26, 27, 30, 31, 32, 33, 34, 35, 36, 37, 38, 40, 41, 42, 43);
    $count = count($types);
    for($i=0; $i < $count; $i++) {
        $query = "select count(*) as type from general where crewtype={$types[$i]} and crew>=100";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $general = MYDB_fetch_array($result);

        $crewtype .= getTypeName($types[$i])."({$general['type']}), ";
    }

    $query = "
        insert into statistic (
            year, month,
            nation_count, nation_name, nation_hist,
            gen_count, personal_hist, special_hist, power_hist,
            crewtype, etc
        ) values (
            '{$admin['year']}', '{$admin['month']}',
            '$nationCount', '$nationName', '$nationHist',
            '$generalCountStr', '$personalHist', '$specialHist', '$power_hist',
            '$crewtype', '$etc'
        )";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
}

function checkEmperior($connect) {
    $query = "select year,month,isUnited from game where no='1'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $admin = MYDB_fetch_array($result);

    $query = "select nation,name,history from nation where level>0";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $count = MYDB_num_rows($result);

    if($count == 1 && $admin['isUnited'] == 0) {
        $nation = MYDB_fetch_array($result);

        $query = "select city from city where nation='{$nation['nation']}'";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $count = MYDB_num_rows($result);

        $query = "select city from city";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $allcount = MYDB_num_rows($result);

        if($count == $allcount) {
            $nation = addNationHistory($connect, $nation, "<C>●</>{$admin['year']}년 {$admin['month']}월:<D><b>{$nation['name']}</b></>(이)가 전토를 통일");

            $query = "update game set isUnited=2,conlimit=conlimit*100 where no='1'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

            $query = "select no from general where npc<2 and age>50";
            $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            $count = MYDB_num_rows($result);

            for($i=0; $i < $count; $i++) {
                $general = MYDB_fetch_array($result);
                CheckHall($connect, $general['no']);
            }

            $query = "select nation,name,type,color,gold,rice,power,gennum,history from nation where nation='{$nation['nation']}'";
            $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            $nation = MYDB_fetch_array($result);

            $query = "select SUM(pop) as totalpop,SUM(pop2) as maxpop from city where nation='{$nation['nation']}'"; // 도시 이름 목록
            $cityresult = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            $city = MYDB_fetch_array($cityresult);
            $pop = "{$city['totalpop']} / {$city['maxpop']}";
            $poprate = round($city['totalpop']/$city['maxpop']*100, 2);
            $poprate .= " %";

            $query = "select name,picture,belong from general where nation='{$nation['nation']}' and level='12'";
            $genresult = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            $level12 = MYDB_fetch_array($genresult);

            $query = "select name,picture,belong from general where nation='{$nation['nation']}' and level='11'";
            $genresult = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            $level11 = MYDB_fetch_array($genresult);

            $query = "select name,picture,belong from general where nation='{$nation['nation']}' and level='10'";
            $genresult = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            $level10 = MYDB_fetch_array($genresult);

            $query = "select name,picture,belong from general where nation='{$nation['nation']}' and level='9'";
            $genresult = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            $level9 = MYDB_fetch_array($genresult);

            $query = "select name,picture,belong from general where nation='{$nation['nation']}' and level='8'";
            $genresult = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            $level8 = MYDB_fetch_array($genresult);

            $query = "select name,picture,belong from general where nation='{$nation['nation']}' and level='7'";
            $genresult = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            $level7 = MYDB_fetch_array($genresult);

            $query = "select name,picture,belong from general where nation='{$nation['nation']}' and level='6'";
            $genresult = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            $level6 = MYDB_fetch_array($genresult);

            $query = "select name,picture,belong from general where nation='{$nation['nation']}' and level='5'";
            $genresult = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            $level5 = MYDB_fetch_array($genresult);

            $query = "select name,picture,killnum from general where nation='{$nation['nation']}' order by killnum desc limit 5";   // 오호장군
            $tigerresult = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            $tigernum = MYDB_num_rows($tigerresult);
            for($i=0; $i < $tigernum; $i++) {
                $tiger = MYDB_fetch_array($tigerresult);
                if($tiger['killnum'] > 0) {
                    $tigerstr .= "{$tiger['name']}【{$tiger['killnum']}】, ";
                }
            }

            $query = "select name,picture,firenum from general where nation='{$nation['nation']}' order by firenum desc limit 7";   // 건안칠자
            $eagleresult = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            $eaglenum = MYDB_num_rows($eagleresult);
            for($i=0; $i < $eaglenum; $i++) {
                $eagle = MYDB_fetch_array($eagleresult);
                if($eagle['firenum'] > 0) {
                    $eaglestr .= "{$eagle['name']}【{$eagle['firenum']}】, ";
                }
            }

            $log[0] = "<C>●</>{$admin['year']}년 {$admin['month']}월: <D><b>{$nation['name']}</b></>(이)가 전토를 통일하였습니다.";

            $query = "select no,name from general where nation='{$nation['nation']}' order by dedication desc";
            $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            $gencount = MYDB_num_rows($result);
            for($i=0; $i < $gencount; $i++) {
                $general = MYDB_fetch_array($result);
                $gen .= "{$general['name']}, ";

                pushGenLog($general, $log);
            }

            $nation['type'] = getNationType($nation['type']);

            $query = "select MAX(nation_count) as nc,MAX(gen_count) as gc from statistic";
            $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            $stat = MYDB_fetch_array($result);

            $query = "select count(*) as cnt from general";
            $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            $gencount = MYDB_fetch_array($result);

            $statNC = "1 / {$stat['nc']}";
            $statGC = "{$gencount['cnt']} / {$stat['gc']}";

            $query = "select nation_count,nation_name,nation_hist from statistic where nation_count='{$stat['nc']}' limit 0,1";
            $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            $statNation = MYDB_fetch_array($result);

            $query = "select gen_count,personal_hist,special_hist from statistic order by no desc limit 0,1";
            $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            $statGeneral = MYDB_fetch_array($result);

            $query = "
                insert into emperior (
                    phase,
                    nation_count, nation_name, nation_hist,
                    gen_count, personal_hist, special_hist,
                    name, type, color, year, month, power, gennum, citynum,
                    pop, poprate, gold, rice,
                    l12name, l12pic, l11name, l11pic,
                    l10name, l10pic, l9name, l9pic,
                    l8name, l8pic, l7name, l7pic,
                    l6name, l6pic, l5name, l5pic,
                    tiger, eagle, gen, history
                ) values (
                    '-',
                    '$statNC', '{$statNation['nation_name']}', '{$statNation['nation_hist']}',
                    '$statGC', '{$statGeneral['personal_hist']}', '{$statGeneral['special_hist']}',
                    '{$nation['name']}', '{$nation['type']}', '{$nation['color']}', '{$admin['year']}', '{$admin['month']}', '{$nation['power']}', '{$nation['gennum']}', '$allcount',
                    '$pop', '$poprate', '{$nation['gold']}', '{$nation['rice']}',
                    '{$level12['name']}', '{$level12['picture']}', '{$level11['name']}', '{$level11['picture']}',
                    '{$level10['name']}', '{$level10['picture']}', '{$level9['name']}', '{$level9['picture']}',
                    '{$level8['name']}', '{$level8['picture']}', '{$level7['name']}', '{$level7['picture']}',
                    '{$level6['name']}', '{$level6['picture']}', '{$level5['name']}', '{$level5['picture']}',
                    '$tigerstr', '$eaglestr', '$gen', '{$nation['history']}'
                )";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

            $history[count($history)] = "<C>●</>{$admin['year']}년 {$admin['month']}월:<Y><b>【통일】</b></><D><b>{$nation['name']}</b></>(이)가 전토를 통일하였습니다.";
            pushHistory($connect, $history);

            //연감 월결산
            LogHistory($connect);
        }
    }
}

function PreprocessCommand($connect, $no) {
    $query = "select no,name,city,injury,special2,item,turn0 from general where no='$no'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $general = MYDB_fetch_array($result);

    if($general['special2'] == 73 || $general['item'] == 23 || $general['item'] == 24) {
        //특기보정 : 의술
        //의서 사용
        if($general['injury'] > 0) {
            $general['injury'] = 0;
            $query = "update general set injury=0 where no='$no'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

            $log[0] = "<C>●</><C>의술</>을 펼쳐 스스로 치료합니다!";
            pushGenLog($general, $log);
        }
            
        $query = "select no,name,injury from general where city='{$general['city']}' and injury>10 order by rand()";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $patientCount = MYDB_num_rows($result);
    
        if($patientCount > 0) {
            // 50% 확률로 치료
            $patientCount = round($patientCount * 0.5);
    
            $patientName = "";
            for($i=0; $i < $patientCount; $i++) {
                $patient = MYDB_fetch_array($result);
    
                //부상 치료
                $query = "update general set injury=0 where no='{$patient['no']}'";
                MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    
                $log[0] = "<C>●</><Y>{$general['name']}</>(이)가 <C>의술</>로써 치료해줍니다!";
                pushGenLog($patient, $log);
                
                if($patientName == "") {
                    $patientName = $patient['name'];
                }
            }

            if($patientCount == 1) {
                $log[0] = "<C>●</><C>의술</>을 펼쳐 도시의 장수 <Y>{$patientName}</>(을)를 치료합니다!";
            } else {
                $patientCount -= 1;
                $log[0] = "<C>●</><C>의술</>을 펼쳐 도시의 장수들 <Y>{$patientName}</> 외 <C>{$patientCount}</>명을 치료합니다!";
            }
            pushGenLog($general, $log);
        }
    }
    
    if($general['injury'] > 0) {
        if($general['item'] >=7 && $general['item'] <= 11) {
            //영구약 사용
            $query = "update general set injury=0 where no='$no'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

            $log[0] = "<C>●</><C>".getItemName($general['item'])."</>(을)를 사용하여 치료합니다!";
            pushGenLog($general, $log);
        } elseif($general['injury'] > 10 && $general['item'] == 1 && $general[turn0] != EncodeCommand(0, 0, 0, 50)) {
            //환약 사용
            $query = "update general set injury=0,item=0 where no='$no'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

            $log[0] = "<C>●</><C>환약</>을 사용하여 치료합니다!";
            pushGenLog($general, $log);
        } elseif($general['injury'] > 10) {
            //부상 감소
            $query = "update general set injury=injury-10 where no='$no'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        } else {
            //부상 감소
            $query = "update general set injury=0 where no='$no'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        }
    }
}

function processCommand($connect, $no) {
    $query = "select npc,no,name,userlevel,picture,imgsvr,nation,nations,city,troop,injury,leader,leader2,power,power2,intel,intel2,experience,dedication,level,gold,rice,crew,crewtype,train,atmos,weap,book,horse,item,turntime,makenation,makelimit,killturn,block,dedlevel,explevel,age,history,belong,personal,special,special2,term,turn0,dex0,dex10,dex20,dex30,dex40 from general where no='$no'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $general = MYDB_fetch_array($result);

    $query = "select month,killturn from game where no='1'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $admin = MYDB_fetch_array($result);

    // 블럭자는 미실행. 삭턴 감소
    if($general['block'] == 2) {
        $date = substr($general['turntime'],11,5);
        $log[count($log)] = "<C>●</>{$admin['month']}월:현재 멀티, 또는 비매너로 인한<R>블럭</> 대상자입니다. <1>$date</>";
        pushGenLog($general, $log);

        $query = "update general set recturn='',resturn='BLOCK_2',myset=3,con=0,killturn=killturn-1 where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    } elseif($general['block'] == 3) {
        $date = substr($general['turntime'],11,5);
        $log[count($log)] = "<C>●</>{$admin['month']}월:현재 악성유저로 분류되어 <R>블럭, 발언권 무효</> 대상자입니다. <1>$date</>";
        pushGenLog($general, $log);

        $query = "update general set recturn='',resturn='BLOCK_3',myset=3,con=0,killturn=killturn-1 where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    } else {
        if($general['level'] >= 5 && $general['level'] <= 12) {
            $query = "select l{$general['level']}turn0,l{$general['level']}term from nation where nation='{$general['nation']}'";
            $coreresult = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            $core = MYDB_fetch_array($coreresult);
            $corecommand = DecodeCommand($core["l{$general['level']}turn0"]);
            //연속턴 아닌경우 텀 리셋
            if($core["l{$general['level']}term"]%100 != $corecommand[0]) {
                $query = "update nation set l{$general['level']}term=0 where nation='{$general['nation']}'";
                MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            }

            switch($corecommand[0]) {
                case 23: process_23($connect, $general); break; //포상
                case 24: process_24($connect, $general); break; //몰수
                case 27: process_27($connect, $general); break; //발령
                case 51: process_51($connect, $general); break; //항복권고
                case 52: process_52($connect, $general); break; //원조
                case 53: process_53($connect, $general); break; //통합제의
                case 61: process_61($connect, $general); break; //불가침제의
                case 62: process_62($connect, $general); break; //선전 포고
                case 63: process_63($connect, $general); break; //종전 제의
                case 64: process_64($connect, $general); break; //파기 제의
                case 65: process_65($connect, $general); break; //초토화
                case 66: process_66($connect, $general); break; //천도
                case 67: process_67($connect, $general); break; //증축
                case 68: process_68($connect, $general); break; //감축
                case 71: process_71($connect, $general); break; //필사즉생
                case 72: process_72($connect, $general); break; //백성동원
                case 73: process_73($connect, $general); break; //수몰
                case 74: process_74($connect, $general); break; //허보
                case 75: process_75($connect, $general); break; //피장파장
                case 76: process_76($connect, $general); break; //의병모집
                case 77: process_77($connect, $general); break; //이호경식
                case 78: process_78($connect, $general); break; //급습
                case 81: process_81($connect, $general); break; //국기변경
                case 99: break; //수뇌부휴식
            }

            //장수정보 재로드
            $query = "select npc,no,name,userlevel,picture,imgsvr,nation,nations,city,troop,injury,leader,leader2,power,power2,intel,intel2,experience,dedication,level,gold,rice,crew,crewtype,train,atmos,weap,book,horse,item,turntime,makenation,makelimit,killturn,block,dedlevel,explevel,age,history,belong,personal,special,special2,term,turn0,dex0,dex10,dex20,dex30,dex40 from general where no='$no'";
            $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            $general = MYDB_fetch_array($result);
        }

        $command = DecodeCommand($general['turn0']);
        //삭턴 처리
        if($general['npc'] >= 2 || $general['killturn'] > $admin['killturn']) {
            $query = "update general set recturn=turn0,resturn='FAIL',myset=3,con=0,killturn=killturn-1 where no='{$general['no']}'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        } elseif(floor($command[0]) == 0 && $general['userlevel'] < 5) {
            $query = "update general set recturn=turn0,resturn='FAIL',myset=3,con=0,killturn=killturn-1 where no='{$general['no']}'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        } else {
            $query = "update general set recturn=turn0,resturn='FAIL',myset=3,con=0,killturn='{$admin['killturn']}' where no='{$general['no']}'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        }
        //연속턴 아닌경우 텀 리셋
        if($general['term']%100 != $command[0]) {
            $query = "update general set term=0 where no='{$general['no']}'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        }
        //턴 처리
        switch($command[0]) {
            case 0: //휴식
                $date = substr($general['turntime'],11,5);
                $log[count($log)] = "<C>●</>{$admin['month']}월:아무것도 실행하지 않았습니다. <1>$date</>";
                pushGenLog($general, $log);
                break;
            case  1: process_1($connect, $general, 1); break; //농업
            case  2: process_1($connect, $general, 2); break; //상업
            case  3: process_3($connect, $general); break; //기술
            case  4: process_4($connect, $general); break; //선정
            case  5: process_5($connect, $general, 1); break; //수비
            case  6: process_5($connect, $general, 2); break; //성벽
            case  7: process_7($connect, $general); break; //정착 장려
            case  8: process_8($connect, $general); break; //치안
            case  9: process_9($connect, $general); break; //조달

            case 11: process_11($connect, $general, 1); break; //징병
            case 12: process_11($connect, $general, 2); break; //모병
            case 13: process_13($connect, $general); break; //훈련
            case 14: process_14($connect, $general); break; //사기진작
            case 15: process_15($connect, $general); break; //전투태세
            case 16: process_16($connect, $general); break; //전쟁
            case 17: process_17($connect, $general); break; //소집해제

            case 21: process_21($connect, $general); break; //이동
            case 22: process_22($connect, $general); break; //등용
            case 25: process_25($connect, $general); break; //임관
            case 26: process_26($connect, $general); break; //집합
            case 28: process_28($connect, $general); break; //귀환
            case 29: process_29($connect, $general); break; //인재탐색
            case 30: process_30($connect, $general); break; //강행
            
            case 31: process_31($connect, $general); break; //첩보
            case 32: process_32($connect, $general); break; //화계
            case 33: process_33($connect, $general); break; //탈취
            case 34: process_34($connect, $general); break; //파괴
            case 35: process_35($connect, $general); break; //선동
            case 36: process_36($connect, $general); break; //기습

            case 41: process_41($connect, $general); break; //단련
            case 42: process_42($connect, $general); break; //견문
            case 43: process_43($connect, $general); break; //증여
            case 44: process_44($connect, $general); break; //헌납
            case 45: process_45($connect, $general); break; //하야
            case 46: process_46($connect, $general); break; //건국
            case 47: process_47($connect, $general); break; //방랑
            case 48: process_48($connect, $general); break; //장비매매
            case 49: process_49($connect, $general); break; //군량매매
            case 50: process_50($connect, $general); break; //요양

            case 54: process_54($connect, $general); break; //선양
            case 55: process_55($connect, $general); break; //거병
            case 56: process_56($connect, $general); break; //해산
            case 57: process_57($connect, $general); break; //모반 시도
        }
    }
}

function updateCommand($connect, $no, $type=0) {
    $query = "select no,nation,level from general where no='$no'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $general = MYDB_fetch_array($result);

    if($type == 0 || $type == 1) {  // 턴처리후, 당기기
        $query = "
update general set
turn0=turn1,turn1=turn2,turn2=turn3,turn3=turn4,turn4=turn5,turn5=turn6,turn6=turn7,turn7=turn8,turn8=turn9,
turn9=turn10,turn10=turn11,turn11=turn12,turn12=turn13,turn13=turn14,turn14=turn15,turn15=turn16,turn16=turn17,
turn17=turn18,turn18=turn19,turn19=turn20,turn20=turn21,turn21=turn22,turn22=turn23,turn23='00000000000000'
where no='{$general['no']}'
";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    }
    if($type == 2 || ($general['level'] >= 5 && $general['level'] <= 12 && $type == 0)) {   // 턴 처리후 수뇌부, 수뇌부 당기기
        $turn = "l{$general['level']}turn";
        $query = "
update nation set
{$turn}0={$turn}1,{$turn}1={$turn}2,
{$turn}2={$turn}3,{$turn}3={$turn}4,
{$turn}4={$turn}5,{$turn}5={$turn}6,
{$turn}6={$turn}7,{$turn}7={$turn}8,
{$turn}8={$turn}9,{$turn}9={$turn}10,
{$turn}10={$turn}11,{$turn}11='00000000000099'
where nation='{$general['nation']}'
";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    }
}

function backupdateCommand($connect, $no, $type=0) {
    $query = "select no,nation,level from general where no='$no'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $general = MYDB_fetch_array($result);

    if($type == 1) {  // 미루기
        $query = "
update general set
turn23=turn22,turn22=turn21,
turn21=turn20,turn20=turn19,
turn19=turn18,turn18=turn17,
turn17=turn16,turn16=turn15,
turn15=turn14,turn14=turn13,
turn13=turn12,turn12=turn11,
turn11=turn10,turn10=turn9,
turn9=turn8,turn8=turn7,
turn7=turn6,turn6=turn5,
turn5=turn4,turn4=turn3,
turn3=turn2,turn2=turn1,
turn1=turn0,turn0='00000000000000'
where no='$no'
";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    } elseif($type == 2) {  // 수뇌부 미루기
        $turn = "l{$general['level']}turn";
        $query = "
update nation set
{$turn}11={$turn}10,{$turn}10={$turn}9,
{$turn}9={$turn}8,{$turn}8={$turn}7,
{$turn}7={$turn}6,{$turn}6={$turn}5,
{$turn}5={$turn}4,{$turn}4={$turn}3,
{$turn}3={$turn}2,{$turn}2={$turn}1,
{$turn}1={$turn}0,{$turn}0='00000000000099'
where nation='{$general['nation']}'
";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    }
}

function updateTurntime($connect, $no) {
    $query = "select year,month,isUnited,turnterm from game where no='1'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $admin = MYDB_fetch_array($result);

    $query = "select no,name,name2,nation,troop,age,turntime,history,killturn,level,deadyear,npc,npc_org,npcmatch,npcid from general where no='$no'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $general = MYDB_fetch_array($result);

    // 삭턴장수 삭제처리
    if($general['killturn'] <= 0) {
        // npc유저 삭턴시 npc로 전환
        if($general['npc'] == 1 && $general['deadyear'] > $admin['year']) {
            $npcid = "gen" . $general['npcid'];
            $pw = md5("18071807");
            $general['killturn'] = ($general['deadyear'] - $admin['year']) * 12;
            $general['npc'] = $general['npc_org'];
            $query = "update general set user_id='$npcid',password='$pw',npc='{$general['npc']}',killturn='{$general['killturn']}',mode=2 where no='$no'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

            $alllog[0] = "<C>●</>{$admin['month']}월:<Y>$general[name2]</>(이)가 <Y>{$general['name']}</>의 육체에서 <S>유체이탈</>합니다!";
            pushAllLog($alllog);

            if($admin['isUnited'] == 0) {
                CheckHall($connect, $no);
            }
        } else {
            // 군주였으면 유지 이음
            if($general['level'] == 12) {
                nextRuler($connect, $general);
            }

            //도시의 태수, 군사, 시중직도 초기화
            $query = "update city set gen1='0' where gen1='{$general['no']}'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            $query = "update city set gen2='0' where gen2='{$general['no']}'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            $query = "update city set gen3='0' where gen3='{$general['no']}'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

            // 부대 처리
            $query = "select no from troop where troop='{$general['troop']}'";
            $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            $troop = MYDB_fetch_array($result);

            //부대장일 경우
            if($troop['no'] == $general['no']) {
                // 모두 탈퇴
                $query = "update general set troop='0' where troop='{$general['troop']}'";
                MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
                // 부대 삭제
                $query = "delete from troop where troop='{$general['troop']}'";
                MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            } else {
                $query = "update general set troop='0' where no='{$general['no']}'";
                MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            }
            // 장수 삭제
            $query = "delete from general where no='{$general['no']}'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            //기존 국가 기술력 그대로
            $query = "select no from general where nation='{$general['nation']}'";
            $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            $gencount = MYDB_num_rows($result);
            $gennum = $gencount;
            if($gencount < 10) $gencount = 10;

            $query = "update nation set totaltech=tech*'$gencount',gennum='$gennum' where nation='{$general['nation']}'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

            // 병, 요절, 객사, 번개, 사채, 일확천금, 호랑이, 곰, 수영, 처형, 발견
            switch(rand()%42) {
            case 0:  $alllog[0] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>(이)가 역병에 걸려 <R>죽고</> 말았습니다."; break;
            case 1:  $alllog[0] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>(이)가 <R>요절</>하고 말았습니다."; break;
            case 2:  $alllog[0] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>(이)가 거리에서 갑자기 <R>객사</>하고 말았습니다."; break;
            case 3:  $alllog[0] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>(이)가 안타깝게도 번개에 맞아 <R>죽고</> 말았습니다."; break;
            case 4:  $alllog[0] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>(이)가 고리대금에 시달리다가 <R>자살</>하고 말았습니다."; break;
            case 5:  $alllog[0] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>(이)가 일확천금에 놀라 심장마비로 <R>죽고</> 말았습니다."; break;
            case 6:  $alllog[0] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>(이)가 산속에서 호랑이에게 물려 <R>죽고</> 말았습니다."; break;
            case 7:  $alllog[0] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>(이)가 산책중 곰에게 할퀴어 <R>죽고</> 말았습니다."; break;
            case 8:  $alllog[0] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>(이)가 수영을 하다 <R>익사</>하고 말았습니다."; break;
            case 9:  $alllog[0] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>(이)가 황제를 모독하다가 <R>처형</>당하고 말았습니다."; break;
            case 10: $alllog[0] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>(이)가 이튿날 침실에서 <R>죽은채로</>발견되었습니다."; break;
            case 11: $alllog[0] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>(이)가 색에 빠져 기력이 쇠진해 <R>죽고</>말았습니다."; break;
            case 12: $alllog[0] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>(이)가 미녀를 보고 심장마비로 <R>죽고</>말았습니다."; break;
            case 13: $alllog[0] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>(이)가 우울증에 걸려 <R>자살</>하고 말았습니다."; break;
            case 14: $alllog[0] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>(이)가 천하 정세를 비관하며 <R>분신</>하고 말았습니다."; break;
            case 15: $alllog[0] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>(이)가 어떤 관심도 못받고 쓸쓸히 <R>죽고</>말았습니다."; break;
            case 16: $alllog[0] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>(이)가 유산 상속 문제로 다투다가 <R>살해</>당했습니다."; break;
            case 17: $alllog[0] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>(이)가 누군가의 사주로 자객에게 <R>암살</>당했습니다."; break;
            case 18: $alllog[0] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>(이)가 바람난 배우자에게 <R>독살</>당하고 말았습니다."; break;
            case 19: $alllog[0] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>(이)가 농약을 술인줄 알고 마셔 <R>죽고</>말았습니다."; break;
            case 20: $alllog[0] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>(이)가 아무 이유 없이 <R>죽고</>말았습니다."; break;
            case 21: $alllog[0] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>(이)가 전재산을 잃고 화병으로 <R>죽고</>말았습니다."; break;
            case 22: $alllog[0] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>(이)가 단식운동을 하다가 굶어 <R>죽고</>말았습니다."; break;
            case 23: $alllog[0] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>(이)가 귀신에게 홀려 시름 앓다가 <R>죽고</>말았습니다."; break;
            case 24: $alllog[0] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>(이)가 사람들에게 집단으로 맞아서 <R>죽고</>말았습니다."; break;
            case 25: $alllog[0] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>(이)가 갑자기 성벽에서 뛰어내려 <R>죽고</>말았습니다."; break;
            case 26: $alllog[0] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>(이)가 농사중 호미에 머리를 맞아 <R>죽고</>말았습니다."; break;
            case 27: $alllog[0] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>(이)가 저세상이 궁금하다며 <R>자살</>하고 말았습니다."; break;
            case 28: $alllog[0] = "<C>●</>{$admin['month']}월:운좋기로 소문난 <Y>{$general['name']}</>(이)가 불운하게도 <R>죽고</>말았습니다."; break;
            case 29: $alllog[0] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>(이)가 무리하게 단련을 하다가 <R>죽고</>말았습니다."; break;
            case 30: $alllog[0] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>(이)가 생활고를 비관하며 <R>자살</>하고 말았습니다."; break;
            case 31: $alllog[0] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>(이)가 평생 결혼도 못해보고 <R>죽고</> 말았습니다."; break;
            case 32: $alllog[0] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>(이)가 과식하다 배가 터져 <R>죽고</> 말았습니다."; break;
            case 33: $alllog[0] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>(이)가 웃다가 숨이 넘어가 <R>죽고</> 말았습니다."; break;
            case 34: $alllog[0] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>(이)가 추녀를 보고 놀라서 <R>죽고</> 말았습니다."; break;
            case 35: $alllog[0] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>(이)가 물에 빠진 사람을 구하려다 같이 <R>죽고</> 말았습니다."; break;
            case 36: $alllog[0] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>(이)가 독살을 준비하다 독에 걸려 <R>죽고</> 말았습니다."; break;
            case 37: $alllog[0] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>(이)가 뒷간에서 너무 힘을 주다가 <R>죽고</> 말았습니다."; break;
            case 38: $alllog[0] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>(이)가 돌팔이 의사에게 치료받다가 <R>죽고</> 말았습니다."; break;
            case 39: $alllog[0] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>(이)가 남의 보약을 훔쳐먹다 부작용으로 <R>죽고</> 말았습니다."; break;
            case 40: $alllog[0] = "<C>●</>{$admin['month']}월:희대의 사기꾼 <Y>{$general['name']}</>(이)가 <R>사망</>했습니다."; break;
            case 41: $alllog[0] = "<C>●</>{$admin['month']}월:희대의 호색한 <Y>{$general['name']}</>(이)가 <R>사망</>했습니다."; break;
            default: $alllog[0] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>(이)가 <R>사망</>했습니다."; break;
            }
            // 엔피씨,엠피씨,의병 사망로그
            if($general['npc'] == 2) {
                $alllog[0] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>(이)가 <R>사망</>했습니다.";
            } elseif($general['npc'] >= 3) {
                switch(rand()%10) {
                case 0: $alllog[0] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>(이)가 푸대접에 실망하여 떠났습니다."; break;
                case 1: $alllog[0] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>(이)가 갑자기 화를 내며 떠났습니다."; break;
                case 2: $alllog[0] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>(이)가 의견차이를 좁히지 못하고 떠났습니다."; break;
                case 3: $alllog[0] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>(이)가 판단 착오였다며 떠났습니다."; break;
                case 4: $alllog[0] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>(이)가 생활고가 나아지지 않는다며 떠났습니다."; break;
                case 5: $alllog[0] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>(이)가 기대가 너무 컸다며 떠났습니다."; break;
                case 6: $alllog[0] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>(이)가 아무 이유 없이 떠났습니다."; break;
                case 7: $alllog[0] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>(이)가 자기 목적은 달성했다며 떠났습니다."; break;
                case 8: $alllog[0] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>(이)가 자기가 없어도 될것 같다며 떠났습니다."; break;
                case 9: $alllog[0] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>(이)가 처자식이 그립다며 떠났습니다."; break;
                }
            }

            pushAllLog($alllog);

            return;
        }
    }

    if($general['age'] >= 80 && $general['npc'] == 0) {
        if($admin['isUnited'] == 0) {
            CheckHall($connect, $no);
        }

        $query = "update general set leader=leader*0.85,power=power*0.85,intel=intel*0.85,injury=0,experience=experience*0.5,dedication=dedication*0.5,firenum=0,warnum=0,killnum=0,deathnum=0,killcrew=0,deathcrew=0,age=20,specage=0,specage2=0,crew=crew*0.85,dex0=dex0*0.5,dex10=dex10*0.5,dex20=dex20*0.5,dex30=dex30*0.5,dex40=dex40*0.5 where no='$no'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        $alllog[0] = "<C>●</>{$admin['month']}월:<Y>{$general['name']}</>(이)가 <R>은퇴</>하고 그 자손이 유지를 이어받았습니다.";
        pushAllLog($alllog);

        $log[0] = "<C>●</>나이가 들어 <R>은퇴</>하고 자손에게 자리를 물려줍니다.";
        pushGenLog($general, $log);
        $general = addHistory($connect, $general, "<C>●</>{$admin['year']}년 {$admin['month']}월:나이가 들어 은퇴하고, 자손에게 관직을 물려줌");
    }

    $turntime = addTurn($general['turntime'], $admin['turnterm']);

    $query = "update general set turntime='$turntime' where no='$no'";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
}

function CheckHall($connect, $no) {
    $type = array(
        "experience",
        "dedication",
        "firenum",
        "warnum",
        "killnum",
        "winrate",
        "killcrew",
        "killrate",
        "dex0",
        "dex10",
        "dex20",
        "dex30",
        "dex40",
        "ttrate",
        "tlrate",
        "tprate",
        "tirate",
        "betgold",
        "betwin",
        "betwingold",
        "betrate"
    );

    $query = "select name,nation,picture,
        experience,dedication,warnum,firenum,killnum,
        killnum/warnum*10000 as winrate,killcrew,killcrew/deathcrew*10000 as killrate,
        dex0,dex10,dex20,dex30,dex40,
        ttw/(ttw+ttd+ttl)*10000 as ttrate, ttw+ttd+ttl as tt,
        tlw/(tlw+tld+tll)*10000 as tlrate, tlw+tld+tll as tl,
        tpw/(tpw+tpd+tpl)*10000 as tprate, tpw+tpd+tpl as tp,
        tiw/(tiw+tid+til)*10000 as tirate, tiw+tid+til as ti,
        betgold, betwin, betwingold, betwingold/betgold*10000 as betrate
        from general where no='$no'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $general = MYDB_fetch_array($result);

    $query = "select nation,name,color from nation where nation='{$general['nation']}'";
    $nationresult = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $nation = MYDB_fetch_array($nationresult);

    for($k=0; $k < 21; $k++) {
        $query = "select * from hall where type='$k' order by data desc";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $count = MYDB_num_rows($result);

        //승률,살상률인데 10회 미만 전투시 스킵
        if(($k == 5 || $k == 7) && $general['warnum']<10) { continue; }
        //토너승률인데 50회 미만시 스킵
        if($k == 13 && $general['tt'] < 50) { continue; }
        //토너승률인데 50회 미만시 스킵
        if($k == 14 && $general['tl'] < 50) { continue; }
        //토너승률인데 50회 미만시 스킵
        if($k == 15 && $general['tp'] < 50) { continue; }
        //토너승률인데 50회 미만시 스킵
        if($k == 16 && $general['ti'] < 50) { continue; }
        //수익률인데 1000미만시 스킵
        if($k == 20 && $general['betgold'] < 1000) { continue; }

        $rank = 10;
        for($i=0; $i < $count; $i++) {
            $ranker = MYDB_fetch_array($result);

            if($general[$type[$k]] >= $ranker['data']) {
                $rank = $i;
                break;
            }
        }
        for($i=8; $i >= $rank; $i--) {
            $j = $i + 1;
            $query = "select * from hall where type='$k' and rank='$i'";
            $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            $ranker = MYDB_fetch_array($result);

            $query = "update hall set name='{$ranker['name']}', nation='{$ranker['nation']}', data='{$ranker['data']}', color='{$ranker['color']}', picture='{$ranker['picture']}' where type='$k' and rank='$j'";
            $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        }
        $query = "update hall set name='{$general['name']}', nation='{$nation['name']}', data='{$general[$type[$k]]}', color='{$nation['color']}', picture='{$general['picture']}' where type='$k' and rank='$rank'";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    }
}

function info($connect, $type=0, $skin=1) {
    $query = "select year,month,turnterm,maxgeneral from game where no='1'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $admin = MYDB_fetch_array($result);

    switch($admin['turnterm']) {
        case 0: $termtype="120분 턴"; break;
        case 1: $termtype="60분 턴"; break;
        case 2: $termtype="30분 턴"; break;
        case 3: $termtype="20분 턴"; break;
        case 4: $termtype="10분 턴"; break;
        case 5: $termtype="5분 턴"; break;
        case 6: $termtype="2분 턴"; break;
        case 7: $termtype="1분 턴"; break;
    }

    $query = "select no from general where npc<2";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $gencount = MYDB_num_rows($result);

    $query = "select no from general where npc>=2";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $npccount = MYDB_num_rows($result);

    switch($type) {
    case 0:
        echo "현재 : {$admin['year']}年 {$admin['month']}月 (<font color="; echo $skin>0?"cyan":"white"; echo ">$termtype</font> 서버)<br> 등록 장수 : 유저 {$gencount} / {$admin['maxgeneral']} 명 + <font color="; echo $skin>0?"cyan":"white"; echo ">NPC {$npccount} 명</font>";
        break;
    case 1:
        echo "현재 : {$admin['year']}年 {$admin['month']}月 (<font color="; echo $skin>0?"cyan":"white"; echo ">$termtype</font> 서버)&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 등록 장수 : 유저 {$gencount} / {$admin['maxgeneral']} 명 + <font color="; echo $skin>0?"cyan":"white"; echo ">NPC {$npccount} 명</font>";
        break;
    case 2:
        echo "현재 : {$admin['year']}年 {$admin['month']}月 (<font color="; echo $skin>0?"cyan":"white"; echo ">$termtype</font> 서버)";
        break;
    case 3:
        echo "등록 장수 : 유저 {$gencount} / {$admin['maxgeneral']} 명 + <font color="; echo $skin>0?"cyan":"white"; echo ">NPC {$npccount} 명</font>";
        break;
    }
}

function uniqueItem($connect, $general, $log, $vote=0) {
    if($general['npc'] >= 2 || $general['betray'] > 1) { return $log; }
    if($general['weap'] > 6 || $general['book'] > 6 || $general['horse'] > 6 || $general['item'] > 6) { return $log; }

    $query = "select year,month,scenario from game where no='1'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $game = MYDB_fetch_array($result);

    $query = "select count(*) as cnt from general where npc<2";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $gen = MYDB_fetch_array($result);

    if($game['scenario'] == 0)  { $prob = $gen['cnt'] * 5; }  // 5~6개월에 하나씩 등장
    else { $prob = $gen['cnt']; }  // 1~2개월에 하나씩 등장

    if($vote == 1) { $prob = round($gen['cnt'] * 0.7 / 3); }     // 투표율 70%, 투표 한번에 2~3개 등장
    elseif($vote == 2) { $prob = round($gen['cnt'] / 10 / 2); }   // 랜임시 2개(10%) 등장(200명중 20명 랜임시도?)
    elseif($vote == 3) { $prob = round($gen['cnt'] / 10 / 4); }   // 건국시 4개(20%) 등장(200명시 20국 정도 됨)

    if($prob < 3) { $prob = 3; }
    //아이템 습득 상황
    if(rand() % $prob == 0) {
        //셋중 선택
        $sel = rand() % 4;
        switch($sel) {
        case 0: $type = "weap"; break;
        case 1: $type = "book"; break;
        case 2: $type = "horse"; break;
        case 3: $type = "item"; break;
        }
        $query = "select no,{$type} from general where {$type}>6";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $count = MYDB_num_rows($result);
        if($count < 20) {
            for($i=0; $i < $count; $i++) {
                $gen = MYDB_fetch_array($result);
                $occupied[$gen[$type]] = 1;
            }
            for($i=7; $i <= 26; $i++) {
                if($occupied[$i] == 0) {
                    $item[count($item)] = $i;
                }
            }
            $it = $item[rand() % count($item)];

            $query = "update general set {$type}='$it' where no='{$general['no']}'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

            $query = "select name from nation where nation='{$general['nation']}'";
            $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            $nation = MYDB_fetch_array($result);

            if($nation['name'] == "") {
                $nation['name'] = "재야";
            }

            switch($sel) {
            case 0:
                $log[count($log)] = "<C>●</><C>".getWeapName($it)."</>(을)를 습득했습니다!";
                $alllog[0] = "<C>●</>{$game['month']}월:<Y>{$general['name']}</>(이)가 <C>".getWeapName($it)."</>(을)를 습득했습니다!";
                $general = addHistory($connect, $general, "<C>●</>{$game['year']}년 {$game['month']}월:<C>".getWeapName($it)."</>(을)를 습득");
                if($vote == 0) {
                    $history[0] = "<C>●</>{$game['year']}년 {$game['month']}월:<C><b>【아이템】</b></><D><b>{$nation['name']}</b></>의 <Y>{$general['name']}</>(이)가 <C>".getWeapName($it)."</>(을)를 습득했습니다!";
                } elseif($vote == 1) {
                    $history[0] = "<C>●</>{$game['year']}년 {$game['month']}월:<C><b>【설문상품】</b></><D><b>{$nation['name']}</b></>의 <Y>{$general['name']}</>(이)가 <C>".getWeapName($it)."</>(을)를 습득했습니다!";
                } elseif($vote == 2) {
                    $history[0] = "<C>●</>{$game['year']}년 {$game['month']}월:<C><b>【랜덤임관상품】</b></><D><b>{$nation['name']}</b></>의 <Y>{$general['name']}</>(이)가 <C>".getWeapName($it)."</>(을)를 습득했습니다!";
                } elseif($vote == 3) {
                    $history[0] = "<C>●</>{$game['year']}년 {$game['month']}월:<C><b>【건국상품】</b></><D><b>{$nation['name']}</b></>의 <Y>{$general['name']}</>(이)가 <C>".getWeapName($it)."</>(을)를 습득했습니다!";
                }
                break;
            case 1:
                $log[count($log)] = "<C>●</><C>".getBookName($it)."</>(을)를 습득했습니다!";
                $alllog[0] = "<C>●</>{$game['month']}월:<Y>{$general['name']}</>(이)가 <C>".getBookName($it)."</>(을)를 습득했습니다!";
                $general = addHistory($connect, $general, "<C>●</>{$game['year']}년 {$game['month']}월:<C>".getBookName($it)."</>(을)를 습득");
                if($vote == 0) {
                    $history[0] = "<C>●</>{$game['year']}년 {$game['month']}월:<C><b>【아이템】</b></><D><b>{$nation['name']}</b></>의 <Y>{$general['name']}</>(이)가 <C>".getBookName($it)."</>(을)를 습득했습니다!";
                } elseif($vote == 1) {
                    $history[0] = "<C>●</>{$game['year']}년 {$game['month']}월:<C><b>【설문상품】</b></><D><b>{$nation['name']}</b></>의 <Y>{$general['name']}</>(이)가 <C>".getBookName($it)."</>(을)를 습득했습니다!";
                } elseif($vote == 2) {
                    $history[0] = "<C>●</>{$game['year']}년 {$game['month']}월:<C><b>【랜덤임관상품】</b></><D><b>{$nation['name']}</b></>의 <Y>{$general['name']}</>(이)가 <C>".getBookName($it)."</>(을)를 습득했습니다!";
                } elseif($vote == 3) {
                    $history[0] = "<C>●</>{$game['year']}년 {$game['month']}월:<C><b>【건국상품】</b></><D><b>{$nation['name']}</b></>의 <Y>{$general['name']}</>(이)가 <C>".getBookName($it)."</>(을)를 습득했습니다!";
                }
                break;
            case 2:
                $log[count($log)] = "<C>●</><C>".getHorseName($it)."</>(을)를 습득했습니다!";
                $alllog[0] = "<C>●</>{$game['month']}월:<Y>{$general['name']}</>(이)가 <C>".getHorseName($it)."</>(을)를 습득했습니다!";
                $general = addHistory($connect, $general, "<C>●</>{$game['year']}년 {$game['month']}월:<C>".getHorseName($it)."</>(을)를 습득");
                if($vote == 0) {
                    $history[0] = "<C>●</>{$game['year']}년 {$game['month']}월:<C><b>【아이템】</b></><D><b>{$nation['name']}</b></>의 <Y>{$general['name']}</>(이)가 <C>".getHorseName($it)."</>(을)를 습득했습니다!";
                } elseif($vote == 1) {
                    $history[0] = "<C>●</>{$game['year']}년 {$game['month']}월:<C><b>【설문상품】</b></><D><b>{$nation['name']}</b></>의 <Y>{$general['name']}</>(이)가 <C>".getHorseName($it)."</>(을)를 습득했습니다!";
                } elseif($vote == 2) {
                    $history[0] = "<C>●</>{$game['year']}년 {$game['month']}월:<C><b>【랜덤임관상품】</b></><D><b>{$nation['name']}</b></>의 <Y>{$general['name']}</>(이)가 <C>".getHorseName($it)."</>(을)를 습득했습니다!";
                } elseif($vote == 3) {
                    $history[0] = "<C>●</>{$game['year']}년 {$game['month']}월:<C><b>【건국상품】</b></><D><b>{$nation['name']}</b></>의 <Y>{$general['name']}</>(이)가 <C>".getHorseName($it)."</>(을)를 습득했습니다!";
                }
                break;
            case 3:
                $log[count($log)] = "<C>●</><C>".getItemName($it)."</>(을)를 습득했습니다!";
                $alllog[0] = "<C>●</>{$game['month']}월:<Y>{$general['name']}</>(이)가 <C>".getItemName($it)."</>(을)를 습득했습니다!";
                $general = addHistory($connect, $general, "<C>●</>{$game['year']}년 {$game['month']}월:<C>".getItemName($it)."</>(을)를 습득");
                if($vote == 0) {
                    $history[0] = "<C>●</>{$game['year']}년 {$game['month']}월:<C><b>【아이템】</b></><D><b>{$nation['name']}</b></>의 <Y>{$general['name']}</>(이)가 <C>".getItemName($it)."</>(을)를 습득했습니다!";
                } elseif($vote == 1) {
                    $history[0] = "<C>●</>{$game['year']}년 {$game['month']}월:<C><b>【설문상품】</b></><D><b>{$nation['name']}</b></>의 <Y>{$general['name']}</>(이)가 <C>".getItemName($it)."</>(을)를 습득했습니다!";
                } elseif($vote == 2) {
                    $history[0] = "<C>●</>{$game['year']}년 {$game['month']}월:<C><b>【랜덤임관상품】</b></><D><b>{$nation['name']}</b></>의 <Y>{$general['name']}</>(이)가 <C>".getItemName($it)."</>(을)를 습득했습니다!";
                } elseif($vote == 3) {
                    $history[0] = "<C>●</>{$game['year']}년 {$game['month']}월:<C><b>【건국상품】</b></><D><b>{$nation['name']}</b></>의 <Y>{$general['name']}</>(이)가 <C>".getItemName($it)."</>(을)를 습득했습니다!";
                }
                break;
            }
            pushAllLog($alllog);
            pushHistory($connect, $history);
        }
    }
    return $log;
}

function checkAbility($connect, $general, $log) {
    global $_upgradeLimit;

    $limit = $_upgradeLimit;

    $query = "select no,leader,leader2,power,power2,intel,intel2 from general where no='{$general['no']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $general = MYDB_fetch_array($result);

    if($general['leader2'] < 0) {
        $query = "update general set leader2='$limit'+leader2,leader=leader-1 where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        $log[count($log)] = "<C>●</><R>통솔</>이 <C>1</> 떨어졌습니다!";
    } elseif($general['leader2'] >= $limit) {
        $query = "update general set leader2=leader2-'$limit',leader=leader+1 where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        $log[count($log)] = "<C>●</><Y>통솔</>이 <C>1</> 올랐습니다!";
    }

    if($general['power2'] < 0) {
        $query = "update general set power2='$limit'+power2,power=power-1 where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        $log[count($log)] = "<C>●</><R>무력</>이 <C>1</> 떨어졌습니다!";
    } elseif($general['power2'] >= $limit) {
        $query = "update general set power2=power2-'$limit',power=power+1 where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        $log[count($log)] = "<C>●</><Y>무력</>이 <C>1</> 올랐습니다!";
    }

    if($general['intel2'] < 0) {
        $query = "update general set intel2='$limit'+intel2,intel=intel-1 where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        $log[count($log)] = "<C>●</><R>지력</>이 <C>1</> 떨어졌습니다!";
    } elseif($general['intel2'] >= $limit) {
        $query = "update general set intel2=intel2-'$limit',intel=intel+1 where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        $log[count($log)] = "<C>●</><Y>지력</>이 <C>1</> 올랐습니다!";
    }

    return $log;
}

function checkDedication($connect, $general, $log) {
    $dedlevel = getDedLevel($general['dedication']);

    $query = "update general set dedlevel='$dedlevel' where no='{$general['no']}'";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

    // 승급했다면
    if($general['dedlevel'] < $dedlevel) {
        $log[count($log)] = "<C>●</><Y>".getDed($general['dedication'])."</>(으)로 <C>승급</>하여 봉록이 <C>".getBill($general['dedication'])."</>(으)로 <C>상승</>했습니다!";
    // 강등했다면
    } elseif($general['dedlevel'] > $dedlevel) {
        $log[count($log)] = "<C>●</><Y>".getDed($general['dedication'])."</>(으)로 <R>강등</>되어 봉록이 <C>".getBill($general['dedication'])."</>(으)로 <R>하락</>했습니다!";
    }

    return $log;
}

function checkExperience($connect, $general, $log) {
    $explevel = getExpLevel($general['experience']);

    $query = "update general set explevel='$explevel' where no='{$general['no']}'";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

    // 승급했다면
    if($general['explevel'] < $explevel) {
        $log[count($log)] = "<C>●</><C>Lv $explevel</>로 <C>레벨업</>!";
    // 강등했다면
    } elseif($general['explevel'] > $explevel) {
        $log[count($log)] = "<C>●</><C>Lv $explevel</>로 <R>레벨다운</>!";
    }

    return $log;
}

//1월마다 실행
function processSpring($connect) {
    //인구 증가
    popIncrease($connect);
    // 1월엔 무조건 내정 1% 감소
    $query = "update city set dead=0,agri=agri*0.99,comm=comm*0.99,secu=secu*0.99,def=def*0.99,wall=wall*0.99";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

    // 유지비 3% 거상 1.5%
    $query = "update general set gold=gold*0.97 where gold>10000 and special!=30";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $query = "update general set gold=gold*0.985 where gold>10000 and special=30";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    // 유지비 1% 거상 0.5%
    $query = "update general set gold=gold*0.99 where gold>1000 and gold<=10000 and special!=30";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $query = "update general set gold=gold*0.995 where gold>1000 and gold<=10000 and special=30";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    // 유지비 5%
    $query = "update nation set gold=gold*0.95 where gold>100000";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    // 유지비 3%
    $query = "update nation set gold=gold*0.97 where gold>10000 and gold<=100000";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    // 유지비 1%
    $query = "update nation set gold=gold*0.99 where gold>1000 and gold<=10000";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

    $query = "select year,month from game where no='1'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $admin = MYDB_fetch_array($result);

    $history[0] = "<R>★</>{$admin['year']}년 {$admin['month']}월: <S>모두들 즐거운 게임 하고 계신가요? ^^ <Y>삼국일보</> 애독해 주시고, <M>훼접</>은 삼가주세요~</>";
    pushHistory($connect, $history);
}

function processGoldIncome($connect) {
    global $_basegold;

    $query = "select year,month,gold_rate from game where no='1'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $admin = MYDB_fetch_array($result);

    $query = "select name,nation,gold,rate_tmp,bill,type from nation";
    $nationresult = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $nationcount = MYDB_num_rows($nationresult);

    //국가별 처리
    for($i=0; $i < $nationcount; $i++) {
        $nation = MYDB_fetch_array($nationresult);

        $incomeList = getGoldIncome($connect, $nation['nation'], $nation['rate_tmp'], $admin['gold_rate'], $nation['type']);
        $income = $incomeList[0] + $incomeList[1];
        $originoutcome = getGoldOutcome($connect, $nation['nation'], 100);    // 100%의 지급량
        $outcome = round($originoutcome * $nation['bill'] / 100);   // 지급량에 따른 요구량
        // 실제 지급량 계산
        $nation['gold'] += $income;
        // 기본량도 안될경우
        if($nation['gold'] < $_basegold) {
            $realoutcome = 0;
            // 실지급율
            $ratio = 0;
        //기본량은 넘지만 요구량이 안될경우
        } elseif($nation['gold'] - $_basegold < $outcome) {
            $realoutcome = $nation['gold'] - $_basegold;
            $nation['gold'] = $_basegold;
            // 실지급율
            $ratio = $realoutcome / $originoutcome;
        } else {
            $realoutcome = $outcome;
            $nation['gold'] -= $realoutcome;
            // 실지급율
            $ratio = $realoutcome / $originoutcome;
        }
        $adminLog[count($adminLog)] = _String::Fill2($nation['name'],12," ")." // 세금 : "._String::Fill2($income,6," ")." // 세출 : "._String::Fill2($originoutcome,6," ")." // 실제 : ".tab2($realoutcome,6," ")." // 지급율 : ".tab2(round($ratio*100,2),5," ")." % // 결과금 : ".tab2($nation['gold'],6," ");

        $query = "select no,name,nation from general where nation='{$nation['nation']}' and level>='9'";
        $coreresult = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $corecount = MYDB_num_rows($coreresult);
        $corelog[0] = "<C>●</>이번 수입은 금 <C>$income</>입니다.";
        for($j=0; $j < $corecount; $j++) {
            $coregen = MYDB_fetch_array($coreresult);
            pushGenLog($coregen, $corelog);
        }

        $query = "update nation set gold='{$nation['gold']}' where nation='{$nation['nation']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        $query = "select no,name,nation,dedication,gold from general where nation='{$nation['nation']}'";
        $genresult = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $gencount = MYDB_num_rows($genresult);

        // 각 장수들에게 지급
        for($j=0; $j < $gencount; $j++) {
            $general = MYDB_fetch_array($genresult);
            $gold = round(getBill($general['dedication'])*$ratio);
            $general['gold'] += $gold;

            $query = "update general set gold='{$general['gold']}' where no='{$general['no']}'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

            $log[0] = "<C>●</>봉급으로 금 <C>$gold</>을 받았습니다.";
            pushGenLog($general, $log);
        }
    }

    $history[0] = "<C>●</>{$admin['year']}년 {$admin['month']}월:<W><b>【지급】</b></>봄이 되어 봉록에 따라 자금이 지급됩니다.";
    pushHistory($connect, $history);
    pushAdminLog($connect, $adminLog);
}

function popIncrease($connect) {
    $query = "select nation,rate_tmp,type from nation";
    $nationresult = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $nationcount = MYDB_num_rows($nationresult);

    for($i=0; $i < $nationcount; $i++) {
        $nation = MYDB_fetch_array($nationresult);
        $rate[$nation['nation']] = $nation['rate_tmp'];
        $type[$nation['nation']] = $nation['type'];
    }

    $query = "select * from city where supply='1'"; // 도시 목록
    $cityresult = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $citycount = MYDB_num_rows($cityresult);

    // 인구 및 민심
    for($i=0; $i < $citycount; $i++) {
        $city = MYDB_fetch_array($cityresult);

        $pop = $city['pop'];
        if($city['nation'] == 0) {
            $pop = $city['pop'];  // 공백지는 증가하지 않게
            $cityrate = 50;

            $ratio = 0.99;   // 공백지는 수비 빼고 약간씩 감소
            $agri = floor($city['agri'] * $ratio);
            $comm = floor($city['comm'] * $ratio);
            $secu = floor($city['secu'] * $ratio);
            $def  = $city['def'];
            $wall = $city['wall'];
        } else {
            $ratio = (20 - $rate[$city['nation']])/200;  // 20일때 0% 0일때 10% 100일때 -40%
            $agri = $city['agri'] + floor($city['agri'] * $ratio);  //내정도 증감
            $comm = $city['comm'] + floor($city['comm'] * $ratio);
            $secu = $city['secu'] + floor($city['secu'] * $ratio);
            $def  = $city['def']  + floor($city['def']  * $ratio);
            $wall = $city['wall'] + floor($city['wall'] * $ratio);
            $ratio = (30 - $rate[$city['nation']])/200;  // 20일때 5% 5일때 12.5% 50일때 -10%
            if($ratio >= 0) {
                // 국가보정
                if($type[$city['nation']] == 4 || $type[$city['nation']] == 6 || $type[$city['nation']] == 7 || $type[$city['nation']] == 8 || $type[$city['nation']] == 12 || $type[$city['nation']] == 13) { $ratio *= 1.2; }
                if($type[$city['nation']] == 1 || $type[$city['nation']] == 3) { $ratio *= 0.8; }
                $ratio *= (1 + $city['secu']/$city[secu2]/10);    //치안에 따라 최대 10% 추가
            } else {
                // 국가보정
                if($type[$city['nation']] == 4 || $type[$city['nation']] == 6 || $type[$city['nation']] == 7 || $type[$city['nation']] == 8 || $type[$city['nation']] == 12 || $type[$city['nation']] == 13) { $ratio *= 0.8; }
                if($type[$city['nation']] == 1 || $type[$city['nation']] == 3) { $ratio *= 1.2; }
                $ratio *= (1 - $city['secu']/$city[secu2]/10);    //치안에 따라 최대 10% 경감
            }

            $pop = $city['pop'] + floor($city['pop'] * $ratio) + 5000;  // 기본 5000명은 증가

            $ratio = round($ratio*100, 2);
            $cityrate = $city['rate'];
            $cityrate = $cityrate + (20 - $rate[$city['nation']]);
            if($cityrate > 100) { $cityrate = 100; }
            if($cityrate < 0) { $cityrate = 0; }
        }
        if($pop > $city['pop2']) { $pop = $city['pop2']; }
        if($pop < 0) { $pop = 0; }
        if($agri > $city['agri2']) { $agri = $city['agri2']; }
        if($comm > $city['comm2']) { $comm = $city['comm2']; }
        if($secu > $city['secu2']) { $secu = $city['secu2']; }
        if($def > $city['def2']) { $def= $city['def2']; }
        if($wall > $city['wall2']) { $wall = $city['wall2']; }

        //시세
        $query = "update city set pop='$pop',rate='$cityrate',agri='$agri',comm='$comm',secu='$secu',def='$def',wall='$wall' where city='{$city['city']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    }
}

function getGoldIncome($connect, $nationNo, $rate, $admin_rate, $type) {
    $query = "select no,city from general where nation='$nationNo' and level=4"; // 태수
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $count = MYDB_num_rows($result);
    for($j=0; $j < $count; $j++) {
        $gen = MYDB_fetch_array($result);
        $level4[$gen['no']] = $gen['city'];
    }
    $query = "select no,city from general where nation='$nationNo' and level=3"; // 군사
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $count = MYDB_num_rows($result);
    for($j=0; $j < $count; $j++) {
        $gen = MYDB_fetch_array($result);
        $level3[$gen['no']] = $gen['city'];
    }
    $query = "select no,city from general where nation='$nationNo' and level=2"; // 시중
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $count = MYDB_num_rows($result);
    for($j=0; $j < $count; $j++) {
        $gen = MYDB_fetch_array($result);
        $level2[$gen['no']] = $gen['city'];
    }

    $query = "select capital,level from nation where nation='$nationNo'"; // 수도
    $cityresult = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $nation = MYDB_fetch_array($cityresult);

    $query = "select * from city where nation='$nationNo' and supply='1'"; // 도시 목록
    $cityresult = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $citycount = MYDB_num_rows($cityresult);

    //총 수입 구함
    $income[0] = 0;    $income[1] = 0;  // income[0] : 세수, income[1] : 수비병 세수
    for($j=0; $j < $citycount; $j++) {
        $city = MYDB_fetch_array($cityresult);

        //민충 0~100 : 50~100 수입
        $ratio = $city['rate'] / 2 + 50;
        $tax1 = ($city['pop'] * $city['comm'] / $city['comm2'] * $ratio / 1000) / 3;
        $tax1 *= (1 + $city['secu']/$city['secu2']/10);    //치안에 따라 최대 10% 추가
        //도시 관직 추가 세수
        if($level4[$city['gen1']] == $city['city']) { $tax1 *= 1.05;  }
        if($level3[$city['gen2']] == $city['city']) { $tax1 *= 1.05;  }
        if($level2[$city['gen3']] == $city['city']) { $tax1 *= 1.05;  }
        //수도 추가 세수 130%~105%
        if($city['city'] == $nation['capital']) { $tax1 *= 1+(1/3/$nation['level']); };

        $income[0] += $tax1;
    }
    $income[0] *= ($rate / 20);

    // 국가보정
    if($type == 1)                                              { $income[0] *= 1.1; $income[1] *= 1.1; }
    if($type == 9 || $type == 10 || $type == 11)                { $income[0] *= 0.9; $income[1] *= 0.9; }

    $income[0] = round($income[0] * ($admin_rate/100));
    $income[1] = round($income[1] * ($admin_rate/100));

    return $income;
}

function processDeadIncome($connect, $admin_rate) {
    $query = "select nation,type from nation where level>0";  // 도시 가진 국가
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $nationCount = MYDB_num_rows($result);

    for($i=0; $i < $nationCount; $i++) {
        $nation = MYDB_fetch_array($result);

        $income = getDeadIncome($connect, $nation['nation'], $nation['type'], $admin_rate);

//  단기수입 금만적용
//        $query = "update nation set gold=gold+'$income',rice=rice+'$income' where nation='{$nation['nation']}'";
        $query = "update nation set gold=gold+'$income' where nation='{$nation['nation']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    }
    // 10%수입, 20%부상병
    $query = "update city set pop=pop+dead*0.2,dead='0'";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
}

function getDeadIncome($connect, $nation, $type, $admin_rate) {
    $query = "select dead from city where nation='$nation' and dead>'0' and supply='1'"; // 도시 목록
    $cityResult = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $cityCount = MYDB_num_rows($cityResult);

    $income = 0;    // 단기수입
    if($cityCount > 0) {
        for($k=0; $k < $cityCount; $k++) {
            $city = MYDB_fetch_array($cityResult);

            $income += $city['dead'];
        }
        $income /= 10;

        // 국가보정
        if($type == 1)                { $income *= 1.1; }
        if($type == 9 || $type == 10) { $income *= 0.9; }

        $income = round($income * $admin_rate / 100);
    }
    return $income;
}

function getGoldOutcome($connect, $nation, $bill) {
    $query = "select dedication from general where nation='$nation'"; // 장수 목록
    $genresult = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $gencount = MYDB_num_rows($genresult);

    //총 지출 구함
    $outcome = 0;
    for($j=0; $j < $gencount; $j++) {
        $general = MYDB_fetch_array($genresult);
        $outcome += getBill($general['dedication']);
    }

    $outcome = round($outcome * $bill / 100);

    return $outcome;
}

//7월마다 실행
function processFall($connect) {
    //인구 증가
    popIncrease($connect);
    // 7월엔 무조건 내정 1% 감소
    $query = "update city set dead=0,agri=agri*0.99,comm=comm*0.99,secu=secu*0.99,def=def*0.99,wall=wall*0.99";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

    // 유지비 3% 거상 1.5%
    $query = "update general set rice=rice*0.97 where rice>10000 and special!=30";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $query = "update general set rice=rice*0.985 where rice>10000 and special=30";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    // 유지비 1% 거상 0.5%
    $query = "update general set rice=rice*0.99 where rice>1000 and rice<=10000 and special!=30";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $query = "update general set rice=rice*0.995 where rice>1000 and rice<=10000 and special=30";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    // 유지비 5%
    $query = "update nation set rice=rice*0.95 where rice>100000";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    // 유지비 3%
    $query = "update nation set rice=rice*0.97 where rice>10000 and rice<=100000";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    // 유지비 1%
    $query = "update nation set rice=rice*0.99 where rice>2000 and rice<=10000";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
}

function processRiceIncome($connect) {
    global $_baserice;

    $query = "select year,month,rice_rate from game where no='1'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $admin = MYDB_fetch_array($result);

    $query = "select name,nation,rice,rate_tmp,bill,type from nation";
    $nationresult = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $nationcount = MYDB_num_rows($nationresult);

    //국가별 처리
    for($i=0; $i < $nationcount; $i++) {
        $nation = MYDB_fetch_array($nationresult);

        $incomeList = getRiceIncome($connect, $nation['nation'], $nation['rate_tmp'], $admin['rice_rate'], $nation['type']);
        $income = $incomeList[0] + $incomeList[1];
        $originoutcome = getRiceOutcome($connect, $nation['nation'], 100);    // 100%의 지급량
        $outcome = round($originoutcome * $nation['bill'] / 100);   // 지급량에 따른 요구량

        // 실제 지급량 계산
        $nation['rice'] += $income;
        // 기본량도 안될경우
        if($nation['rice'] < $_baserice) {
            $realoutcome = 0;
            // 실지급율
            $ratio = 0;
        //기본량은 넘지만 요구량이 안될경우
        } elseif($nation['rice'] - $_baserice < $outcome) {
            $realoutcome = $nation['rice'] - $_baserice;
            $nation['rice'] = $_baserice;
            // 실지급율
            $ratio = $realoutcome / $originoutcome;
        } else {
            $realoutcome = $outcome;
            $nation['rice'] -= $realoutcome;
            // 실지급율
            $ratio = $realoutcome / $originoutcome;
        }
        $adminLog[count($adminLog)] = _String::Fill2($nation['name'],12," ")." // 세곡 : "._String::Fill2($income,6," ")." // 세출 : "._String::Fill2($originoutcome,6," ")." // 실제 : ".tab2($realoutcome,6," ")." // 지급율 : ".tab2(round($ratio*100,2),5," ")." % // 결과곡 : ".tab2($nation['rice'],6," ");

        $query = "select no,name,nation from general where nation='{$nation['nation']}' and level>='9'";
        $coreresult = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $corecount = MYDB_num_rows($coreresult);
        $corelog[0] = "<C>●</>이번 수입은 쌀 <C>$income</>입니다.";
        for($j=0; $j < $corecount; $j++) {
            $coregen = MYDB_fetch_array($coreresult);
            pushGenLog($coregen, $corelog);
        }

        $query = "update nation set rice='{$nation['rice']}' where nation='{$nation['nation']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

        $query = "select no,name,nation,dedication,rice from general where nation='{$nation['nation']}'";
        $genresult = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $gencount = MYDB_num_rows($genresult);

        // 각 장수들에게 지급
        for($j=0; $j < $gencount; $j++) {
            $general = MYDB_fetch_array($genresult);
            $rice = round(getBill($general['dedication'])*$ratio);
            $general['rice'] += $rice;

            $query = "update general set rice='{$general['rice']}' where no='{$general['no']}'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

            $log[0] = "<C>●</>봉급으로 쌀 <C>$rice</>을 받았습니다.";
            pushGenLog($general, $log);
        }
    }

    $history[0] = "<C>●</>{$admin['year']}년 {$admin['month']}월:<W><b>【지급】</b></>가을이 되어 봉록에 따라 군량이 지급됩니다.";
    pushHistory($connect, $history);
    pushAdminLog($connect, $adminLog);
}

function getRiceIncome($connect, $nationNo, $rate, $admin_rate, $type) {
    $query = "select no,city from general where nation='$nationNo' and level=4"; // 태수
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $count = MYDB_num_rows($result);
    for($j=0; $j < $count; $j++) {
        $gen = MYDB_fetch_array($result);
        $level4[$gen['no']] = $gen['city'];
    }
    $query = "select no,city from general where nation='$nationNo' and level=3"; // 군사
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $count = MYDB_num_rows($result);
    for($j=0; $j < $count; $j++) {
        $gen = MYDB_fetch_array($result);
        $level3[$gen['no']] = $gen['city'];
    }
    $query = "select no,city from general where nation='$nationNo' and level=2"; // 시중
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $count = MYDB_num_rows($result);
    for($j=0; $j < $count; $j++) {
        $gen = MYDB_fetch_array($result);
        $level2[$gen['no']] = $gen['city'];
    }

    $query = "select capital,level from nation where nation='$nationNo'"; // 수도
    $cityresult = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $nation = MYDB_fetch_array($cityresult);

    $query = "select * from city where nation='$nationNo' and supply='1'"; // 도시 목록
    $cityresult = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $citycount = MYDB_num_rows($cityresult);

    //총 수입 구함
    $income[0] = 0;    $income[1] = 0;  // income[0] : 세수, income[1] : 수비병 세수
    for($j=0; $j < $citycount; $j++) {
        $city = MYDB_fetch_array($cityresult);

        //민충 0~100 : 50~100 수입
        $ratio = $city['rate'] / 2 + 50;
        $tax1 = ($city['pop'] * $city['agri'] / $city[agri2] * $ratio / 1000) / 3;
        $tax2 = $city['def'] * $city['wall'] / $city[wall2] / 3;
        $tax1 *= (1 + $city['secu']/$city[secu2]/10);    //치안에 따라 최대 10% 추가
        $tax2 *= (1 + $city['secu']/$city[secu2]/10);    //치안에 따라 최대 10% 추가
        //도시 관직 추가 세수
        if($level4[$city[gen1]] == $city['city']) { $tax1 *= 1.05; $tax2 *= 1.05; }
        if($level3[$city[gen2]] == $city['city']) { $tax1 *= 1.05; $tax2 *= 1.05; }
        if($level2[$city[gen3]] == $city['city']) { $tax1 *= 1.05; $tax2 *= 1.05; }
        //수도 추가 세수 130%~105%
        if($city['city'] == $nation['capital']) { $tax1 *= 1+(1/3/$nation['level']); $tax2 *= 1+(1/3/$nation['level']); }
        $income[0] += $tax1;
        $income[1] += $tax2;
    }
    $income[0] *= ($rate / 20);

    // 국가보정
    if($type == 8)                              { $income[0] *= 1.1; $income[1] *= 1.1; }
    if($type == 2 || $type == 4 || $type == 13) { $income[0] *= 0.9; $income[1] *= 0.9; }

    $income[0] = round($income[0] * ($admin_rate/100));
    $income[1] = round($income[1] * ($admin_rate/100));

    return $income;
}

function getRiceOutcome($connect, $nation, $bill) {
    $query = "select dedication from general where nation='$nation'"; // 장수 목록
    $genresult = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $gencount = MYDB_num_rows($genresult);

    //총 지출 구함
    $outcome = 0;
    for($j=0; $j < $gencount; $j++) {
        $general = MYDB_fetch_array($genresult);
        $outcome += getBill($general['dedication']);
    }

    $outcome = round($outcome * $bill / 100);

    return $outcome;
}

function tradeRate($connect) {
    $query = "select city,level,trade from city"; // 도시 목록
    $cityresult = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $citycount = MYDB_num_rows($cityresult);

    for($i=0; $i < $citycount; $i++) {
        $city = MYDB_fetch_array($cityresult);
        //시세
        switch($city['level']) {
        case 1: $per =   0; break;
        case 2: $per =   0; break;
        case 3: $per =   0; break;
        case 4: $per =  20; break;
        case 5: $per =  40; break;
        case 6: $per =  60; break;
        case 7: $per =  80; break;
        case 8: $per = 100; break;
        default:$per =   0; break;
        }
        if($per > rand()%100) {
            $trade = rand() % 11 + 95;
        } else {
            $trade = 0;
        }
        $query = "update city set trade='$trade' where city='{$city['city']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    }
}

function disaster($connect) {
    $query = "select startyear,year,month from game where no='1'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $admin = MYDB_fetch_array($result);

    //재난표시 초기화
    $query = "update city set state=0 where state<=10";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

    // 초반 3년은 스킵
    if($admin['startyear'] + 3 > $admin['year']) return;
    
    $query = "select city,name,secu,secu2 from city"; // 도시 목록
    $cityresult = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $citycount = MYDB_num_rows($cityresult);

    $disastertype = rand() % 4;
    $isgood = 0;
    if($admin['month'] == 4 && $disastertype == 3) { $isgood = 1; }
    if($admin['month'] == 7 && $disastertype == 3) { $isgood = 1; }

    for($i=0; $i < $citycount; $i++) {
        $city = MYDB_fetch_array($cityresult);
        //호황 발생 도시 선택 ( 기본 3% 이므로 약 3개 도시 )
        //재해 발생 도시 선택 ( 기본 6% 이므로 약 6개 도시 )
        if($isgood == 1) { $ratio = 3 + round(1.0*$city['secu']/$city[secu2]*3); }    // 3 ~ 6%
        else { $ratio = 6 - round(1.0*$city['secu']/$city[secu2]*3); }    // 3 ~ 6%

        if(rand()%100+1 < $ratio) {
            $disastercity[count($disastercity)] = $city['city'];
            $disasterratio[count($disastercity)] = 1.0 * $city['secu'] / $city[secu2];
            $disastername .= $city['name']." ";
        }
    }

    $disastername = "<G><b>{$disastername}</b></>";

    //재해 처리
    if(count($disastercity)) {
        $state = 0;
        switch($admin['month']) {
        //봄
        case 1:
            switch($disastertype) {
            case 0:
                $disaster[count($disaster)] = "<C>●</>{$admin['year']}년 {$admin['month']}월:<M><b>【재난】</b></>{$disastername}에 역병이 발생하여 도시가 황폐해지고 있습니다.";
                $state = 4;
                break;
            case 1:
                $disaster[count($disaster)] = "<C>●</>{$admin['year']}년 {$admin['month']}월:<M><b>【재난】</b></>{$disastername}에 지진으로 피해가 속출하고 있습니다.";
                $state = 5;
                break;
            case 2:
                $disaster[count($disaster)] = "<C>●</>{$admin['year']}년 {$admin['month']}월:<M><b>【재난】</b></>{$disastername}에 추위가 풀리지 않아 얼어죽는 백성들이 늘어나고 있습니다.";
                $state = 3;
                break;
            case 3:
                $disaster[count($disaster)] = "<C>●</>{$admin['year']}년 {$admin['month']}월:<M><b>【재난】</b></>{$disastername}에 황건적이 출현해 도시를 습격하고 있습니다.";
                $state = 9;
                break;
            }
            break;
        //여름
        case 4:
            switch($disastertype) {
            case 0:
                $disaster[count($disaster)] = "<C>●</>{$admin['year']}년 {$admin['month']}월:<M><b>【재난】</b></>{$disastername}에 홍수로 인해 피해가 급증하고 있습니다.";
                $state = 7;
                break;
            case 1:
                $disaster[count($disaster)] = "<C>●</>{$admin['year']}년 {$admin['month']}월:<M><b>【재난】</b></>{$disastername}에 지진으로 피해가 속출하고 있습니다.";
                $state = 5;
                break;
            case 2:
                $disaster[count($disaster)] = "<C>●</>{$admin['year']}년 {$admin['month']}월:<M><b>【재난】</b></>{$disastername}에 태풍으로 인해 피해가 속출하고 있습니다.";
                $state = 6;
                break;
            case 3:
                $disaster[count($disaster)] = "<C>●</>{$admin['year']}년 {$admin['month']}월:<C><b>【호황】</b></>{$disastername}에 호황으로 도시가 번창하고 있습니다.";
                $state = 2;
                $isGood = 1;
                break;
            }
            break;
        //가을
        case 7:
            switch($disastertype) {
            case 0:
                $disaster[count($disaster)] = "<C>●</>{$admin['year']}년 {$admin['month']}월:<M><b>【재난】</b></>{$disastername}에 메뚜기 떼가 발생하여 도시가 황폐해지고 있습니다.";
                $state = 8;
                break;
            case 1:
                $disaster[count($disaster)] = "<C>●</>{$admin['year']}년 {$admin['month']}월:<M><b>【재난】</b></>{$disastername}에 지진으로 피해가 속출하고 있습니다.";
                $state = 5;
                break;
            case 2:
                $disaster[count($disaster)] = "<C>●</>{$admin['year']}년 {$admin['month']}월:<M><b>【재난】</b></>{$disastername}에 흉년이 들어 굶어죽는 백성들이 늘어나고 있습니다.";
                $state = 8;
                break;
            case 3:
                $disaster[count($disaster)] = "<C>●</>{$admin['year']}년 {$admin['month']}월:<C><b>【풍작】</b></>{$disastername}에 풍작으로 도시가 번창하고 있습니다.";
                $state = 1;
                $isGood = 1;
                break;
            }
            break;
        //겨울
        case 10:
            switch($disastertype) {
            case 0:
                $disaster[count($disaster)] = "<C>●</>{$admin['year']}년 {$admin['month']}월:<M><b>【재난】</b></>{$disastername}에 혹한으로 도시가 황폐해지고 있습니다.";
                $state = 3;
                break;
            case 1:
                $disaster[count($disaster)] = "<C>●</>{$admin['year']}년 {$admin['month']}월:<M><b>【재난】</b></>{$disastername}에 지진으로 피해가 속출하고 있습니다.";
                $state = 5;
                break;
            case 2:
                $disaster[count($disaster)] = "<C>●</>{$admin['year']}년 {$admin['month']}월:<M><b>【재난】</b></>{$disastername}에 눈이 많이 쌓여 도시가 황폐해지고 있습니다.";
                $state = 3;
                break;
            case 3:
                $disaster[count($disaster)] = "<C>●</>{$admin['year']}년 {$admin['month']}월:<M><b>【재난】</b></>{$disastername}에 황건적이 출현해 도시를 습격하고 있습니다.";
                $state = 9;
                break;
            }
            break;
        }
        
        if($isgood == 0) {
            for($i=0; $i < count($disastercity); $i++) {
                $ratio = 15 * $disasterratio[$i];
                $ratio = (80 + $ratio) / 100.0; // 치안률 따라서 80~95%
        
                $query = "update city set state='$state',pop=pop*{$ratio},rate=rate*{$ratio},agri=agri*{$ratio},comm=comm*{$ratio},secu=secu*{$ratio},def=def*{$ratio},wall=wall*{$ratio} where city='$disastercity[$i]'";
                MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        
                TrickInjury($connect, $disastercity[$i], 1);
            }
        } else {
            for($i=0; $i < count($disastercity); $i++) {
                $ratio = 4 * $disasterratio[$i];
                $ratio = (101 + $ratio) / 100.0; // 치안률 따라서 101~105%
        
                $city = getCity($connect, $disastercity[$i]);
                $city['pop'] *= $ratio;   $city['rate'] *= $ratio;  $city['agri'] *= $ratio;
                $city['comm'] *= $ratio;  $city['secu'] *= $ratio;  $city['def'] *= $ratio;
                $city['wall'] *= $ratio;
        
                if($city['pop'] > $city[pop2]) { $city['pop'] = $city[pop2]; }
                if($city['rate'] > 100) { $city['rate'] = 100; }
                if($city['agri'] > $city[agri2]) { $city['agri'] = $city[agri2]; }
                if($city['comm'] > $city[comm2]) { $city['comm'] = $city[comm2]; }
                if($city['secu'] > $city[secu2]) { $city['secu'] = $city[secu2]; }
                if($city['def'] > $city[def2]) { $city['def'] = $city[def2]; }
                if($city['wall'] > $city[wall2]) { $city['wall'] = $city[wall2]; }
        
                $query = "update city set state='$state',pop='{$city['pop']}',rate='{$city['rate']}',agri='{$city['agri']}',comm='{$city['comm']}',secu='{$city['secu']}',def='{$city['def']}',wall='{$city['wall']}' where city='$disastercity[$i]'";
                MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            }
        }
    }

    pushHistory($connect, $disaster);
}

function getAdmin($connect) {
    $query = "select * from game where no='1'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $admin = MYDB_fetch_array($result);

    return $admin;
}

function getMe($connect) {
    $query = "select * from general where user_id='{$_SESSION['p_id']}'";
    $result = MYDB_query($query, $connect) or Error("접속자가 많아 접속을 중단합니다. 잠시후 갱신해주세요.<br>getMe : ".MYDB_error($connect),"");
    $me = MYDB_fetch_array($result);

    return $me;
}

function getTroop($connect, $troop) {
    $query = "select * from troop where troop='$troop'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $troop = MYDB_fetch_array($result);

    return $troop;
}

function getCity($connect, $city, $sel="*") {
    $query = "select {$sel} from city where city='$city'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $city = MYDB_fetch_array($result);

    return $city;
}

function getNation($connect, $nation) {
    $query = "select * from nation where nation='$nation'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $nation = MYDB_fetch_array($result);

    return $nation;
}

function newColor($color) {
    switch($color) {
        case "":
        case "330000":
        case "FF0000":
        case "800000":
        case "A0522D":
        case "FF6347":
        case "808000":
        case "008000":
        case "2E8B57":
        case "008080":
        case "6495ED":
        case "0000FF":
        case "000080":
        case "483D8B":
        case "7B68EE":
        case "800080":
        case "A9A9A9":
        case "000000":
            $color = "FFFFFF"; break;
        default:
            $color = "000000"; break;
    }
    return $color;
}

function backColor($color) {
    return newColor($color);
}

function ConvertLog($str, $type=1) {
    if($type > 0) {
        $str = str_replace("<1>", "<font size=1>", $str);
        $str = str_replace("<Y1>", "<font size=1 color=yellow>", $str);
        $str = str_replace("<R>", "<font color=red>", $str);
        $str = str_replace("<B>", "<font color=blue>", $str);
        $str = str_replace("<G>", "<font color=green>", $str);
        $str = str_replace("<M>", "<font color=magenta>", $str);
        $str = str_replace("<C>", "<font color=cyan>", $str);
        $str = str_replace("<L>", "<font color=limegreen>", $str);
        $str = str_replace("<S>", "<font color=skyblue>", $str);
        //$str = str_replace("<O>", "<font color=orange>", $str);
        //$str = str_replace("<D>", "<font color=darkorange>", $str);
        $str = str_replace("<O>", "<font color=orangered>", $str);
        $str = str_replace("<D>", "<font color=orangered>", $str);
        $str = str_replace("<Y>", "<font color=yellow>", $str);
        $str = str_replace("<W>", "<font color=white>", $str);
        $str = str_replace("</>", "</font>", $str);
    } else {
        $str = str_replace("<1>", "", $str);
        $str = str_replace("<Y1>", "", $str);
        $str = str_replace("<R>", "", $str);
        $str = str_replace("<B>", "", $str);
        $str = str_replace("<G>", "", $str);
        $str = str_replace("<M>", "", $str);
        $str = str_replace("<C>", "", $str);
        $str = str_replace("<L>", "", $str);
        $str = str_replace("<S>", "", $str);
        $str = str_replace("<O>", "", $str);
        $str = str_replace("<D>", "", $str);
        $str = str_replace("<Y>", "", $str);
        $str = str_replace("<W>", "", $str);
        $str = str_replace("</>", "", $str);
    }

    return $str;
}

function deleteNation($connect, $general) {
    $date = substr($general['turntime'],11,5);

    $query = "select year,month from game where no='1'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $admin = MYDB_fetch_array($result);

    $query = "select name from nation where nation='{$general['nation']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $nation = MYDB_fetch_array($result);

    $history[count($history)] = "<C>●</>{$admin['year']}년 {$admin['month']}월:<R><b>【멸망】</b></><D><b>{$nation['name']}</b></>은(는) <R>멸망</>했습니다.";

    // 전 장수 재야로    // 전 장수 소속 무소속으로
    $query = "update general set belong=0,troop=0,level=0,nation=0,makelimit=12 where nation='{$general['nation']}'";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    // 도시 공백지로
    $query = "update city set nation=0,front=0,gen1=0,gen2=0,gen3=0 where nation='{$general['nation']}'";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    // 부대 삭제
    $query = "delete from troop where nation='{$general['nation']}'";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    // 국가 삭제
    $query = "delete from nation where nation='{$general['nation']}'";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    // 외교 삭제
    $query = "delete from diplomacy where me='{$general['nation']}' or you='{$general['nation']}'";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

    pushHistory($connect, $history);
}

function nextRuler($connect, $general) {
    $query = "select year,month from game where no='1'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $admin = MYDB_fetch_array($result);

    $query = "select nation,name,history from nation where nation='{$general['nation']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $nation = MYDB_fetch_array($result);

    $query = "select no,name from general where nation='{$general['nation']}' and level!='12' and level>='9' order by level desc";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $corecount = MYDB_num_rows($result);

    //npc or npc유저인 경우 후계 찾기
    if($general['npc'] > 0) {
        $query = "select no,name,nation,IF(ABS(npcmatch-'{$general['npcmatch']}')>75,150-ABS(npcmatch-'{$general['npcmatch']}'),ABS(npcmatch-'{$general['npcmatch']}')) as npcmatch2 from general where nation='{$general['nation']}' and level!=12 and npc>0 order by npcmatch2,rand() limit 0,1";
        $npcresult = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $npccount = MYDB_num_rows($npcresult);
    } else {
        $npccount = 0;
    }

    // 수뇌부가 없으면 공헌도 최고 장수
    if($npccount > 0) {
        $nextruler = MYDB_fetch_array($npcresult);
        //국명 교체
        //$query = "update nation set name='{$nextruler['name']}' where nation='{$general['nation']}'";
        //MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    } elseif($corecount == 0) {
        $query = "select no,name from general where nation='{$general['nation']}' and level!='12' order by dedication desc";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $corecount = MYDB_num_rows($result);

        // 아무도 없으면 국가 삭제
        if($corecount == 0) {
            //분쟁기록 모두 지움
            DeleteConflict($connect, $general['nation']);
            deleteNation($connect, $general);
            return;
        } else {
            $nextruler = MYDB_fetch_array($result);
        }
    } else {
        $nextruler = MYDB_fetch_array($result);
    }

    //군주 교체
    $query = "update general set level='12' where no='{$nextruler['no']}'";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    //도시관직해제
    $query = "update city set gen1=0 where gen1='{$nextruler['no']}'";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    //도시관직해제
    $query = "update city set gen2=0 where gen2='{$nextruler['no']}'";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    //도시관직해제
    $query = "update city set gen3=0 where gen3='{$nextruler['no']}'";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

    $history[count($history)] = "<C>●</>{$admin['year']}년 {$admin['month']}월:<C><b>【유지】</b></><Y>{$nextruler['name']}</>(이)가 <D><b>{$nation['name']}</b></>의 유지를 이어 받았습니다";

    pushHistory($connect, $history);
    $nation = addNationHistory($connect, $nation, "<C>●</>{$admin['year']}년 {$admin['month']}월:<C><b>【유지】</b></><Y>{$nextruler['name']}</>(이)가 <D><b>{$nation['name']}</b></>의 유지를 이어 받음.");
    // 장수 삭제 및 부대처리는 checkTurn에서
}

function printCitysName($connect, $cityNo, $distance=1) {
    $dist = distance($connect, $cityNo, $distance);

    $citynames = CityNameArray();
    $citynum = 94;

    $citystr = "";
    for($i=1; $i <= $citynum; $i++) {

        if($dist[$i] == $distance) {
            $citystr = $citystr.$citynames[$i].", ";
        }
    }

    switch($distance) {
    case 1: $color = "magenta"; break;
    case 2: $color = "orange"; break;
    default: $color = "yellow"; break;
    }
    echo "{$distance}칸 떨어진 도시 : <font color={$color}><b>{$citystr}</b></font><br>";
}

function backButton() {
    echo "
<input type=button value='돌아가기' onclick=location.replace('main.php')><br>
";
}

function CoreBackButton() {
    echo "
<input type=button value='돌아가기' onclick=location.replace('b_chiefcenter.php')><br>
";
}

function closeButton() {
    echo "
<input type=button value='창 닫기' onclick=window.close()><br>
";
}

function distance($connect, $from, $maxDist=99) {
    include_once("queue.php");

    $query = "select city,path from city";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $cityNum = MYDB_num_rows($result);
    for($i=0; $i < $cityNum; $i++) {
        $city = MYDB_fetch_array($result);
        $cityPath[$city['city']] = $city['path'];
        $dist[$city['city']] = 99;
    }

    $select = 0;
    $queue = new Queue(20);
    $queue2 = new Queue(20);
    $q = $queue;
    $q2 = $queue2;
    $distance = $dist[$from] = 0;
    $q->push($from);
    while($q->getSize() > 0 || $q2->getSize() > 0) {
        $distance++;
        if($distance > $maxDist) return $dist;
        while($q->getSize() > 0) {
            $city = $q->pop();
            unset($path);
            $path = explode("|", $cityPath[$city]);
            for($i=0; $i < count($path); $i++) {
                if($dist[$path[$i]] > $distance) {
                    $dist[$path[$i]] = $distance;
                    $q2->push($path[$i]);
                }
            }
        }
        if($select == 0) {
            $q2 = $queue;
            $q = $queue2;
        } else {
            $q = $queue;
            $q2 = $queue2;
        }
        $select = 1 - $select;
    }

    return $dist;
}

function isClose($connect, $nation1, $nation2) {
    $isClose = 0;
    // $nation1의 모든 도시
    $query = "select path from city where nation='$nation1'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $citycount = MYDB_num_rows($result);

    // 국가의 모든 도시 검색
    for($i=0; $i < $citycount; $i++) {
        $city = MYDB_fetch_array($result);

        // 각 도시당 모든 인접 도시 플래그 세팅
        $path = explode("|", $city['path']);
        for($j=0; $j < count($path); $j++) {
            $barrier[$path[$j]] = 1;
        }
    }

    // $nation2의 모든 도시 선택
    $query = "select city from city where nation='$nation2'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $citycount = MYDB_num_rows($result);

    for($i=0; $i < $citycount; $i++) {
        $city = MYDB_fetch_array($result);

        if($barrier[$city['city']] == 1) {
            $isClose = 1;
        }
    }

    return $isClose;
}

function CharExperience($exp, $personal) {
    switch($personal) {
        case  0:    case  1;    case  6:
            $exp *= 1.1; break;
        case  4:    case  5:    case  7:    case 10:
            $exp *= 0.9; break;
    }
    $exp = round($exp);

    return $exp;
}

function CharDedication($ded, $personal) {
    switch($personal) {
        case 10:
            $ded *= 0.9; break;
    }
    $ded = round($ded);

    return $ded;
}

function CharAtmos($atmos, $personal) {
    switch($personal) {
        case  2:    case  4:
            $atmos += 5; break;
        case  0:    case  9:    case 10:
            $atmos -= 5; break;
    }

    return $atmos;
}

function CharTrain($train, $personal) {
    switch($personal) {
        case  3:    case  5:
            $train += 5; break;
        case  1:    case  8:    case 10:
            $train -= 5; break;
    }

    return $train;
}

function CharCost($cost, $personal) {
    switch($personal) {
        case  7:    case  8:    case 9:
            $cost *= 0.8; break;
        case  2:    case  3:    case 6:
            $cost *= 1.2; break;
    }

    return $cost;
}

function CharCritical($rate, $personal) {
    switch($personal) {
        case 10:
            $rate += 10; break;
    }

    return $rate;
}

function TrickInjury($connect, $city, $type=0) {
    $query = "select year,month from game where no='1'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $admin = MYDB_fetch_array($result);

    $query = "select no,name,nation from general where city='$city'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $gencount = MYDB_num_rows($result);
    if($type == 0) {
        $log[0] = "<C>●</>{$admin['month']}월:<M>계략</>으로 인해 <R>부상</>을 당했습니다.";
    } else {
        $log[0] = "<C>●</>{$admin['month']}월:<M>재난</>으로 인해 <R>부상</>을 당했습니다.";
    }
    for($i=0; $i < $gencount; $i++) {
        $general = MYDB_fetch_array($result);

        $injury = rand() % 100;
        if($injury < 30) {  // 부상률 30%
            $injury = floor($injury / 2) + 1;   // 부상 1~16

            $query = "update general set crew=crew*0.98,atmos=atmos*0.98,train=train*0.98,injury=injury+'$injury' where no='{$general['no']}'";
            MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

            pushGenLog($general, $log);
        }
    }
}

function getRandTurn($term) {
    switch($term) {
        case 0: $randtime = rand() % 7200; break;
        case 1: $randtime = rand() % 3600; break;
        case 2: $randtime = rand() % 1800; break;
        case 3: $randtime = rand() % 1200; break;
        case 4: $randtime = rand() % 600; break;
        case 5: $randtime = rand() % 300; break;
        case 6: $randtime = rand() % 120; break;
        case 7: $randtime = rand() % 60; break;
        default:$randtime = rand() % 3600; break;
    }

    $turntime = date('Y-m-d H:i:s', strtotime('now') + $randtime);

    return $turntime;
}

function getRandTurn2($term) {
    switch($term) {
        case 0: $randtime = rand() % 7200; break;
        case 1: $randtime = rand() % 3600; break;
        case 2: $randtime = rand() % 1800; break;
        case 3: $randtime = rand() % 1200; break;
        case 4: $randtime = rand() % 600; break;
        case 5: $randtime = rand() % 300; break;
        case 6: $randtime = rand() % 120; break;
        case 7: $randtime = rand() % 60; break;
        default:$randtime = rand() % 3600; break;
    }

    $turntime = date('Y-m-d H:i:s', strtotime('now') - $randtime);

    return $turntime;
}

function ScoutMsg($connect, $genNum, $nationName, $who, $msgIndex) {
    // 상대에게 발송
    $msgIndex++;
    if($msgIndex >= 10) { $msgIndex = 0; }

    $date = date('Y-m-d H:i:s');
    //등용 서신시 장수번호/내 번호
    $me = $genNum * 10000 + $who;
    $query = "update general set msgindex='$msgIndex',msg{$msgIndex}='{$nationName}(으)로 망명 권유 서신',msg{$msgIndex}_who='$me',msg{$msgIndex}_when='$date',msg{$msgIndex}_type='11' where no='$who'";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
}

function command_Single($connect, $turn, $command) {
    $command = EncodeCommand(0, 0, 0, $command);

    $count = sizeof($turn);
    $str = "con=con";
    for($i=0; $i < $count; $i++) {
        $str .= ",turn{$turn[$i]}='{$command}'";
    }
    $query = "update general set {$str} where user_id='{$_SESSION['p_id']}'";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    echo "<script>location.replace('commandlist.php');</script>";

}

function command_Chief($connect, $turn, $command) {
    $command = EncodeCommand(0, 0, 0, $command);

    $query = "select nation,level from general where user_id='{$_SESSION['p_id']}'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $me = MYDB_fetch_array($result);

    if($me['level'] >= 5) {
        $count = sizeof($turn);
        $str = "type=type";
        for($i=0; $i < $count; $i++) {
            $str .= ",l{$me['level']}turn{$turn[$i]}='{$command}'";
        }
        $query = "update nation set {$str} where nation='{$me['nation']}'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    }
    echo "<script>location.replace('b_chiefcenter.php');</script>";
}

function command_Other($connect, $turn, $commandtype) {
    echo "<form name=form1 action=processing.php method=post target=_parent>";
    $count = sizeof($turn);
    for($i=0; $i < $count; $i++) {
        echo "<input type=hidden name=turn[] value=$turn[$i]>";
    }
    echo "<input type=hidden name=commandtype value={$commandtype}>";
    echo "</form>";
    echo "a";   // 없으면 파폭에서 아래 스크립트 실행 안됨
    echo "<script>form1.submit();</script>";
}

function GetNationColors() {
    $colors = array("FF0000", "800000", "A0522D", "FF6347", "FFA500", "FFDAB9", "FFD700", "FFFF00",
        "7CFC00", "00FF00", "808000", "008000", "2E8B57", "008080", "20B2AA", "6495ED", "7FFFD4",
        "AFEEEE", "87CEEB", "00FFFF", "00BFFF", "0000FF", "000080", "483D8B", "7B68EE", "BA55D3",
        "800080", "FF00FF", "FFC0CB", "F5F5DC", "E0FFFF", "FFFFFF", "A9A9A9");
    return $colors;
}

function EncodeCommand($fourth, $third, $double, $command) {
    $str  = _String::Fill2($fourth, 4, "0");
    $str .= _String::Fill2($third,  4, "0");
    $str .= _String::Fill2($double, 4, "0");
    $str .= _String::Fill2($command, 2, "0");
    return $str;
}

function DecodeCommand($str) {
    $command[3] = floor(substr($str, 0, 4));
    $command[2] = floor(substr($str, 4, 4));
    $command[1] = floor(substr($str, 8, 4));
    $command[0] = floor(substr($str, 12, 2));
    return $command;
}

function OptionsForCitys() {
    $citynames = CityNameArray();

    for($i=1; $i <= 94; $i++) {
        echo "
    <option value={$i}>{$citynames[$i]}</option>";
    }
}

function Submit($url, $msg="", $msg2="") {
    echo "a";   // 파폭 버그 때문
    echo "
<form method=post name=f1 action='{$url}'>
    <input type=hidden name=msg value='{$msg}'>
    <input type=hidden name=msg2 value='{$msg2}'>
</form>
<script>f1.submit();</script>
    ";
}

