<?php
//////////////////////////국가색////////////////////////////////////////////////
//"#FF0000",빨강 "#800000",갈색 "#A0522D",연갈색 "#FF6347",토마토 "#FFA500",오렌지
//"#FFDAB9",살색 "#FFD700",금색 "#FFFF00",노랑색 "#7CFC00",잔디색 "#00FF00",밝은녹색
//"#808000",카키 "#008000",녹색 "#2E8B57",청록색 "#008080",진청록 "#20B2AA",연청록
//"#6495ED",연보 "#7FFFD4",상아 "#AFEEEE",연상아 "#87CEEB",진상아 "#00FFFF",사이안
//"#00BFFF",하늘 "#0000FF",파랑 "#000080",바다색 "#483D8B",탁바다 "#7B68EE",연바다
//"#BA55D3",핑크 "#800080",보라 "#FF00FF",마젠타 "#FFC0CB",연핑크 "#F5F5DC",베이지
//"#E0FFFF",샤얀 "#FFFFFF",하양 "#A9A9A9",연회색
//////////////////////////국가종류//////////////////////////////////////////////
// 7 황제 6 왕 5 공 4 주목 3 주자사 2 군벌 1 호족 0 방랑군
////////////////////////////////////////////////////////////////////////
//////////////////////////국가종류//////////////////////////////////////////////
//도적 오두미도 태평도 도가 묵가 덕가 병가 유가 법가
////////////////////////////////////////////////////////////////////////
//////////////////////////장수성격//////////////////////////////////////////////
//은둔 안전 유지 재간 출세 할거 정복 패권 의협 대의 왕좌
////////////////////////////////////////////////////////////////////////
//////////////////////////도시목록//////////////////////////////////////////////
//   업 허창 낙양 장안 성도 양양 건업 북평 남피   완 수춘 서주 강릉 장사 시상 위례
//   계 복양 진류 여남 하비 서량 하내 한중 상용 덕양 강주 건녕 남해 계양   오 평양
// 사비 계림 진양 평원 북해   초   패 천수 안정 홍농 하변 자동 영안 귀양 주시 운남
// 남영 교지 신야 강하 무릉 영릉 상동 여강 회계 고창   대 양평 졸본 이도   강   저
// 흉노 남만 산월 오환   왜 호관 호로 사곡 함곡 사수 양평 가맹 역경 계교 동황 관도
// 정도 합비 광릉 적도 가정 기산 면죽 이릉 장판 백랑 적벽 파양 탐라 유구
////////////////////////////////////////////////////////////////////////
//////////////////////////관직정보//////////////////////////////////////
// 7 "황제"
// 6 "왕"
// 5 "공"
// 4 "주목"
// 3 "주자사"
// 2 "군벌"
// 1 "호족"
// 0 "방랑군"
////////////////////////////////////////////////////////////////////////

include "lib.php";
include "func.php";

$connect=dbConn();

$query = "select startyear,year,month,turnterm,scenario,extend,fiction,img from game where no='1'";
$result = MYDB_query($query, $connect) or Error("scenario_194A ".MYDB_error($connect),"");
$admin = MYDB_fetch_array($result);
$fiction = 1;    $turnterm = $admin['turnterm'];    $startyear = $admin['startyear'];    $year = $admin['year'];    $extend = $admin['extend'];
$img = $admin['img'];
//가상모드8 : 180년 확산성 밀리언 아서
/*
//////////////////////////국가1/////////////////////////////////////////////////
RegNation( "SM", "#000080", 10000, 10000,  "SM", 0, 1, "병가", 1);
//////////////////////////국가2/////////////////////////////////////////////////
RegNation("JYP", "#FF0000", 10000, 10000, "JYP", 0, 1, "덕가", 1);
//////////////////////////국가3/////////////////////////////////////////////////
RegNation( "YG", "#008000", 10000, 10000,  "YG", 0, 1, "유가", 1);

//////////////////////////외교//////////////////////////////////////////////////
$query = "insert into diplomacy (me, you, state, term) values ('1', '2', '2', '0')";
MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$query = "insert into diplomacy (me, you, state, term) values ('1', '3', '2', '0')";
MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

$query = "insert into diplomacy (me, you, state, term) values ('2', '1', '2', '0')";
MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$query = "insert into diplomacy (me, you, state, term) values ('2', '3', '2', '0')";
MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

$query = "insert into diplomacy (me, you, state, term) values ('3', '1', '2', '0')";
MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$query = "insert into diplomacy (me, you, state, term) values ('3', '2', '2', '0')";
MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
//////////////////////////외교 끝//////////////////////////////////////////////////
*/
//////////////////////////장수//////////////////////////////////////////////////
//                                                               상성           이름       사진 국가  도시   통  무  지 급 출생 사망    꿈     특기
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8000, 146,       "란슬롯",$img,8000,  0,    "-", 92, 91, 85, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8001,  83,       "가레스",$img,8001,  0,    "-", 79, 76, 84, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8002,  59, "금발의이졸데",$img,8002,  0,    "-", 77, 73, 84, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8003,  74,     "리오네스",$img,8003,  0,    "-", 62, 55, 77, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8004,  47,     "트리스탄",$img,8004,  0,    "-", 69, 70, 83, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8005,  62,         "우서",$img,8005,  0,    "-", 81, 80, 75, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8006, 103,       "엘레인",$img,8006,  0,    "-", 77, 74, 82, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8007,  33,         "리넷",$img,8007,  0,    "-", 63, 61, 91, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8008,  81,         "이텔",$img,8008,  0,    "-", 59, 52, 82, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8009,  69, "흰손의이졸데",$img,8009,  0,    "-", 49, 62, 65, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8010,  77, "아서검술의성",$img,8010,  0,    "-", 73, 70, 86, 0, 160, 300,    "-",    "-", "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8011,  56,   "온즈레이크",$img,8011,  0,    "-", 64, 64, 83, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8012, 135,     "가헤리스",$img,8012,  0,    "-", 69, 59, 84, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8013,  62,     "라모라크",$img,8013,  0,    "-", 60, 58, 81, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8014,  33,       "보티건",$img,8014,  0,    "-", 62, 67, 70, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8015,  76,         "엘렉",$img,8015,  0,    "-", 62, 65, 82, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8016,  12,       "마로스",$img,8016,  0,    "-", 56, 58, 74, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8017, 111,       "다마스",$img,8017,  0,    "-", 65, 60, 81, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8018,  81,       "빌헬름",$img,8018,  0,    "-", 81, 78, 77, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8019, 132,   "오디세우스",$img,8019,  0,    "-", 67, 63, 78, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8020,  62,         "시저",$img,8020,  0,    "-", 79, 76, 84, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8021,  65,       "시그룬",$img,8021,  0,    "-", 98, 96, 93, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8022,  63,     "나제지터",$img,8022,  0,    "-", 82, 78, 77, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8023, 138,       "한니발",$img,8023,  0,    "-", 75, 82, 80, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8024,   7,         "메리",$img,8024,  0,    "-", 74, 72, 80, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8025, 100,       "로빈훗",$img,8025,  0,    "-", 90, 87, 90, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8026,  42,     "세이메이",$img,8026,  0,    "-", 91, 92, 90, 0, 160, 300,    "-",    "-", "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8027,  14,       "어부왕",$img,8027,  0,    "-", 81, 80, 78, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8028,  33, "브란데고리스",$img,8028,  0,    "-", 74, 74, 80, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8029,  43,     "라이엔스",$img,8029,  0,    "-", 75, 76, 77, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8030,  26,         "롯뜨",$img,8030,  0,    "-", 79, 80, 80, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8031,  35,     "글리플렛",$img,8031,  0,    "-", 76, 75, 84, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8032,  26,       "베이란",$img,8032,  0,    "-", 71, 69, 83, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8033,  21,     "골로이스",$img,8033,  0,    "-", 66, 65, 81, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8034,   3,         "멀린",$img,8034,  0,    "-", 77, 69, 84, 0, 160, 300,    "-",    "-", "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8035, 134,     "모르가즈",$img,8035,  0,    "-", 76, 77, 86, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8036,  35,       "가웨인",$img,8036,  0,    "-", 90, 93, 88, 0, 160, 300,    "-",    "-", "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8037,  45,     "모드레드",$img,8037,  0,    "-", 98, 98, 91, 0, 160, 300,    "-",    "-", "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8038,  66,     "베더비어",$img,8038,  0,    "-", 57, 55, 82, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8039,  71, "유웨인라이온",$img,8039,  0,    "-", 58, 60, 77, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8040,   9,           "반",$img,8040,  0,    "-", 74, 66, 75, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8041,   5,   "콜그레반스",$img,8041,  0,    "-", 82, 75, 82, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8042, 142,       "유웨인",$img,8042,  0,    "-", 79, 74, 84, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8043,  25, "아서기교의장",$img,8043,  0,    "-", 73, 70, 86, 0, 160, 300,    "-",    "-", "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8044,  81,   "아그라바인",$img,8044,  0,    "-", 60, 60, 74, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8045,  93,       "칼디스",$img,8045,  0,    "-", 61, 59, 74, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8046,  35,       "디나단",$img,8046,  0,    "-", 89, 87, 88, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8047,  90,         "류넷",$img,8047,  0,    "-", 74, 66, 92, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8048,  77,       "보어스",$img,8048,  0,    "-", 76, 74, 77, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8049,  37,       "카엘단",$img,8049,  0,    "-", 58, 62, 74, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8050,  50, "칼로그레반스",$img,8050,  0,    "-", 62, 64, 67, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8051,  97,       "로디네",$img,8051,  0,    "-", 65, 64, 84, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8052,  72,   "마보나그린",$img,8052,  0,    "-", 63, 62, 70, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8053,  52,     "이카로스",$img,8053,  0,    "-", 91, 93, 89, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8054, 129,       "베르뷰",$img,8054,  0,    "-", 75, 72, 80, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8055, 104,       "오리온",$img,8055,  0,    "-", 77, 82, 81, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8056,  52, "게오르기오스",$img,8056,  0,    "-", 78, 76, 84, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8057,  25,     "잔다르크",$img,8057,  0,    "-", 72, 86, 75, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8058, 121,     "디트리히",$img,8058,  0,    "-", 70, 72, 82, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8059,  44,     "나폴레옹",$img,8059,  0,    "-", 79, 77, 81, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8060, 138,       "헬보르",$img,8060,  0,    "-", 71, 68, 81, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8061, 118,   "페르세우스",$img,8061,  0,    "-", 90, 91, 86, 0, 160, 300,    "-",    "-", "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8062,  62,   "루크레시아",$img,8062,  0,    "-", 78, 76, 83, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8063, 102,     "그린골렛",$img,8063,  0,    "-", 56, 54, 81, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8064,  65,         "케이",$img,8064,  0,    "-", 59, 58, 76, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8065,  80,     "유리엔스",$img,8065,  0,    "-", 70, 69, 80, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8066, 143,       "버나드",$img,8066,  0,    "-", 59, 56, 79, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8067, 102,         "루칸",$img,8067,  0,    "-", 66, 59, 86, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8068,  84,   "마녀엘레인",$img,8068,  0,    "-", 77, 75, 87, 0, 160, 300,    "-",    "-", "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8069,  76,     "콘스탄틴",$img,8069,  0,    "-",101, 97, 90, 0, 160, 300,    "-",    "-", "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8070,  95,     "갤러헤드",$img,8070,  0,    "-", 92, 89, 89, 0, 160, 300,    "-",    "-", "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8071,  16, "아이언사이드",$img,8071,  0,    "-", 64, 57, 73, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8072, 110,   "고트프리트",$img,8072,  0,    "-", 63, 66, 80, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8073,  50,     "로엔그린",$img,8073,  0,    "-", 78, 69, 77, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8074, 141,       "니무에",$img,8074,  0,    "-", 90, 90, 90, 0, 160, 300,    "-",    "-", "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8075,   8,     "올트리트",$img,8075,  0,    "-", 60, 55, 80, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8076,  15,   "테르라문트",$img,8076,  0,    "-", 61, 60, 75, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8077,  29,       "베이린",$img,8077,  0,    "-", 83, 73, 82, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8078, 150, "아서마법의파",$img,8078,  0,    "-", 73, 70, 86, 0, 160, 300,    "-",    "-", "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8079,  88,   "브랑크베인",$img,8079,  0,    "-", 65, 61, 78, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8080, 100,       "브리센",$img,8080,  0,    "-", 59, 51, 82, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8081,  58,       "라그넬",$img,8081,  0,    "-", 68, 64, 78, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8082, 102,     "블레어놀",$img,8082,  0,    "-", 56, 58, 78, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8083,  73,       "에니드",$img,8083,  0,    "-", 79, 74, 81, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8084, 117,         "엘자",$img,8084,  0,    "-", 67, 57, 75, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8085,  33,   "고르네먼트",$img,8085,  0,    "-", 69, 65, 77, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8086,  83,   "멜레어건스",$img,8086,  0,    "-", 59, 58, 81, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8087,  66,   "앙트와네트",$img,8087,  0,    "-", 79, 76, 86, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8088, 129, "클레오파트라",$img,8088,  0,    "-", 74, 75, 76, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8089, 105,       "살로메",$img,8089,  0,    "-", 75, 72, 78, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8090,  59,     "생제르맹",$img,8090,  0,    "-", 71, 69, 83, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8091,  54,   "산타클로스",$img,8091,  0,    "-", 70, 74, 80, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8092,  70, "스노우화이트",$img,8092,  0,    "-", 72, 69, 84, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8093,  62,       "다빈치",$img,8093,  0,    "-", 92, 88, 87, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8094, 135,   "리틀그레이",$img,8094,  0,    "-", 99, 98, 90, 0, 160, 300,    "-",    "-", "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8095,  36,     "카이히메",$img,8095,  0,    "-", 81, 77, 79, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8096,  73,       "히미코",$img,8096,  0,    "-", 78, 75, 83, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8097,  66,   "브란슈플르",$img,8097,  0,    "-", 67, 56, 70, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8098,  88,       "클레어",$img,8098,  0,    "-",100, 93, 97, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8099,  11, "성배의엘레인",$img,8099,  0,    "-", 72, 67, 78, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8100, 111,       "에타드",$img,8100,  0,    "-", 70, 71, 87, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8101,  25,         "갈론",$img,8101,  0,    "-", 63, 64, 86, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8102, 110,         "카돌",$img,8102,  0,    "-", 57, 63, 74, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8103, 124,         "터퀸",$img,8103,  0,    "-", 71, 64, 80, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8104, 145,       "퍼시발",$img,8104,  0,    "-", 74, 75, 76, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8105,  68,     "펠리노어",$img,8105,  0,    "-", 59, 53, 83, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8106,  70,     "기네비어",$img,8106,  0,    "-", 77, 74, 84, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8107, 132,       "모르간",$img,8107,  0,    "-", 93, 90, 84, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8108, 132,           "엘",$img,8108,  0,    "-", 89, 85, 97, 0, 160, 300,    "-",    "-", "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8109,   5,         "켈피",$img,8109,  0,    "-", 77, 84, 77, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8110,  75,       "듀라한",$img,8110,  0,    "-", 72, 76, 78, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8111, 120,   "드라이아드",$img,8111,  0,    "-", 58, 84, 71, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8112, 133,         "페이",$img,8112,  0,    "-", 91, 85, 96, 0, 160, 300,    "-",    "-", "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8113, 142,     "머메이드",$img,8113,  0,    "-", 91, 86, 88, 0, 160, 300,    "-",    "-", "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8114, 148,       "마나난",$img,8114,  0,    "-", 57, 61, 65, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8115, 120,         "리페",$img,8115,  0,    "-", 89, 88, 94, 0, 160, 300,    "-",    "-", "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8116,  16,   "리바이어선",$img,8116,  0,    "-", 90, 85, 90, 0, 160, 300,    "-",    "-", "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8117,  81,           "루",$img,8117,  0,    "-", 73, 73, 81, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8118,  84,     "레프라혼",$img,8118,  0,    "-", 90, 88, 92, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8119,  53, "비스크라브렛",$img,8119,  0,    "-", 64, 60,101, 0, 160, 300,    "-",    "-", "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8120,  71,     "루키우스",$img,8120,  0,    "-", 80, 76, 78, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8121, 138, "바그데마구스",$img,8121,  0,    "-", 61, 58, 74, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8122,  96,   "기프레이스",$img,8122,  0,    "-", 67, 66, 82, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8123, 150,         "토르",$img,8123,  0,    "-", 86, 88, 87, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8124,  95, "그로마소마죠",$img,8124,  0,    "-", 64, 63, 82, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8125, 135,       "마르크",$img,8125,  0,    "-", 65, 61, 82, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8126,  11, "보어스주니어",$img,8126,  0,    "-", 58, 53, 82, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8127, 136,     "가네이다",$img,8127,  0,    "-", 82, 72, 81, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8128, 120,         "론펄",$img,8128,  0,    "-", 65, 62, 87, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8129,  95,     "펠리어스",$img,8129,  0,    "-", 81, 71, 83, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8130,  49,     "메리어트",$img,8130,  0,    "-", 77, 72, 79, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8131,  35, "엑터드마리스",$img,8131,  0,    "-", 73, 67, 83, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8132,  64,         "헬린",$img,8132,  0,    "-", 59, 53, 83, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8133, 122, "암브로시우스",$img,8133,  0,    "-", 68, 59, 68, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8134,  73,         "올웬",$img,8134,  0,    "-", 60, 58, 76, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8135, 150,         "란솔",$img,8135,  0,    "-", 62, 61, 91, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8136,  25,       "에베인",$img,8136,  0,    "-", 73, 67, 87, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8137, 105,   "아그라바딘",$img,8137,  0,    "-", 66, 59, 87, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8138, 113,       "로엔나",$img,8138,  0,    "-", 67, 63, 84, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8139,  88,       "블루캡",$img,8139,  0,    "-", 85, 81, 80, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8140,  71,         "실키",$img,8140,  0,    "-", 86, 83, 78, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8141,   5,     "세리코트",$img,8141,  0,    "-", 78, 82, 83, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8142, 100,       "파울러",$img,8142,  0,    "-", 76, 69, 79, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8143,  98,   "그루아가흐",$img,8143,  0,    "-", 78, 90, 75, 0, 160, 300,    "-",    "-", "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8144, 109,         "푸카",$img,8144,  0,    "-", 77, 74, 85, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8145,  87,         "픽시",$img,8145,  0,    "-", 86, 69, 75, 0, 160, 300,    "-",    "-", "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8146,  40,       "무리안",$img,8146,  0,    "-", 83, 86, 80, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8147,  10,   "녹색의기사",$img,8147,  0,    "-", 82, 75, 84, 0, 160, 300,    "-",    "-", "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8148, 112,         "여왕",$img,8148,  0,    "-", 73, 73, 73, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8149,   9,   "선발의기사",$img,8149,  0,    "-", 70, 74, 83, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8150,  85, "시시라라팬텀",$img,8150,  0,    "-", 88, 81, 97, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8151,   1, "시시라라퓨어",$img,8151,  0,    "-", 85, 81, 99, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8152,  54, "시시라라빠삐",$img,8152,  0,    "-", 84, 83,103, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8153,   2, "시시라라오버",$img,8153,  0,    "-", 95, 91, 99, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8154, 119,     "페리도트",$img,8154,  0,    "-", 90, 90, 88, 0, 160, 300,    "-",    "-", "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8155,  46,         "캔디",$img,8155,  0,    "-", 78, 75, 86, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8156,  33,       "화니타",$img,8156,  0,    "-", 82, 75, 81, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8157, 140,       "티니아",$img,8157,  0,    "-", 80, 81, 82, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8158, 113,         "팬지",$img,8158,  0,    "-", 78, 71, 79, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8159,  54,     "시트링크",$img,8159,  0,    "-", 93, 86, 90, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8160,  54,       "슬리트",$img,8160,  0,    "-", 70, 63, 75, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8161,  84,       "체리니",$img,8161,  0,    "-", 66, 68, 80, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8162, 104,     "스사노오",$img,8162,  0,    "-", 87, 83, 89, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8163, 139,   "레클레어스",$img,8163,  0,    "-", 79, 81, 79, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8164, 148,         "가넷",$img,8164,  0,    "-", 81, 77, 80, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8165,  47,     "사파이어",$img,8165,  0,    "-", 84, 74, 80, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8166, 142,       "토파즈",$img,8166,  0,    "-", 72, 75, 82, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8167,   5,       "스와리",$img,8167,  0,    "-", 74, 75, 81, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8168,  67,     "카넬리언",$img,8168,  0,    "-", 84, 76, 81, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8169, 116,         "퍼기",$img,8169,  0,    "-", 76, 75, 87, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8170, 127,         "라바",$img,8170,  0,    "-", 75, 75, 81, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8171,  30,       "오닉스",$img,8171,  0,    "-", 74, 72, 81, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8172, 133,       "라피스",$img,8172,  0,    "-", 98, 98, 90, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8173,  11,     "터쿼이즈",$img,8173,  0,    "-", 76, 73, 79, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8174, 131,   "셀레나이트",$img,8174,  0,    "-", 74, 74, 83, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8175, 118,         "피케",$img,8175,  0,    "-", 76, 75, 80, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8176,  50,         "듀티",$img,8176,  0,    "-", 82, 79, 80, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8177,  15,       "키안티",$img,8177,  0,    "-", 77, 74, 80, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8178,  53,         "멜트",$img,8178,  0,    "-", 82, 78, 80, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8179,  68,         "쿼츠",$img,8179,  0,    "-", 78, 71, 77, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8180, 126,         "마린",$img,8180,  0,    "-", 84, 78, 81, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8181,  19,         "루비",$img,8181,  0,    "-", 78, 81, 80, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8182,  38,         "에기",$img,8182,  0,    "-", 74, 75, 81, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8183,  36,     "프란시스",$img,8183,  0,    "-", 77, 71, 79, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8184, 145,       "유니아",$img,8184,  0,    "-", 81, 80, 82, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8185, 117,     "아이리쉬",$img,8185,  0,    "-", 74, 62, 80, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8186,  21,         "란테",$img,8186,  0,    "-", 71, 78, 75, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8187,  68,         "라비",$img,8187,  0,    "-", 88, 86, 92, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8188, 131,       "슬랩스",$img,8188,  0,    "-", 78, 79, 90, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8189,  94,       "조르쥬",$img,8189,  0,    "-", 66, 49, 66, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8190, 112,   "에톨리레어",$img,8190,  0,    "-", 69, 74, 69, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8191, 147,         "랭크",$img,8191,  0,    "-", 68, 60, 66, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8192, 117,     "로자리아",$img,8192,  0,    "-", 80, 81, 79, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8193, 123,       "우네리",$img,8193,  0,    "-", 80, 85, 82, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8194,  26,     "카토레아",$img,8194,  0,    "-", 91, 89, 86, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8195,  28,     "샐러피어",$img,8195,  0,    "-", 85, 85, 83, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8196,  20,         "포클",$img,8196,  0,    "-", 85, 82, 81, 0, 160, 300,    "-",    "-", "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8197,  35,     "시시리아",$img,8197,  0,    "-", 72, 88, 82, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8198, 133,       "엘레나",$img,8198,  0,    "-", 79, 83, 78, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8199,  72,       "리리드",$img,8199,  0,    "-", 71, 77, 91, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8200,   5,     "아테나이",$img,8200,  0,    "-", 80, 88, 82, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8201, 118,         "펑크",$img,8201,  0,    "-", 67, 67, 71, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8202, 113,         "니스",$img,8202,  0,    "-", 82, 83, 87, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8203,  24,       "키쵸우",$img,8203,  0,    "-", 78, 79, 90, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8204,  32,         "멘탈",$img,8204,  0,    "-", 69, 70, 78, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8205, 105,         "하른",$img,8205,  0,    "-", 86, 77, 83, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8206,  71,       "엑시올",$img,8206,  0,    "-", 69, 55, 71, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8207,  57,         "오트",$img,8207,  0,    "-", 79, 85, 92, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8208, 115,     "에피드나",$img,8208,  0,    "-", 86, 78, 87, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8209,  89,       "테리메",$img,8209,  0,    "-", 92, 97, 96, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8210, 139,   "알카로이드",$img,8210,  0,    "-", 90, 92, 86, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8211, 125,       "라스카",$img,8211,  0,    "-", 78, 73, 83, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8212,   9,       "크럭키",$img,8212,  0,    "-", 81, 83, 91, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8213,  83,       "치아리",$img,8213,  0,    "-", 80, 80, 50, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8214,  54,   "수퍼치아리",$img,8214,  0,    "-", 80, 80, 50, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8215,  43, "얼티밋치아리",$img,8215,  0,    "-", 80, 80, 50, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8216,  75, "브레이크치아",$img,8216,  0,    "-", 80, 80, 50, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8217,  97,       "쿠루밍",$img,8217,  0,    "-", 90, 83, 84, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8218,  43,         "로벨",$img,8218,  0,    "-", 97,102, 93, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8219, 125,       "바토리",$img,8219,  0,    "-", 85, 85, 87, 0, 160, 300,    "-",    "-", "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8220, 101,         "소랑",$img,8220,  0,    "-", 88, 89, 87, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8221,  11,       "어우동",$img,8221,  0,    "-", 99, 97, 91, 0, 160, 300,    "-",    "-", "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8222,  80,         "춘향",$img,8222,  0,    "-", 96,101, 90, 0, 160, 300,    "-",    "-", "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8223, 132,         "묘묘",$img,8223,  0,    "-", 90, 91, 87, 0, 160, 300,    "-",    "-", "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8224,  94,       "석탈해",$img,8224,  0,    "-", 93, 90, 87, 0, 160, 300,    "-",    "-", "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8225, 115,         "색동",$img,8225,  0,    "-", 87, 88, 89, 0, 160, 300,    "-",    "-", "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8226,  47,         "로키",$img,8226,  0,    "-", 84, 91, 85, 0, 160, 300,    "-",    "-", "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8227,  21,       "세이렌",$img,8227,  0,    "-", 94, 95, 85, 0, 160, 300,    "-",    "-", "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8228,  40,     "카나리아",$img,8228,  0,    "-", 75, 69, 72, 0, 160, 300,    "-",    "-", "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,8229, 110,   "지크프리트",$img,8229,  0,    "-", 92, 93, 85, 0, 160, 300,    "-",    "-", "-");



//////////////////////////장수 끝///////////////////////////////////////////////

//////////////////////////도시 소속/////////////////////////////////////////////
//RegCity(0, "업");
//RegCity(0, "허창");
//RegCity(0,    "-");
//RegCity(0, "장안");
//RegCity(0, "성도");
//RegCity(0, "양양");
//RegCity(0, "건업");

//RegCity(0, "북평");
//RegCity(0, "남피");
//RegCity(0, "완");
//RegCity(0, "수춘");
//RegCity(0, "서주");
//RegCity(0, "강릉");
//RegCity(0, "장사");
//RegCity(0, "시상");
//RegCity(0, "위례");

//RegCity(0, "계");
//RegCity(0, "복양");
//RegCity(0, "진류");
//RegCity(0, "여남");
//RegCity(0, "하비");
//RegCity(0, "서량");
//RegCity(0, "하내");
//RegCity(0, "한중");
//RegCity(0, "상용");
//RegCity(0, "덕양");
//RegCity(0, "강주");
//RegCity(0, "건녕");
//RegCity(0, "남해");
//RegCity(0, "계양");
//RegCity(0, "오");
//RegCity(0, "평양");
//RegCity(0, "사비");
//RegCity(0, "계림");

//RegCity(0, "진양");
//RegCity(0, "평원");
//RegCity(0, "북해");
//RegCity(0, "초");
//RegCity(0, "패");
//RegCity(0, "천수");
//RegCity(0, "안정");
//RegCity(0, "홍농");
//RegCity(0, "하변");
//RegCity(0, "자동");
//RegCity(0, "영안");
//RegCity(0, "귀양");
//RegCity(0, "주시");
//RegCity(0, "운남");
//RegCity(0, "남영");
//RegCity(0, "교지");
//RegCity(0, "신야");
//RegCity(0, "강하");
//RegCity(0, "무릉");
//RegCity(0, "영릉");
//RegCity(0, "상동");
//RegCity(0, "여강");
//RegCity(0, "회계");
//RegCity(0, "고창");
//RegCity(0, "대");
//RegCity(0, "안평");
//RegCity(0, "졸본");
//RegCity(0, "이도");

//RegCity(0, "강");
//RegCity(0, "저");
//RegCity(0, "흉노");
//RegCity(0, "남만");
//RegCity(0, "산월");
//RegCity(0, "오환");
//RegCity(0, "왜");

//RegCity(0, "호관");
//RegCity(0, "호로");
//RegCity(0, "사곡");
//RegCity(0, "함곡");
//RegCity(0, "사수");
//RegCity(0, "양평");
//RegCity(0, "가맹");
//RegCity(0, "역경");
//RegCity(0, "계교");
//RegCity(0, "동황");
//RegCity(0, "관도");
//RegCity(0, "정도");
//RegCity(0, "합비");
//RegCity(0, "광릉");
//RegCity(0, "적도");
//RegCity(0, "가정");
//RegCity(0, "기산");
//RegCity(0, "면죽");
//RegCity(0, "이릉");
//RegCity(0, "장판");
//RegCity(0, "백랑");

//RegCity(0, "적벽");
//RegCity(0, "파양");
//RegCity(0, "탐라");
//RegCity(0, "유구");

//////////////////////////도시 끝///////////////////////////////////////////////

//////////////////////////이벤트///////////////////////////////////////////////

$history[count($history)] = "<C>●</>180년 1월:<L><b>【가상모드8】</b>확산성 밀리언 아서</>";
$history[count($history)] = "<C>●</>180년 1월:<L><b>【이벤트】</b></>삼모전에 확밀아가 빙의됩니다!";
pushHistory($history);

//echo "<script>location.replace('install3_ok.php');</script>";
echo 'install3_ok.php';//TODO:debug all and replace
