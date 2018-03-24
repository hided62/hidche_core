<?php
include "lib.php";
include "func.php";



$turnterm = util::array_get($_POST['turnterm'],'0');
$sync = util::array_get($_POST['sync'],'0');
$scenario = util::array_get($_POST['scenario'],'0');
$fiction = util::array_get($_POST['fiction'],'0');
$extend = util::array_get($_POST['extend'],'0');
$npcmode = util::array_get($_POST['npcmode'],'0');
$img = util::array_get($_POST['img'],'0');

if(getUserGrade(true) < 5){
    die('관리자 아님');
}

$connect=dbConn();

switch($scenario) {
    case  0: $startyear = 180; break;
    case  1: $startyear = 184 - 3; break;
    case  2: $startyear = 190 - 3; break;
    case  3: $startyear = 194 - 3; break;
    case  4: $startyear = 196 - 3; break;
    case  5: $startyear = 200 - 3; break;
    case  6: $startyear = 202 - 3; break;
    case  7: $startyear = 207 - 3; break;
    case  8: $startyear = 213 - 3; break;
    case  9: $startyear = 219 - 3; break;
    case 10: $startyear = 225 - 3; break;
    case 11: $startyear = 228 - 3; break;

    case 12: $startyear = 191 - 3; break;

    case 20: $startyear = 180; break;
    case 21: $startyear = 180; break;
    case 22: $startyear = 179; break;
    case 23: $startyear = 180; break;
    case 24: $startyear = 180; break;
    case 25: $startyear = 180; break;
    case 26: $startyear = 180; break;
    case 27: $startyear = 180; break;
    case 28: $startyear = 180; break;
    default: $startyear = 180; break;
}

// 관리자 등록
$turntime = date('Y-m-d H:i:s');
$lastconnect = $turntime;
$turntime = addTurn($turntime, $turnterm);
$turntime = cutTurn($turntime, $turnterm);

$picture = 'pic_1.jpg';
if($img < 1) { $picture = 'default.jpg'; };

@MYDB_query("
    insert into general (
        owner, connect, name, picture, nation, city, troop, makelimit,
        leader, power, intel, experience, dedication, gold, rice, crew, train, atmos,
        weap, book, level, turntime, killturn, lastconnect
    ) values (
        '1', '0', '운영자', '$picture', '0', '3', '0', '0',
        '50', '50', '50', '0', '0', '10000', '10000', '0', '0', '0',
        '0', '0', '0', '$turntime', '80', '$lastconnect'
    )",
    $connect
) or Error(__LINE__.MYDB_error($connect),"");

$picture = 'pic_2.jpg';
if($img < 1) { $picture = 'default.jpg'; };

//부운영자는 비밀번호를 지정하지 않아 로그인할수 없도록 처리한다.
@MYDB_query("
    insert into general (
        owner, connect, name, picture, nation, city, troop, makelimit,
        leader, power, intel, experience, dedication, gold, rice, crew, train, atmos,
        weap, book, level, turntime, killturn, lastconnect
    ) values (
        '2', '0', '부운영자', '$picture', '0', '3', '0', '0',
        '50', '50', '50', '0', '0', '10000', '10000', '0', '0', '0',
        '0', '0', '0', '$turntime', '80', '$lastconnect'
    )",
    $connect
) or Error(__LINE__.MYDB_error($connect),"");

// 게임정보 입력
$turntime = date('Y-m-d H:i:s');
$time = substr($turntime, 11, 2);
if($sync == 0) {
    // 항상 정각으로 설정
    $starttime = cutTurn($turntime, $turnterm);
} else {
    // 항상 13시로 설정
    $starttime = CutDay($turntime);
}

switch($turnterm) {
    case 0: $killturn = 40; break;
    case 1: $killturn = 80; break;
    case 2: $killturn = 160; break;
    case 3: $killturn = 240; break;
    case 4: $killturn = 480; break;
    case 5: $killturn = 960; break;
    case 6: $killturn =2400; break;
    case 7: $killturn =4800; break;
}
if($npcmode == 1) { $killturn = floor($killturn / 3); }

//락 생성
@MYDB_query("insert into plock ( plock ) values ( 0 )", $connect) or Error(__LINE__.MYDB_error($connect),"");

//게임 정보 생성
@MYDB_query("
    insert into game (
        year, month, msg,
        maxgeneral, normgeneral, maxnation, conlimit, gold_rate, rice_rate,
        turntime, starttime, turnterm, killturn,
        genius, startyear,scenario,img,npcmode,extend,fiction,

        att0,  def0,  spd0,  avd0,  cst0,  ric0,
        att1,  def1,  spd1,  avd1,  cst1,  ric1,
        att2,  def2,  spd2,  avd2,  cst2,  ric2,
        att3,  def3,  spd3,  avd3,  cst3,  ric3,
        att4,  def4,  spd4,  avd4,  cst4,  ric4,
        att5,  def5,  spd5,  avd5,  cst5,  ric5,

        att10, def10, spd10, avd10, cst10, ric10,
        att11, def11, spd11, avd11, cst11, ric11,
        att12, def12, spd12, avd12, cst12, ric12,
        att13, def13, spd13, avd13, cst13, ric13,
        att14, def14, spd14, avd14, cst14, ric14,

        att20, def20, spd20, avd20, cst20, ric20,
        att21, def21, spd21, avd21, cst21, ric21,
        att22, def22, spd22, avd22, cst22, ric22,
        att23, def23, spd23, avd23, cst23, ric23,
        att24, def24, spd24, avd24, cst24, ric24,
        att25, def25, spd25, avd25, cst25, ric25,
        att26, def26, spd26, avd26, cst26, ric26,
        att27, def27, spd27, avd27, cst27, ric27,

        att30, def30, spd30, avd30, cst30, ric30,
        att31, def31, spd31, avd31, cst31, ric31,
        att32, def32, spd32, avd32, cst32, ric32,
        att33, def33, spd33, avd33, cst33, ric33,
        att34, def34, spd34, avd34, cst34, ric34,
        att35, def35, spd35, avd35, cst35, ric35,
        att36, def36, spd36, avd36, cst36, ric36,
        att37, def37, spd37, avd37, cst37, ric37,
        att38, def38, spd38, avd38, cst38, ric38,

        att40, def40, spd40, avd40, cst40, ric40,
        att41, def41, spd41, avd41, cst41, ric41,
        att42, def42, spd42, avd42, cst42, ric42,
        att43, def43, spd43, avd43, cst43, ric43
    ) values (
        '$startyear', 1, '삼모전의 매력은 IRC에서의 커뮤니티입니다. 흠냐 채널을 찾아주세요^^ <font color=cyan>웹 IRC</font> : <a target=_blank href=http://barosl.com/webirc/흠냐>http://barosl.com/webirc/흠냐</a><br><font color=orange size=6>폭력적/선정적인 전콘/전메는 블럭/삭제 대상이 될 수 있습니다.</font>',
        500, 300, 55, 120, 100, 100,
        '$turntime', '$starttime', '$turnterm', '$killturn',
        5, '$startyear', '$scenario', '$img', '$npcmode', '$extend', '$fiction',

        100, 150, 7, 10,  9,  9,    -- 보병
        100, 200, 7, 10, 10, 11,    -- 청주병(중원)
        150, 150, 7, 10, 11, 10,    -- 수병(오월)
        100, 150, 7, 20, 10, 10,    -- 자객병(저)
        150, 200, 7, 10, 12, 12,    -- 근위병(낙양)
        100, 250, 7,  5, 13, 10,    -- 등갑병(남중)

        100, 100, 7, 20, 10, 10,    -- 궁병
        100, 100, 8, 30, 11, 12,    -- 궁기병(동이)
        150, 100, 8, 20, 12, 11,    -- 연노병(서촉)
        150, 150, 7, 20, 13, 13,    -- 강궁병(양양)
        200, 100, 7, 20, 13, 13,    -- 석궁병(건업)

        150, 100, 7,  5, 11, 11,    -- 기병
        200, 100, 7,  5, 12, 13,    -- 백마병(하북)
        150, 150, 7,  5, 13, 12,    -- 중장기병(서북)
        200, 100, 8,  5, 13, 11,    -- 돌격기병(흉노)
        100, 200, 7,  5, 11, 13,    -- 철기병(강)
        150, 100, 8, 15, 12, 12,    -- 수렵기병(산월)
        250, 200, 6,  0, 16, 16,    -- 맹수병(남만)
        200, 150, 7,  5, 14, 14,    -- 호표기병(허창)

         80,  80, 7,  5,  9,  9,    -- 귀병
         80,  80, 7, 20, 10, 10,    -- 신귀병(초)
         80, 130, 7,  5,  9, 11,    -- 백귀병(오환)
        130,  80, 7,  5, 11,  9,    -- 흑귀병(왜)
        130, 130, 7,  0, 12, 12,    -- 악귀병(장안)
         60,  60, 7, 10,  8,  8,    -- 남귀병
        110, 110, 7,  0, 13, 10,    -- 황귀병(낙양)
         80, 130, 7, 15, 11, 12,    -- 천귀병(성도)
        130,  80, 7, 15, 12, 11,    -- 마귀병(업)

        100, 100, 6,  0, 15,  5,    -- 정란
        150, 100, 6,  0, 20,  5,    -- 충차
        200, 100, 6,  0, 25,  5,    -- 벽력거(업)
         50, 200, 5,  0, 30,  5     -- 목우(성도)
    )",
    $connect
) or Error(__LINE__.MYDB_error($connect),"");

// 도시정보 입력
//                    이름 규모   인구   농업   상업   치안   수비   성벽 지역 경로
insertCity(  "업", 8, 620500, 12500, 11300, 10000, 11700, 12200, 1, "9|18|70|78|80");     //  1 : 업
insertCity("허창", 8, 587600, 12100, 12400, 10000, 11700, 12500, 2, "10|19|38|71|74|80"); //  2 : 허창
insertCity("낙양", 8, 835700, 11700, 12000, 10000, 12100, 12400, 2, "70|71|72|74");       //  3 : 낙양
insertCity("장안", 8, 592300, 11600, 12300, 10000, 12000, 11800, 3, "41|73|86");          //  4 : 장안
insertCity("성도", 8, 652500, 12300, 12500, 10000, 12500, 12300, 4, "26|27|87");          //  5 : 성도
insertCity("양양", 8, 583700, 12000, 12600, 10000, 11500, 11700, 6, "51|89");             //  6 : 양양
insertCity("건업", 8, 638600, 11600, 12300, 10000, 11500, 11900, 7, "31|82|83");          //  7 : 건업

insertCity("북평", 7, 486200, 10200,  9500,  8000, 10300,  9900, 1, "77|90");             //  8 : 북평
insertCity("남피", 7, 503200,  9900, 10100,  8000, 10100, 10500, 1, "1|36|77");           //  9 : 남피
insertCity(  "완", 7, 472400, 10300, 10000,  8000, 10100,  9900, 2, "2|20|51|71");        // 10 : 완
insertCity("수춘", 7, 514300,  9900,  9600,  8000,  9900,  9500, 2, "12|20|38|82");       // 11 : 수춘
insertCity("서주", 7, 485300, 10100,  9800,  8000, 10200,  9700, 2, "11|21|38|39");       // 12 : 서주
insertCity("강릉", 7, 485000, 10500,  9600,  8000,  9500,  9600, 6, "14|53|88|89");       // 13 : 강릉
insertCity("장사", 7, 471000,  9700,  9900,  8000, 10000, 10500, 6, "13|15|30|53|54");    // 14 : 장사
insertCity("시상", 7, 525200,  9800, 10000, 10000,  9900,  9600, 7, "14|56|58|91|92");    // 15 : 시상
insertCity("위례", 7, 492600, 10000,  9300,  8000,  9800, 10300, 8, "32|33|34|60|79");    // 16 : 위례

insertCity(  "계", 6, 388500,  7500,  8000,  6000,  7800,  8100, 1, "35|77");             // 17 : 계
insertCity("복양", 6, 418500,  8000,  8300,  6000,  8200,  8000, 2, "1|19|78|81");        // 18 : 복양
insertCity("진류", 6, 395700,  8200,  8000,  6000,  8000,  8300, 2, "2|18|74|80|81");     // 19 : 진류
insertCity("여남", 6, 383100,  7700,  8100,  6000,  8400,  7700, 2, "10|11|38");          // 20 : 여남
insertCity("하비", 6, 427800,  8500,  8300,  6000,  8200,  7800, 2, "12|83");             // 21 : 하비
insertCity("서량", 6, 387400,  7700,  7900,  6000,  8300,  8000, 3, "40|63|64");          // 22 : 서량
insertCity("하내", 6, 373600,  7700,  8100,  6000,  8100,  8000, 3, "35|42|65|70");       // 23 : 하내
insertCity("한중", 6, 402700,  7700,  8400,  6000,  8000,  8500, 4, "25|75|86");          // 24 : 한중
insertCity("상용", 6, 368700,  7800,  7600,  6000,  7700,  8100, 4, "24|51");             // 25 : 상용
insertCity("덕양", 6, 380300,  8100,  8400,  6000,  7900,  7700, 4, "5|27|44|45");        // 26 : 덕양
insertCity("강주", 6, 412600,  7900,  8000,  6000,  8400,  8100, 4, "5|26|45|46|47");     // 27 : 강주
insertCity("건녕", 6, 376500,  8200,  8000,  6000,  8600,  8100, 5, "46|48|49");          // 28 : 건녕
insertCity("남해", 6, 380300,  8200,  7600,  6000,  8000,  8100, 5, "50|55|59|67");       // 29 : 남해
insertCity("계양", 6, 395500,  8300,  8000,  6000,  8100,  7700, 6, "14|54|55");          // 30 : 계양
insertCity(  "오", 6, 435500,  7700,  8100,  6000,  7700,  7600, 7, "7|57|92|93");        // 31 : 오
insertCity("평양", 6, 398200,  7800,  8000,  6000,  8300,  7800, 8, "16|61");             // 32 : 평양
insertCity("사비", 6, 415700,  7700,  7900,  6000,  7800,  8000, 8, "16|34|93");          // 33 : 사비
insertCity("계림", 6, 391100,  8000,  7400,  6000,  8100,  7800, 8, "16|33|62|93");       // 34 : 계림

insertCity("진양", 5, 307400,  5600,  5900,  4000,  6400,  5900, 1, "17|23|70");          // 35 : 진양
insertCity("평원", 5, 307400,  6200,  6500,  4000,  6100,  6300, 1, "9|37|78");           // 36 : 평원
insertCity("북해", 5, 314600,  5500,  6300,  4000,  6300,  5800, 1, "36|39|79");          // 37 : 북해
insertCity(  "초", 5, 328600,  6000,  6200,  4000,  6200,  5700, 2, "2|11|12|20|81");     // 38 : 초
insertCity(  "패", 5, 287700,  6400,  5800,  4000,  5800,  5900, 2, "12|37|81");          // 39 : 패
insertCity("천수", 5, 298500,  5900,  6400,  4000,  6000,  5800, 3, "22|41|64|84");       // 40 : 천수
insertCity("안정", 5, 276400,  5700,  5900,  4000,  5700,  6200, 3, "4|40|85");           // 41 : 안정
insertCity("홍농", 5, 274800,  5700,  6300,  4000,  5800,  6300, 3, "23|72|73");          // 42 : 홍농
insertCity("하변", 5, 278500,  5800,  6200,  4000,  6000,  5600, 4, "76|85");             // 43 : 하변
insertCity("자동", 5, 287000,  5700,  5500,  4000,  6000,  5800, 4, "26|75|76|87");       // 44 : 자동
insertCity("영안", 5, 315300,  6200,  5900,  4000,  5800,  5900, 4, "26|27|88");          // 45 : 영안
insertCity("귀양", 5, 274600,  5800,  6100,  4000,  6100,  5800, 5, "27|28|47");          // 46 : 귀양
insertCity("주시", 5, 282800,  6000,  5900,  4000,  5800,  6300, 5, "27|46|48");          // 47 : 주시
insertCity("운남", 5, 325800,  6200,  6000,  4000,  6400,  6100, 5, "28|47|66");          // 48 : 운남
insertCity("남영", 5, 285300,  5900,  6200,  4000,  5800,  5700, 5, "28|54|66");          // 49 : 남영
insertCity("교지", 5, 319500,  5800,  5900,  4000,  5800,  5900, 5, "29|66");             // 50 : 교지
insertCity("신야", 5, 278600,  6000,  6200,  4000,  5800,  5500, 6, "6|10|25");           // 51 : 신야
insertCity("강하", 5, 307400,  5500,  5600,  4000,  5700,  6000, 6, "89|91");             // 52 : 강하
insertCity("무릉", 5, 319600,  5800,  6300,  4000,  6300,  5800, 6, "13|14|54");          // 53 : 무릉
insertCity("영릉", 5, 284900,  6200,  5800,  4000,  6200,  6200, 6, "14|30|49|53");       // 54 : 영릉
insertCity("상동", 5, 276700,  5800,  5900,  4000,  6200,  5800, 6, "29|30|58");          // 55 : 상동
insertCity("여강", 5, 290500,  5600,  5800,  4000,  6000,  5500, 7, "15|82|91|92");       // 56 : 여강
insertCity("회계", 5, 300500,  6400,  5900,  4000,  6200,  6400, 7, "31|67");             // 57 : 회계
insertCity("고창", 5, 280200,  5700,  6200,  4000,  5800,  6300, 7, "15|55|67");          // 58 : 고창
insertCity(  "대", 5, 325600,  6000,  6200,  4000,  5700,  6000, 7, "29|67|94");          // 59 : 대
insertCity("안평", 5, 293700,  6300,  5900,  4000,  5900,  6300, 8, "16|61|79|90");       // 60 : 안평
insertCity("졸본", 5, 293900,  5500,  5900,  4000,  6000,  5800, 8, "32|60|68");          // 61 : 졸본
insertCity("이도", 5, 317400,  5800,  6100,  4000,  5800,  5600, 8, "34|69|93");          // 62 : 이도

insertCity(  "강", 4, 209500,  4000,  4200,  2000,  4300,  4000, 3, "22|84");             // 63 : 강
insertCity(  "저", 4, 195700,  4000,  4200,  2000,  4300,  4200, 3, "22|40|85");          // 64 : 저
insertCity("흉노", 4, 206400,  4000,  4100,  2000,  4000,  3800, 3, "23|84");             // 65 : 흉노
insertCity("남만", 4, 237800,  4000,  4200,  2000,  4300,  4500, 5, "48|49|50");          // 66 : 남만
insertCity("산월", 4, 227500,  4000,  3700,  2000,  4300,  3800, 7, "29|57|58|59");       // 67 : 산월
insertCity("오환", 4, 215300,  4200,  3700,  2000,  4300,  4000, 8, "61|90");             // 68 : 오환
insertCity(  "왜", 4, 206500,  3900,  3700,  2000,  4300,  4100, 8, "62|94");             // 69 : 왜

insertCity("호관", 3,  88700,  1900,  1800,  2000,  9500,  9600, 1, "1|3|23|35");         // 70 : 호관
insertCity("호로", 3, 111200,  2200,  2100,  2000, 10300,  9800, 2, "2|3|10");            // 71 : 호로
insertCity("사곡", 3, 100800,  2100,  1900,  2000,  9900, 10100, 3, "3|42");              // 72 : 사곡
insertCity("함곡", 3, 108100,  2000,  2200,  2000, 10100, 10200, 3, "4|42");              // 73 : 함곡
insertCity("사수", 3,  95800,  1700,  1900,  2000,  9500,  9600, 2, "2|3|19|80");         // 74 : 사수
insertCity("양평", 3,  86800,  1900,  1900,  2000,  9700,  9600, 4, "24|44|86");          // 75 : 양평
insertCity("가맹", 3,  85500,  1700,  1800,  2000,  9600,  9500, 4, "43|44");             // 76 : 가맹

insertCity("역경", 2,  98500,  1800,  1900,  2000,  3900,  4100, 1, "8|9|17");            // 77 : 역경
insertCity("계교", 2, 101200,  2100,  1900,  2000,  4000,  4200, 1, "1|18|36");           // 78 : 계교
insertCity("동황", 2,  99200,  1900,  2100,  2000,  3800,  4000, 1, "16|37|60");          // 79 : 동황
insertCity("관도", 2, 112300,  2200,  2000,  2000,  4200,  4300, 2, "1|2|19|74");         // 80 : 관도
insertCity("정도", 2, 108500,  2100,  2100,  2000,  4100,  3800, 2, "18|19|38|39");       // 81 : 정도
insertCity("합비", 2,  99800,  2000,  1900,  2000,  3900,  4100, 2, "7|11|56");           // 82 : 합비
insertCity("광릉", 2, 100100,  2000,  2100,  2000,  4100,  4000, 2, "7|21");              // 83 : 광릉
insertCity("적도", 2,  95200,  1800,  1700,  2000,  3800,  3700, 3, "40|63|65");          // 84 : 적도
insertCity("가정", 2,  93100,  1700,  1700,  2000,  3600,  3800, 3, "41|43|64");          // 85 : 가정
insertCity("기산", 2, 100500,  1900,  1800,  2000,  4100,  4000, 4, "4|24|75");           // 86 : 기산
insertCity("면죽", 3, 109300,  2200,  2100,  2000, 10800,  9900, 4, "5|44");              // 87 : 면죽
insertCity("이릉", 2,  96800,  1800,  1900,  2000,  3900,  4100, 6, "13|45");             // 88 : 이릉
insertCity("장판", 2, 103200,  2100,  2000,  2000,  4000,  3700, 6, "6|13|52|91");        // 89 : 장판
insertCity("백랑", 2, 105200,  2200,  1900,  2000,  3800,  4200, 8, "8|60|68");           // 90 : 백랑

insertCity("적벽", 1, 111700,  2300,  2100,  2000,  4200,  4100, 7, "15|52|56|89");       // 91 : 적벽
insertCity("파양", 1, 103700,  2000,  2200,  2000,  3800,  3800, 7, "15|31|56");          // 92 : 파양
insertCity("탐라", 1, 113000,  2200,  2100,  2000,  4300,  4100, 8, "31|33|34|62");       // 93 : 탐라
insertCity("유구", 1,  92100,  1700,  1800,  2000,  3700,  3700, 8, "59|69");             // 94 : 유구

//TODO:debug all and replace
switch($scenario) {
    case  0: echo "index.php";     break;
    case  1: echo "scenario_1.php";  break;
    case  2: echo "scenario_2.php";  break;
    case  3: echo "scenario_3.php";  break;
    case  4: echo "scenario_4.php";  break;
    case  5: echo "scenario_5.php";  break;
    case  6: echo "scenario_6.php";  break;
    case  7: echo "scenario_7.php";  break;
    case  8: echo "scenario_8.php";  break;
    case  9: echo "scenario_9.php";  break;
    case 10: echo "scenario_10.php";  break;
    case 11: echo "scenario_11.php";  break;

    case 12: echo "scenario_12.php";  break;

    case 20: echo "scenario_20.php";  break;
    case 21: echo "scenario_21.php";  break;
    case 22: echo "scenario_22.php";  break;
    case 23: echo "scenario_23.php";  break;
    case 24: echo "scenario_24.php";  break;
    case 25: echo "scenario_25.php";  break;
    case 26: echo "scenario_26.php";  break;
    case 27: echo "scenario_27.php";  break;
    case 28: echo "scenario_28.php";  break;
    default: echo "install3_ok.php";     break;
}
/*
switch($scenario) {
    case  0: echo "<script>location.replace('index.php');</script>";     break;
    case  1: echo "<script>location.replace('scenario_1.php');</script>";  break;
    case  2: echo "<script>location.replace('scenario_2.php');</script>";  break;
    case  3: echo "<script>location.replace('scenario_3.php');</script>";  break;
    case  4: echo "<script>location.replace('scenario_4.php');</script>";  break;
    case  5: echo "<script>location.replace('scenario_5.php');</script>";  break;
    case  6: echo "<script>location.replace('scenario_6.php');</script>";  break;
    case  7: echo "<script>location.replace('scenario_7.php');</script>";  break;
    case  8: echo "<script>location.replace('scenario_8.php');</script>";  break;
    case  9: echo "<script>location.replace('scenario_9.php');</script>";  break;
    case 10: echo "<script>location.replace('scenario_10.php');</script>";  break;
    case 11: echo "<script>location.replace('scenario_11.php');</script>";  break;

    case 12: echo "<script>location.replace('scenario_12.php');</script>";  break;

    case 20: echo "<script>location.replace('scenario_20.php');</script>";  break;
    case 21: echo "<script>location.replace('scenario_21.php');</script>";  break;
    case 22: echo "<script>location.replace('scenario_22.php');</script>";  break;
    case 23: echo "<script>location.replace('scenario_23.php');</script>";  break;
    case 24: echo "<script>location.replace('scenario_24.php');</script>";  break;
    case 25: echo "<script>location.replace('scenario_25.php');</script>";  break;
    case 26: echo "<script>location.replace('scenario_26.php');</script>";  break;
    case 27: echo "<script>location.replace('scenario_27.php');</script>";  break;
    case 28: echo "<script>location.replace('scenario_28.php');</script>";  break;
    default: echo "<script>location.replace('install3_ok.php');</script>";     break;
}
*/
function insertCity($name, $level, $pop2, $agri2, $comm2, $secu2, $def2, $wall2, $region, $path) {
    switch($level) {
    case 1: $pop =   5000; $agri =  100; $comm =  100; $secu =  100; $def =  500; $wall =  500; break;
    case 2: $pop =   5000; $agri =  100; $comm =  100; $secu =  100; $def =  500; $wall =  500; break;
    case 3: $pop =  10000; $agri =  100; $comm =  100; $secu =  100; $def = 1000; $wall = 1000; break;
    case 4: $pop =  50000; $agri = 1000; $comm = 1000; $secu = 1000; $def = 1000; $wall = 1000; break;
    case 5: $pop = 100000; $agri = 1000; $comm = 1000; $secu = 1000; $def = 2000; $wall = 2000; break;
    case 6: $pop = 100000; $agri = 1000; $comm = 1000; $secu = 1000; $def = 3000; $wall = 3000; break;
    case 7: $pop = 150000; $agri = 1000; $comm = 1000; $secu = 1000; $def = 4000; $wall = 4000; break;
    case 8: $pop = 150000; $agri = 1000; $comm = 1000; $secu = 1000; $def = 5000; $wall = 5000; break;
    }

    getDB()->insert('city',[
        'name'=>$name, 
        'level'=>$level, 
        'path'=>$path, 
        'pop'=>$pop, 
        'pop2'=>$pop2, 
        'agri'=>$agri, 
        'agri2'=>$agri2, 
        'comm'=>$comm, 
        'comm2'=>$comm2, 
        'secu'=>$secu, 
        'secu2'=>$secu2, 
        'rate'=>50,
        'trade'=>100, 
        'def'=>$def, 
        'def2'=>$def2, 
        'wall'=>$wall, 
        'wall2'=>$wall2, 
        'gen1'=>0, 
        'gen2'=>0, 
        'gen3'=>0, 
        'region'=>$region
    ]);
}

