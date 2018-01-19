<?php
//////////////////////////국가색////////////////////////////////////////////////
//"FF0000",빨강 "800000",갈색 "A0522D",연갈색 "FF6347",토마토 "FFA500",오렌지
//"FFDAB9",살색 "FFD700",금색 "FFFF00",노랑색 "7CFC00",잔디색 "00FF00",밝은녹색
//"808000",카키 "008000",녹색 "2E8B57",청록색 "008080",진청록 "20B2AA",연청록
//"6495ED",연보 "7FFFD4",상아 "AFEEEE",연상아 "87CEEB",진상아 "00FFFF",사이안
//"00BFFF",하늘 "0000FF",파랑 "000080",바다색 "483D8B",탁바다 "7B68EE",연바다
//"BA55D3",핑크 "800080",보라 "FF00FF",마젠타 "FFC0CB",연핑크 "F5F5DC",베이지
//"E0FFFF",샤얀 "FFFFFF",하양 "A9A9A9",연회색
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
//가상모드7 : 180년 가요대잔치
/*
//////////////////////////국가1/////////////////////////////////////////////////
RegNation($connect,  "SM", "000080", 10000, 10000,  "SM", 0, 1, "병가", 1);
//////////////////////////국가2/////////////////////////////////////////////////
RegNation($connect, "JYP", "FF0000", 10000, 10000, "JYP", 0, 1, "덕가", 1);
//////////////////////////국가3/////////////////////////////////////////////////
RegNation($connect,  "YG", "008000", 10000, 10000,  "YG", 0, 1, "유가", 1);

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
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1001,  25,       "이수만",$img,9009,  0,    "-", 81, 20, 74, 0, 160, 300,    "-",    "-", "SM이 갑이제");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1002,  25,         "강타",$img,  -1,  0,    "-", 83, 61, 66, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1003,  10,       "장우혁",$img,  -1,  0,    "-", 52, 92, 73, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1004,  10,       "이재원",$img,  -1,  0,    "-", 50, 73, 51, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1005,  40,       "문희준",$img,  -1,  0,    "-", 67, 70, 41, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1006,  10,         "토니",$img,  -1,  0,    "-", 71, 55, 72, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1007,  40,         "바다",$img,  -1,  0,    "-", 94, 67, 36, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1008,  25,         "유진",$img,  -1,  0,    "-", 70, 63, 87, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1009,  10,           "슈",$img,  -1,  0,    "-", 75, 61, 72, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1010,  25,         "환희",$img,  -1,  0,    "-", 92, 64, 48, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1011,  25,     "브라이언",$img,  -1,  0,    "-", 73, 70, 78, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1012,  25, "천무스테파니",$img,  -1,  0,    "-", 32, 95, 71, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1013,  25,     "상미린아",$img,  -1,  0,    "-", 72, 31, 78, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1014,  25,   "지성선데이",$img,  -1,  0,    "-", 60, 62, 47, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1015,  25,     "희열다나",$img,  -1,  0,    "-", 76, 53, 69, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1016,  25,         "기범",$img,  -1,  0,    "-", 57, 52, 84, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1017,  25,         "한경",$img,  -1,  0,    "-", 63, 67, 64, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1018,  25,         "강인",$img,  -1,  0,    "-", 70, 62, 75, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1019,  25,         "이특",$img,  -1,  0,    "-", 62, 59, 68, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1020,  25,         "려욱",$img,  -1,  0,    "-", 70, 31, 58, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1021,  25,         "성민",$img,  -1,  0,    "-", 65, 51, 64, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1022,  25,         "신동",$img,  -1,  0,    "-", 50, 74, 61, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1023,  25,         "은혁",$img,  -1,  0,    "-", 65, 75, 68, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1024,  25,         "예성",$img,  -1,  0,    "-", 76, 37, 54, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1025,  25,         "시원",$img,  -1,  0,    "-", 42, 66, 85, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1026,  25,         "규현",$img,  -1,  0,    "-", 71, 46, 64, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1027,  25,         "희철",$img,  -1,  0,    "-", 60, 47, 62, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1028,  25,         "동해",$img,  -1,  0,    "-", 47, 62, 70, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1029,  25,         "태연",$img,9003,  0,    "-", 98, 71, 97, 0, 160, 300,    "-",    "-", "어리다고 놀리지 말아요~");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1030,  25,       "티파니",$img,9001,  0,    "-", 84, 66, 98, 0, 160, 300,    "-",    "-", "빠밤~");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1031,  25,         "유리",$img,9000,  0,    "-", 71, 93, 96, 0, 160, 300,    "-",    "-", "뿌잉뿌잉");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1032,  25,       "제시카",$img,9002,  0,    "-", 95, 69, 92, 0, 160, 300,    "-",    "-", "Bring the boys out!");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1033,  25,         "수영",$img,9008,  0,    "-", 78, 83, 83, 0, 160, 300,    "-",    "-", "기럭지 하면 나~");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1034,  25,         "써니",$img,9007,  0,    "-", 83, 63, 85, 0, 160, 300,    "-",    "-", "소녀시대에서 귀여움을 맡고 있죠~");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1035,  25,         "윤아",$img,9005,  0,    "-", 61, 84, 99, 0, 160, 300,    "-",    "-", "사슴 융이에요~");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1036,  25,         "서현",$img,9004,  0,    "-", 80, 72, 95, 0, 160, 300,    "-",    "-", "막내 포텐 폭발~");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1037,  25,         "효연",$img,9006,  0,    "-", 70, 99, 74, 0, 160, 300,    "-",    "-", "댄스퀸~");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1038,  25,         "온유",$img,  -1,  0,    "-", 60, 62, 72, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1039,  25,         "종현",$img,  -1,  0,    "-", 70, 75, 80, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1040,  25,           "키",$img,  -1,  0,    "-", 62, 60, 73, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1041,  25,         "민호",$img,  -1,  0,    "-", 60, 71, 64, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1042,  25,         "태민",$img,  -1,  0,    "-", 75, 70, 81, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1043,  20,       "이민우",$img,  -1,  0,    "-", 72, 85, 78, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1044,  25,       "신혜성",$img,  -1,  0,    "-", 85, 37, 80, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1045,  30,         "에릭",$img,  -1,  0,    "-", 50, 72, 83, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1046,  25,         "앤디",$img,  -1,  0,    "-", 55, 62, 75, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1047,  20,         "전진",$img,  -1,  0,    "-", 62, 90, 71, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1048,  30,       "김동완",$img,  -1,  0,    "-", 83, 60, 70, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1049,  25,     "유노윤호",$img,  -1,  0,    "-", 77, 92, 82, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1050,  25,     "최강창민",$img,  -1,  0,    "-", 82, 72, 70, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1051,  25,     "시아준수",$img,  -1,  0,    "-", 94, 68, 72, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1052,  25,     "영웅재중",$img,  -1,  0,    "-", 72, 63, 79, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1053,  25,     "믹키유천",$img,  -1,  0,    "-", 70, 68, 90, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1054,  25,         "보아",$img,  -1,  0,    "-", 87, 84, 82, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1055,  25,       "장리인",$img,  -1,  0,    "-", 85, 17, 62, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1056,  75,       "박진영",$img,9010,  0,    "-", 73, 90, 17, 0, 160, 300,    "-",    "-", "게...게임할까");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1057,  75,       "윤계상",$img,  -1,  0,    "-", 63, 37, 72, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1058,  75,       "손호영",$img,  -1,  0,    "-", 71, 48, 78, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1059,  75,       "데니안",$img,  -1,  0,    "-", 47, 52, 68, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1060,  75,           "준",$img,  -1,  0,    "-", 37, 73, 74, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1061,  75,       "김태우",$img,  -1,  0,    "-", 84, 37, 32, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1062,  75,         "선미",$img,  -1,  0,    "-", 63, 68, 87, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1063,  75,         "소희",$img,  -1,  0,    "-", 46, 73, 83, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1064,  75,         "선예",$img,  -1,  0,    "-", 82, 76, 73, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1065,  75,         "예은",$img,  -1,  0,    "-", 53, 61, 60, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1066,  75,         "유빈",$img,  -1,  0,    "-", 48, 77, 72, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1067,  75,           "주",$img,  -1,  0,    "-", 80, 12, 37, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1068,  75,           "별",$img,  -1,  0,    "-", 83, 46, 77, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1069,  55,           "비",$img,9011,  0,    "-", 92, 86, 89, 0, 160, 300,    "-",    "-", "우워어우어어어~");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1070, 125,       "양현석",$img,9012,  0,    "-", 50, 88, 72, 0, 160, 300,    "-",    "-", "YG 무시하냐능");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1071, 125,          "TOP",$img,  -1,  0,    "-", 77, 72, 78, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1072, 125,         "승리",$img,  -1,  0,    "-", 52, 73, 64, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1073, 125,         "태양",$img,  -1,  0,    "-", 83, 82, 66, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1074, 125,         "대성",$img,  -1,  0,    "-", 74, 68, 43, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1075, 125,      "G드래곤",$img,  -1,  0,    "-", 78, 63, 73, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1076, 140,       "아이비",$img,  -1,  0,    "-", 73, 86, 82, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1077, 125,         "거미",$img,  -1,  0,    "-", 90, 34, 37, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1078, 110,         "휘성",$img,  -1,  0,    "-", 86, 66, 62, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1079, 120,         "세븐",$img,  -1,  0,    "-", 78, 73, 80, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1080,   0,       "강성훈",$img,  -1,  0,    "-", 82, 47, 75, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1081,   0,       "은지원",$img,  -1,  0,    "-", 65, 62, 74, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1082,   0,       "고지용",$img,  -1,  0,    "-", 65, 38, 67, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1083,   0,       "장수원",$img,  -1,  0,    "-", 73, 37, 71, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1084,   0,      "이재진1",$img,  -1,  0,    "-", 45, 84, 67, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1085,   0,       "김재덕",$img,  -1,  0,    "-", 50, 81, 57, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1086,   0,       "옥주현",$img,  -1,  0,    "-", 92, 55, 33, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1087,   0,       "이효리",$img,  -1,  0,    "-", 75, 72, 87, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1088,   0,         "이진",$img,  -1,  0,    "-", 66, 38, 81, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1089,   0,       "성유리",$img,  -1,  0,    "-", 63, 41, 95, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1090,   0,       "한승연",$img,  -1,  0,    "-", 60, 62, 82, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1091,   0,         "나얼",$img,  -1,  0,    "-", 90, 10, 52, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1092,   0,         "윤건",$img,  -1,  0,    "-", 79, 16, 62, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1093,   0,         "제아",$img,  -1,  0,    "-", 87, 39, 73, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1094,   0,       "나르샤",$img,  -1,  0,    "-", 76, 46, 71, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1095,   0,         "미료",$img,  -1,  0,    "-", 80, 41, 64, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1096,   0,         "가인",$img,  -1,  0,    "-", 88, 51, 73, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1097,   0,       "남규리",$img,  -1,  0,    "-", 73, 47, 87, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1098,   0,       "김연지",$img,  -1,  0,    "-", 72, 38, 71, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1099,   0,       "이보람",$img,  -1,  0,    "-", 83, 48, 61, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1100,   0,       "타블로",$img,  -1,  0,    "-", 64, 38, 73, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1101,   0,     "미쓰라진",$img,  -1,  0,    "-", 68, 42, 68, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1102,   0,         "투컷",$img,  -1,  0,    "-", 48, 37, 52, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1103,   0,       "박정아",$img,  -1,  0,    "-", 76, 63, 72, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1104,   0,       "서인영",$img,  -1,  0,    "-", 73, 72, 62, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1105,   0,       "조민아",$img,  -1,  0,    "-", 70, 47, 68, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1106,   0,       "이지현",$img,  -1,  0,    "-", 60, 57, 75, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1107,   0,       "강산에",$img,  -1,  0,    "-", 73, 17, 37, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1108,   0,       "고유진",$img,  -1,  0,    "-", 80, 14, 70, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1109,   0,       "김건모",$img,  -1,  0,    "-", 81, 62, 37, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1110,   0,       "김경호",$img,  -1,  0,    "-", 93, 21, 60, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1111,   0,       "김광석",$img,  -1,  0,    "-", 80, 20, 63, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1112,   0,       "김동률",$img,  -1,  0,    "-", 84, 14, 68, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1113,   0,       "김민종",$img,  -1,  0,    "-", 73, 36, 73, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1114,   0,       "김범수",$img,  -1,  0,    "-", 86, 15, 47, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1115,   0,       "김장훈",$img,  -1,  0,    "-", 68, 48, 62, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1116,   0,       "김정민",$img,  -1,  0,    "-", 82, 21, 84, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1117,   0,       "김종국",$img,  -1,  0,    "-", 78, 38, 62, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1118,   0,       "김종서",$img,  -1,  0,    "-", 79, 26, 72, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1119,   0,       "김창렬",$img,  -1,  0,    "-", 92, 48, 48, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1120,   0,       "김현정",$img,  -1,  0,    "-", 83, 73, 72, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1121,   0,       "김현철",$img,  -1,  0,    "-", 84, 26, 62, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1122,   0,       "마이키",$img,  -1,  0,    "-", 77, 62, 67, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1123,   0,       "민경훈",$img,  -1,  0,    "-", 79, 16, 74, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1124,   0,       "박명수",$img,  -1,  0,    "-", 61, 66, 24, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1125,   0,       "박미경",$img,  -1,  0,    "-", 84, 52, 63, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1126,   0,       "박상민",$img,  -1,  0,    "-", 89, 36, 55, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1127,   0,       "박완규",$img,  -1,  0,    "-", 92, 13, 46, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1128,   0,       "박지윤",$img,  -1,  0,    "-", 74, 62, 72, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1129,   0,       "박현빈",$img,  -1,  0,    "-", 88, 72, 83, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1130,   0,       "박혜경",$img,  -1,  0,    "-", 84, 16, 36, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1131,   0,       "박효신",$img,  -1,  0,    "-", 86, 15, 61, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1132,   0,     "박화요비",$img,  -1,  0,    "-", 82, 21, 57, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1133,   0,       "백지영",$img,  -1,  0,    "-", 79, 81, 74, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1134,   0,       "서문탁",$img,  -1,  0,    "-", 94, 35, 47, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1135,   0,       "서영은",$img,  -1,  0,    "-", 82, 25, 73, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1136,   0,       "서지원",$img,  -1,  0,    "-", 71, 25, 62, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1137,   0,       "서태지",$img,  -1,  0,    "-", 73, 38, 82, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1138,   0,       "설운도",$img,  -1,  0,    "-", 82, 54, 45, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1139,   0,       "성시경",$img,  -1,  0,    "-", 86, 11, 71, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1140,   0,       "손담비",$img,  -1,  0,    "-", 70, 72, 83, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1141,   0,       "송대관",$img,9013,  0,    "-", 93, 18, 64, 0, 160, 300,    "-",    "-", "쿵짝쿵짝 쿵짜자쿵짝 네박자 속에~");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1142,   0,       "신성우",$img,  -1,  0,    "-", 81, 18, 72, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1143,   0,       "신승훈",$img,  -1,  0,    "-", 86, 26, 61, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1144,   0,       "신해철",$img,  -1,  0,    "-", 81, 16, 52, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1145,   0,         "싸이",$img,9014,  0,    "-", 84, 94, 37, 0, 160, 300,    "-",    "-", "오빤 강남스타일!");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1146,   0,       "안재욱",$img,  -1,  0,    "-", 72, 37, 67, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1147,   0,       "안치환",$img,  -1,  0,    "-", 84, 14, 63, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1148,   0,       "양동근",$img,  -1,  0,    "-", 77, 63, 47, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1149,   0,         "양파",$img,  -1,  0,    "-", 85, 22, 47, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1150,   0,       "엄정화",$img,  -1,  0,    "-", 73, 63, 62, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1151,   0,         "왁스",$img,  -1,  0,    "-", 86, 25, 60, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1152,   0,       "유승준",$img,  -1,  0,    "-", 78, 82, 73, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1153,   0,       "유희열",$img,  -1,  0,    "-", 81, 13, 63, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1154,   0,       "윤도현",$img,  -1,  0,    "-", 85, 17, 71, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1155,   0,       "윤종신",$img,  -1,  0,    "-", 76, 15, 61, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1156,   0,         "윤하",$img,  -1,  0,    "-", 81, 21, 75, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1157,   0,       "이기찬",$img,  -1,  0,    "-", 82, 10, 64, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1158,   0,         "이루",$img,  -1,  0,    "-", 78, 20, 65, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1159,   0,       "이문세",$img,  -1,  0,    "-", 85, 15, 40, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1160,   0,       "이소라",$img,  -1,  0,    "-", 91, 10, 40, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1161,   0,       "이수영",$img,  -1,  0,    "-", 81, 50, 50, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1162,   0,       "이승기",$img,  -1,  0,    "-", 80, 25, 81, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1163,   0,       "이승철",$img,  -1,  0,    "-", 93, 47, 71, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1164,   0,       "이승환",$img,  -1,  0,    "-", 73, 55, 81, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1165,   0,       "이예린",$img,  -1,  0,    "-", 73, 67, 60, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1166,   0,         "이정",$img,  -1,  0,    "-", 79, 68, 52, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1167,   0,       "이정현",$img,  -1,  0,    "-", 72, 61, 63, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1168,   0,       "이지훈",$img,  -1,  0,    "-", 78, 26, 79, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1169,   0,       "이하늘",$img,  -1,  0,    "-", 57, 83, 30, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1170,   0,       "이현우",$img,  -1,  0,    "-", 71, 26, 65, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1171,   0,     "일기예보",$img,  -1,  0,    "-", 78, 15, 47, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1172,   0,       "임재범",$img,  -1,  0,    "-", 89, 26, 52, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1173,   0,         "일락",$img,  -1,  0,    "-", 79, 26, 61, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1174,   0,       "임정희",$img,  -1,  0,    "-", 83, 37, 66, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1175,   0,       "임창정",$img,  -1,  0,    "-", 88, 68, 62, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1176,   0,         "자두",$img,  -1,  0,    "-", 82, 37, 38, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1177,   0,       "장나라",$img,  -1,  0,    "-", 74, 38, 72, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1178,   0,       "장윤정",$img,  -1,  0,    "-", 88, 61, 84, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1179,   0,       "장혜진",$img,  -1,  0,    "-", 84, 10, 64, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1180,   0,       "전람회",$img,  -1,  0,    "-", 81, 20, 58, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1181,   0,       "정재용",$img,  -1,  0,    "-", 60, 72, 72, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1182,   0,       "조관우",$img,  -1,  0,    "-", 86, 17, 47, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1183,   0,       "조성모",$img,  -1,  0,    "-", 82, 58, 62, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1184,   0,       "조수미",$img,  -1,  0,    "-", 99, 10, 43, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1185,   0,       "조장혁",$img,  -1,  0,    "-", 78, 20, 67, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1186,   0,         "조PD",$img,  -1,  0,    "-", 62, 27, 53, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1187,   0,       "주영훈",$img,  -1,  0,    "-", 74, 12, 47, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1188,   0,         "진주",$img,  -1,  0,    "-", 85, 20, 37, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1189,   0,       "태진아",$img,9015,  0,    "-", 87, 24, 60, 0, 160, 300,    "-",    "-", "다음 순서는~ 동바~안자!");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1190,   0,       "차태현",$img,  -1,  0,    "-", 71, 26, 68, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1191,   0,         "채연",$img,  -1,  0,    "-", 72, 72, 77, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1192,   0,       "최재훈",$img,  -1,  0,    "-", 85, 17, 69, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1193,   0,         "하하",$img,  -1,  0,    "-", 65, 62, 61, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1194,   0,         "MC몽",$img,  -1,  0,    "-", 60, 70, 47, 0, 160, 300,    "-",    "-");



RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1195,   0,       "전효성",$img,9016,  0,    "-", 92, 82, 96, 0, 160, 300,    "-",    "-", "마돈나 돈나 마돈나 돈나 돈나");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1196,   0,         "징거",$img,  -1,  0,    "-", 76, 72, 63, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1197,   0,       "한선화",$img,9017,  0,    "-", 77, 63, 84, 0, 160, 300,    "-",    "-", "백치미가 뭐에요?");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1198,   0,       "송지은",$img,9018,  0,    "-", 90, 71, 83, 0, 160, 300,    "-",    "-", "슈비두바 빠빠빠 슈비두바 빠빠빠");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1199,   0,         "보람",$img,9019,  0,    "-", 69, 71, 82, 0, 160, 300,    "-",    "-", "박수를...짝짝짝!!");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1200,   0,         "지연",$img,9020,  0,    "-", 65, 79, 94, 0, 160, 300,    "-",    "-", "멘탈갑 박얘쁜이예요. 의지의차이^^ 개념있게^^ 항상겸손하기^^ 연기천재 박수를드려요^^");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1201,   0,         "효민",$img,9021,  0,    "-", 69, 70, 82, 0, 160, 300,    "-",    "-", "생수머신 효민이에요. 의지의 차이 ^^ 우리 모두 의지를갖고 화이팅!!!!!!");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1202,   0,         "은정",$img,9022,  0,    "-", 73, 66, 72, 0, 160, 300,    "-",    "-", "떡은정!");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1203,   0,         "큐리",$img,  -1,  0,    "-", 61, 68, 69, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1204,   0,         "소연",$img,9023,  0,    "-", 80, 70, 81, 0, 160, 300,    "-",    "-", "의지+예의+배려 의 차이♥ 오늘도 우리 힘내자구");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1205,   0,       "류화영",$img,9024,  0,    "-", 70, 69, 64, 0, 160, 300,    "-",    "-", "광수 나쁜 놈아");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1206,   0,       "김현아",$img,9025,  0,    "-", 68, 92, 82, 0, 160, 300,    "-",    "-", "오빤 딱 내스타일!");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1207,   0,       "남지현",$img,  -1,  0,    "-", 75, 72, 73, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1208,   0,       "허가윤",$img,  -1,  0,    "-", 75, 70, 79, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1209,   0,       "전지윤",$img,  -1,  0,    "-", 71, 73, 66, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1210,   0,       "권소현",$img,  -1,  0,    "-", 74, 68, 70, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1211,   0,       "김재경",$img,  -1,  0,    "-", 62, 56, 81, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1212,   0,       "고우리",$img,  -1,  0,    "-", 58, 64, 77, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1213,   0,       "김지숙",$img,  -1,  0,    "-", 59, 66, 64, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1214,   0,         "오늘",$img,  -1,  0,    "-", 32, 67, 62, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1215,   0,       "오승아",$img,  -1,  0,    "-", 64, 62, 60, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1216,   0,       "정윤혜",$img,  -1,  0,    "-", 59, 63, 58, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1217,   0,       "조현영",$img,  -1,  0,    "-", 58, 57, 61, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1218,   0,         "소아",$img,  -1,  0,    "-", 62, 54, 65, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1219,   0,         "티아",$img,  -1,  0,    "-", 45, 41, 66, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1220,   0,       "줄리앤",$img,  -1,  0,    "-", 66, 59, 72, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1221,   0,       "멜라니",$img,  -1,  0,    "-", 62, 55, 83, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1222,   0,         "수민",$img,  -1,  0,    "-", 71, 62, 58, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1223,   0,         "쿠지",$img,  -1,  0,    "-", 23, 68, 54, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1224,   0,         "해즌",$img,  -1,  0,    "-", 69, 67, 69, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1225,   0,         "사라",$img,  -1,  0,    "-", 62, 57, 52, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1226,   0,         "줄리",$img,  -1,  0,    "-", 59, 62, 58, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1227,   0,         "시호",$img,  -1,  0,    "-", 63, 71, 63, 0, 160, 300,    "-",    "-");
//에이핑크
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1228,   0,       "박초롱",$img,9044,  0,    "-", 81, 93, 70, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1229,   0,       "윤보미",$img,9045,  0,    "-", 89, 92, 69, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1230,   0,       "정은지",$img,9046,  0,    "-", 93, 63, 73, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1231,   0,       "손나은",$img,9047,  0,    "-", 76, 69, 89, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1232,   0,       "홍유경",$img,  -1,  0,    "-", 60, 41, 75, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1233,   0,       "김남주",$img,9048,  0,    "-", 78, 68, 84, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1234,   0,       "오하영",$img,9049,  0,    "-", 72, 72, 82, 0, 160, 300,    "-",    "-");
//걸스데이
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1235,   0,         "소진",$img,9050,  0,    "-", 85, 76, 79, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1236,   0,         "지해",$img,  -1,  0,    "-", 54, 42, 71, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1237,   0,         "민아",$img,9051,  0,    "-", 81, 69, 93, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1238,   0,         "유라",$img,9052,  0,    "-", 71, 68, 71, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1239,   0,         "혜리",$img,9053,  0,    "-", 69, 71, 89, 0, 160, 300,    "-",    "-");

RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1240,   0,         "비키",$img,  -1,  0,    "-", 53, 37, 68, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1241,   0,         "세리",$img,  -1,  0,    "-", 52, 32, 62, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1242,   0,         "지율",$img,  -1,  0,    "-", 56, 36, 65, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1243,   0,         "아영",$img,  -1,  0,    "-", 59, 39, 64, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1244,   0,         "가은",$img,  -1,  0,    "-", 61, 41, 63, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1245,   0,         "수빈",$img,  -1,  0,    "-", 66, 32, 61, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1246,   0,         "세라",$img,  -1,  0,    "-", 42, 46, 60, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1247,   0,         "민하",$img,  -1,  0,    "-", 51, 42, 61, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1248,   0,         "은지",$img,  -1,  0,    "-", 43, 43, 69, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1249,   0,         "이샘",$img,  -1,  0,    "-", 50, 41, 68, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1250,   0,     "이유애린",$img,  -1,  0,    "-", 61, 33, 64, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1251,   0,         "혜미",$img,  -1,  0,    "-", 32, 28, 63, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1252,   0,         "현아",$img,  -1,  0,    "-", 41, 31, 66, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1253,   0,         "경리",$img,  -1,  0,    "-", 46, 36, 69, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1254,   0,       "윤두준",$img,  -1,  0,    "-", 62, 74, 71, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1255,   0,       "양요섭",$img,9026,  0,    "-", 76, 62, 62, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1256,   0,       "장현승",$img,  -1,  0,    "-", 56, 67, 63, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1257,   0,       "이기광",$img,9027,  0,    "-", 48, 69, 76, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1258,   0,       "용준형",$img,  -1,  0,    "-", 41, 55, 72, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1259,   0,       "손동운",$img,  -1,  0,    "-", 46, 42, 64, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1260,   0,       "김성규",$img,  -1,  0,    "-", 32, 49, 69, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1261,   0,       "장동우",$img,  -1,  0,    "-", 33, 44, 71, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1262,   0,       "남우현",$img,  -1,  0,    "-", 46, 48, 72, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1263,   0,         "호야",$img,  -1,  0,    "-", 48, 48, 69, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1264,   0,       "이성열",$img,  -1,  0,    "-", 47, 42, 66, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1265,   0,           "엘",$img,  -1,  0,    "-", 44, 39, 63, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1266,   0,       "이성종",$img,  -1,  0,    "-", 44, 36, 64, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1267,   0,           "캡",$img,  -1,  0,    "-", 52, 42, 68, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1268,   0,         "니엘",$img,  -1,  0,    "-", 43, 39, 67, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1269,   0,         "리키",$img,  -1,  0,    "-", 31, 39, 71, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1270,   0,         "엘조",$img,  -1,  0,    "-", 41, 31, 72, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1271,   0,         "창조",$img,  -1,  0,    "-", 40, 48, 64, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1272,   0,         "천지",$img,  -1,  0,    "-", 33, 48, 74, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1273,   0,       "문준영",$img,  -1,  0,    "-", 41, 59, 69, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1274,   0,         "시완",$img,  -1,  0,    "-", 40, 62, 64, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1275,   0,        "케빈2",$img,  -1,  0,    "-", 39, 67, 66, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1276,   0,       "황광희",$img,  -1,  0,    "-", 23, 60, 61, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1277,   0,       "김태헌",$img,  -1,  0,    "-", 26, 66, 71, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1278,   0,       "정희철",$img,  -1,  0,    "-", 32, 71, 76, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1279,   0,       "하민우",$img,  -1,  0,    "-", 31, 62, 65, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1280,   0,       "박형식",$img,  -1,  0,    "-", 36, 69, 64, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1281,   0,       "김동준",$img,  -1,  0,    "-", 38, 73, 73, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1282,   0,         "진영",$img,  -1,  0,    "-", 45, 77, 71, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1283,   0,         "바로",$img,  -1,  0,    "-", 41, 79, 65, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1284,   0,         "산들",$img,  -1,  0,    "-", 43, 65, 69, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1285,   0,         "신우",$img,  -1,  0,    "-", 40, 56, 68, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1286,   0,         "공찬",$img,  -1,  0,    "-", 38, 61, 66, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1287,   0,       "아이유",$img,9028,  0,    "-", 94, 56, 97, 0, 160, 300,    "-",    "-", "니가 있는 미래에서~ 내 이름을 불~러~줘!");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1288,   0,         "설리",$img,9029,  0,    "-", 68, 48, 93, 0, 160, 300,    "-",    "-", "설리가 진리에요.");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1289,   0,     "빅토리아",$img,9030,  0,    "-", 72, 71, 90, 0, 160, 300,    "-",    "-", "닭대가리가 제일 맛있어요!");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1290,   0,     "크리스탈",$img,9031,  0,    "-", 69, 79, 86, 0, 160, 300,    "-",    "-", "정자매 강림!");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1291,   0,         "엠버",$img,9032,  0,    "-", 70, 68, 43, 0, 160, 300,    "-",    "-", "낙타 아니예요");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1292,   0,         "루나",$img,  -1,  0,    "-", 81, 39, 68, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1293,   0,         "지아",$img,  -1,  0,    "-", 68, 81, 51, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1294,   0,           "민",$img,  -1,  0,    "-", 72, 83, 59, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1295,   0,         "수지",$img,9033,  0,    "-", 71, 80, 98, 0, 160, 300,    "-",    "-", "국민 첫사랑 수지에요.");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1296,   0,         "페이",$img,  -1,  0,    "-", 74, 86, 71, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1297,   0,         "소유",$img,  -1,  0,    "-", 60, 80, 84, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1298,   0,         "보라",$img,9034,  0,    "-", 71, 96, 89, 0, 160, 300,    "-",    "-", "오빠야~");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1299,   0,         "다솜",$img,  -1,  0,    "-", 62, 69, 62, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1300,   0,         "효린",$img,9035,  0,    "-", 97, 88, 38, 0, 160, 300,    "-",    "-", "이마는 무덤까지 가져간다.");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1301,   0,         "영민",$img,  -1,  0,    "-", 52, 56, 48, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1302,   0,         "광민",$img,  -1,  0,    "-", 46, 54, 39, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1303,   0,         "민우",$img,  -1,  0,    "-", 53, 52, 41, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1304,   0,         "현성",$img,  -1,  0,    "-", 41, 48, 43, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1305,   0,         "정민",$img,  -1,  0,    "-", 32, 52, 46, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1306,   0,         "동현",$img,  -1,  0,    "-", 39, 59, 42, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1307,   0,       "방용국",$img,  -1,  0,    "-", 56, 66, 61, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1308,   0,         "젤로",$img,  -1,  0,    "-", 52, 71, 41, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1309,   0,         "힘찬",$img,  -1,  0,    "-", 53, 68, 68, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1310,   0,         "대현",$img,  -1,  0,    "-", 54, 69, 62, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1311,   0,         "영재",$img,  -1,  0,    "-", 52, 68, 60, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1312,   0,         "종업",$img,  -1,  0,    "-", 51, 68, 61, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1313,   0,         "수미",$img,  -1,  0,    "-", 46, 46, 64, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1314,   0,         "혜원",$img,  -1,  0,    "-", 42, 36, 69, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1315,   0,         "효영",$img,  -1,  0,    "-", 39, 56, 52, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1316,   0,         "찬미",$img,  -1,  0,    "-", 39, 26, 62, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1317,   0,         "은교",$img,  -1,  0,    "-", 38, 66, 68, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1318,   0,         "수현",$img,  -1,  0,    "-", 46, 71, 64, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1319,   0,         "기섭",$img,  -1,  0,    "-", 58, 78, 59, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1320,   0,       "일라이",$img,  -1,  0,    "-", 51, 71, 63, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1321,   0,        "케빈1",$img,  -1,  0,    "-", 52, 36, 71, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1322,   0,         "동호",$img,  -1,  0,    "-", 58, 64, 65, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1323,   0,           "훈",$img,  -1,  0,    "-", 58, 62, 66, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1324,   0,     "에이제이",$img,  -1,  0,    "-", 41, 42, 73, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1325,   0,       "정용화",$img,  -1,  0,    "-", 67, 21, 80, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1326,   0,       "이정신",$img,  -1,  0,    "-", 23, 18, 76, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1327,   0,       "이종현",$img,  -1,  0,    "-", 19, 17, 65, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1328,   0,       "강민혁",$img,  -1,  0,    "-", 12, 65, 49, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1329,   0,       "최종훈",$img,  -1,  0,    "-", 13, 32, 71, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1330,   0,       "이홍기",$img,  -1,  0,    "-", 73, 12, 73, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1331,   0,      "이재진2",$img,  -1,  0,    "-", 19, 45, 49, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1332,   0,       "최민환",$img,  -1,  0,    "-", 22, 79, 49, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1333,   0,       "송승현",$img,  -1,  0,    "-", 24, 42, 56, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1334,   0,       "서인국",$img,  -1,  0,    "-", 80, 31, 69, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1335,   0,         "이현",$img,  -1,  0,    "-", 87, 22, 70, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1336,   0,         "백찬",$img,  -1,  0,    "-", 76, 26, 71, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1337,   0,         "주희",$img,  -1,  0,    "-", 83, 49, 80, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1338,   0,       "강균성",$img,  -1,  0,    "-", 76, 61, 70, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1339,   0,       "전우성",$img,  -1,  0,    "-", 79, 48, 64, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1340,   0,       "이상곤",$img,  -1,  0,    "-", 77, 39, 69, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1341,   0,       "나성호",$img,  -1,  0,    "-", 71, 42, 63, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1342,   0,       "인호진",$img,  -1,  0,    "-", 76, 23, 46, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1343,   0,       "송우진",$img,  -1,  0,    "-", 77, 24, 49, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1344,   0,       "김영우",$img,  -1,  0,    "-", 77, 29, 52, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1345,   0,       "성진환",$img,  -1,  0,    "-", 80, 33, 54, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1346,   0,         "조권",$img,  -1,  0,    "-", 82, 64, 62, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1347,   0,       "임슬옹",$img,  -1,  0,    "-", 71, 46, 73, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1348,   0,       "정진운",$img,  -1,  0,    "-", 67, 52, 71, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1349,   0,       "이창민",$img,  -1,  0,    "-", 86, 32, 18, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1350,   0,         "준수",$img,  -1,  0,    "-", 72, 77, 70, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1351,   0,         "닉쿤",$img,9036,  0,    "-", 69, 72, 86, 0, 160, 300,    "-",    "-", "딱 맥주 두잔");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1352,   0,         "택연",$img,  -1,  0,    "-", 62, 78, 74, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1353,   0,         "우영",$img,  -1,  0,    "-", 66, 68, 70, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1354,   0,         "준호",$img,  -1,  0,    "-", 70, 62, 72, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1355,   0,         "찬성",$img,  -1,  0,    "-", 43, 59, 63, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1356,   0,         "조빈",$img,9037,  0,    "-", 76, 68, 51, 0, 160, 300,    "-",    "-", "올백머리 근육빵빵 난 슈퍼맨");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1357,   0,         "이혁",$img,  -1,  0,    "-", 91, 26, 83, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1358,   0,           "샘",$img,  -1,  0,    "-", 62, 56, 71, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1359,   0,         "조이",$img,  -1,  0,    "-", 58, 49, 73, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1360,   0,           "디",$img,  -1,  0,    "-", 56, 45, 66, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1361,   0,         "주이",$img,  -1,  0,    "-", 54, 48, 71, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1362,   0,         "티애",$img,  -1,  0,    "-", 53, 23, 73, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1363,   0,         "리코",$img,  -1,  0,    "-", 58, 47, 72, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1364,   0,         "시아",$img,  -1,  0,    "-", 51, 41, 77, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1365,   0,     "악동광행",$img,  -1,  0,    "-", 43, 33, 56, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1366,   0,     "천지유성",$img,  -1,  0,    "-", 42, 22, 59, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1367,   0,     "지혜태운",$img,  -1,  0,    "-", 49, 46, 54, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1368,   0,     "가온누리",$img,  -1,  0,    "-", 51, 49, 61, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1369,   0,     "알찬성민",$img,  -1,  0,    "-", 42, 61, 52, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1370,   0,       "신종국",$img,  -1,  0,    "-", 51, 51, 59, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1371,   0,       "김현중",$img,9038,  0,    "-", 62, 47, 93, 0, 160, 300,    "-",    "-", "F4 지후선배야. 하얀 천과 바람만 있으면 어디든 갈수있어.");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1372,   0,       "허영생",$img,  -1,  0,    "-", 73, 41, 71, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1373,   0,       "김규종",$img,  -1,  0,    "-", 70, 46, 67, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1374,   0,       "박종민",$img,  -1,  0,    "-", 64, 59, 66, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1375,   0,       "김형준",$img,  -1,  0,    "-", 62, 40, 63, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1376,   0,       "유세윤",$img,9039,  0,    "-", 73, 22, 61, 0, 160, 300,    "-",    "-", "이태원 프리덤!");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1377,   0,         "뮤지",$img,9040,  0,    "-", 72, 18, 32, 0, 160, 300,    "-",    "-", "쿨하지 못해 미안해");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1378,   0,     "사이먼디",$img,  -1,  0,    "-", 77, 62, 69, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1379,   0,       "이센스",$img,  -1,  0,    "-", 69, 43, 33, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1380,   0,         "장현",$img,  -1,  0,    "-", 79, 29, 66, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1381,   0,         "주비",$img,  -1,  0,    "-", 78, 33, 59, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1382,   0,         "승아",$img,  -1,  0,    "-", 80, 41, 62, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1383,   0,         "코타",$img,  -1,  0,    "-", 79, 52, 60, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1384,   0,         "미성",$img,  -1,  0,    "-", 72, 43, 70, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1385,   0,         "지나",$img,9041,  0,    "-", 88, 63, 87, 0, 160, 300,    "-",    "-", "난 탑걸~");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1386,   0,       "이주연",$img,  -1,  0,    "-", 71, 54, 76, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1387,   0,         "가희",$img,  -1,  0,    "-", 64, 88, 63, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1388,   0,         "정아",$img,  -1,  0,    "-", 76, 53, 69, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1389,   0,         "유이",$img,  -1,  0,    "-", 62, 83, 86, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1390,   0,         "나나",$img,  -1,  0,    "-", 67, 72, 83, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1391,   0,       "레이나",$img,  -1,  0,    "-", 73, 52, 78, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1392,   0,         "리지",$img,  -1,  0,    "-", 76, 51, 74, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1393,   0,         "이영",$img,  -1,  0,    "-", 68, 66, 70, 0, 160, 300,    "-",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1394,   0,       "정형돈",$img,9042,  0,    "-", 68, 76, 30, 0, 160, 300,    "-",    "-", "아니아니아니아니아니아니아니아니아니~");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1395,   0,       "데프콘",$img,9043,  0,    "-", 88, 63, 27, 0, 160, 300,    "-",    "-", "이게 끝이 아니야~ 이게 다가 아니야~");



//////////////////////////장수 끝///////////////////////////////////////////////

//////////////////////////도시 소속/////////////////////////////////////////////
//RegCity($connect, 0, "업");
//RegCity($connect, 0, "허창");
//RegCity($connect, 0,    "-");
//RegCity($connect, 0, "장안");
//RegCity($connect, 0, "성도");
//RegCity($connect, 0, "양양");
//RegCity($connect, 0, "건업");

//RegCity($connect, 0, "북평");
//RegCity($connect, 0, "남피");
//RegCity($connect, 0, "완");
//RegCity($connect, 0, "수춘");
//RegCity($connect, 0, "서주");
//RegCity($connect, 0, "강릉");
//RegCity($connect, 0, "장사");
//RegCity($connect, 0, "시상");
//RegCity($connect, 0, "위례");

//RegCity($connect, 0, "계");
//RegCity($connect, 0, "복양");
//RegCity($connect, 0, "진류");
//RegCity($connect, 0, "여남");
//RegCity($connect, 0, "하비");
//RegCity($connect, 0, "서량");
//RegCity($connect, 0, "하내");
//RegCity($connect, 0, "한중");
//RegCity($connect, 0, "상용");
//RegCity($connect, 0, "덕양");
//RegCity($connect, 0, "강주");
//RegCity($connect, 0, "건녕");
//RegCity($connect, 0, "남해");
//RegCity($connect, 0, "계양");
//RegCity($connect, 0, "오");
//RegCity($connect, 0, "평양");
//RegCity($connect, 0, "사비");
//RegCity($connect, 0, "계림");

//RegCity($connect, 0, "진양");
//RegCity($connect, 0, "평원");
//RegCity($connect, 0, "북해");
//RegCity($connect, 0, "초");
//RegCity($connect, 0, "패");
//RegCity($connect, 0, "천수");
//RegCity($connect, 0, "안정");
//RegCity($connect, 0, "홍농");
//RegCity($connect, 0, "하변");
//RegCity($connect, 0, "자동");
//RegCity($connect, 0, "영안");
//RegCity($connect, 0, "귀양");
//RegCity($connect, 0, "주시");
//RegCity($connect, 0, "운남");
//RegCity($connect, 0, "남영");
//RegCity($connect, 0, "교지");
//RegCity($connect, 0, "신야");
//RegCity($connect, 0, "강하");
//RegCity($connect, 0, "무릉");
//RegCity($connect, 0, "영릉");
//RegCity($connect, 0, "상동");
//RegCity($connect, 0, "여강");
//RegCity($connect, 0, "회계");
//RegCity($connect, 0, "고창");
//RegCity($connect, 0, "대");
//RegCity($connect, 0, "안평");
//RegCity($connect, 0, "졸본");
//RegCity($connect, 0, "이도");

//RegCity($connect, 0, "강");
//RegCity($connect, 0, "저");
//RegCity($connect, 0, "흉노");
//RegCity($connect, 0, "남만");
//RegCity($connect, 0, "산월");
//RegCity($connect, 0, "오환");
//RegCity($connect, 0, "왜");

//RegCity($connect, 0, "호관");
//RegCity($connect, 0, "호로");
//RegCity($connect, 0, "사곡");
//RegCity($connect, 0, "함곡");
//RegCity($connect, 0, "사수");
//RegCity($connect, 0, "양평");
//RegCity($connect, 0, "가맹");
//RegCity($connect, 0, "역경");
//RegCity($connect, 0, "계교");
//RegCity($connect, 0, "동황");
//RegCity($connect, 0, "관도");
//RegCity($connect, 0, "정도");
//RegCity($connect, 0, "합비");
//RegCity($connect, 0, "광릉");
//RegCity($connect, 0, "적도");
//RegCity($connect, 0, "가정");
//RegCity($connect, 0, "기산");
//RegCity($connect, 0, "면죽");
//RegCity($connect, 0, "이릉");
//RegCity($connect, 0, "장판");
//RegCity($connect, 0, "백랑");

//RegCity($connect, 0, "적벽");
//RegCity($connect, 0, "파양");
//RegCity($connect, 0, "탐라");
//RegCity($connect, 0, "유구");

//////////////////////////도시 끝///////////////////////////////////////////////

//////////////////////////이벤트///////////////////////////////////////////////

$history[count($history)] = "<C>●</>180년 1월:<L><b>【가상모드7】</b>가요대잔치</>";
$history[count($history)] = "<C>●</>180년 1월:<L><b>【이벤트】</b></>올해의 가요대상은!";
pushHistory($connect, $history);

//echo "<script>location.replace('install3_ok.php');</script>";
echo 'install3_ok.php';//TODO:debug all and replace
