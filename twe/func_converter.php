<?php 

namespace sammo;

/**
 * Value Converter
 * 
 * Side effect 없이 값의 변환만을 수행하는 함수들의 모음.
 */


function NationCharCall($call) {
    switch($call) {
        case '명가':    $type =13; break;
        case '음양가':  $type =12; break;
        case '종횡가':  $type =11; break;
        case '불가':    $type =10; break;
        case '도적':    $type = 9; break;
        case '오두미도':$type = 8; break;
        case '태평도':  $type = 7; break;
        case '도가':    $type = 6; break;
        case '묵가':    $type = 5; break;
        case '덕가':    $type = 4; break;
        case '병가':    $type = 3; break;
        case '유가':    $type = 2; break;
        case '법가':    $type = 1; break;
        default:        $type = 0; break;
    }
    return $type;
}

function CharCall($call) {
    switch($call) {
        case '은둔':    $type =10; break;
        case '안전';    $type = 9; break;
        case '유지';    $type = 8; break;
        case '재간';    $type = 7; break;
        case '출세';    $type = 6; break;
        case '할거';    $type = 5; break;
        case '정복';    $type = 4; break;
        case '패권';    $type = 3; break;
        case '의협';    $type = 2; break;
        case '대의';    $type = 1; break;
        case '왕좌';    $type = 0; break;
    }
    return $type;
}

function SpecCall($call) {
    switch($call) {
        case '-':       $type =  0; break;
        case '경작':    $type =  1; break;
        case '상재':    $type =  2; break;
        case '발명':    $type =  3; break;

        case '축성':    $type = 10; break;
        case '수비':    $type = 11; break;
        case '통찰':    $type = 12; break;

        case '인덕':    $type = 20; break;

        case '거상':    $type = 30; break;
        case '귀모':    $type = 31; break;

        case '귀병':    $type = 40; break;
        case '신산':    $type = 41; break;
        case '환술':    $type = 42; break;
        case '집중':    $type = 43; break;
        case '신중':    $type = 44; break;
        case '반계':    $type = 45; break;

        case '보병':    $type = 50; break;
        case '궁병':    $type = 51; break;
        case '기병':    $type = 52; break;
        case '공성':    $type = 53; break;

        case '돌격':    $type = 60; break;
        case '무쌍':    $type = 61; break;
        case '견고':    $type = 62; break;
        case '위압':    $type = 63; break;

        case '저격':    $type = 70; break;
        case '필살':    $type = 71; break;
        case '징병':    $type = 72; break;
        case '의술':    $type = 73; break;
        case '격노':    $type = 74; break;
        case '척사':    $type = 75; break;
        default: $type = 0; break;
    }
    return $type;
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
        case 7: $call = '황제'; break;
        case 6: $call = '왕'; break;
        case 5: $call = '공'; break;
        case 4: $call = '주목'; break;
        case 3: $call = '주자사'; break;
        case 2: $call = '군벌'; break;
        case 1: $call = '호족'; break;
        case 0: $call = '방랑군'; break;
    }
    return $call;
}

function getGenChar($type) {
    switch($type) {
        case 10: $call = '은둔'; break;
        case  9: $call = '안전'; break;
        case  8: $call = '유지'; break;
        case  7: $call = '재간'; break;
        case  6: $call = '출세'; break;
        case  5: $call = '할거'; break;
        case  4: $call = '정복'; break;
        case  3: $call = '패권'; break;
        case  2: $call = '의협'; break;
        case  1: $call = '대의'; break;
        case  0: $call = '왕좌'; break;
    }
    return $call;
}

function getGenSpecial($type) {
    switch($type) {
        case  0: $call = '-'; break;
        case  1: $call = '경작'; break;
        case  2: $call = '상재'; break;
        case  3: $call = '발명'; break;

        case 10: $call = '축성'; break;
        case 11: $call = '수비'; break;
        case 12: $call = '통찰'; break;

        case 20: $call = '인덕'; break;

        case 30: $call = '거상'; break;
        case 31: $call = '귀모'; break;

        case 40: $call = '귀병'; break;
        case 41: $call = '신산'; break;
        case 42: $call = '환술'; break;
        case 43: $call = '집중'; break;
        case 44: $call = '신중'; break;
        case 45: $call = '반계'; break;

        case 50: $call = '보병'; break;
        case 51: $call = '궁병'; break;
        case 52: $call = '기병'; break;
        case 53: $call = '공성'; break;

        case 60: $call = '돌격'; break;
        case 61: $call = '무쌍'; break;
        case 62: $call = '견고'; break;
        case 63: $call = '위압'; break;

        case 70: $call = '저격'; break;
        case 71: $call = '필살'; break;
        case 72: $call = '징병'; break;
        case 73: $call = '의술'; break;
        case 74: $call = '격노'; break;
        case 75: $call = '척사'; break;
    }
    return $call;
}

function getNationType($type) {
    switch($type) {
        case 13: $call = '명 가'; break;
        case 12: $call = '음 양 가'; break;
        case 11: $call = '종 횡 가'; break;
        case 10: $call = '불 가'; break;
        case 9: $call = '도 적'; break;
        case 8: $call = '오 두 미 도'; break;
        case 7: $call = '태 평 도'; break;
        case 6: $call = '도 가'; break;
        case 5: $call = '묵 가'; break;
        case 4: $call = '덕 가'; break;
        case 3: $call = '병 가'; break;
        case 2: $call = '유 가'; break;
        case 1: $call = '법 가'; break;
        case 0: $call = '-'; break;
    }
    return $call;
}


function getConnect($con) {
    if($con < 50)        $conname = '안함';
    elseif($con <   100) $conname = '무관심';
    elseif($con <   200) $conname = '가끔';
    elseif($con <   400) $conname = '보통';
    elseif($con <   800) $conname = '자주';
    elseif($con <  1600) $conname = '열심';
    elseif($con <  3200) $conname = '중독';
    elseif($con <  6400) $conname = '폐인';
    elseif($con < 12800) $conname = '경고';
    else $conname = '헐...';

    return $conname;
}

function getNationType2($type) {
    switch($type) {
        case 13: $call = '<font color=cyan>기술↑ 인구↑</font> <font color=magenta>쌀수입↓ 수성↓</font>'; break;
        case 12: $call = '<font color=cyan>내정↑ 인구↑</font> <font color=magenta>기술↓ 전략↓</font>'; break;
        case 11: $call = '<font color=cyan>전략↑ 수성↑</font> <font color=magenta>금수입↓ 내정↓</font>'; break;
        case 10: $call = '<font color=cyan>민심↑ 수성↑</font> <font color=magenta>금수입↓</font>'; break;
        case 9: $call = '<font color=cyan>계략↑</font> <font color=magenta>금수입↓ 치안↓ 민심↓</font>'; break;
        case 8: $call = '<font color=cyan>쌀수입↑ 인구↑</font> <font color=magenta>기술↓ 수성↓ 내정↓</font>'; break;
        case 7: $call = '<font color=cyan>인구↑ 민심↑</font> <font color=magenta>기술↓ 수성↓</font>'; break;
        case 6: $call = '<font color=cyan>인구↑</font> <font color=magenta>기술↓ 치안↓</font>'; break;
        case 5: $call = '<font color=cyan>수성↑</font> <font color=magenta>기술↓</font>'; break;
        case 4: $call = '<font color=cyan>치안↑인구↑ 민심↑</font> <font color=magenta>쌀수입↓ 수성↓</font>'; break;
        case 3: $call = '<font color=cyan>기술↑ 수성↑</font> <font color=magenta>인구↓ 민심↓</font>'; break;
        case 2: $call = '<font color=cyan>내정↑ 민심↑</font> <font color=magenta>쌀수입↓</font>'; break;
        case 1: $call = '<font color=cyan>금수입↑ 치안↑</font> <font color=magenta>인구↓ 민심↓</font>'; break;
        case 0: $call = '-'; break;
    }
    return $call;
}

function getLevel($level, $nlevel=8) {
    if($level >= 0 && $level <= 4) { $nlevel = 0; }
    $code = $nlevel * 100 + $level;
    switch($code) {
        case 812: $call =     '군주'; break;
        case 811: $call =     '참모'; break;
        case 810: $call =  '제1장군'; break;
        case 809: $call =  '제1모사'; break;
        case 808: $call =  '제2장군'; break;
        case 807: $call =  '제2모사'; break;
        case 806: $call =  '제3장군'; break;
        case 805: $call =  '제3모사'; break;

        case 712: $call =     '황제'; break;    case 612: $call =       '왕'; break;
        case 711: $call =     '승상'; break;    case 611: $call =   '광록훈'; break;
        case 710: $call =   '위장군'; break;    case 610: $call =   '전장군'; break;
        case 709: $call =     '사공'; break;    case 609: $call =   '상서령'; break;
        case 708: $call = '표기장군'; break;    case 608: $call =   '좌장군'; break;
        case 707: $call =     '태위'; break;    case 607: $call =   '중서령'; break;
        case 706: $call = '거기장군'; break;    case 606: $call =   '우장군'; break;
        case 705: $call =     '사도'; break;    case 605: $call =   '비서령'; break;

        case 512: $call =       '공'; break;    case 412: $call =     '주목'; break;
        case 511: $call = '광록대부'; break;    case 411: $call =   '태사령'; break;
        case 510: $call = '안국장군'; break;    case 410: $call = '아문장군'; break;
        case 509: $call =   '집금오'; break;    case 409: $call =     '낭중'; break;
        case 508: $call = '파로장군'; break;    case 408: $call =     '호군'; break;
        case 507: $call =     '소부'; break;    case 407: $call = '종사중랑'; break;

        case 312: $call =   '주자사'; break;    case 212: $call =     '군벌'; break;
        case 311: $call =     '주부'; break;    case 211: $call =     '참모'; break;
        case 310: $call =   '편장군'; break;    case 210: $call =   '비장군'; break;
        case 309: $call = '간의대부'; break;    case 209: $call =   '부참모'; break;

        case 112: $call =     '영주'; break;    case  12: $call =     '두목'; break;
        case 111: $call =     '참모'; break;    case  11: $call =   '부두목'; break;

        case   4: $call =     '태수'; break;
        case   3: $call =     '군사'; break;
        case   2: $call =     '시중'; break;
        case   1: $call =     '일반'; break;
        case   0: $call =     '재야'; break;
        default:  $call =        '-'; break;
    }
    return $call;
}

function getCall($leader, $power, $intel) {
    $call = '평범';
    if($leader >= GameConst::$goodgenleader && $power >= GameConst::$goodgenpower && $intel >= GameConst::$goodgenintel) {
        $call = '만능';
    } elseif($leader >= GameConst::$goodgenleader && $power >= GameConst::$goodgenpower) {
        $call = '용장';
    } elseif($leader >= GameConst::$goodgenleader && $intel >= GameConst::$goodgenintel) {
        $call = '지장';
    } elseif($power >= GameConst::$goodgenpower && $intel >= GameConst::$goodgenintel) {
        $call = '명장';
    } elseif($leader >= GameConst::$goodgenleader) {
        $call = '명사';
    } elseif($power >= GameConst::$goodgenpower) {
        $call = '용맹';
    } elseif($intel >= GameConst::$goodgenintel) {
        $call = '현명';
    }
    return $call;
}

function getDed($dedication) {
    if($dedication < 1 ) $level2 = '무품관';
    elseif($dedication < 10*10) $level2 = '30품관';
    elseif($dedication < 20*20) $level2 = '29품관';
    elseif($dedication < 30*30) $level2 = '28품관';
    elseif($dedication < 40*40) $level2 = '27품관';
    elseif($dedication < 50*50) $level2 = '26품관';
    elseif($dedication < 60*60) $level2 = '25품관';
    elseif($dedication < 70*70) $level2 = '24품관';
    elseif($dedication < 80*80) $level2 = '23품관';
    elseif($dedication < 90*90) $level2 = '22품관';
    elseif($dedication < 100*100) $level2 = '21품관';
    elseif($dedication < 110*110) $level2 = '20품관';
    elseif($dedication < 120*120) $level2 = '19품관';
    elseif($dedication < 130*130) $level2 = '18품관';
    elseif($dedication < 140*140) $level2 = '17품관';
    elseif($dedication < 150*150) $level2 = '16품관';
    elseif($dedication < 160*160) $level2 = '15품관';
    elseif($dedication < 170*170) $level2 = '14품관';
    elseif($dedication < 180*180) $level2 = '13품관';
    elseif($dedication < 190*190) $level2 = '12품관';
    elseif($dedication < 200*200) $level2 = '11품관'; // 40000
    elseif($dedication < 210*210) $level2 = '10품관'; // 44100
    elseif($dedication < 220*220) $level2 =  '9품관'; // 48400
    elseif($dedication < 230*230) $level2 =  '8품관'; // 52900
    elseif($dedication < 240*240) $level2 =  '7품관'; // 57600
    elseif($dedication < 250*250) $level2 =  '6품관'; // 62500
    elseif($dedication < 260*260) $level2 =  '5품관'; // 67600
    elseif($dedication < 270*270) $level2 =  '4품관'; // 72900
    elseif($dedication < 280*280) $level2 =  '3품관'; // 78400
    elseif($dedication < 290*290) $level2 =  '2품관'; // 84100
    else {
        $level2 = '1품관';
    }

    return $level2;
}


function getHonor($experience) {
    if($experience < 640 ) $honor = '전무';
    elseif($experience < 2560) $honor = '무명';
    elseif($experience < 5760) $honor = '신동';
    elseif($experience < 10240) $honor = '약간';
    elseif($experience < 16000) $honor = '평범';
    elseif($experience < 23040) $honor = '지역적';
    elseif($experience < 31360) $honor = '전국적';
    elseif($experience < 40960) $honor = '세계적';
    elseif($experience < 45000) $honor = '유명';
    elseif($experience < 51840) $honor = '명사';
    elseif($experience < 55000) $honor = '호걸';
    elseif($experience < 64000) $honor = '효웅';
    elseif($experience < 77440) $honor = '영웅';
    else $honor = '구세주';

    return $honor;
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
    return $exp / GameConst::$upgradeLimit * 100;
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

function getCost($armtype) {
    //FIXME: 정말로 side effect가 없으려면 query는 밖으로 이동해야함.
    //TODO: 병종 값이 column으로 들어있는건 전혀 옳지 않음. key->value 형태로 바꿔야함
    return DB::db()->queryFirstColumn('select %b from game where no=1', sprintf('cst%d', $armtype));
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
    if($tech < 1000)      { $str = '0등급'; }
    elseif($tech < 2000)  { $str = '1등급'; }
    elseif($tech < 3000)  { $str = '2등급'; }
    elseif($tech < 4000)  { $str = '3등급'; }
    elseif($tech < 5000)  { $str = '4등급'; }
    elseif($tech < 6000)  { $str = '5등급'; }
    elseif($tech < 7000)  { $str = '6등급'; }
    elseif($tech < 8000)  { $str = '7등급'; }
    elseif($tech < 9000)  { $str = '8등급'; }
    elseif($tech < 10000) { $str = '9등급'; }
    elseif($tech < 11000) { $str = '10등급'; }
    elseif($tech < 12000) { $str = '11등급'; }
    else                  { $str = '12등급'; }
    return $str;
}

function getDexCall($dex) {
    if($dex < 2500)        { $str = '<font color="navy">F-</font>'; }
    elseif($dex <    7500) { $str = '<font color="navy">F</font>'; }
    elseif($dex <   15000) { $str = '<font color="navy">F+</font>'; }
    elseif($dex <   25000) { $str = '<font color="skyblue">E-</font>'; }
    elseif($dex <   37500) { $str = '<font color="skyblue">E</font>'; }
    elseif($dex <   52500) { $str = '<font color="skyblue">E+</font>'; }
    elseif($dex <   70000) { $str = '<font color="seagreen">D-</font>'; }
    elseif($dex <   90000) { $str = '<font color="seagreen">D</font>'; }
    elseif($dex <  112500) { $str = '<font color="seagreen">D+</font>'; }
    elseif($dex <  137500) { $str = '<font color="teal">C-</font>'; }
    elseif($dex <  165000) { $str = '<font color="teal">C</font>'; }
    elseif($dex <  195000) { $str = '<font color="teal">C+</font>'; }
    elseif($dex <  227500) { $str = '<font color="limegreen">B-</font>'; }
    elseif($dex <  262500) { $str = '<font color="limegreen">B</font>'; }
    elseif($dex <  300000) { $str = '<font color="limegreen">B+</font>'; }
    elseif($dex <  340000) { $str = '<font color="gold">A-</font>'; }
    elseif($dex <  382500) { $str = '<font color="gold">A</font>'; }
    elseif($dex <  427500) { $str = '<font color="gold">A+</font>'; }
    elseif($dex <  475000) { $str = '<font color="darkorange">S-</font>'; }
    elseif($dex <  525000) { $str = '<font color="darkorange">S</font>'; }
    elseif($dex <  577500) { $str = '<font color="darkorange">S+</font>'; }
    elseif($dex <  632500) { $str = '<font color="tomato">SS-</font>'; }
    elseif($dex <  690000) { $str = '<font color="tomato">SS</font>'; }
    elseif($dex <  750000) { $str = '<font color="tomato">SS+</font>'; }
    elseif($dex <  812500) { $str = '<font color="red">SSS-</font>'; }
    elseif($dex <  877500) { $str = '<font color="red">SSS</font>'; }
    elseif($dex <  945000) { $str = '<font color="red">SSS+</font>'; }
    elseif($dex < 1015000) { $str = '<font color="darkviolet">Z-</font>'; }
    elseif($dex < 1087500) { $str = '<font color="darkviolet">Z</font>'; }
    elseif($dex < 1162500) { $str = '<font color="darkviolet">Z+</font>'; }
    else                   { $str = '<font color="white">?</font>'; }
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


function getWeapName($weap) {
    switch($weap) {
        case  0: $weapname = '-'; break;
        case  1: $weapname = '단도(+1)'; break;
        case  2: $weapname = '단궁(+2)'; break;
        case  3: $weapname = '단극(+3)'; break;
        case  4: $weapname = '목검(+4)'; break;
        case  5: $weapname = '죽창(+5)'; break;
        case  6: $weapname = '소부(+6)'; break;

        case  7: $weapname = '동추(+7)'; break;
        case  8: $weapname = '철편(+7)'; break;
        case  9: $weapname = '철쇄(+7)'; break;
        case 10: $weapname = '맥궁(+7)'; break;
        case 11: $weapname = '유성추(+8)'; break;
        case 12: $weapname = '철질여골(+8)'; break;
        case 13: $weapname = '쌍철극(+9)'; break;
        case 14: $weapname = '동호비궁(+9)'; break;
        case 15: $weapname = '삼첨도(+10)'; break;
        case 16: $weapname = '대부(+10)'; break;
        case 17: $weapname = '고정도(+11)'; break;
        case 18: $weapname = '이광궁(+11)'; break;
        case 19: $weapname = '철척사모(+12)'; break;
        case 20: $weapname = '칠성검(+12)'; break;
        case 21: $weapname = '사모(+13)'; break;
        case 22: $weapname = '양유기궁(+13)'; break;
        case 23: $weapname = '언월도(+14)'; break;
        case 24: $weapname = '방천화극(+14)'; break;
        case 25: $weapname = '청홍검(+15)'; break;
        case 26: $weapname = '의천검(+15)'; break;
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
        case  0: $bookname = '-'; break;
        case  1: $bookname = '효경전(+1)'; break;
        case  2: $bookname = '회남자(+2)'; break;
        case  3: $bookname = '변도론(+3)'; break;
        case  4: $bookname = '건상역주(+4)'; break;
        case  5: $bookname = '여씨춘추(+5)'; break;
        case  6: $bookname = '사민월령(+6)'; break;

        case  7: $bookname = '위료자(+7)'; break;
        case  8: $bookname = '사마법(+7)'; break;
        case  9: $bookname = '한서(+7)'; break;
        case 10: $bookname = '논어(+7)'; break;
        case 11: $bookname = '전론(+8)'; break;
        case 12: $bookname = '사기(+8)'; break;
        case 13: $bookname = '장자(+9)'; break;
        case 14: $bookname = '역경(+9)'; break;
        case 15: $bookname = '시경(+10)'; break;
        case 16: $bookname = '구국론(+10)'; break;
        case 17: $bookname = '상군서(+11)'; break;
        case 18: $bookname = '춘추전(+11)'; break;
        case 19: $bookname = '산해경(+12)'; break;
        case 20: $bookname = '맹덕신서(+12)'; break;
        case 21: $bookname = '관자(+13)'; break;
        case 22: $bookname = '병법24편(+13)'; break;
        case 23: $bookname = '한비자(+14)'; break;
        case 24: $bookname = '오자병법(+14)'; break;
        case 25: $bookname = '노자(+15)'; break;
        case 26: $bookname = '손자병법(+15)'; break;
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
        case  0: $horsename = '-'; break;
        case  1: $horsename = '노기(+1)'; break;
        case  2: $horsename = '조랑(+2)'; break;
        case  3: $horsename = '노새(+3)'; break;
        case  4: $horsename = '나귀(+4)'; break;
        case  5: $horsename = '갈색마(+5)'; break;
        case  6: $horsename = '흑색마(+6)'; break;

        case  7: $horsename = '백마(+7)'; break;
        case  8: $horsename = '백마(+7)'; break;
        case  9: $horsename = '기주마(+7)'; break;
        case 10: $horsename = '기주마(+7)'; break;
        case 11: $horsename = '양주마(+8)'; break;
        case 12: $horsename = '양주마(+8)'; break;
        case 13: $horsename = '과하마(+9)'; break;
        case 14: $horsename = '과하마(+9)'; break;
        case 15: $horsename = '대완마(+10)'; break;
        case 16: $horsename = '대완마(+10)'; break;
        case 17: $horsename = '서량마(+11)'; break;
        case 18: $horsename = '서량마(+11)'; break;
        case 19: $horsename = '사륜거(+12)'; break;
        case 20: $horsename = '사륜거(+12)'; break;
        case 21: $horsename = '절영(+13)'; break;
        case 22: $horsename = '적로(+13)'; break;
        case 23: $horsename = '적란마(+14)'; break;
        case 24: $horsename = '조황비전(+14)'; break;
        case 25: $horsename = '한혈마(+15)'; break;
        case 26: $horsename = '적토마(+15)'; break;
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
        case  0: $itemname = '-'; break;
        case  1: $itemname = '환약(치료)'; break;
        case  2: $itemname = '수극(저격)'; break;
        case  3: $itemname = '탁주(사기)'; break;
        case  4: $itemname = '청주(훈련)'; break;
        case  5: $itemname = '이추(계략)'; break;
        case  6: $itemname = '향낭(계략)'; break;

        case  7: $itemname = '오석산(치료)'; break;
        case  8: $itemname = '무후행군(치료)'; break;
        case  9: $itemname = '도소연명(치료)'; break;
        case 10: $itemname = '칠엽청점(치료)'; break;
        case 11: $itemname = '정력견혈(치료)'; break;
        case 12: $itemname = '과실주(훈련)'; break;
        case 13: $itemname = '이강주(훈련)'; break;
        case 14: $itemname = '의적주(사기)'; break;
        case 15: $itemname = '두강주(사기)'; break;
        case 16: $itemname = '보령압주(사기)'; break;
        case 17: $itemname = '철벽서(훈련)'; break;
        case 18: $itemname = '단결도(훈련)'; break;
        case 19: $itemname = '춘화첩(사기)'; break;
        case 20: $itemname = '초선화(사기)'; break;
        case 21: $itemname = '육도(계략)'; break;
        case 22: $itemname = '삼략(계략)'; break;
        case 23: $itemname = '청낭서(의술)'; break;
        case 24: $itemname = '태평청령(의술)'; break;
        case 25: $itemname = '태평요술(회피)'; break;
        case 26: $itemname = '둔갑천서(회피)'; break;
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



function ConvertLog($str, $type=1) {
    //TODO: 이 함수는 없애야 한다. CSS로 대신하자
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



function newColor($color) {
    switch($color) {
        case "":
        case "330000":
        case "#FF0000":
        case "#800000":
        case "#A0522D":
        case "#FF6347":
        case "#808000":
        case "#008000":
        case "#2E8B57":
        case "#008080":
        case "#6495ED":
        case "#0000FF":
        case "#000080":
        case "#483D8B":
        case "#7B68EE":
        case "#800080":
        case "#A9A9A9":
        case "000000":
            $color = "#FFFFFF"; break;
        default:
            $color = "000000"; break;
    }
    return $color;
}

function backColor($color) {
    return newColor($color);
}
