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
//가상모드6 : 180년 무풍지대

//////////////////////////국가1/////////////////////////////////////////////////

//////////////////////////외교//////////////////////////////////////////////////
//////////////////////////외교 끝//////////////////////////////////////////////////

//////////////////////////장수//////////////////////////////////////////////////
//                                                               상성       이름       사진 국가  도시   통  무  지 급 출생 사망    꿈     특기
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1001,   1,    "소제1",$img,1001,  0,    "-", 20, 11, 48, 0, 160, 300, "유지",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1002,   1,     "헌제",$img,1002,  0,    "-", 17, 13, 61, 0, 160, 300, "안전",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1003, 999,   "사마휘",$img,1003,  0,    "-", 71, 11, 96, 0, 160, 300, "은둔", "신산", "좋지, 좋아~");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1004, 999,     "우길",$img,1004,  0,    "-", 17, 13, 83, 0, 160, 300, "은둔", "신산");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1005, 999,     "화타",$img,1005,  0,    "-", 53, 25, 70, 0, 160, 300, "은둔", "의술", "아픈 사람들은 모두 내게 오시오. 껄껄껄.");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1006, 999,     "길평",$img,1006,  0,    "-", 27, 15, 72, 0, 160, 300, "은둔", "의술");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1007,  29,     "가규",$img,1007,  0,    "-", 55, 55, 74, 0, 160, 300, "재간",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1008, 136,     "가범",$img,1008,  0,    "-", 58, 48, 73, 0, 160, 300, "유지",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1009,  73,     "간옹",$img,1012,  0,    "-", 31, 33, 70, 0, 160, 300, "안전", "경작");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1010,  60,     "강단",$img,1015,  0,    "-", 41, 73, 43, 0, 160, 300, "재간",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1011, 102,     "고간",$img,1017,  0,    "-", 60, 57, 51, 0, 160, 300, "유지",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1012,  69,     "고상",$img,1019,  0,    "-", 41, 40, 38, 0, 160, 300, "재간",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1013,   7,     "고승",$img,1021,  0,    "-", 42, 73, 24, 0, 160, 300, "출세",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1014, 120,     "고옹",$img,1022,  0,    "-", 57, 21, 79, 0, 160, 300, "출세",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1015,  53,     "고패",$img,1024,  0,    "-", 53, 56, 28, 0, 160, 300, "유지",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1016,  74,     "공도",$img,1025,  0,    "-", 26, 73, 19, 0, 160, 300, "재간",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1017,  65,   "공손월",$img,1032,  0,    "-", 47, 63, 46, 0, 160, 300, "정복",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1018,  83,     "공지",$img,1036,  0,    "-", 57, 54, 64, 0, 160, 300, "대의",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1019,   2,     "곽사",$img,1039,  0,    "-", 58, 67, 31, 0, 160, 300, "패권",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1020,  80,   "곽유지",$img,1040,  0,    "-", 37, 22, 71, 0, 160, 300, "재간", "상재");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1021,  76,     "관이",$img,1048,  0,    "-", 48, 60, 58, 0, 160, 300, "안전",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1022,  76,     "관통",$img,1049,  0,    "-", 49, 63, 60, 0, 160, 300, "대의",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1023,  98,     "교모",$img,1055,  0,    "-", 59, 58, 61, 0, 160, 300, "안전",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1024,  98,     "교현",$img,1056,  0,    "-", 50, 18, 60, 0, 160, 300, "재간",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1025,   6,   "구역거",$img,1057,  0,    "-", 51, 72, 49, 0, 160, 300, "안전",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1026,  80,     "극정",$img,1058,  0,    "-", 38, 25, 75, 0, 160, 300, "유지",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1027,  46,     "금선",$img,1059,  0,    "-", 55, 49, 36, 0, 160, 300, "할거",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1028,  62, "금환삼결",$img,1060,  0,    "-", 46, 76, 17, 0, 160, 300, "출세",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1029, 122,     "낙통",$img,1062,  0,    "-", 57, 44, 69, 0, 160, 300, "유지",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1030, 142,     "뇌박",$img,1066,  0,    "-", 54, 54, 33, 0, 160, 300, "유지",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1031, 132,     "담웅",$img,1070,  0,    "-", 52, 77, 19, 0, 160, 300, "출세",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1032,  99,     "답둔",$img,1071,  0,    "-", 59, 71, 31, 0, 160, 300, "유지",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1033, 132,     "당자",$img,1072,  0,    "-", 59, 56, 45, 0, 160, 300, "안전",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1034,  60, "대래동주",$img,1073,  0,    "-", 40, 65, 24, 0, 160, 300, "안전",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1035,  82,     "도겸",$img,1074,  0,    "-", 51, 32, 61, 0, 160, 300, "할거", "인덕");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1036, 120,     "도준",$img,1075,  0,    "-", 64, 57, 50, 0, 160, 300, "안전",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1037,  62,   "동다나",$img,1077,  0,    "-", 51, 71, 27, 0, 160, 300, "유지",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1038,   2,     "동민",$img,1078,  0,    "-", 52, 65, 49, 0, 160, 300, "안전",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1039,  21,     "동소",$img,1079,  0,    "-", 46, 46, 62, 0, 160, 300, "패권",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1040, 127,     "동습",$img,1080,  0,    "-", 53, 64, 32, 0, 160, 300, "출세",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1041,  66,     "동화",$img,1084,  0,    "-", 48, 64, 53, 0, 160, 300, "대의",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1042,   7,     "등무",$img,1086,  0,    "-", 43, 74, 19, 0, 160, 300, "정복",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1043, 116,     "등윤",$img,1088,  0,    "-", 34, 42, 68, 0, 160, 300, "안전",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1044,  48,     "마완",$img,1097,  0,    "-", 49, 64, 26, 0, 160, 300, "안전", "기병");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1045,  19,     "마준",$img,1098,  0,    "-", 45, 63, 62, 0, 160, 300, "안전",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1046,  51,   "망아장",$img,1105,  0,    "-", 29, 64, 20, 0, 160, 300, "출세",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1047,  29,     "모개",$img,1109,  0,    "-", 46, 56, 56, 0, 160, 300, "대의",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1048,  96,     "목순",$img,1111,  0,    "-", 17, 21, 68, 0, 160, 300, "왕좌",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1049,  43,   "무안국",$img,1112,  0,    "-", 51, 73, 18, 0, 160, 300, "재간",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1050, 108,     "미방",$img,1118,  0,    "-", 58, 65, 37, 0, 160, 300, "패권", "징병");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1051,  77,     "미축",$img,1119,  0,    "-", 26, 30, 65, 0, 160, 300, "왕좌", "상재");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1052,  44,     "반준",$img,1121,  0,    "-", 41, 21, 67, 0, 160, 300, "안전",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1053,  65,     "방회",$img,1124,  0,    "-", 25, 33, 59, 0, 160, 300, "안전",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1054,  68,   "배원소",$img,1125,  0,    "-", 45, 69, 33, 0, 160, 300, "재간",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1055,  78,     "번건",$img,1126,  0,    "-", 28, 31, 68, 0, 160, 300, "대의",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1056, 121,     "보질",$img,1130,  0,    "-", 58, 28, 77, 0, 160, 300, "유지",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1057, 113,   "복양흥",$img,1131,  0,    "-", 58, 51, 71, 0, 160, 300, "유지",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1058, 108,   "부사인",$img,1134,  0,    "-", 54, 59, 51, 0, 160, 300, "출세",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1059,  38,     "부손",$img,1135,  0,    "-", 24, 43, 68, 0, 160, 300, "안전",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1060,  74,     "부첨",$img,1136,  0,    "-", 61, 74, 45, 0, 160, 300, "대의",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1061,  66,     "비시",$img,1137,  0,    "-", 18, 36, 61, 0, 160, 300, "재간",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1062, 144,     "사광",$img,1140,  0,    "-", 57, 49, 66, 0, 160, 300, "안전",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1063,  20,   "사마랑",$img,1142,  0,    "-", 25, 32, 63, 0, 160, 300, "대의",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1064,  24,   "사마부",$img,1144,  0,    "-", 55, 31, 73, 0, 160, 300, "대의",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1065,  30,   "사마유",$img,1148,  0,    "-", 62, 45, 79, 0, 160, 300, "왕좌",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1066, 139,     "사일",$img,1151,  0,    "-", 59, 44, 68, 0, 160, 300, "유지",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1067, 146,     "사지",$img,1153,  0,    "-", 61, 49, 70, 0, 160, 300, "안전",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1068,  35,     "서막",$img,1155,  0,    "-", 56, 41, 72, 0, 160, 300, "유지",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1069, 142,     "서영",$img,1158,  0,    "-", 47, 63, 33, 0, 160, 300, "안전",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1070,  23,     "서질",$img,1159,  0,    "-", 55, 73, 34, 0, 160, 300, "재간",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1071, 131,     "설영",$img,1162,  0,    "-", 46, 23, 64, 0, 160, 300, "유지",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1072, 128,     "설종",$img,1163,  0,    "-", 27, 33, 67, 0, 160, 300, "유지",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1073,  69,     "성의",$img,1164,  0,    "-", 45, 64, 22, 0, 160, 300, "재간",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1074,  76,     "손건",$img,1166,  0,    "-", 42, 33, 73, 0, 160, 300, "대의", "거상");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1075, 126,     "손광",$img,1168,  0,    "-", 63, 54, 58, 0, 160, 300, "유지",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1076, 126,     "손랑",$img,1170,  0,    "-", 27, 54, 28, 0, 160, 300, "안전",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1077, 126,     "손등",$img,1171,  0,    "-", 52, 39, 77, 0, 160, 300, "할거",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1078, 125,     "손량",$img,1172,  0,    "-", 24, 23, 79, 0, 160, 300, "할거",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1079, 122,     "손이",$img,1177,  0,    "-", 57, 62, 57, 0, 160, 300, "안전",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1080, 126,     "손정",$img,1178,  0,    "-", 59, 56, 62, 0, 160, 300, "유지",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1081, 115,     "손준",$img,1179,  0,    "-", 59, 69, 51, 0, 160, 300, "정복",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1082,   7,     "손중",$img,1180,  0,    "-", 53, 63, 24, 0, 160, 300, "안전",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1083, 115,     "손침",$img,1182,  0,    "-", 49, 71, 40, 0, 160, 300, "패권",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1084, 114,     "손호",$img,1183,  0,    "-", 20, 78, 67, 0, 160, 300, "패권",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1085, 126,     "손화",$img,1184,  0,    "-", 35, 25, 71, 0, 160, 300, "왕좌",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1086, 126,     "손휴",$img,1186,  0,    "-", 63, 43, 64, 0, 160, 300, "정복",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1087, 117,     "손흠",$img,1187,  0,    "-", 66, 63, 33, 0, 160, 300, "정복",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1088, 147,     "송헌",$img,1188,  0,    "-", 42, 63, 41, 0, 160, 300, "안전",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1089,  29,     "신비",$img,1192,  0,    "-", 47, 28, 74, 0, 160, 300, "재간",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1090,  37,     "신의",$img,1193,  0,    "-", 55, 61, 51, 0, 160, 300, "출세",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1091,  37,     "신탐",$img,1194,  0,    "-", 56, 58, 57, 0, 160, 300, "출세",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1092,  85,     "신평",$img,1195,  0,    "-", 68, 51, 75, 0, 160, 300, "왕좌",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1093, 126,     "심영",$img,1197,  0,    "-", 53, 72, 51, 0, 160, 300, "재간",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1094,  47, "아하소과",$img,1198,  0,    "-", 53, 75, 15, 0, 160, 300, "안전", "척사");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1095,  62,   "아회남",$img,1199,  0,    "-", 50, 74, 30, 0, 160, 300, "출세", "척사");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1096,  23,     "악침",$img,1201,  0,    "-", 45, 52, 33, 0, 160, 300, "대의",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1097,  14,     "양백",$img,1204,  0,    "-", 55, 54, 53, 0, 160, 300, "안전",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1098,  92,    "양봉1",$img,1205,  0,    "-", 57, 64, 36, 0, 160, 300, "유지",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1099,  13,     "양송",$img,1207,  0,    "-", 15, 35, 34, 0, 160, 300, "안전",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1100,  61,     "양의",$img,1209,  0,    "-", 67, 56, 71, 0, 160, 300, "유지",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1101, 141,    "양조1",$img,1210,  0,    "-", 68, 54, 60, 0, 160, 300, "출세",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1102,   9,    "양추1",$img,1211,  0,    "-", 51, 67, 16, 0, 160, 300, "정복",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1103,  53,     "양회",$img,1213,  0,    "-", 60, 67, 40, 0, 160, 300, "출세",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1104, 141,     "양흥",$img,1214,  0,    "-", 52, 68, 17, 0, 160, 300, "안전",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1105,  64,     "엄강",$img,1216,  0,    "-", 57, 65, 44, 0, 160, 300, "재간",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1106,  10,   "엄백호",$img,1217,  0,    "-", 48, 68, 30, 0, 160, 300, "할거",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1107,  10,     "엄여",$img,1218,  0,    "-", 35, 66, 24, 0, 160, 300, "할거",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1108,   7,     "엄정",$img,1220,  0,    "-", 31, 68, 49, 0, 160, 300, "출세",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1109, 121,     "엄준",$img,1221,  0,    "-", 44, 24, 71, 0, 160, 300, "재간",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1110,  71,     "여개",$img,1222,  0,    "-", 51, 42, 67, 0, 160, 300, "유지",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1111,  29,     "여건",$img,1223,  0,    "-", 44, 68, 29, 0, 160, 300, "출세",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1112, 107,     "여광",$img,1224,  0,    "-", 60, 67, 27, 0, 160, 300, "유지",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1113, 123,     "여범",$img,1226,  0,    "-", 43, 34, 71, 0, 160, 300, "할거",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1114, 107,     "여상",$img,1227,  0,    "-", 62, 68, 26, 0, 160, 300, "유지",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1115, 105,   "여위황",$img,1228,  0,    "-", 42, 62, 38, 0, 160, 300, "정복",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1116,  50,     "염우",$img,1230,  0,    "-", 58, 51, 18, 0, 160, 300, "출세",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1117,  18,     "염포",$img,1232,  0,    "-", 33, 35, 77, 0, 160, 300, "유지",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1118, 133,     "오강",$img,1234,  0,    "-", 47, 37, 61, 0, 160, 300, "안전",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1119,  72,     "오반",$img,1236,  0,    "-", 70, 66, 45, 0, 160, 300, "재간",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1120, 126,     "오연",$img,1238,  0,    "-", 36, 70, 31, 0, 160, 300, "정복",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1121,  19,     "오질",$img,1240,  0,    "-", 43, 37, 69, 0, 160, 300, "출세",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1122,  52,     "옹개",$img,1242,  0,    "-", 58, 67, 51, 0, 160, 300, "안전",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1123,  20,     "왕경",$img,1243,  0,    "-", 55, 47, 65, 0, 160, 300, "재간",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1124,  34,     "왕랑",$img,1246,  0,    "-", 49, 29, 51, 0, 160, 300, "할거",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1125,  57,     "왕루",$img,1247,  0,    "-", 40, 28, 76, 0, 160, 300, "유지",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1126,  76,     "왕보",$img,1248,  0,    "-", 47, 34, 75, 0, 160, 300, "대의",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1127,  86,     "왕수",$img,1249,  0,    "-", 34, 34, 67, 0, 160, 300, "재간",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1128,  92,     "왕윤",$img,1252,  0,    "-", 16, 18, 77, 0, 160, 300, "왕좌",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1129,  40,     "왕융",$img,1253,  0,    "-", 62, 41, 77, 0, 160, 300, "재간",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1130,  30,     "왕찬",$img,1255,  0,    "-", 28, 28, 78, 0, 160, 300, "안전",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1131,  33,     "왕창",$img,1256,  0,    "-", 74, 57, 52, 0, 160, 300, "정복",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1132,  71,     "왕항",$img,1258,  0,    "-", 51, 43, 60, 0, 160, 300, "대의",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1133,  33,     "왕혼",$img,1259,  0,    "-", 69, 32, 59, 0, 160, 300, "정복",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1134, 122,     "우번",$img,1264,  0,    "-", 23, 42, 73, 0, 160, 300, "유지",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1135, 126,     "우전",$img,1265,  0,    "-", 63, 55, 41, 0, 160, 300, "안전",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1136, 140,     "원윤",$img,1270,  0,    "-", 41, 34, 60, 0, 160, 300, "패권",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1137, 147,     "위속",$img,1273,  0,    "-", 57, 59, 41, 0, 160, 300, "안전",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1138,  96,     "위유",$img,1275,  0,    "-", 53, 69, 71, 0, 160, 300, "재간",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1139,  76,     "유기",$img,1276,  0,    "-", 57, 19, 73, 0, 160, 300, "왕좌",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1140, 134,     "유도",$img,1278,  0,    "-", 35, 33, 68, 0, 160, 300, "재간",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1141,  75,    "유선1",$img,1282,  0,    "-", 24, 17, 21, 0, 160, 300, "대의",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1142,  75,     "유심",$img,1284,  0,    "-", 63, 46, 70, 0, 160, 300, "왕좌",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1143,  55,     "유언",$img,1286,  0,    "-", 60, 40, 74, 0, 160, 300, "할거",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1144,  27,     "유엽",$img,1287,  0,    "-", 40, 29, 79, 0, 160, 300, "유지",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1145,  11,     "유요",$img,1288,  0,    "-", 23, 22, 48, 0, 160, 300, "안전", "발명");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1146,  96,     "유우",$img,1289,  0,    "-", 68, 34, 72, 0, 160, 300, "왕좌",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1147,  55,     "유장",$img,1290,  0,    "-", 38, 31, 63, 0, 160, 300, "할거", "수비");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1148,  45,     "유종",$img,1291,  0,    "-", 22, 26, 61, 0, 160, 300, "할거",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1149,  56,     "유파",$img,1292,  0,    "-", 47, 32, 70, 0, 160, 300, "대의",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1150, 134,     "유현",$img,1295,  0,    "-", 32, 56, 55, 0, 160, 300, "안전",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1151, 122,     "육개",$img,1296,  0,    "-", 66, 30, 72, 0, 160, 300, "재간",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1152, 121,     "육적",$img,1298,  0,    "-", 44, 29, 73, 0, 160, 300, "안전",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1153,  38,   "윤대목",$img,1300,  0,    "-", 62, 49, 69, 0, 160, 300, "재간",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1154,  80,     "윤묵",$img,1301,  0,    "-", 19, 28, 73, 0, 160, 300, "대의",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1155,  72,     "윤상",$img,1302,  0,    "-", 30, 32, 42, 0, 160, 300, "대의",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1156, 136,     "윤직",$img,1303,  0,    "-", 44, 58, 63, 0, 160, 300, "재간",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1157,   2,     "이각",$img,1304,  0,    "-", 56, 77, 43, 0, 160, 300, "패권",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1158, 146,     "이숙",$img,1305,  0,    "-", 27, 45, 67, 0, 160, 300, "재간",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1159, 132,     "이이",$img,1308,  0,    "-", 55, 75, 20, 0, 160, 300, "정복",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1160,  77,     "이적",$img,1309,  0,    "-", 55, 27, 77, 0, 160, 300, "할거",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1161,  71,    "이풍1",$img,1311,  0,    "-", 59, 56, 62, 0, 160, 300, "재간",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1162,  66,     "이회",$img,1312,  0,    "-", 67, 50, 79, 0, 160, 300, "유지",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1163, 114,     "잠혼",$img,1313,  0,    "-", 15, 16, 44, 0, 160, 300, "할거",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1164,  34,     "장간",$img,1315,  0,    "-", 19, 20, 70, 0, 160, 300, "재간",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1165,   7,     "장개",$img,1316,  0,    "-", 48, 69, 19, 0, 160, 300, "안전",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1166,  62,     "장서",$img,1323,  0,    "-", 44, 48, 35, 0, 160, 300, "출세",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1167,  78,    "장소2",$img,1325,  0,    "-", 51, 44, 71, 0, 160, 300, "재간",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1168,  11,     "장영",$img,1330,  0,    "-", 55, 65, 40, 0, 160, 300, "재간",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1169, 118,     "장온",$img,1331,  0,    "-", 21, 30, 69, 0, 160, 300, "할거",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1170,  15,     "장위",$img,1333,  0,    "-", 65, 70, 29, 0, 160, 300, "대의",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1171, 104,   "장의거",$img,1335,  0,    "-", 68, 59, 34, 0, 160, 300, "대의",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1172, 126,    "장제3",$img,1340,  0,    "-", 74, 49, 61, 0, 160, 300, "할거",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1173,  17,     "장패",$img,1341,  0,    "-", 44, 78, 43, 0, 160, 300, "할거",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1174, 113,    "장포2",$img,1343,  0,    "-", 63, 66, 51, 0, 160, 300, "유지",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1175,  23,     "장호",$img,1345,  0,    "-", 56, 62, 54, 0, 160, 300, "정복",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1176,  48,     "장횡",$img,1347,  0,    "-", 53, 67, 25, 0, 160, 300, "재간",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1177, 120,     "장휴",$img,1349,  0,    "-", 42, 35, 70, 0, 160, 300, "재간",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1178,  42,     "전속",$img,1353,  0,    "-", 66, 57, 49, 0, 160, 300, "안전",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1179,  24,     "정무",$img,1359,  0,    "-", 54, 38, 74, 0, 160, 300, "출세",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1180, 119,     "정병",$img,1360,  0,    "-", 22, 25, 67, 0, 160, 300, "유지",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1181,   7,   "정원지",$img,1365,  0,    "-", 41, 74, 38, 0, 160, 300, "출세",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1182,  69,     "정은",$img,1366,  0,    "-", 53, 62, 26, 0, 160, 300, "재간",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1183,  76,   "제갈균",$img,1368,  0,    "-", 59, 45, 74, 0, 160, 300, "안전", "상재");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1184, 135,   "제갈정",$img,1372,  0,    "-", 56, 57, 54, 0, 160, 300, "안전",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1185,  74,     "조루",$img,1376,  0,    "-", 49, 37, 60, 0, 160, 300, "대의",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1186,  24,     "조모",$img,1377,  0,    "-", 53, 32, 30, 0, 160, 300, "할거",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1187,  26,     "조방",$img,1379,  0,    "-", 50, 20, 31, 0, 160, 300, "정복",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1188,  83,     "조범",$img,1380,  0,    "-", 58, 40, 63, 0, 160, 300, "유지",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1189, 147,     "조성",$img,1383,  0,    "-", 44, 69, 51, 0, 160, 300, "안전",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1190,  26,     "조순",$img,1384,  0,    "-", 66, 57, 72, 0, 160, 300, "정복",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1191,  25,     "조앙",$img,1386,  0,    "-", 44, 65, 62, 0, 160, 300, "왕좌",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1192,  26,     "조우",$img,1388,  0,    "-", 67, 55, 67, 0, 160, 300, "대의",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1193,  25,     "조웅",$img,1390,  0,    "-", 59, 27, 44, 0, 160, 300, "안전",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1194,  84,     "조표",$img,1396,  0,    "-", 34, 70, 16, 0, 160, 300, "안전",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1195,   7,    "조홍1",$img,1397,  0,    "-", 52, 66, 42, 0, 160, 300, "유지",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1196,  26,     "조환",$img,1399,  0,    "-", 34, 24, 42, 0, 160, 300, "정복",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1197,  19,     "조훈",$img,1400,  0,    "-", 67, 63, 30, 0, 160, 300, "출세",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1198,  19,     "조희",$img,1402,  0,    "-", 64, 57, 71, 0, 160, 300, "대의",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1199,  22,     "종요",$img,1403,  0,    "-", 16, 20, 74, 0, 160, 300, "유지",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1200, 118,     "주방",$img,1406,  0,    "-", 56, 36, 76, 0, 160, 300, "할거",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1201, 128,     "주이",$img,1409,  0,    "-", 61, 55, 61, 0, 160, 300, "안전",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1202,  32,     "주지",$img,1411,  0,    "-", 52, 77, 47, 0, 160, 300, "안전",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1203,  76,     "주창",$img,1412,  0,    "-", 42, 79, 30, 0, 160, 300, "의협", "궁병");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1204, 126,     "주치",$img,1413,  0,    "-", 58, 55, 56, 0, 160, 300, "안전",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1205,  41,    "주태1",$img,1414,  0,    "-", 62, 55, 61, 0, 160, 300, "안전",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1206,  29,     "진건",$img,1417,  0,    "-", 62, 70, 62, 0, 160, 300, "정복",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1207,  29,     "진교",$img,1418,  0,    "-", 21, 25, 67, 0, 160, 300, "유지",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1208,  79,     "진규",$img,1421,  0,    "-", 22, 19, 71, 0, 160, 300, "할거",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1209,  66,     "진복",$img,1426,  0,    "-", 36, 27, 76, 0, 160, 300, "재간",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1210,  81,     "진식",$img,1428,  0,    "-", 47, 68, 52, 0, 160, 300, "출세",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1211,  79,     "진진",$img,1429,  0,    "-", 58, 38, 64, 0, 160, 300, "재간",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1212,  11,     "진횡",$img,1431,  0,    "-", 38, 58, 47, 0, 160, 300, "출세",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1213,  21,     "차주",$img,1432,  0,    "-", 55, 66, 62, 0, 160, 300, "안전",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1214,  11,     "착융",$img,1433,  0,    "-", 62, 59, 21, 0, 160, 300, "출세",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1215,  36,     "채중",$img,1435,  0,    "-", 58, 43, 55, 0, 160, 300, "출세",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1216,  36,     "채화",$img,1436,  0,    "-", 56, 47, 49, 0, 160, 300, "안전",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1217,  20,     "최염",$img,1438,  0,    "-", 43, 54, 67, 0, 160, 300, "대의",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1218, 124,   "태사향",$img,1442,  0,    "-", 51, 69, 50, 0, 160, 300, "재간",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1219,  12,     "포륭",$img,1443,  0,    "-", 53, 74, 20, 0, 160, 300, "안전",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1220,  74,     "풍습",$img,1444,  0,    "-", 36, 64, 44, 0, 160, 300, "재간",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1221,   7,     "하의",$img,1445,  0,    "-", 49, 68, 25, 0, 160, 300, "출세",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1222,  90,     "하진",$img,1447,  0,    "-", 49, 69, 37, 0, 160, 300, "왕좌",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1223,  19,   "하후무",$img,1450,  0,    "-", 38, 33, 37, 0, 160, 300, "안전",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1224,  26,   "하후은",$img,1454,  0,    "-", 49, 51, 39, 0, 160, 300, "안전",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1225,  20,   "하후현",$img,1456,  0,    "-", 57, 23, 75, 0, 160, 300, "유지",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1226,  93,     "한복",$img,1461,  0,    "-", 66, 59, 42, 0, 160, 300, "안전",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1227,   8,     "한섬",$img,1462,  0,    "-", 39, 62, 35, 0, 160, 300, "출세",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1228,  40,     "한숭",$img,1464,  0,    "-", 21, 25, 70, 0, 160, 300, "재간",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1229,   7,     "한충",$img,1465,  0,    "-", 41, 66, 29, 0, 160, 300, "안전",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1230,   9,     "한현",$img,1466,  0,    "-", 43, 61, 20, 0, 160, 300, "유지",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1231,  27,     "한호",$img,1467,  0,    "-", 60, 73, 45, 0, 160, 300, "유지",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1232,  79,     "향랑",$img,1468,  0,    "-", 51, 21, 77, 0, 160, 300, "대의",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1233,  21,     "허유",$img,1471,  0,    "-", 47, 47, 57, 0, 160, 300, "재간",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1234,  23,     "허의",$img,1472,  0,    "-", 31, 74, 47, 0, 160, 300, "출세",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1235,  87,     "허정",$img,1474,  0,    "-", 18, 29, 74, 0, 160, 300, "재간",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1236,  12,   "형도영",$img,1475,  0,    "-", 49, 78, 23, 0, 160, 300, "출세",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1237, 148,   "호거아",$img,1476,  0,    "-", 35, 76, 61, 0, 160, 300, "안전",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1238,  76,     "호반",$img,1477,  0,    "-", 61, 58, 46, 0, 160, 300, "재간",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1239,  20,     "호준",$img,1480,  0,    "-", 67, 60, 46, 0, 160, 300, "재간",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1240, 131,     "화핵",$img,1482,  0,    "-", 37, 27, 75, 0, 160, 300, "안전",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1241,  10,     "화흠",$img,1483,  0,    "-", 18, 43, 75, 0, 160, 300, "출세",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1242,  41,     "황란",$img,1487,  0,    "-", 29, 70, 25, 0, 160, 300, "재간",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1243,  50,     "황호",$img,1490,  0,    "-", 15, 17, 48, 0, 160, 300, "안전",    "-");
RegGeneral($connect,1,1,$fiction,$turnterm,$startyear,$year,1244, 147,     "후성",$img,1491,  0,    "-", 56, 62, 33, 0, 160, 300, "정복",    "-");

//////////////////////////장수 끝///////////////////////////////////////////////

//////////////////////////도시 소속/////////////////////////////////////////////

//////////////////////////도시 끝///////////////////////////////////////////////

//////////////////////////이벤트///////////////////////////////////////////////

$history[count($history)] = "<C>●</>180년 1월:<L><b>【가상모드6】</b>무풍지대</>";
$history[count($history)] = "<C>●</>180년 1월:<L><b>【이벤트】</b></>영웅은 없다! 오직 내가 영웅일 뿐이다!";
pushHistory($connect, $history);

echo "<script>location.replace('install3_ok.php');</script>";

