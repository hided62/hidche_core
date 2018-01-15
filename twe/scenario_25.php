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
//가상모드5 : 180년 영웅독존

//////////////////////////국가1/////////////////////////////////////////////////

//////////////////////////외교//////////////////////////////////////////////////
//////////////////////////외교 끝//////////////////////////////////////////////////

//////////////////////////장수//////////////////////////////////////////////////
//                                                               상성       이름       사진 국가  도시   통  무  지 급 출생 사망    꿈     특기
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1001,  49,   "가비능",$img,1009,  0,    "-", 58, 83, 32, 0, 160, 300, "정복",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1002,  31,     "가충",$img,1010,  0,    "-", 50, 25, 87, 0, 160, 300, "출세",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1003,  20,     "가후",$img,1011,  0,    "-", 69, 30, 94, 0, 160, 300, "할거", "귀병");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1004, 129,     "감녕",$img,1013,  0,    "-", 78, 95, 71, 0, 160, 300, "출세", "무쌍");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1005, 127,     "감택",$img,1014,  0,    "-", 62, 44, 79, 0, 160, 300, "유지",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1006,  73,     "강유",$img,1016,  0,    "-", 95, 90, 94, 0, 160, 300, "왕좌", "집중", "갈고 닦은 무예와 승상께 배운 책략을 발휘해 보이겠다!");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1007,  27,     "고람",$img,1018,  0,    "-", 72, 67, 59, 0, 160, 300, "출세", "기병");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1008, 144,     "고순",$img,1020,  0,    "-", 79, 82, 65, 0, 160, 300, "의협", "기병");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1009,  63,     "고정",$img,1023,  0,    "-", 67, 65, 55, 0, 160, 300, "정복",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1010, 142,   "공손강",$img,1026,  0,    "-", 64, 72, 61, 0, 160, 300, "정복",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1011, 142,   "공손공",$img,1027,  0,    "-", 68, 41, 75, 0, 160, 300, "정복",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1012, 142,   "공손도",$img,1028,  0,    "-", 62, 72, 41, 0, 160, 300, "정복", "기병");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1013,  65,   "공손범",$img,1029,  0,    "-", 61, 67, 61, 0, 160, 300, "출세",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1014,  65,   "공손속",$img,1030,  0,    "-", 60, 76, 41, 0, 160, 300, "정복",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1015,  10,   "공손연",$img,1031,  0,    "-", 74, 79, 64, 0, 160, 300, "패권", "돌격");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1016,  65,   "공손찬",$img,1033,  0,    "-", 61, 87, 67, 0, 160, 300, "패권", "기병");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1017,  43,     "공융",$img,1034,  0,    "-", 63, 48, 85, 0, 160, 300, "왕좌", "경작");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1018,  35,     "공주",$img,1035,  0,    "-", 64, 35, 78, 0, 160, 300, "재간",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1019,  26,     "곽가",$img,1037,  0,    "-", 47, 23, 99, 0, 160, 300, "패권", "귀모");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1020, 111,     "곽도",$img,1038,  0,    "-", 63, 67, 81, 0, 160, 300, "재간",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1021,  67,     "곽익",$img,1041,  0,    "-", 67, 60, 67, 0, 160, 300, "재간",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1022,  67,     "곽준",$img,1042,  0,    "-", 76, 69, 73, 0, 160, 300, "출세",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1023,  27,     "곽혁",$img,1043,  0,    "-", 40, 29, 80, 0, 160, 300, "안전",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1024,  20,     "곽회",$img,1044,  0,    "-", 77, 75, 71, 0, 160, 300, "정복",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1025,  39,   "관구검",$img,1045,  0,    "-", 72, 68, 77, 0, 160, 300, "대의",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1026,  76,     "관색",$img,1046,  0,    "-", 69, 85, 67, 0, 160, 300, "의협", "징병");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1027,  76,     "관우",$img,1047,  0,    "-", 96, 98, 80, 0, 160, 300, "의협", "위압", "나의 청룡 언월도를 과연 막아낼 수 있겠소?");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1028,  76,     "관평",$img,1050,  0,    "-", 77, 80, 70, 0, 160, 300, "의협", "보병");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1029,   7,     "관해",$img,1051,  0,    "-", 66, 90, 35, 0, 160, 300, "출세",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1030,  76,     "관흥",$img,1052,  0,    "-", 69, 84, 72, 0, 160, 300, "의협", "돌격");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1031,  40,     "괴량",$img,1053,  0,    "-", 41, 28, 81, 0, 160, 300, "안전", "신중");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1032,  40,     "괴월",$img,1054,  0,    "-", 26, 30, 84, 0, 160, 300, "유지", "귀병");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1033, 141,     "기령",$img,1061,  0,    "-", 76, 81, 33, 0, 160, 300, "대의", "무쌍");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1034, 124,    "노숙1",$img,1063,  0,    "-", 90, 42, 94, 0, 160, 300, "왕좌", "상재");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1035,  75,     "노식",$img,1064,  0,    "-", 91, 54, 80, 0, 160, 300, "왕좌", "징병");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1036,  59,     "뇌동",$img,1065,  0,    "-", 70, 77, 45, 0, 160, 300, "출세", "궁병");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1037, 127,     "능조",$img,1067,  0,    "-", 67, 80, 44, 0, 160, 300, "재간", "공성");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1038, 127,     "능통",$img,1068,  0,    "-", 71, 78, 58, 0, 160, 300, "의협", "궁병");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1039,  64,     "단경",$img,1069,  0,    "-", 68, 61, 68, 0, 160, 300, "정복",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1040,  73,     "동궐",$img,1076,  0,    "-", 66, 50, 76, 0, 160, 300, "대의",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1041,  89,     "동승",$img,1081,  0,    "-", 75, 66, 65, 0, 160, 300, "왕좌",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1042,  78,     "동윤",$img,1082,  0,    "-", 64, 26, 78, 0, 160, 300, "왕좌",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1043,   2,     "동탁",$img,1083,  0,    "-", 87, 91, 54, 0, 160, 300, "패권", "기병");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1044,  32,     "두예",$img,1085,  0,    "-", 88, 80, 84, 0, 160, 300, "정복",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1045,  41,     "등애",$img,1087,  0,    "-", 94, 82, 92, 0, 160, 300, "패권", "신산");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1046,  73,     "등지",$img,1089,  0,    "-", 74, 51, 80, 0, 160, 300, "할거", "경작");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1047,  41,     "등충",$img,1090,  0,    "-", 60, 82, 55, 0, 160, 300, "출세",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1048,  54,     "등현",$img,1091,  0,    "-", 65, 59, 61, 0, 160, 300, "출세",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1049,  29,     "마균",$img,1092,  0,    "-", 33, 38, 80, 0, 160, 300, "안전",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1050,  71,     "마대",$img,1093,  0,    "-", 77, 79, 49, 0, 160, 300, "대의", "기병");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1051,  70,     "마등",$img,1094,  0,    "-", 80, 87, 56, 0, 160, 300, "왕좌", "기병");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1052,  80,     "마속",$img,1095,  0,    "-", 73, 64, 82, 0, 160, 300, "패권", "집중");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1053,  77,     "마량",$img,1096,  0,    "-", 57, 25, 87, 0, 160, 300, "왕좌",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1054,  71,     "마철",$img,1099,  0,    "-", 71, 60, 31, 0, 160, 300, "대의", "기병");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1055,  70,     "마초",$img,1100,  0,    "-", 78, 97, 40, 0, 160, 300, "대의", "기병", "금마초 나가신닷!");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1056, 131,    "마충1",$img,1101,  0,    "-", 67, 62, 51, 0, 160, 300, "재간",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1057,  69,    "마충2",$img,1102,  0,    "-", 61, 68, 51, 0, 160, 300, "출세",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1058,  71,     "마휴",$img,1103,  0,    "-", 71, 60, 32, 0, 160, 300, "대의", "기병");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1059, 116,     "만총",$img,1104,  0,    "-", 79, 40, 78, 0, 160, 300, "할거", "신중");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1060,  44,     "맹달",$img,1106,  0,    "-", 70, 66, 72, 0, 160, 300, "할거", "귀병");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1061,  60,     "맹우",$img,1107,  0,    "-", 63, 79, 26, 0, 160, 300, "정복",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1062,  60,     "맹획",$img,1108,  0,    "-", 78, 92, 50, 0, 160, 300, "왕좌", "격노");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1063,  51, "목록대왕",$img,1110,  0,    "-", 58, 71, 65, 0, 160, 300, "재간", "척사");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1064,  28,     "문빙",$img,1113,  0,    "-", 70, 77, 43, 0, 160, 300, "유지",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1065,  38,     "문앙",$img,1114,  0,    "-", 71, 91, 46, 0, 160, 300, "대의",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1066, 102,     "문추",$img,1115,  0,    "-", 72, 94, 25, 0, 160, 300, "출세", "무쌍");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1067,  38,     "문흠",$img,1116,  0,    "-", 76, 77, 43, 0, 160, 300, "대의",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1068,  49, "미당대왕",$img,1117,  0,    "-", 64, 75, 32, 0, 160, 300, "유지",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1069,  94,     "반봉",$img,1120,  0,    "-", 61, 75, 17, 0, 160, 300, "출세",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1070,  65,     "방덕",$img,1122,  0,    "-", 76, 90, 67, 0, 160, 300, "의협", "기병");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1071,  73,     "방통",$img,1123,  0,    "-", 86, 41, 97, 0, 160, 300, "패권", "반계");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1072, 149,     "번주",$img,1127,  0,    "-", 67, 77, 21, 0, 160, 300, "할거",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1073,  72,     "법정",$img,1128,  0,    "-", 81, 29, 93, 0, 160, 300, "패권", "신산");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1074,   8,     "변희",$img,1129,  0,    "-", 65, 65, 27, 0, 160, 300, "안전",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1075, 110,     "봉기",$img,1132,  0,    "-", 68, 52, 80, 0, 160, 300, "패권", "집중");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1076,  74,     "부동",$img,1133,  0,    "-", 58, 69, 69, 0, 160, 300, "대의",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1077, 141,     "비연",$img,1138,  0,    "-", 66, 65, 53, 0, 160, 300, "출세",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1078,  77,     "비위",$img,1139,  0,    "-", 72, 26, 73, 0, 160, 300, "대의",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1079,  71,   "사마가",$img,1141,  0,    "-", 61, 85, 18, 0, 160, 300, "정복", "돌격");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1080,  24,   "사마망",$img,1143,  0,    "-", 71, 61, 65, 0, 160, 300, "정복",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1081,  31,   "사마사",$img,1145,  0,    "-", 87, 64, 91, 0, 160, 300, "정복",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1082,  31,   "사마소",$img,1146,  0,    "-", 93, 63, 84, 0, 160, 300, "정복",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1083,  31,   "사마염",$img,1147,  0,    "-", 92, 78, 72, 0, 160, 300, "패권",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1084,  31,   "사마의",$img,1149,  0,    "-", 98, 67, 98, 0, 160, 300, "패권", "반계");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1085, 139,     "사섭",$img,1150,  0,    "-", 63, 61, 71, 0, 160, 300, "안전",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1086, 132,     "사정",$img,1152,  0,    "-", 67, 71, 20, 0, 160, 300, "안전",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1087, 144,     "사휘",$img,1154,  0,    "-", 67, 71, 61, 0, 160, 300, "유지",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1088,  76,     "서서",$img,1156,  0,    "-", 90, 70, 96, 0, 160, 300, "의협", "귀병");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1089, 124,     "서성",$img,1157,  0,    "-", 83, 76, 83, 0, 160, 300, "대의",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1090,  23,     "서황",$img,1160,  0,    "-", 79, 89, 68, 0, 160, 300, "의협", "필살");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1091,  32,     "석포",$img,1161,  0,    "-", 71, 63, 59, 0, 160, 300, "정복",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1092, 129,     "소비",$img,1165,  0,    "-", 67, 63, 49, 0, 160, 300, "대의",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1093, 125,     "손견",$img,1167,  0,    "-", 96, 95, 76, 0, 160, 300, "왕좌", "무쌍");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1094, 125,     "손권",$img,1169,  0,    "-", 90, 77, 83, 0, 160, 300, "할거", "수비");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1095,  20,     "손례",$img,1173,  0,    "-", 64, 64, 69, 0, 160, 300, "안전",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1096, 124,     "손소",$img,1174,  0,    "-", 76, 80, 68, 0, 160, 300, "정복",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1097, 130,     "손수",$img,1175,  0,    "-", 67, 57, 59, 0, 160, 300, "안전",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1098, 126,     "손유",$img,1176,  0,    "-", 77, 60, 67, 0, 160, 300, "정복",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1099, 125,     "손책",$img,1181,  0,    "-", 96, 96, 78, 0, 160, 300, "패권", "필살", "소패왕 손책이 나가신다! 길을 비켜라!");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1100, 127,     "손환",$img,1185,  0,    "-", 79, 65, 70, 0, 160, 300, "정복",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1101,  95,   "순우경",$img,1189,  0,    "-", 72, 67, 60, 0, 160, 300, "할거",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1102,  22,    "순욱1",$img,1190,  0,    "-", 54, 29, 97, 0, 160, 300, "왕좌", "집중");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1103,  22,     "순유",$img,1191,  0,    "-", 73, 41, 90, 0, 160, 300, "대의", "신중");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1104, 102,     "심배",$img,1196,  0,    "-", 75, 66, 68, 0, 160, 300, "패권", "귀병");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1105,  23,     "악진",$img,1200,  0,    "-", 73, 67, 56, 0, 160, 300, "대의", "돌격");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1106,  63,     "악환",$img,1202,  0,    "-", 54, 82, 55, 0, 160, 300, "재간",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1107, 102,     "안량",$img,1203,  0,    "-", 73, 93, 36, 0, 160, 300, "출세", "위압");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1108,  91,    "양봉2",$img,1206,  0,    "-", 62, 78, 61, 0, 160, 300, "정복",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1109,  43,     "양수",$img,1208,  0,    "-", 18, 31, 91, 0, 160, 300, "재간", "귀병");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1110,  31,     "양호",$img,1212,  0,    "-", 91, 69, 80, 0, 160, 300, "패권",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1111,   6,   "어부라",$img,1215,  0,    "-", 78, 80, 61, 0, 160, 300, "정복",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1112,  69,     "엄안",$img,1219,  0,    "-", 72, 84, 67, 0, 160, 300, "왕좌",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1113, 124,     "여몽",$img,1225,  0,    "-", 92, 78, 93, 0, 160, 300, "패권", "궁병");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1114, 145,     "여포",$img,1229,  0,    "-", 74,100, 29, 0, 160, 300, "패권", "돌격", "다 죽여버리겠다! 으하하.");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1115,  40,     "염유",$img,1231,  0,    "-", 59, 75, 51, 0, 160, 300, "출세",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1116,  40,     "예형",$img,1233,  0,    "-", 77, 31, 95, 0, 160, 300, "은둔", "통찰");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1117,  59,     "오란",$img,1235,  0,    "-", 67, 75, 42, 0, 160, 300, "재간",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1118, 128,     "오언",$img,1237,  0,    "-", 71, 60, 52, 0, 160, 300, "재간",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1119,  69,     "오의",$img,1239,  0,    "-", 75, 72, 74, 0, 160, 300, "유지",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1120,  51,   "올돌골",$img,1241,  0,    "-", 77, 92, 15, 0, 160, 300, "출세", "척사");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1121,  97,     "왕광",$img,1244,  0,    "-", 72, 67, 54, 0, 160, 300, "유지",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1122,  30,    "왕기1",$img,1245,  0,    "-", 76, 62, 70, 0, 160, 300, "유지",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1123,  26,     "왕쌍",$img,1250,  0,    "-", 58, 89, 15, 0, 160, 300, "정복", "보병");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1124,  46,     "왕위",$img,1251,  0,    "-", 59, 60, 68, 0, 160, 300, "안전",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1125,  32,     "왕준",$img,1254,  0,    "-", 81, 83, 76, 0, 160, 300, "대의",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1126,  69,     "왕평",$img,1257,  0,    "-", 77, 76, 71, 0, 160, 300, "재간",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1127,  71,     "요립",$img,1260,  0,    "-", 65, 41, 84, 0, 160, 300, "출세",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1128,  74,     "요화",$img,1261,  0,    "-", 67, 58, 60, 0, 160, 300, "의협",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1129,  22,    "우금1",$img,1262,  0,    "-", 80, 74, 71, 0, 160, 300, "정복",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1130,  27,    "우금2",$img,1263,  0,    "-", 63, 77, 37, 0, 160, 300, "대의",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1131,  86,     "원담",$img,1266,  0,    "-", 67, 59, 55, 0, 160, 300, "왕좌",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1132, 101,     "원상",$img,1267,  0,    "-", 54, 72, 68, 0, 160, 300, "패권",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1133, 101,     "원소",$img,1268,  0,    "-", 85, 67, 76, 0, 160, 300, "패권", "위압");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1134, 140,     "원술",$img,1269,  0,    "-", 77, 59, 71, 0, 160, 300, "패권", "축성");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1135, 101,     "원희",$img,1271,  0,    "-", 69, 57, 72, 0, 160, 300, "할거",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1136, 131,     "위소",$img,1272,  0,    "-", 39, 24, 82, 0, 160, 300, "재간",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1137,  81,     "위연",$img,1274,  0,    "-", 78, 94, 62, 0, 160, 300, "패권", "보병");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1138,  36,     "유대",$img,1277,  0,    "-", 61, 57, 62, 0, 160, 300, "재간",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1139,  46,     "유벽",$img,1279,  0,    "-", 63, 71, 23, 0, 160, 300, "출세",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1140,  75,     "유봉",$img,1280,  0,    "-", 60, 65, 62, 0, 160, 300, "출세",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1141,  75,     "유비",$img,1281,  0,    "-", 85, 75, 70, 0, 160, 300, "왕좌", "인덕");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1142,  55,     "유순",$img,1283,  0,    "-", 67, 61, 54, 0, 160, 300, "출세",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1143, 129,     "유약",$img,1285,  0,    "-", 67, 63, 61, 0, 160, 300, "유지",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1144,  45,    "유표1",$img,1293,  0,    "-", 71, 57, 71, 0, 160, 300, "할거",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1145,  27,    "유표2",$img,1294,  0,    "-", 76, 55, 71, 0, 160, 300, "안전",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1146, 122,     "육손",$img,1297,  0,    "-", 98, 68, 98, 0, 160, 300, "왕좌", "귀병");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1147, 122,     "육항",$img,1299,  0,    "-", 95, 69, 94, 0, 160, 300, "왕좌",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1148,  71,     "이엄",$img,1306,  0,    "-", 80, 84, 81, 0, 160, 300, "패권",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1149,   2,     "이유",$img,1307,  0,    "-", 64, 22, 90, 0, 160, 300, "패권", "귀모");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1150,  22,     "이전",$img,1310,  0,    "-", 75, 68, 82, 0, 160, 300, "왕좌",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1151,   7,     "장각",$img,1314,  0,    "-", 93, 25, 93, 0, 160, 300, "패권", "환술");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1152, 122,     "장굉",$img,1317,  0,    "-", 25, 21, 85, 0, 160, 300, "왕좌",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1153,   7,     "장량",$img,1318,  0,    "-", 68, 81, 68, 0, 160, 300, "정복", "환술");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1154,  15,     "장로",$img,1319,  0,    "-", 76, 44, 80, 0, 160, 300, "유지", "축성");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1155,  23,     "장료",$img,1320,  0,    "-", 89, 93, 83, 0, 160, 300, "의협", "견고");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1156,   7,     "장보",$img,1321,  0,    "-", 78, 81, 76, 0, 160, 300, "패권", "환술");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1157,  76,     "장비",$img,1322,  0,    "-", 79, 99, 48, 0, 160, 300, "의협", "무쌍", "어쭈. 해보자 이거냐?");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1158, 120,    "장소1",$img,1324,  0,    "-", 42, 24, 91, 0, 160, 300, "안전",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1159,  72,     "장송",$img,1326,  0,    "-", 49, 28, 93, 0, 160, 300, "할거", "통찰");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1160, 148,     "장수",$img,1327,  0,    "-", 71, 72, 69, 0, 160, 300, "할거",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1161, 145,    "장양1",$img,1328,  0,    "-", 62, 66, 65, 0, 160, 300, "출세",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1162,   8,     "장연",$img,1329,  0,    "-", 78, 66, 47, 0, 160, 300, "안전",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1163,  78,     "장완",$img,1332,  0,    "-", 70, 55, 86, 0, 160, 300, "할거", "상재");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1164,  36,     "장윤",$img,1334,  0,    "-", 67, 59, 60, 0, 160, 300, "정복",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1165,  68,     "장익",$img,1336,  0,    "-", 75, 68, 63, 0, 160, 300, "유지",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1166,  56,     "장임",$img,1337,  0,    "-", 83, 82, 74, 0, 160, 300, "대의", "견고");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1167, 148,    "장제1",$img,1338,  0,    "-", 70, 65, 59, 0, 160, 300, "할거",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1168,  32,    "장제2",$img,1339,  0,    "-", 30, 33, 84, 0, 160, 300, "유지",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1169,  76,    "장포1",$img,1342,  0,    "-", 69, 85, 49, 0, 160, 300, "재간", "징병");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1170,  27,     "장합",$img,1344,  0,    "-", 83, 91, 63, 0, 160, 300, "출세", "궁병");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1171,  31,     "장화",$img,1346,  0,    "-", 49, 24, 86, 0, 160, 300, "유지",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1172, 141,     "장훈",$img,1348,  0,    "-", 67, 61, 60, 0, 160, 300, "안전",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1173, 126,     "장흠",$img,1350,  0,    "-", 64, 66, 67, 0, 160, 300, "대의", "저격");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1174,  96,     "저수",$img,1351,  0,    "-", 82, 54, 88, 0, 160, 300, "할거", "반계");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1175, 130,     "전단",$img,1352,  0,    "-", 64, 73, 61, 0, 160, 300, "출세",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1176,  26,    "전위1",$img,1354,  0,    "-", 61, 96, 34, 0, 160, 300, "의협", "필살");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1177, 130,    "전위2",$img,1355,  0,    "-", 74, 69, 62, 0, 160, 300, "출세",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1178, 128,     "전종",$img,1356,  0,    "-", 79, 77, 74, 0, 160, 300, "패권",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1179,  42,     "전주",$img,1357,  0,    "-", 69, 67, 51, 0, 160, 300, "의협",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1180,  96,     "전풍",$img,1358,  0,    "-", 81, 41, 96, 0, 160, 300, "왕좌",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1181, 126,     "정보",$img,1361,  0,    "-", 81, 64, 76, 0, 160, 300, "패권",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1182, 123,    "정봉1",$img,1362,  0,    "-", 70, 77, 64, 0, 160, 300, "패권",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1183,  24,     "정욱",$img,1363,  0,    "-", 80, 39, 90, 0, 160, 300, "패권", "신중");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1184,  88,     "정원",$img,1364,  0,    "-", 64, 77, 58, 0, 160, 300, "왕좌", "기병");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1185, 121,   "제갈각",$img,1367,  0,    "-", 61, 53, 92, 0, 160, 300, "왕좌",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1186, 121,   "제갈근",$img,1369,  0,    "-", 60, 42, 88, 0, 160, 300, "왕좌", "경작");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1187,  76,   "제갈량",$img,1370,  0,    "-", 97, 55,100, 0, 160, 300, "왕좌", "집중", "슬슬 나의 지모를 발휘해 보겠습니다...");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1188,  76,   "제갈상",$img,1371,  0,    "-", 52, 75, 71, 0, 160, 300, "대의",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1189,  76,   "제갈첨",$img,1373,  0,    "-", 73, 52, 76, 0, 160, 300, "대의",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1190, 135,   "제갈탄",$img,1374,  0,    "-", 79, 79, 73, 0, 160, 300, "패권",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1191,  76,     "조광",$img,1375,  0,    "-", 65, 67, 54, 0, 160, 300, "재간",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1192, 127,     "조무",$img,1378,  0,    "-", 71, 68, 71, 0, 160, 300, "의협",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1193,  26,     "조비",$img,1381,  0,    "-", 72, 69, 75, 0, 160, 300, "패권", "징병");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1194,  19,     "조상",$img,1382,  0,    "-", 68, 62, 31, 0, 160, 300, "패권",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1195,  25,     "조식",$img,1385,  0,    "-", 19, 19, 90, 0, 160, 300, "왕좌", "귀모");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1196,  25,     "조예",$img,1387,  0,    "-", 57, 55, 82, 0, 160, 300, "정복",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1197,  76,     "조운",$img,1389,  0,    "-", 95, 98, 87, 0, 160, 300, "왕좌", "무쌍", "창술의 달인 상산 조자룡 여기 있소!");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1198,  26,     "조인",$img,1391,  0,    "-", 74, 79, 62, 0, 160, 300, "패권", "보병");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1199,  25,     "조조",$img,1392,  0,    "-",100, 80, 95, 0, 160, 300, "패권", "반계");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1200,  26,     "조진",$img,1393,  0,    "-", 82, 67, 65, 0, 160, 300, "정복",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1201,  25,     "조창",$img,1394,  0,    "-", 75, 88, 37, 0, 160, 300, "정복", "돌격");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1202,  76,     "조통",$img,1395,  0,    "-", 65, 64, 55, 0, 160, 300, "재간",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1203,  24,    "조홍2",$img,1398,  0,    "-", 72, 69, 44, 0, 160, 300, "패권",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1204,  26,     "조휴",$img,1401,  0,    "-", 75, 71, 70, 0, 160, 300, "패권",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1205,  20,     "종회",$img,1404,  0,    "-", 84, 58, 93, 0, 160, 300, "패권",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1206, 128,     "주거",$img,1405,  0,    "-", 73, 71, 72, 0, 160, 300, "유지",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1207, 126,     "주연",$img,1407,  0,    "-", 73, 72, 51, 0, 160, 300, "할거",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1208, 126,     "주유",$img,1408,  0,    "-", 97, 73, 97, 0, 160, 300, "패권", "신산");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1209,  88,     "주준",$img,1410,  0,    "-", 82, 75, 65, 0, 160, 300, "왕좌",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1210, 126,    "주태2",$img,1415,  0,    "-", 74, 88, 60, 0, 160, 300, "정복", "필살");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1211, 128,     "주환",$img,1416,  0,    "-", 84, 86, 74, 0, 160, 300, "정복",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1212,  28,     "진군",$img,1419,  0,    "-", 60, 38, 87, 0, 160, 300, "대의",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1213, 143,     "진궁",$img,1420,  0,    "-", 77, 51, 90, 0, 160, 300, "할거", "신중");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1214,  72,     "진도",$img,1422,  0,    "-", 71, 85, 70, 0, 160, 300, "대의",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1215,  79,     "진등",$img,1423,  0,    "-", 64, 62, 71, 0, 160, 300, "할거",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1216,  37,     "진림",$img,1424,  0,    "-", 50, 28, 82, 0, 160, 300, "재간",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1217, 124,     "진무",$img,1425,  0,    "-", 62, 74, 59, 0, 160, 300, "재간",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1218,  50,     "진수",$img,1427,  0,    "-", 25, 29, 83, 0, 160, 300, "대의",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1219,  28,     "진태",$img,1430,  0,    "-", 79, 76, 70, 0, 160, 300, "대의",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1220,  36,     "채모",$img,1434,  0,    "-", 79, 69, 68, 0, 160, 300, "정복", "궁병");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1221,  66,     "초주",$img,1437,  0,    "-", 22, 26, 81, 0, 160, 300, "대의",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1222,  60,     "축융",$img,1439,  0,    "-", 59, 87, 25, 0, 160, 300, "정복", "척사");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1223,  60, "타사대왕",$img,1440,  0,    "-", 61, 72, 67, 0, 160, 300, "출세", "척사");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1224, 124,   "태사자",$img,1441,  0,    "-", 71, 97, 65, 0, 160, 300, "대의", "무쌍");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1225, 121,     "하제",$img,1446,  0,    "-", 74, 73, 64, 0, 160, 300, "재간",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1226,  24,   "하후덕",$img,1448,  0,    "-", 67, 64, 39, 0, 160, 300, "출세",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1227,  26,   "하후돈",$img,1449,  0,    "-", 88, 92, 71, 0, 160, 300, "의협", "돌격", "다 나오거라! 상대해 주마!");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1228,  24,   "하후상",$img,1451,  0,    "-", 67, 62, 71, 0, 160, 300, "출세",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1229,  26,   "하후연",$img,1452,  0,    "-", 79, 90, 58, 0, 160, 300, "패권", "궁병", "궁술로 날 당해낼 자가 있을까? 후후.");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1230,  26,   "하후위",$img,1453,  0,    "-", 73, 76, 71, 0, 160, 300, "정복",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1231,  23,   "하후패",$img,1455,  0,    "-", 78, 88, 69, 0, 160, 300, "대의",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1232,  26,   "하후혜",$img,1457,  0,    "-", 76, 66, 78, 0, 160, 300, "정복",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1233,  26,   "하후화",$img,1458,  0,    "-", 77, 61, 80, 0, 160, 300, "정복",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1234,  22,     "학소",$img,1459,  0,    "-", 89, 81, 86, 0, 160, 300, "대의", "견고");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1235, 126,     "한당",$img,1460,  0,    "-", 68, 67, 64, 0, 160, 300, "할거",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1236,  48,     "한수",$img,1463,  0,    "-", 66, 76, 77, 0, 160, 300, "대의", "기병");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1237,  79,     "향총",$img,1469,  0,    "-", 76, 42, 73, 0, 160, 300, "왕좌",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1238, 139,     "허공",$img,1470,  0,    "-", 65, 63, 59, 0, 160, 300, "유지",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1239,  26,     "허저",$img,1473,  0,    "-", 57, 98, 27, 0, 160, 300, "정복", "무쌍");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1240,  20,     "호분",$img,1478,  0,    "-", 71, 60, 61, 0, 160, 300, "할거",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1241,  27,   "호주천",$img,1479,  0,    "-", 77, 75, 65, 0, 160, 300, "정복",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1242,   2,     "화웅",$img,1481,  0,    "-", 68, 88, 24, 0, 160, 300, "출세", "돌격");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1243,  21,     "환범",$img,1484,  0,    "-", 20, 25, 81, 0, 160, 300, "유지",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1244, 127,     "황개",$img,1485,  0,    "-", 78, 85, 69, 0, 160, 300, "왕좌", "징병");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1245,  56,     "황권",$img,1486,  0,    "-", 76, 46, 77, 0, 160, 300, "대의",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1246,  88,   "황보숭",$img,1488,  0,    "-", 83, 63, 73, 0, 160, 300, "왕좌",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1247,  72,     "황충",$img,1489,  0,    "-", 84, 94, 67, 0, 160, 300, "왕좌", "궁병");

//////////////////////////장수 끝///////////////////////////////////////////////

//////////////////////////도시 소속/////////////////////////////////////////////

//////////////////////////도시 끝///////////////////////////////////////////////

//////////////////////////이벤트///////////////////////////////////////////////

$history[count($history)] = "<C>●</>180년 1월:<L><b>【가상모드5】</b>영웅독존</>";
$history[count($history)] = "<C>●</>180년 1월:<L><b>【이벤트】</b></>진정한 영웅들만이 재야로 등장하는 가상 시나리오.";
pushHistory($connect, $history);

echo "<script>location.replace('install3_ok.php');</script>";

