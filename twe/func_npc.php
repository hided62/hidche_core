<?php

function RegNPC($connect) {
    $query = "select startyear,year,turnterm,scenario,extend,fiction,img from game where no='1'";
    $result = MYDB_query($query, $connect) or Error("scenario_194A ".MYDB_error($connect),"");
    $admin = MYDB_fetch_array($result);

    $fiction = $admin['fiction'];    $turnterm = $admin['turnterm'];    $startyear = $admin['startyear'];    $year = $admin['year'];
    $img = $admin['img'];
//                                                                    상성      이름       사진 국가  도시   통  무  지 급 출생 사망    꿈     특기
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1001,   1,    "소제1",$img,1001,  0,    "-", 20, 11, 48, 0, 168, 190, "유지",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1002,   1,     "헌제",$img,1002,  0,    "-", 17, 13, 61, 0, 170, 250, "안전",    "-", "한 왕실을 구해줄 이는 진정 없는 것인가...");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1003, 999,   "사마휘",$img,1003,  0,    "-", 71, 11, 96, 0, 173, 234, "은둔", "신산", "좋지, 좋아~");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1004, 999,     "우길",$img,1004,  0,    "-", 17, 13, 83, 0, 131, 200, "은둔", "신산");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1005, 999,     "화타",$img,1005,  0,    "-", 53, 25, 70, 0, 151, 220, "은둔", "의술", "아픈 사람들은 모두 내게 오시오. 껄껄껄.");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1006, 999,     "길평",$img,1006,  0,    "-", 27, 15, 72, 0, 158, 200, "은둔", "의술");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1007,  29,     "가규",$img,1007,  0,    "-", 55, 55, 74, 0, 177, 231, "재간",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1008, 136,     "가범",$img,1008,  0,    "-", 58, 48, 73, 0, 202, 237, "유지",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1009,  49,   "가비능",$img,1009,  0,    "-", 58, 83, 32, 0, 172, 235, "정복",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1010,  31,     "가충",$img,1010,  0,    "-", 50, 25, 87, 0, 217, 282, "출세",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1011,  20,     "가후",$img,1011,  0,    "-", 69, 30, 94, 0, 147, 223, "할거", "귀병");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1012,  73,     "간옹",$img,1012,  0,    "-", 31, 33, 70, 0, 164, 225, "안전", "경작");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1013, 129,     "감녕",$img,1013,  0,    "-", 78, 95, 71, 0, 174, 222, "출세", "무쌍");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1014, 127,     "감택",$img,1014,  0,    "-", 62, 44, 79, 0, 182, 243, "유지",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1015,  60,     "강단",$img,1015,  0,    "-", 41, 73, 43, 0, 168, 230, "재간",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1016,  73,     "강유",$img,1016,  0,    "-", 95, 90, 94, 0, 202, 264, "왕좌", "집중");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1017, 102,     "고간",$img,1017,  0,    "-", 60, 57, 51, 0, 168, 206, "유지",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1018,  27,     "고람",$img,1018,  0,    "-", 72, 67, 59, 0, 159, 201, "출세", "기병");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1019,  69,     "고상",$img,1019,  0,    "-", 41, 40, 38, 0, 194, 252, "재간",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1020, 144,     "고순",$img,1020,  0,    "-", 79, 82, 65, 0, 162, 198, "의협", "기병");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1021,   7,     "고승",$img,1021,  0,    "-", 42, 73, 24, 0, 145, 185, "출세",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1022, 120,     "고옹",$img,1022,  0,    "-", 57, 21, 79, 0, 168, 243, "출세",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1023,  63,     "고정",$img,1023,  0,    "-", 67, 65, 55, 0, 190, 251, "정복",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1024,  53,     "고패",$img,1024,  0,    "-", 53, 56, 28, 0, 170, 212, "유지",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1025,  74,     "공도",$img,1025,  0,    "-", 26, 73, 19, 0, 164, 200, "재간",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1026, 142,   "공손강",$img,1026,  0,    "-", 64, 72, 61, 0, 172, 210, "정복",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1027, 142,   "공손공",$img,1027,  0,    "-", 68, 41, 75, 0, 174, 238, "정복",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1028, 142,   "공손도",$img,1028,  0,    "-", 62, 72, 41, 0, 154, 204, "정복", "기병");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1029,  65,   "공손범",$img,1029,  0,    "-", 61, 67, 61, 0, 158, 199, "출세",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1030,  65,   "공손속",$img,1030,  0,    "-", 60, 76, 41, 0, 176, 199, "정복",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1031,  10,   "공손연",$img,1031,  0,    "-", 74, 79, 64, 0, 205, 238, "패권", "돌격");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1032,  65,   "공손월",$img,1032,  0,    "-", 47, 63, 46, 0, 160, 192, "정복",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1033,  65,   "공손찬",$img,1033,  0,    "-", 61, 87, 67, 0, 152, 199, "패권", "기병");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1034,  43,     "공융",$img,1034,  0,    "-", 63, 48, 85, 0, 153, 208, "왕좌", "경작");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1035,  35,     "공주",$img,1035,  0,    "-", 64, 35, 78, 0, 151, 194, "재간",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1036,  83,     "공지",$img,1036,  0,    "-", 57, 54, 64, 0, 178, 242, "대의",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1037,  26,     "곽가",$img,1037,  0,    "-", 47, 23, 99, 0, 170, 207, "패권", "귀모");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1038, 111,     "곽도",$img,1038,  0,    "-", 63, 67, 81, 0, 155, 205, "재간",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1039,   2,     "곽사",$img,1039,  0,    "-", 58, 67, 31, 0, 146, 197, "패권",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1040,  80,   "곽유지",$img,1040,  0,    "-", 37, 22, 71, 0, 190, 259, "재간", "상재");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1041,  67,     "곽익",$img,1041,  0,    "-", 67, 60, 67, 0, 207, 270, "재간",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1042,  67,     "곽준",$img,1042,  0,    "-", 76, 69, 73, 0, 178, 217, "출세",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1043,  27,     "곽혁",$img,1043,  0,    "-", 40, 29, 80, 0, 187, 228, "안전",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1044,  20,     "곽회",$img,1044,  0,    "-", 77, 75, 71, 0, 187, 255, "정복",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1045,  39,   "관구검",$img,1045,  0,    "-", 72, 68, 77, 0, 202, 255, "대의",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1046,  76,     "관색",$img,1046,  0,    "-", 69, 85, 67, 0, 200, 239, "의협", "징병");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1047,  76,     "관우",$img,1047,  0,    "-", 96, 98, 80, 0, 162, 219, "의협", "위압");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1048,  76,     "관이",$img,1048,  0,    "-", 48, 60, 58, 0, 219, 263, "안전",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1049,  76,     "관통",$img,1049,  0,    "-", 49, 63, 60, 0, 218, 259, "대의",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1050,  76,     "관평",$img,1050,  0,    "-", 77, 80, 70, 0, 186, 219, "의협", "보병");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1051,   7,     "관해",$img,1051,  0,    "-", 66, 90, 35, 0, 160, 193, "출세",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1052,  76,     "관흥",$img,1052,  0,    "-", 69, 84, 72, 0, 199, 234, "의협", "돌격");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1053,  40,     "괴량",$img,1053,  0,    "-", 41, 28, 81, 0, 155, 204, "안전", "신중");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1054,  40,     "괴월",$img,1054,  0,    "-", 26, 30, 84, 0, 157, 214, "유지", "귀병");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1055,  98,     "교모",$img,1055,  0,    "-", 59, 58, 61, 0, 150, 191, "안전",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1056,  98,     "교현",$img,1056,  0,    "-", 50, 18, 60, 0, 158, 210, "재간",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1057,   6,   "구역거",$img,1057,  0,    "-", 51, 72, 49, 0, 152, 193, "안전",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1058,  80,     "극정",$img,1058,  0,    "-", 38, 25, 75, 0, 208, 278, "유지",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1059,  46,     "금선",$img,1059,  0,    "-", 55, 49, 36, 0, 155, 208, "할거",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1060,  62, "금환삼결",$img,1060,  0,    "-", 46, 76, 17, 0, 192, 225, "출세",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1061, 141,     "기령",$img,1061,  0,    "-", 76, 81, 33, 0, 155, 199, "대의", "무쌍");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1062, 122,     "낙통",$img,1062,  0,    "-", 57, 44, 69, 0, 193, 228, "유지",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1063, 124,    "노숙1",$img,1063,  0,    "-", 90, 42, 94, 0, 172, 217, "왕좌", "상재");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1064,  75,     "노식",$img,1064,  0,    "-", 91, 54, 80, 0, 139, 192, "왕좌", "징병", "한 황실의 앞날이 심히 걱정되는구나...");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1065,  59,     "뇌동",$img,1065,  0,    "-", 70, 77, 45, 0, 172, 218, "출세", "궁병");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1066, 142,     "뇌박",$img,1066,  0,    "-", 54, 54, 33, 0, 157, 206, "유지",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1067, 127,     "능조",$img,1067,  0,    "-", 67, 80, 44, 0, 165, 203, "재간", "공성");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1068, 127,     "능통",$img,1068,  0,    "-", 71, 78, 58, 0, 189, 237, "의협", "궁병");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1069,  64,     "단경",$img,1069,  0,    "-", 68, 61, 68, 0, 156, 199, "정복",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1070, 132,     "담웅",$img,1070,  0,    "-", 52, 77, 19, 0, 188, 221, "출세",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1071,  99,     "답둔",$img,1071,  0,    "-", 59, 71, 31, 0, 158, 207, "유지",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1072, 132,     "당자",$img,1072,  0,    "-", 59, 56, 45, 0, 196, 265, "안전",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1073,  60, "대래동주",$img,1073,  0,    "-", 40, 65, 24, 0, 195, 249, "안전",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1074,  82,     "도겸",$img,1074,  0,    "-", 51, 32, 61, 0, 132, 194, "할거", "인덕");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1075, 120,     "도준",$img,1075,  0,    "-", 64, 57, 50, 0, 238, 285, "안전",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1076,  73,     "동궐",$img,1076,  0,    "-", 66, 50, 76, 0, 204, 271, "대의",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1077,  62,   "동다나",$img,1077,  0,    "-", 51, 71, 27, 0, 189, 225, "유지",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1078,   2,     "동민",$img,1078,  0,    "-", 52, 65, 49, 0, 149, 192, "안전",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1079,  21,     "동소",$img,1079,  0,    "-", 46, 46, 62, 0, 156, 236, "패권",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1080, 127,     "동습",$img,1080,  0,    "-", 53, 64, 32, 0, 169, 215, "출세",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1081,  89,     "동승",$img,1081,  0,    "-", 75, 66, 65, 0, 154, 200, "왕좌",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1082,  78,     "동윤",$img,1082,  0,    "-", 64, 26, 78, 0, 192, 246, "왕좌",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1083,   2,     "동탁",$img,1083,  0,    "-", 87, 91, 54, 0, 139, 192, "패권", "기병");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1084,  66,     "동화",$img,1084,  0,    "-", 48, 64, 53, 0, 168, 219, "대의",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1085,  32,     "두예",$img,1085,  0,    "-", 88, 80, 84, 0, 222, 284, "정복",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1086,   7,     "등무",$img,1086,  0,    "-", 43, 74, 19, 0, 147, 185, "정복",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1087,  41,     "등애",$img,1087,  0,    "-", 94, 82, 92, 0, 197, 264, "패권", "신산");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1088, 116,     "등윤",$img,1088,  0,    "-", 34, 42, 68, 0, 194, 256, "안전",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1089,  73,     "등지",$img,1089,  0,    "-", 74, 51, 80, 0, 182, 251, "할거", "경작");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1090,  41,     "등충",$img,1090,  0,    "-", 60, 82, 55, 0, 230, 264, "출세",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1091,  54,     "등현",$img,1091,  0,    "-", 65, 59, 61, 0, 188, 248, "출세",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1092,  29,     "마균",$img,1092,  0,    "-", 33, 38, 80, 0, 200, 259, "안전",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1093,  71,     "마대",$img,1093,  0,    "-", 77, 79, 49, 0, 183, 246, "대의", "기병");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1094,  70,     "마등",$img,1094,  0,    "-", 80, 87, 56, 0, 149, 211, "왕좌", "기병");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1095,  80,     "마속",$img,1095,  0,    "-", 73, 64, 82, 0, 190, 228, "패권", "집중");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1096,  77,     "마량",$img,1096,  0,    "-", 57, 25, 87, 0, 187, 225, "왕좌",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1097,  48,     "마완",$img,1097,  0,    "-", 49, 64, 26, 0, 170, 211, "안전", "기병");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1098,  19,     "마준",$img,1098,  0,    "-", 45, 63, 62, 0, 196, 260, "안전",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1099,  71,     "마철",$img,1099,  0,    "-", 71, 60, 31, 0, 179, 211, "대의", "기병");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1100,  70,     "마초",$img,1100,  0,    "-", 78, 97, 40, 0, 176, 226, "대의", "기병");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1101, 131,    "마충1",$img,1101,  0,    "-", 67, 62, 51, 0, 186, 222, "재간",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1102,  69,    "마충2",$img,1102,  0,    "-", 61, 68, 51, 0, 187, 249, "출세",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1103,  71,     "마휴",$img,1103,  0,    "-", 71, 60, 32, 0, 178, 211, "대의", "기병");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1104, 116,     "만총",$img,1104,  0,    "-", 79, 40, 78, 0, 170, 242, "할거", "신중");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1105,  51,   "망아장",$img,1105,  0,    "-", 29, 64, 20, 0, 191, 225, "출세",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1106,  44,     "맹달",$img,1106,  0,    "-", 70, 66, 72, 0, 172, 228, "할거", "귀병");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1107,  60,     "맹우",$img,1107,  0,    "-", 63, 79, 26, 0, 190, 251, "정복",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1108,  60,     "맹획",$img,1108,  0,    "-", 78, 92, 50, 0, 186, 245, "왕좌", "격노");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1109,  29,     "모개",$img,1109,  0,    "-", 46, 56, 56, 0, 161, 216, "대의",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1110,  51, "목록대왕",$img,1110,  0,    "-", 58, 71, 65, 0, 184, 225, "재간", "척사");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1111,  96,     "목순",$img,1111,  0,    "-", 17, 21, 68, 0, 157, 191, "왕좌",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1112,  43,   "무안국",$img,1112,  0,    "-", 51, 73, 18, 0, 156, 191, "재간",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1113,  28,     "문빙",$img,1113,  0,    "-", 70, 77, 43, 0, 178, 237, "유지",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1114,  38,     "문앙",$img,1114,  0,    "-", 71, 91, 46, 0, 222, 285, "대의",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1115, 102,     "문추",$img,1115,  0,    "-", 72, 94, 25, 0, 161, 200, "출세", "무쌍");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1116,  38,     "문흠",$img,1116,  0,    "-", 76, 77, 43, 0, 200, 258, "대의",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1117,  49, "미당대왕",$img,1117,  0,    "-", 64, 75, 32, 0, 202, 260, "유지",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1118, 108,     "미방",$img,1118,  0,    "-", 58, 65, 37, 0, 169, 222, "패권", "징병");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1119,  77,     "미축",$img,1119,  0,    "-", 26, 30, 65, 0, 165, 220, "왕좌", "상재");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1120,  94,     "반봉",$img,1120,  0,    "-", 61, 75, 17, 0, 155, 191, "출세",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1121,  44,     "반준",$img,1121,  0,    "-", 41, 21, 67, 0, 174, 239, "안전",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1122,  65,     "방덕",$img,1122,  0,    "-", 76, 90, 67, 0, 170, 219, "의협", "기병");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1123,  73,     "방통",$img,1123,  0,    "-", 86, 41, 97, 0, 179, 214, "패권", "반계");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1124,  65,     "방회",$img,1124,  0,    "-", 25, 33, 59, 0, 205, 272, "안전",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1125,  68,   "배원소",$img,1125,  0,    "-", 45, 69, 33, 0, 169, 200, "재간",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1126,  78,     "번건",$img,1126,  0,    "-", 28, 31, 68, 0, 205, 270, "대의",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1127, 149,     "번주",$img,1127,  0,    "-", 67, 77, 21, 0, 149, 192, "할거",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1128,  72,     "법정",$img,1128,  0,    "-", 81, 29, 93, 0, 176, 220, "패권", "신산");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1129,   8,     "변희",$img,1129,  0,    "-", 65, 65, 27, 0, 169, 200, "안전",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1130, 121,     "보질",$img,1130,  0,    "-", 58, 28, 77, 0, 177, 247, "유지",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1131, 113,   "복양흥",$img,1131,  0,    "-", 58, 51, 71, 0, 224, 264, "유지",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1132, 110,     "봉기",$img,1132,  0,    "-", 68, 52, 80, 0, 153, 202, "패권", "집중");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1133,  74,     "부동",$img,1133,  0,    "-", 58, 69, 69, 0, 183, 222, "대의",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1134, 108,   "부사인",$img,1134,  0,    "-", 54, 59, 51, 0, 182, 222, "출세",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1135,  38,     "부손",$img,1135,  0,    "-", 24, 43, 68, 0, 162, 230, "안전",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1136,  74,     "부첨",$img,1136,  0,    "-", 61, 74, 45, 0, 216, 263, "대의",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1137,  66,     "비시",$img,1137,  0,    "-", 18, 36, 61, 0, 176, 240, "재간",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1138, 141,     "비연",$img,1138,  0,    "-", 66, 65, 53, 0, 196, 238, "출세",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1139,  77,     "비위",$img,1139,  0,    "-", 72, 26, 73, 0, 193, 253, "대의",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1140, 144,     "사광",$img,1140,  0,    "-", 57, 49, 66, 0, 175, 235, "안전",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1141,  71,   "사마가",$img,1141,  0,    "-", 61, 85, 18, 0, 167, 222, "정복", "돌격");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1142,  20,   "사마랑",$img,1142,  0,    "-", 25, 32, 63, 0, 171, 217, "대의",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1143,  24,   "사마망",$img,1143,  0,    "-", 71, 61, 65, 0, 205, 271, "정복",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1144,  24,   "사마부",$img,1144,  0,    "-", 55, 31, 73, 0, 180, 272, "대의",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1145,  31,   "사마사",$img,1145,  0,    "-", 87, 64, 91, 0, 208, 255, "정복",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1146,  31,   "사마소",$img,1146,  0,    "-", 93, 63, 84, 0, 211, 265, "정복",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1147,  31,   "사마염",$img,1147,  0,    "-", 92, 78, 72, 0, 236, 290, "패권",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1148,  30,   "사마유",$img,1148,  0,    "-", 62, 45, 79, 0, 248, 283, "왕좌",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1149,  31,   "사마의",$img,1149,  0,    "-", 98, 67, 98, 0, 179, 251, "패권", "반계");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1150, 139,     "사섭",$img,1150,  0,    "-", 63, 61, 71, 0, 137, 226, "안전",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1151, 139,     "사일",$img,1151,  0,    "-", 59, 44, 68, 0, 153, 230, "유지",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1152, 132,     "사정",$img,1152,  0,    "-", 67, 71, 20, 0, 178, 221, "안전",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1153, 146,     "사지",$img,1153,  0,    "-", 61, 49, 70, 0, 163, 227, "안전",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1154, 144,     "사휘",$img,1154,  0,    "-", 67, 71, 61, 0, 165, 227, "유지",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1155,  35,     "서막",$img,1155,  0,    "-", 56, 41, 72, 0, 171, 249, "유지",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1156,  76,     "서서",$img,1156,  0,    "-", 90, 70, 96, 0, 178, 232, "의협", "귀병");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1157, 124,     "서성",$img,1157,  0,    "-", 83, 76, 83, 0, 177, 234, "대의",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1158, 142,     "서영",$img,1158,  0,    "-", 47, 63, 33, 0, 147, 191, "안전",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1159,  23,     "서질",$img,1159,  0,    "-", 55, 73, 34, 0, 207, 253, "재간",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1160,  23,     "서황",$img,1160,  0,    "-", 79, 89, 68, 0, 165, 228, "의협", "필살");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1161,  32,     "석포",$img,1161,  0,    "-", 71, 63, 59, 0, 214, 272, "정복",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1162, 131,     "설영",$img,1162,  0,    "-", 46, 23, 64, 0, 223, 282, "유지",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1163, 128,     "설종",$img,1163,  0,    "-", 27, 33, 67, 0, 187, 243, "유지",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1164,  69,     "성의",$img,1164,  0,    "-", 45, 64, 22, 0, 168, 211, "재간",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1165, 129,     "소비",$img,1165,  0,    "-", 67, 63, 49, 0, 172, 221, "대의",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1166,  76,     "손건",$img,1166,  0,    "-", 42, 33, 73, 0, 165, 215, "대의", "거상");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1167, 125,     "손견",$img,1167,  0,    "-", 96, 95, 76, 0, 156, 192, "왕좌", "무쌍");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1168, 126,     "손광",$img,1168,  0,    "-", 63, 54, 58, 0, 186, 207, "유지",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1169, 125,     "손권",$img,1169,  0,    "-", 90, 77, 83, 0, 182, 252, "할거", "수비");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1170, 126,     "손랑",$img,1170,  0,    "-", 27, 54, 28, 0, 187, 226, "안전",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1171, 126,     "손등",$img,1171,  0,    "-", 52, 39, 77, 0, 209, 241, "할거",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1172, 125,     "손량",$img,1172,  0,    "-", 24, 23, 79, 0, 243, 260, "할거",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1173,  20,     "손례",$img,1173,  0,    "-", 64, 64, 69, 0, 180, 250, "안전",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1174, 124,     "손소",$img,1174,  0,    "-", 76, 80, 68, 0, 188, 241, "정복",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1175, 130,     "손수",$img,1175,  0,    "-", 67, 57, 59, 0, 235, 299, "안전",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1176, 126,     "손유",$img,1176,  0,    "-", 77, 60, 67, 0, 177, 215, "정복",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1177, 122,     "손이",$img,1177,  0,    "-", 57, 62, 57, 0, 223, 272, "안전",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1178, 126,     "손정",$img,1178,  0,    "-", 59, 56, 62, 0, 160, 206, "유지",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1179, 115,     "손준",$img,1179,  0,    "-", 59, 69, 51, 0, 219, 256, "정복",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1180,   7,     "손중",$img,1180,  0,    "-", 53, 63, 24, 0, 154, 185, "안전",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1181, 125,     "손책",$img,1181,  0,    "-", 96, 96, 78, 0, 175, 200, "패권", "필살");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1182, 115,     "손침",$img,1182,  0,    "-", 49, 71, 40, 0, 231, 258, "패권",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1183, 114,     "손호",$img,1183,  0,    "-", 20, 78, 67, 0, 242, 284, "패권",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1184, 126,     "손화",$img,1184,  0,    "-", 35, 25, 71, 0, 224, 253, "왕좌",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1185, 127,     "손환",$img,1185,  0,    "-", 79, 65, 70, 0, 197, 228, "정복",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1186, 126,     "손휴",$img,1186,  0,    "-", 63, 43, 64, 0, 235, 264, "정복",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1187, 117,     "손흠",$img,1187,  0,    "-", 66, 63, 33, 0, 235, 280, "정복",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1188, 147,     "송헌",$img,1188,  0,    "-", 42, 63, 41, 0, 157, 200, "안전",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1189,  95,   "순우경",$img,1189,  0,    "-", 72, 67, 60, 0, 146, 200, "할거",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1190,  22,    "순욱1",$img,1190,  0,    "-", 54, 29, 97, 0, 163, 212, "왕좌", "집중");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1191,  22,     "순유",$img,1191,  0,    "-", 73, 41, 90, 0, 157, 214, "대의", "신중");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1192,  29,     "신비",$img,1192,  0,    "-", 47, 28, 74, 0, 171, 240, "재간",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1193,  37,     "신의",$img,1193,  0,    "-", 55, 61, 51, 0, 190, 252, "출세",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1194,  37,     "신탐",$img,1194,  0,    "-", 56, 58, 57, 0, 188, 254, "출세",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1195,  85,     "신평",$img,1195,  0,    "-", 68, 51, 75, 0, 165, 204, "왕좌",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1196, 102,     "심배",$img,1196,  0,    "-", 75, 66, 68, 0, 156, 204, "패권", "귀병");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1197, 126,     "심영",$img,1197,  0,    "-", 53, 72, 51, 0, 235, 280, "재간",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1198,  47, "아하소과",$img,1198,  0,    "-", 53, 75, 15, 0, 204, 253, "안전", "척사");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1199,  62,   "아회남",$img,1199,  0,    "-", 50, 74, 30, 0, 190, 225, "출세", "척사");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1200,  23,     "악진",$img,1200,  0,    "-", 73, 67, 56, 0, 159, 218, "대의", "돌격");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1201,  23,     "악침",$img,1201,  0,    "-", 45, 52, 33, 0, 196, 257, "대의",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1202,  63,     "악환",$img,1202,  0,    "-", 54, 82, 55, 0, 196, 251, "재간",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1203, 102,     "안량",$img,1203,  0,    "-", 73, 93, 36, 0, 160, 200, "출세", "위압");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1204,  14,     "양백",$img,1204,  0,    "-", 55, 54, 53, 0, 171, 214, "안전",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1205,  92,    "양봉1",$img,1205,  0,    "-", 57, 64, 36, 0, 153, 197, "유지",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1206,  91,    "양봉2",$img,1206,  0,    "-", 62, 78, 61, 0, 191, 252, "정복",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1207,  13,     "양송",$img,1207,  0,    "-", 15, 35, 34, 0, 167, 215, "안전",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1208,  43,     "양수",$img,1208,  0,    "-", 18, 31, 91, 0, 175, 219, "재간", "귀병");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1209,  61,     "양의",$img,1209,  0,    "-", 67, 56, 71, 0, 190, 235, "유지",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1210, 141,    "양조1",$img,1210,  0,    "-", 68, 54, 60, 0, 202, 256, "출세",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1211,   9,    "양추1",$img,1211,  0,    "-", 51, 67, 16, 0, 159, 199, "정복",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1212,  31,     "양호",$img,1212,  0,    "-", 91, 69, 80, 0, 221, 278, "패권",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1213,  53,     "양회",$img,1213,  0,    "-", 60, 67, 40, 0, 167, 212, "출세",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1214, 141,     "양흥",$img,1214,  0,    "-", 52, 68, 17, 0, 169, 211, "안전",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1215,   6,   "어부라",$img,1215,  0,    "-", 78, 80, 61, 0, 150, 195, "정복",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1216,  64,     "엄강",$img,1216,  0,    "-", 57, 65, 44, 0, 163, 192, "재간",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1217,  10,   "엄백호",$img,1217,  0,    "-", 48, 68, 30, 0, 150, 197, "할거",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1218,  10,     "엄여",$img,1218,  0,    "-", 35, 66, 24, 0, 153, 197, "할거",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1219,  69,     "엄안",$img,1219,  0,    "-", 72, 84, 67, 0, 151, 222, "왕좌",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1220,   7,     "엄정",$img,1220,  0,    "-", 31, 68, 49, 0, 151, 189, "출세",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1221, 121,     "엄준",$img,1221,  0,    "-", 44, 24, 71, 0, 169, 246, "재간",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1222,  71,     "여개",$img,1222,  0,    "-", 51, 42, 67, 0, 194, 227, "유지",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1223,  29,     "여건",$img,1223,  0,    "-", 44, 68, 29, 0, 173, 238, "출세",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1224, 107,     "여광",$img,1224,  0,    "-", 60, 67, 27, 0, 162, 207, "유지",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1225, 124,     "여몽",$img,1225,  0,    "-", 92, 78, 93, 0, 178, 219, "패권", "궁병");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1226, 123,     "여범",$img,1226,  0,    "-", 43, 34, 71, 0, 169, 228, "할거",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1227, 107,     "여상",$img,1227,  0,    "-", 62, 68, 26, 0, 164, 207, "유지",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1228, 105,   "여위황",$img,1228,  0,    "-", 42, 62, 38, 0, 159, 200, "정복",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1229, 145,     "여포",$img,1229,  0,    "-", 74,100, 29, 0, 156, 198, "패권", "돌격");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1230,  50,     "염우",$img,1230,  0,    "-", 58, 51, 18, 0, 209, 264, "출세",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1231,  40,     "염유",$img,1231,  0,    "-", 59, 75, 51, 0, 168, 227, "출세",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1232,  18,     "염포",$img,1232,  0,    "-", 33, 35, 77, 0, 163, 231, "유지",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1233,  40,     "예형",$img,1233,  0,    "-", 77, 31, 95, 0, 173, 209, "은둔", "통찰");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1234, 133,     "오강",$img,1234,  0,    "-", 47, 37, 61, 0, 216, 275, "안전",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1235,  59,     "오란",$img,1235,  0,    "-", 67, 75, 42, 0, 170, 218, "재간",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1236,  72,     "오반",$img,1236,  0,    "-", 70, 66, 45, 0, 171, 234, "재간",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1237, 128,     "오언",$img,1237,  0,    "-", 71, 60, 52, 0, 235, 297, "재간",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1238, 126,     "오연",$img,1238,  0,    "-", 36, 70, 31, 0, 234, 280, "정복",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1239,  69,     "오의",$img,1239,  0,    "-", 75, 72, 74, 0, 165, 237, "유지",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1240,  19,     "오질",$img,1240,  0,    "-", 43, 37, 69, 0, 177, 230, "출세",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1241,  51,   "올돌골",$img,1241,  0,    "-", 77, 92, 15, 0, 186, 225, "출세", "척사");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1242,  52,     "옹개",$img,1242,  0,    "-", 58, 67, 51, 0, 188, 225, "안전",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1243,  20,     "왕경",$img,1243,  0,    "-", 55, 47, 65, 0, 206, 260, "재간",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1244,  97,     "왕광",$img,1244,  0,    "-", 72, 67, 54, 0, 150, 190, "유지",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1245,  30,    "왕기1",$img,1245,  0,    "-", 76, 62, 70, 0, 190, 261, "유지",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1246,  34,     "왕랑",$img,1246,  0,    "-", 49, 29, 51, 0, 162, 228, "할거",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1247,  57,     "왕루",$img,1247,  0,    "-", 40, 28, 76, 0, 173, 211, "유지",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1248,  76,     "왕보",$img,1248,  0,    "-", 47, 34, 75, 0, 171, 219, "대의",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1249,  86,     "왕수",$img,1249,  0,    "-", 34, 34, 67, 0, 168, 218, "재간",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1250,  26,     "왕쌍",$img,1250,  0,    "-", 58, 89, 15, 0, 195, 228, "정복", "보병");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1251,  46,     "왕위",$img,1251,  0,    "-", 59, 60, 68, 0, 163, 208, "안전",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1252,  92,     "왕윤",$img,1252,  0,    "-", 16, 18, 77, 0, 137, 192, "왕좌",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1253,  40,     "왕융",$img,1253,  0,    "-", 62, 41, 77, 0, 234, 305, "재간",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1254,  32,     "왕준",$img,1254,  0,    "-", 81, 83, 76, 0, 206, 285, "대의",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1255,  30,     "왕찬",$img,1255,  0,    "-", 28, 28, 78, 0, 177, 217, "안전",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1256,  33,     "왕창",$img,1256,  0,    "-", 74, 57, 52, 0, 188, 259, "정복",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1257,  69,     "왕평",$img,1257,  0,    "-", 77, 76, 71, 0, 192, 248, "재간",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1258,  71,     "왕항",$img,1258,  0,    "-", 51, 43, 60, 0, 184, 254, "대의",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1259,  33,     "왕혼",$img,1259,  0,    "-", 69, 32, 59, 0, 223, 297, "정복",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1260,  71,     "요립",$img,1260,  0,    "-", 65, 41, 84, 0, 181, 250, "출세",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1261,  74,     "요화",$img,1261,  0,    "-", 67, 58, 60, 0, 170, 264, "의협",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1262,  22,    "우금1",$img,1262,  0,    "-", 80, 74, 71, 0, 159, 221, "정복",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1263,  27,    "우금2",$img,1263,  0,    "-", 63, 77, 37, 0, 173, 226, "대의",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1264, 122,     "우번",$img,1264,  0,    "-", 23, 42, 73, 0, 164, 233, "유지",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1265, 126,     "우전",$img,1265,  0,    "-", 63, 55, 41, 0, 204, 258, "안전",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1266,  86,     "원담",$img,1266,  0,    "-", 67, 59, 55, 0, 173, 205, "왕좌",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1267, 101,     "원상",$img,1267,  0,    "-", 54, 72, 68, 0, 179, 207, "패권",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1268, 101,     "원소",$img,1268,  0,    "-", 85, 67, 76, 0, 154, 202, "패권", "위압");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1269, 140,     "원술",$img,1269,  0,    "-", 77, 59, 71, 0, 155, 199, "패권", "축성");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1270, 140,     "원윤",$img,1270,  0,    "-", 41, 34, 60, 0, 163, 199, "패권",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1271, 101,     "원희",$img,1271,  0,    "-", 69, 57, 72, 0, 176, 207, "할거",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1272, 131,     "위소",$img,1272,  0,    "-", 39, 24, 82, 0, 204, 273, "재간",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1273, 147,     "위속",$img,1273,  0,    "-", 57, 59, 41, 0, 156, 200, "안전",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1274,  81,     "위연",$img,1274,  0,    "-", 78, 94, 62, 0, 175, 234, "패권", "보병");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1275,  96,     "위유",$img,1275,  0,    "-", 53, 69, 71, 0, 151, 193, "재간",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1276,  76,     "유기",$img,1276,  0,    "-", 57, 19, 73, 0, 174, 209, "왕좌",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1277,  36,     "유대",$img,1277,  0,    "-", 61, 57, 62, 0, 147, 202, "재간",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1278, 134,     "유도",$img,1278,  0,    "-", 35, 33, 68, 0, 168, 214, "재간",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1279,  46,     "유벽",$img,1279,  0,    "-", 63, 71, 23, 0, 168, 210, "출세",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1280,  75,     "유봉",$img,1280,  0,    "-", 60, 65, 62, 0, 188, 220, "출세",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1281,  75,     "유비",$img,1281,  0,    "-", 85, 75, 70, 0, 161, 223, "왕좌", "인덕");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1282,  75,    "유선1",$img,1282,  0,    "-", 24, 17, 21, 0, 207, 271, "대의",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1283,  55,     "유순",$img,1283,  0,    "-", 67, 61, 54, 0, 184, 239, "출세",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1284,  75,     "유심",$img,1284,  0,    "-", 63, 46, 70, 0, 238, 263, "왕좌",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1285, 129,     "유약",$img,1285,  0,    "-", 67, 63, 61, 0, 206, 260, "유지",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1286,  55,     "유언",$img,1286,  0,    "-", 60, 40, 74, 0, 132, 194, "할거",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1287,  27,     "유엽",$img,1287,  0,    "-", 40, 29, 79, 0, 176, 235, "유지",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1288,  11,     "유요",$img,1288,  0,    "-", 23, 22, 48, 0, 156, 195, "안전", "발명");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1289,  96,     "유우",$img,1289,  0,    "-", 68, 34, 72, 0, 145, 193, "왕좌",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1290,  55,     "유장",$img,1290,  0,    "-", 38, 31, 63, 0, 162, 219, "할거", "수비");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1291,  45,     "유종",$img,1291,  0,    "-", 22, 26, 61, 0, 191, 208, "할거",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1292,  56,     "유파",$img,1292,  0,    "-", 47, 32, 70, 0, 186, 222, "대의",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1293,  45,    "유표1",$img,1293,  0,    "-", 71, 57, 71, 0, 142, 208, "할거",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1294,  27,    "유표2",$img,1294,  0,    "-", 76, 55, 71, 0, 173, 229, "안전",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1295, 134,     "유현",$img,1295,  0,    "-", 32, 56, 55, 0, 188, 252, "안전",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1296, 122,     "육개",$img,1296,  0,    "-", 66, 30, 72, 0, 198, 269, "재간",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1297, 122,     "육손",$img,1297,  0,    "-", 98, 68, 98, 0, 183, 245, "왕좌", "귀병");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1298, 121,     "육적",$img,1298,  0,    "-", 44, 29, 73, 0, 187, 219, "안전",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1299, 122,     "육항",$img,1299,  0,    "-", 95, 69, 94, 0, 226, 274, "왕좌",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1300,  38,   "윤대목",$img,1300,  0,    "-", 62, 49, 69, 0, 211, 270, "재간",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1301,  80,     "윤묵",$img,1301,  0,    "-", 19, 28, 73, 0, 183, 239, "대의",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1302,  72,     "윤상",$img,1302,  0,    "-", 30, 32, 42, 0, 194, 260, "대의",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1303, 136,     "윤직",$img,1303,  0,    "-", 44, 58, 63, 0, 197, 237, "재간",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1304,   2,     "이각",$img,1304,  0,    "-", 56, 77, 43, 0, 148, 198, "패권",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1305, 146,     "이숙",$img,1305,  0,    "-", 27, 45, 67, 0, 156, 192, "재간",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1306,  71,     "이엄",$img,1306,  0,    "-", 80, 84, 81, 0, 172, 234, "패권",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1307,   2,     "이유",$img,1307,  0,    "-", 64, 22, 90, 0, 150, 192, "패권", "귀모");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1308, 132,     "이이",$img,1308,  0,    "-", 55, 75, 20, 0, 187, 222, "정복",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1309,  77,     "이적",$img,1309,  0,    "-", 55, 27, 77, 0, 162, 226, "할거",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1310,  22,     "이전",$img,1310,  0,    "-", 75, 68, 82, 0, 174, 216, "왕좌",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1311,  71,    "이풍1",$img,1311,  0,    "-", 59, 56, 62, 0, 206, 260, "재간",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1312,  66,     "이회",$img,1312,  0,    "-", 67, 50, 79, 0, 175, 231, "유지",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1313, 114,     "잠혼",$img,1313,  0,    "-", 15, 16, 44, 0, 239, 280, "할거",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1314,   7,     "장각",$img,1314,  0,    "-", 93, 25, 93, 0, 140, 185, "패권", "환술");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1315,  34,     "장간",$img,1315,  0,    "-", 19, 20, 70, 0, 175, 239, "재간",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1316,   7,     "장개",$img,1316,  0,    "-", 48, 69, 19, 0, 155, 202, "안전",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1317, 122,     "장굉",$img,1317,  0,    "-", 25, 21, 85, 0, 153, 212, "왕좌",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1318,   7,     "장량",$img,1318,  0,    "-", 68, 81, 68, 0, 153, 185, "정복", "환술");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1319,  15,     "장로",$img,1319,  0,    "-", 76, 44, 80, 0, 163, 237, "유지", "축성");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1320,  23,     "장료",$img,1320,  0,    "-", 89, 93, 83, 0, 169, 222, "의협", "견고");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1321,   7,     "장보",$img,1321,  0,    "-", 78, 81, 76, 0, 148, 185, "패권", "환술");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1322,  76,     "장비",$img,1322,  0,    "-", 79, 99, 48, 0, 167, 221, "의협", "무쌍");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1323,  62,     "장서",$img,1323,  0,    "-", 44, 48, 35, 0, 225, 290, "출세",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1324, 120,    "장소1",$img,1324,  0,    "-", 42, 24, 91, 0, 156, 236, "안전",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1325,  78,    "장소2",$img,1325,  0,    "-", 51, 44, 71, 0, 202, 264, "재간",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1326,  72,     "장송",$img,1326,  0,    "-", 49, 28, 93, 0, 170, 212, "할거", "통찰");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1327, 148,     "장수",$img,1327,  0,    "-", 71, 72, 69, 0, 154, 207, "할거",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1328, 145,    "장양1",$img,1328,  0,    "-", 62, 66, 65, 0, 150, 199, "출세",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1329,   8,     "장연",$img,1329,  0,    "-", 78, 66, 47, 0, 153, 210, "안전",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1330,  11,     "장영",$img,1330,  0,    "-", 55, 65, 40, 0, 154, 195, "재간",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1331, 118,     "장온",$img,1331,  0,    "-", 21, 30, 69, 0, 193, 231, "할거",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1332,  78,     "장완",$img,1332,  0,    "-", 70, 55, 86, 0, 188, 246, "할거", "상재");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1333,  15,     "장위",$img,1333,  0,    "-", 65, 70, 29, 0, 172, 215, "대의",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1334,  36,     "장윤",$img,1334,  0,    "-", 67, 59, 60, 0, 163, 208, "정복",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1335, 104,   "장의거",$img,1335,  0,    "-", 68, 59, 34, 0, 159, 205, "대의",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1336,  68,     "장익",$img,1336,  0,    "-", 75, 68, 63, 0, 188, 264, "유지",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1337,  56,     "장임",$img,1337,  0,    "-", 83, 82, 74, 0, 169, 214, "대의", "견고");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1338, 148,    "장제1",$img,1338,  0,    "-", 70, 65, 59, 0, 144, 196, "할거",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1339,  32,    "장제2",$img,1339,  0,    "-", 30, 33, 84, 0, 188, 249, "유지",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1340, 126,    "장제3",$img,1340,  0,    "-", 74, 49, 61, 0, 236, 280, "할거",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1341,  17,     "장패",$img,1341,  0,    "-", 44, 78, 43, 0, 165, 231, "할거",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1342,  76,    "장포1",$img,1342,  0,    "-", 69, 85, 49, 0, 198, 229, "재간", "징병");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1343, 113,    "장포2",$img,1343,  0,    "-", 63, 66, 51, 0, 225, 264, "유지",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1344,  27,     "장합",$img,1344,  0,    "-", 83, 91, 63, 0, 167, 231, "출세", "궁병");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1345,  23,     "장호",$img,1345,  0,    "-", 56, 62, 54, 0, 195, 240, "정복",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1346,  31,     "장화",$img,1346,  0,    "-", 49, 24, 86, 0, 232, 300, "유지",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1347,  48,     "장횡",$img,1347,  0,    "-", 53, 67, 25, 0, 178, 211, "재간",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1348, 141,     "장훈",$img,1348,  0,    "-", 67, 61, 60, 0, 156, 206, "안전",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1349, 120,     "장휴",$img,1349,  0,    "-", 42, 35, 70, 0, 204, 244, "재간",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1350, 126,     "장흠",$img,1350,  0,    "-", 64, 66, 67, 0, 168, 219, "대의", "저격");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1351,  96,     "저수",$img,1351,  0,    "-", 82, 54, 88, 0, 156, 201, "할거", "반계");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1352, 130,     "전단",$img,1352,  0,    "-", 64, 73, 61, 0, 204, 261, "출세",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1353,  42,     "전속",$img,1353,  0,    "-", 66, 57, 49, 0, 218, 272, "안전",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1354,  26,    "전위1",$img,1354,  0,    "-", 61, 96, 34, 0, 160, 197, "의협", "필살");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1355, 130,    "전위2",$img,1355,  0,    "-", 74, 69, 62, 0, 230, 274, "출세",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1356, 128,     "전종",$img,1356,  0,    "-", 79, 77, 74, 0, 183, 249, "패권",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1357,  42,     "전주",$img,1357,  0,    "-", 69, 67, 51, 0, 169, 214, "의협",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1358,  96,     "전풍",$img,1358,  0,    "-", 81, 41, 96, 0, 162, 200, "왕좌",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1359,  24,     "정무",$img,1359,  0,    "-", 54, 38, 74, 0, 201, 265, "출세",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1360, 119,     "정병",$img,1360,  0,    "-", 22, 25, 67, 0, 172, 226, "유지",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1361, 126,     "정보",$img,1361,  0,    "-", 81, 64, 76, 0, 151, 216, "패권",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1362, 123,    "정봉1",$img,1362,  0,    "-", 70, 77, 64, 0, 190, 271, "패권",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1363,  24,     "정욱",$img,1363,  0,    "-", 80, 39, 90, 0, 141, 220, "패권", "신중");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1364,  88,     "정원",$img,1364,  0,    "-", 64, 77, 58, 0, 137, 190, "왕좌", "기병");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1365,   7,   "정원지",$img,1365,  0,    "-", 41, 74, 38, 0, 145, 185, "출세",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1366,  69,     "정은",$img,1366,  0,    "-", 53, 62, 26, 0, 169, 211, "재간",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1367, 121,   "제갈각",$img,1367,  0,    "-", 61, 53, 92, 0, 203, 253, "왕좌",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1368,  76,   "제갈균",$img,1368,  0,    "-", 59, 45, 74, 0, 185, 252, "안전", "상재");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1369, 121,   "제갈근",$img,1369,  0,    "-", 60, 42, 88, 0, 174, 241, "왕좌", "경작");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1370,  76,   "제갈량",$img,1370,  0,    "-", 97, 55,100, 0, 181, 234, "왕좌", "집중");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1371,  76,   "제갈상",$img,1371,  0,    "-", 52, 75, 71, 0, 246, 263, "대의",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1372, 135,   "제갈정",$img,1372,  0,    "-", 56, 57, 54, 0, 241, 300, "안전",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1373,  76,   "제갈첨",$img,1373,  0,    "-", 73, 52, 76, 0, 227, 263, "대의",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1374, 135,   "제갈탄",$img,1374,  0,    "-", 79, 79, 73, 0, 206, 258, "패권",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1375,  76,     "조광",$img,1375,  0,    "-", 65, 67, 54, 0, 210, 263, "재간",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1376,  74,     "조루",$img,1376,  0,    "-", 49, 37, 60, 0, 183, 219, "대의",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1377,  24,     "조모",$img,1377,  0,    "-", 53, 32, 30, 0, 241, 260, "할거",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1378, 127,     "조무",$img,1378,  0,    "-", 71, 68, 71, 0, 155, 191, "의협",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1379,  26,     "조방",$img,1379,  0,    "-", 50, 20, 31, 0, 232, 274, "정복",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1380,  83,     "조범",$img,1380,  0,    "-", 58, 40, 63, 0, 168, 218, "유지",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1381,  26,     "조비",$img,1381,  0,    "-", 72, 69, 75, 0, 187, 226, "패권", "징병");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1382,  19,     "조상",$img,1382,  0,    "-", 68, 62, 31, 0, 207, 249, "패권",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1383, 147,     "조성",$img,1383,  0,    "-", 44, 69, 51, 0, 163, 198, "안전",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1384,  26,     "조순",$img,1384,  0,    "-", 66, 57, 72, 0, 170, 210, "정복",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1385,  25,     "조식",$img,1385,  0,    "-", 19, 19, 90, 0, 192, 232, "왕좌", "귀모");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1386,  25,     "조앙",$img,1386,  0,    "-", 44, 65, 62, 0, 175, 197, "왕좌",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1387,  25,     "조예",$img,1387,  0,    "-", 57, 55, 82, 0, 205, 239, "정복",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1388,  26,     "조우",$img,1388,  0,    "-", 67, 55, 67, 0, 199, 260, "대의",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1389,  76,     "조운",$img,1389,  0,    "-", 95, 98, 87, 0, 168, 229, "왕좌", "무쌍");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1390,  25,     "조웅",$img,1390,  0,    "-", 59, 27, 44, 0, 194, 220, "안전",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1391,  26,     "조인",$img,1391,  0,    "-", 74, 79, 62, 0, 168, 223, "패권", "보병");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1392,  25,     "조조",$img,1392,  0,    "-",100, 80, 95, 0, 155, 220, "패권", "반계");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1393,  26,     "조진",$img,1393,  0,    "-", 82, 67, 65, 0, 185, 231, "정복",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1394,  25,     "조창",$img,1394,  0,    "-", 75, 88, 37, 0, 190, 223, "정복", "돌격");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1395,  76,     "조통",$img,1395,  0,    "-", 65, 64, 55, 0, 209, 260, "재간",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1396,  84,     "조표",$img,1396,  0,    "-", 34, 70, 16, 0, 151, 196, "안전",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1397,   7,    "조홍1",$img,1397,  0,    "-", 52, 66, 42, 0, 156, 185, "유지",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1398,  24,    "조홍2",$img,1398,  0,    "-", 72, 69, 44, 0, 169, 232, "패권",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1399,  26,     "조환",$img,1399,  0,    "-", 34, 24, 42, 0, 246, 302, "정복",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1400,  19,     "조훈",$img,1400,  0,    "-", 67, 63, 30, 0, 212, 249, "출세",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1401,  26,     "조휴",$img,1401,  0,    "-", 75, 71, 70, 0, 174, 228, "패권",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1402,  19,     "조희",$img,1402,  0,    "-", 64, 57, 71, 0, 210, 249, "대의",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1403,  22,     "종요",$img,1403,  0,    "-", 16, 20, 74, 0, 151, 230, "유지",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1404,  20,     "종회",$img,1404,  0,    "-", 84, 58, 93, 0, 225, 264, "패권",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1405, 128,     "주거",$img,1405,  0,    "-", 73, 71, 72, 0, 190, 246, "유지",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1406, 118,     "주방",$img,1406,  0,    "-", 56, 36, 76, 0, 200, 240, "할거",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1407, 126,     "주연",$img,1407,  0,    "-", 73, 72, 51, 0, 182, 249, "할거",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1408, 126,     "주유",$img,1408,  0,    "-", 97, 73, 97, 0, 175, 210, "패권", "신산");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1409, 128,     "주이",$img,1409,  0,    "-", 61, 55, 61, 0, 201, 257, "안전",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1410,  88,     "주준",$img,1410,  0,    "-", 82, 75, 65, 0, 149, 195, "왕좌",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1411,  32,     "주지",$img,1411,  0,    "-", 52, 77, 47, 0, 233, 295, "안전",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1412,  76,     "주창",$img,1412,  0,    "-", 42, 79, 30, 0, 164, 219, "의협", "궁병");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1413, 126,     "주치",$img,1413,  0,    "-", 58, 55, 56, 0, 156, 224, "안전",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1414,  41,    "주태1",$img,1414,  0,    "-", 62, 55, 61, 0, 207, 261, "안전",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1415, 126,    "주태2",$img,1415,  0,    "-", 74, 88, 60, 0, 171, 225, "정복", "필살");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1416, 128,     "주환",$img,1416,  0,    "-", 84, 86, 74, 0, 177, 238, "정복",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1417,  29,     "진건",$img,1417,  0,    "-", 62, 70, 62, 0, 214, 292, "정복",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1418,  29,     "진교",$img,1418,  0,    "-", 21, 25, 67, 0, 175, 237, "유지",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1419,  28,     "진군",$img,1419,  0,    "-", 60, 38, 87, 0, 167, 235, "대의",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1420, 143,     "진궁",$img,1420,  0,    "-", 77, 51, 90, 0, 154, 198, "할거", "신중");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1421,  79,     "진규",$img,1421,  0,    "-", 22, 19, 71, 0, 132, 206, "할거",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1422,  72,     "진도",$img,1422,  0,    "-", 71, 85, 70, 0, 171, 237, "대의",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1423,  79,     "진등",$img,1423,  0,    "-", 64, 62, 71, 0, 169, 207, "할거",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1424,  37,     "진림",$img,1424,  0,    "-", 50, 28, 82, 0, 160, 217, "재간",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1425, 124,     "진무",$img,1425,  0,    "-", 62, 74, 59, 0, 176, 215, "재간",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1426,  66,     "진복",$img,1426,  0,    "-", 36, 27, 76, 0, 160, 226, "재간",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1427,  50,     "진수",$img,1427,  0,    "-", 25, 29, 83, 0, 233, 297, "대의",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1428,  81,     "진식",$img,1428,  0,    "-", 47, 68, 52, 0, 191, 230, "출세",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1429,  79,     "진진",$img,1429,  0,    "-", 58, 38, 64, 0, 170, 235, "재간",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1430,  28,     "진태",$img,1430,  0,    "-", 79, 76, 70, 0, 210, 260, "대의",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1431,  11,     "진횡",$img,1431,  0,    "-", 38, 58, 47, 0, 161, 195, "출세",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1432,  21,     "차주",$img,1432,  0,    "-", 55, 66, 62, 0, 164, 199, "안전",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1433,  11,     "착융",$img,1433,  0,    "-", 62, 59, 21, 0, 161, 194, "출세",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1434,  36,     "채모",$img,1434,  0,    "-", 79, 69, 68, 0, 155, 208, "정복", "궁병");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1435,  36,     "채중",$img,1435,  0,    "-", 58, 43, 55, 0, 168, 208, "출세",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1436,  36,     "채화",$img,1436,  0,    "-", 56, 47, 49, 0, 166, 208, "안전",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1437,  66,     "초주",$img,1437,  0,    "-", 22, 26, 81, 0, 201, 270, "대의",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1438,  20,     "최염",$img,1438,  0,    "-", 43, 54, 67, 0, 162, 216, "대의",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1439,  60,     "축융",$img,1439,  0,    "-", 59, 87, 25, 0, 193, 246, "정복", "척사");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1440,  60, "타사대왕",$img,1440,  0,    "-", 61, 72, 67, 0, 186, 225, "출세", "척사");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1441, 124,   "태사자",$img,1441,  0,    "-", 71, 97, 65, 0, 166, 209, "대의", "무쌍");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1442, 124,   "태사향",$img,1442,  0,    "-", 51, 69, 50, 0, 189, 246, "재간",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1443,  12,     "포륭",$img,1443,  0,    "-", 53, 74, 20, 0, 174, 208, "안전",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1444,  74,     "풍습",$img,1444,  0,    "-", 36, 64, 44, 0, 182, 222, "재간",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1445,   7,     "하의",$img,1445,  0,    "-", 49, 68, 25, 0, 161, 195, "출세",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1446, 121,     "하제",$img,1446,  0,    "-", 74, 73, 64, 0, 171, 227, "재간",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1447,  90,     "하진",$img,1447,  0,    "-", 49, 69, 37, 0, 135, 189, "왕좌",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1448,  24,   "하후덕",$img,1448,  0,    "-", 67, 64, 39, 0, 178, 218, "출세",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1449,  26,   "하후돈",$img,1449,  0,    "-", 88, 92, 71, 0, 156, 220, "의협", "돌격");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1450,  19,   "하후무",$img,1450,  0,    "-", 38, 33, 37, 0, 201, 259, "안전",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1451,  24,   "하후상",$img,1451,  0,    "-", 67, 62, 71, 0, 181, 225, "출세",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1452,  26,   "하후연",$img,1452,  0,    "-", 79, 90, 58, 0, 162, 219, "패권", "궁병");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1453,  26,   "하후위",$img,1453,  0,    "-", 73, 76, 71, 0, 204, 254, "정복",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1454,  26,   "하후은",$img,1454,  0,    "-", 49, 51, 39, 0, 180, 208, "안전",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1455,  23,   "하후패",$img,1455,  0,    "-", 78, 88, 69, 0, 202, 262, "대의",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1456,  20,   "하후현",$img,1456,  0,    "-", 57, 23, 75, 0, 208, 254, "유지",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1457,  26,   "하후혜",$img,1457,  0,    "-", 76, 66, 78, 0, 206, 242, "정복",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1458,  26,   "하후화",$img,1458,  0,    "-", 77, 61, 80, 0, 207, 265, "정복",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1459,  22,     "학소",$img,1459,  0,    "-", 89, 81, 86, 0, 185, 229, "대의", "견고");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1460, 126,     "한당",$img,1460,  0,    "-", 68, 67, 64, 0, 156, 225, "할거",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1461,  93,     "한복",$img,1461,  0,    "-", 66, 59, 42, 0, 149, 191, "안전",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1462,   8,     "한섬",$img,1462,  0,    "-", 39, 62, 35, 0, 159, 196, "출세",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1463,  48,     "한수",$img,1463,  0,    "-", 66, 76, 77, 0, 142, 215, "대의", "기병");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1464,  40,     "한숭",$img,1464,  0,    "-", 21, 25, 70, 0, 154, 210, "재간",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1465,   7,     "한충",$img,1465,  0,    "-", 41, 66, 29, 0, 151, 185, "안전",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1466,   9,     "한현",$img,1466,  0,    "-", 43, 61, 20, 0, 163, 208, "유지",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1467,  27,     "한호",$img,1467,  0,    "-", 60, 73, 45, 0, 164, 218, "유지",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1468,  79,     "향랑",$img,1468,  0,    "-", 51, 21, 77, 0, 167, 247, "대의",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1469,  79,     "향총",$img,1469,  0,    "-", 76, 42, 73, 0, 195, 240, "왕좌",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1470, 139,     "허공",$img,1470,  0,    "-", 65, 63, 59, 0, 155, 200, "유지",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1471,  21,     "허유",$img,1471,  0,    "-", 47, 47, 57, 0, 155, 204, "재간",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1472,  23,     "허의",$img,1472,  0,    "-", 31, 74, 47, 0, 213, 263, "출세",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1473,  26,     "허저",$img,1473,  0,    "-", 57, 98, 27, 0, 169, 226, "정복", "무쌍");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1474,  87,     "허정",$img,1474,  0,    "-", 18, 29, 74, 0, 152, 222, "재간",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1475,  12,   "형도영",$img,1475,  0,    "-", 49, 78, 23, 0, 174, 208, "출세",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1476, 148,   "호거아",$img,1476,  0,    "-", 35, 76, 61, 0, 164, 206, "안전",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1477,  76,     "호반",$img,1477,  0,    "-", 61, 58, 46, 0, 179, 233, "재간",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1478,  20,     "호분",$img,1478,  0,    "-", 71, 60, 61, 0, 222, 288, "할거",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1479,  27,   "호주천",$img,1479,  0,    "-", 77, 75, 65, 0, 169, 230, "정복",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1480,  20,     "호준",$img,1480,  0,    "-", 67, 60, 46, 0, 200, 256, "재간",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1481,   2,     "화웅",$img,1481,  0,    "-", 68, 88, 24, 0, 155, 191, "출세", "돌격");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1482, 131,     "화핵",$img,1482,  0,    "-", 37, 27, 75, 0, 217, 278, "안전",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1483,  10,     "화흠",$img,1483,  0,    "-", 18, 43, 75, 0, 157, 231, "출세",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1484,  21,     "환범",$img,1484,  0,    "-", 20, 25, 81, 0, 199, 249, "유지",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1485, 127,     "황개",$img,1485,  0,    "-", 78, 85, 69, 0, 154, 218, "왕좌", "징병");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1486,  56,     "황권",$img,1486,  0,    "-", 76, 46, 77, 0, 167, 240, "대의",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1487,  41,     "황란",$img,1487,  0,    "-", 29, 70, 25, 0, 200, 264, "재간",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1488,  88,   "황보숭",$img,1488,  0,    "-", 83, 63, 73, 0, 132, 195, "왕좌",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1489,  72,     "황충",$img,1489,  0,    "-", 84, 94, 67, 0, 148, 222, "왕좌", "궁병");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1490,  50,     "황호",$img,1490,  0,    "-", 15, 17, 48, 0, 226, 263, "안전",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1491, 147,     "후성",$img,1491,  0,    "-", 56, 62, 33, 0, 158, 199, "정복",    "-");

    if($admin['extend'] > 0) {
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1492, 123,     "가화",$img,1492,  0,    "-", 50, 66, 40, 0, 176, 224,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1493, 999,     "건석",$img,1493,  0,    "-", 21, 12, 61, 0, 155, 189,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1494, 999,     "견씨",$img,1494,  0,    "-", 35, 24, 58, 0, 182, 221,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1495,  40,     "견홍",$img,1495,  0,    "-", 76, 72, 66, 0, 224, 272,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1496, 120,     "고담",$img,1496,  0,    "-", 33, 21, 69, 0, 203, 244,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1497, 101,     "고유",$img,1497,  0,    "-", 56, 44, 73, 0, 174, 263,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1498, 132,     "곽마",$img,1498,  0,    "-", 68, 71, 49, 0, 239, 280,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1499,  39,   "관구수",$img,1499,  0,    "-", 58, 63, 35, 0, 206, 265,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1500,  39,   "관구전",$img,1500,  0,    "-", 63, 58, 68, 0, 224, 255,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1501, 999,     "관로",$img,1501,  0,    "-", 62, 21, 75, 0, 191, 256,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1502,  65,     "관정",$img,1502,  0,    "-", 35, 50, 73, 0, 158, 199,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1503, 137,     "교수",$img,1503,  0,    "-", 67, 69, 39, 0, 143, 195,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1504,  33,     "구건",$img,1504,  0,    "-", 43, 56, 69, 0, 239, 272,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1505,  41,     "구본",$img,1505,  0,    "-", 52, 41, 70, 0, 232, 269,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1506,  12,     "구성",$img,1506,  0,    "-", 56, 71, 31, 0, 157, 187,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1507,  21,     "국연",$img,1507,  0,    "-", 52, 21, 71, 0, 160, 219,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1508,  99,     "국의",$img,1508,  0,    "-", 83, 79, 50, 0, 146, 191,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1509,  34,     "금의",$img,1509,  0,    "-", 18, 40, 63, 0, 177, 218,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1510,  76,     "나헌",$img,1510,  0,    "-", 86, 67, 75, 0, 218, 270,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1511, 999,     "남두",$img,1511,  0,    "-", 35, 25, 54, 0, 130, 200,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1512,  54,     "냉포",$img,1512,  0,    "-", 70, 82, 69, 0, 176, 214,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1513, 124,    "노숙2",$img,1513,  0,    "-", 70, 55, 76, 0, 208, 274,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1514,  42,     "누규",$img,1514,  0,    "-", 54, 19, 88, 0, 143, 212,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1515,   6,     "누반",$img,1515,  0,    "-", 65, 76, 39, 0, 178, 207,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1516, 130,     "누현",$img,1516,  0,    "-", 23, 20, 68, 0, 223, 275,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1517,  40,     "당균",$img,1517,  0,    "-", 33, 19, 81, 0, 229, 264,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1518,  32,     "당빈",$img,1518,  0,    "-", 70, 74, 62, 0, 235, 294,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1519, 999,     "대교",$img,1519,  0,    "-", 42, 10, 54, 0, 177, 235,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1520,  28,     "대릉",$img,1520,  0,    "-", 64, 75, 45, 0, 199, 258,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1521, 129,     "동조",$img,1521,  0,    "-", 16, 15, 51, 0, 221, 281,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1522, 114,     "등수",$img,1522,  0,    "-", 35, 20, 44, 0, 228, 288,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1523,  50,     "마막",$img,1523,  0,    "-", 22, 17,  5, 0, 221, 265,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1524, 116,     "만욱",$img,1524,  0,    "-", 20, 18, 66, 0, 240, 272,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1525, 120,     "맹종",$img,1525,  0,    "-", 48, 48, 67, 0, 216, 271,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1526,  38,     "문호",$img,1526,  0,    "-", 65, 74, 45, 0, 227, 279,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1527, 999,     "미씨",$img,1527,  0,    "-", 59, 15, 68, 0, 176, 208,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1528,  39,     "반림",$img,1528,  0,    "-", 66, 79,  8, 0, 168, 225,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1529, 129,     "반장",$img,1529,  0,    "-", 77, 78, 69, 0, 177, 222,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1530,  94,     "방열",$img,1530,  0,    "-", 58, 82, 28, 0, 153, 190,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1531,  55,     "방희",$img,1531,  0,    "-", 59, 38, 69, 0, 153, 218,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1532,  30,     "배수",$img,1532,  0,    "-", 10, 11, 77, 0, 223, 271,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1533,   8,     "번능",$img,1533,  0,    "-", 70, 61, 47, 0, 158, 194,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1534, 999,     "번씨",$img,1534,  0,    "-", 32, 17, 45, 0, 176, 220,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1535,  49,   "보도근",$img,1535,  0,    "-", 64, 73, 50, 0, 170, 233,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1536, 129,     "보천",$img,1536,  0,    "-", 68, 60, 72, 0, 222, 272,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1537, 121,     "보협",$img,1537,  0,    "-", 73, 53, 75, 0, 216, 264,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1538,  28,     "부하",$img,1538,  0,    "-", 44, 36, 85, 0, 209, 255,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1539,  26,     "비요",$img,1539,  0,    "-", 70, 65, 73, 0, 192, 228,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1540,  31,   "사마주",$img,1540,  0,    "-", 63, 53, 62, 0, 227, 283,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1541,  41,     "사찬",$img,1541,  0,    "-", 61, 71, 54, 0, 215, 264,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1542, 128,     "설후",$img,1542,  0,    "-", 16, 14, 71, 0, 221, 271,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1543,  36,   "성공영",$img,1543,  0,    "-", 73, 58, 80, 0, 172, 220,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1544, 126,     "성만",$img,1544,  0,    "-", 61, 69, 66, 0, 225, 276,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1545, 999,     "소교",$img,1545,  0,    "-", 57, 23, 66, 0, 178, 218,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1546,  46,     "소유",$img,1546,  0,    "-", 51, 61, 48, 0, 164, 210,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1547,  31,    "소제2",$img,1547,  0,    "-", 22, 16, 78, 0, 224, 268,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1548, 126,     "손교",$img,1548,  0,    "-", 77, 60, 69, 0, 181, 219,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1549, 116,     "손기",$img,1549,  0,    "-", 62, 65, 52, 0, 227, 276,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1550, 999,   "손상향",$img,1550,  0,    "-", 72, 62, 42, 0, 193, 244,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1551, 125,     "손익",$img,1551,  0,    "-", 69, 75, 26, 0, 184, 204,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1552, 125,     "손진",$img,1552,  0,    "-", 64, 71, 48, 0, 234, 280,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1553, 123,     "송겸",$img,1553,  0,    "-", 61, 48, 44, 0, 175, 215,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1554,  85,     "순심",$img,1554,  0,    "-", 20, 21, 79, 0, 164, 208,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1555,  31,    "순욱2",$img,1555,  0,    "-", 10, 16, 77, 0, 225, 289,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1556,  31,     "순의",$img,1556,  0,    "-", 16, 11, 73, 0, 207, 281,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1557, 129,     "시삭",$img,1557,  0,    "-", 36, 66, 44, 0, 226, 268,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1558,  29,     "신창",$img,1558,  0,    "-", 51, 29, 46, 0, 210, 272,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1559, 999,     "악신",$img,1559,  0,    "-", 53, 12, 46, 0, 175, 228,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1560, 138,     "악취",$img,1560,  0,    "-", 56, 68, 58, 0, 157, 195,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1561, 138,     "양강",$img,1561,  0,    "-", 62, 70, 42, 0, 160, 199,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1562,  21,     "양부",$img,1562,  0,    "-", 68, 55, 85, 0, 178, 239,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1563,  72,     "양서",$img,1563,  0,    "-", 56, 62, 66, 0, 198, 260,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1564,  34,     "양습",$img,1564,  0,    "-", 67, 49, 73, 0, 168, 230,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1565,  16,     "양앙",$img,1565,  0,    "-", 65, 70, 39, 0, 172, 215,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1566,  17,     "양임",$img,1566,  0,    "-", 71, 78, 56, 0, 170, 215,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1567,  32,     "양제",$img,1567,  0,    "-", 69, 63, 71, 0, 226, 291,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1568,  33,    "양조2",$img,1568,  0,    "-", 65, 61, 67, 0, 223, 286,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1569,  48,    "양추2",$img,1569,  0,    "-", 66, 67, 61, 0, 172, 238,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1570, 140,     "양홍",$img,1570,  0,    "-", 19, 17, 76, 0, 152, 199,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1571,  31,     "양혼",$img,1571,  0,    "-", 60, 67, 63, 0, 220, 278,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1572, 123,     "여거",$img,1572,  0,    "-", 71, 58, 69, 0, 196, 256,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1573, 130,     "여대",$img,1573,  0,    "-", 83, 72, 70, 0, 161, 256,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1574, 136,     "염상",$img,1574,  0,    "-", 29, 27, 69, 0, 158, 199,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1575,  36,     "염행",$img,1575,  0,    "-", 70, 86, 38, 0, 159, 222,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1576,  73,     "영수",$img,1576,  0,    "-", 69, 70, 74, 0, 234, 264,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1577,  39,     "오거",$img,1577,  0,    "-", 49, 63, 32, 0, 151, 211,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1578, 126,     "오경",$img,1578,  0,    "-", 73, 60, 57, 0, 159, 203,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1579, 999,   "오국태",$img,1579,  0,    "-", 31, 11, 60, 0, 161, 222,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1580, 126,     "오찬",$img,1580,  0,    "-", 69, 41, 78, 0, 181, 245,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1581,  23,     "온회",$img,1581,  0,    "-", 42, 40, 78, 0, 178, 222,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1582,  21,    "왕기2",$img,1582,  0,    "-", 70, 66, 63, 0, 217, 281,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1583,  32,     "왕도",$img,1583,  0,    "-", 48, 44, 70, 0, 210, 269,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1584, 123,     "왕돈",$img,1584,  0,    "-", 60, 65, 41, 0, 198, 256,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1585,  34,     "왕릉",$img,1585,  0,    "-", 73, 60, 71, 0, 172, 251,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1586,  63,     "왕문",$img,1586,  0,    "-", 64, 67, 32, 0, 162, 205,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1587,  32,     "왕상",$img,1587,  0,    "-", 25, 19, 65, 0, 180, 268,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1588,  34,     "왕숙",$img,1588,  0,    "-", 35, 21, 80, 0, 195, 256,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1589,  33,     "왕업",$img,1589,  0,    "-", 32,  6, 46, 0, 220, 280,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1590,  35,     "왕충",$img,1590,  0,    "-", 42, 58, 21, 0, 152, 214,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1591,   1,     "우보",$img,1591,  0,    "-", 43, 63, 12, 0, 159, 192,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1592, 122,     "우사",$img,1592,  0,    "-", 70, 33, 79, 0, 217, 273,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1593, 138,     "원요",$img,1593,  0,    "-", 44, 42, 45, 0, 177, 206,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1594,  95,     "원유",$img,1594,  0,    "-", 57, 38, 73, 0, 150, 193,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1595,  31,     "위관",$img,1595,  0,    "-", 69, 45, 81, 0, 220, 291,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1596, 129,     "위막",$img,1596,  0,    "-", 58, 62, 60, 0, 221, 268,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1597,  55,     "유괴",$img,1597,  0,    "-", 75, 72, 66, 0, 165, 214,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1598, 129,     "유략",$img,1598,  0,    "-", 72, 68, 59, 0, 206, 260,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1599,  45,     "유반",$img,1599,  0,    "-", 74, 79, 48, 0, 168, 210,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1600,  29,     "유복",$img,1600,  0,    "-", 54, 50, 73, 0, 164, 208,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1601,  78,    "유선2",$img,1601,  0,    "-",  9, 21, 39, 0, 224, 264,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1602, 139,     "유섭",$img,1602,  0,    "-", 62, 79, 26, 0, 158, 190,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1603,  28,     "유소",$img,1603,  0,    "-", 66, 51, 73, 0, 195, 264,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1604, 128,     "유승",$img,1604,  0,    "-", 46, 69, 29, 0, 215, 258,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1605, 129,     "유찬",$img,1605,  0,    "-", 74, 75, 66, 0, 172, 255,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1606, 129,     "유평",$img,1606,  0,    "-", 65, 70, 67, 0, 218, 272,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1607, 138,     "유훈",$img,1607,  0,    "-", 51, 64, 50, 0, 163, 216,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1608,  48,     "이감",$img,1608,  0,    "-", 59, 67, 33, 0, 176, 211,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1609,  19,     "이승",$img,1609,  0,    "-", 13, 26, 32, 0, 201, 249,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1610,  22,     "이통",$img,1610,  0,    "-", 75, 84, 52, 0, 168, 211,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1611, 138,    "이풍2",$img,1611,  0,    "-", 72, 77, 50, 0, 158, 199,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1612,  20,    "이풍3",$img,1612,  0,    "-", 23, 25, 71, 0, 204, 254,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1613,  29,     "장구",$img,1613,  0,    "-", 69, 71, 47, 0, 201, 263,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1614,  21,     "장기",$img,1614,  0,    "-", 77, 35, 79, 0, 170, 223,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1615,  74,     "장남",$img,1615,  0,    "-", 71, 64, 38, 0, 187, 222,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1616, 100,     "장막",$img,1616,  0,    "-", 53, 52, 70, 0, 155, 195,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1617,   7,   "장만성",$img,1617,  0,    "-", 73, 83, 47, 0, 143, 184,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1618, 135,     "장반",$img,1618,  0,    "-", 56, 73, 66, 0, 227, 282,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1619,  78,     "장빈",$img,1619,  0,    "-", 30, 28, 67, 0, 216, 263,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1620, 124,     "장승",$img,1620,  0,    "-", 75, 68, 75, 0, 178, 244,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1621, 999,    "장양2",$img,1621,  0,    "-", 58, 50, 47, 0, 130, 184,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1622,  68,     "장억",$img,1622,  0,    "-", 82, 80, 54, 0, 190, 254,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1623,  75,     "장준",$img,1623,  0,    "-", 65, 67, 66, 0, 224, 263,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1624,  20,     "장집",$img,1624,  0,    "-", 31, 27, 74, 0, 196, 254,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1625,  39,     "장특",$img,1625,  0,    "-", 71, 53, 74, 0, 209, 265,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1626,  96,     "저곡",$img,1626,  0,    "-", 57, 53, 67, 0, 184, 204,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1627, 128,     "전기",$img,1627,  0,    "-", 51, 69, 55, 0, 231, 258,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1628,  23,     "전만",$img,1628,  0,    "-", 52, 74, 38, 0, 181, 235,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1629, 128,     "전상",$img,1629,  0,    "-",  5,  6, 11, 0, 208, 258,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1630, 130,     "전역",$img,1630,  0,    "-", 60, 62, 37, 0, 212, 265,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1631,  75,     "전예",$img,1631,  0,    "-", 80, 62, 83, 0, 171, 252,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1632,  64,     "전해",$img,1632,  0,    "-", 71, 63, 57, 0, 154, 199,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1633, 123,    "정봉2",$img,1633,  0,    "-", 67, 68, 52, 0, 198, 266,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1634,  22,     "정의",$img,1634,  0,    "-", 17,  3, 66, 0, 184, 220,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1635,  81,   "제갈교",$img,1635,  0,    "-", 55, 17, 77, 0, 204, 228,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1636,  40,   "제갈서",$img,1636,  0,    "-", 45, 43, 27, 0, 218, 286,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1637,  25,     "조충",$img,1637,  0,    "-", 14,  7, 80, 0, 196, 208,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1638, 121,   "종리목",$img,1638,  0,    "-", 84, 68, 75, 0, 214, 269,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1639,  22,     "종육",$img,1639,  0,    "-", 27, 11, 71, 0, 223, 263,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1640, 122,     "좌혁",$img,1640,  0,    "-", 60, 66, 51, 0, 232, 280,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1641,  36,     "주령",$img,1641,  0,    "-", 77, 70, 69, 0, 170, 236,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1642, 115,     "주앙",$img,1642,  0,    "-", 75, 64, 64, 0, 162, 195,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1643,  52,     "주포",$img,1643,  0,    "-", 59, 72, 12, 0, 191, 225,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1644,  11,     "주흔",$img,1644,  0,    "-", 67, 53, 77, 0, 159, 196,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1645, 140,     "진기",$img,1645,  0,    "-", 58, 67, 46, 0, 165, 198,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1646, 142,     "진란",$img,1646,  0,    "-", 65, 70, 43, 0, 157, 204,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1647,  25,     "진랑",$img,1647,  0,    "-", 57, 70, 38, 0, 192, 234,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1648,  12,     "진응",$img,1648,  0,    "-", 62, 69, 49, 0, 172, 208,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1649, 124,     "진표",$img,1649,  0,    "-", 62, 49, 74, 0, 204, 237,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1650, 999,     "채염",$img,1650,  0,    "-", 40, 22, 64, 0, 168, 237,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1651, 999,     "초선",$img,1651,  0,    "-", 66, 15, 72, 0, 176, 211,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1652, 135,     "초이",$img,1652,  0,    "-", 54, 65, 55, 0, 219, 266,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1653,  65,     "추단",$img,1653,  0,    "-", 63, 71, 36, 0, 148, 193,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1654, 999,     "추씨",$img,1654,  0,    "-", 36, 13, 54, 0, 165, 225,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1655,  71,     "추정",$img,1655,  0,    "-", 67, 65, 66, 0, 144, 193,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1656, 145,     "파재",$img,1656,  0,    "-", 69, 75, 52, 0, 145, 184,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1657,  22,     "포신",$img,1657,  0,    "-", 78, 60, 83, 0, 152, 192,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1658, 114,     "하식",$img,1658,  0,    "-", 18, 38, 29, 0, 230, 284,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1659,  36,     "하안",$img,1659,  0,    "-",  6, 27, 72, 0, 190, 249,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1660, 999,   "하후씨",$img,1660,  0,    "-", 29, 16, 47, 0, 186, 249,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1661,   5,     "학맹",$img,1661,  0,    "-", 57, 66, 41, 0, 156, 197,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1662,  98,   "한거자",$img,1662,  0,    "-", 53, 59, 30, 0, 158, 200,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1663,  19,     "한덕",$img,1663,  0,    "-", 62, 79, 24, 0, 171, 228,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1664, 140,     "한윤",$img,1664,  0,    "-", 27, 24, 68, 0, 155, 197,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1665, 999,     "허소",$img,1665,  0,    "-", 53, 27, 60, 0, 150, 195,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1666,  30,     "호열",$img,1666,  0,    "-", 77, 69, 76, 0, 225, 272,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1667,  76,     "호제",$img,1667,  0,    "-", 58, 42, 68, 0, 207, 264,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1668, 149,     "호진",$img,1668,  0,    "-", 65, 77, 13, 0, 146, 190,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1669,  29,     "호질",$img,1669,  0,    "-", 73, 50, 75, 0, 192, 250,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1670,   7,     "환계",$img,1670,  0,    "-", 12, 25, 67, 0, 156, 221,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1671,  56,     "황숭",$img,1671,  0,    "-", 68, 64, 74, 0, 208, 263,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1672, 999,   "황승언",$img,1672,  0,    "-", 68, 17, 81, 0, 165, 222,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1673, 999,   "황월영",$img,1673,  0,    "-", 58, 14, 75, 0, 186, 235,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1674,  45,     "황조",$img,1674,  0,    "-", 74, 65, 57, 0, 148, 208,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1675,  48,     "후선",$img,1675,  0,    "-", 56, 66, 35, 0, 175, 228,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1676,   8,     "휴고",$img,1676,  0,    "-", 61, 72, 40, 0, 151, 199,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1677,  98,   "휴원진",$img,1677,  0,    "-", 53, 63, 38, 0, 155, 200,    "-",    "-");
    RegGeneral($connect,0,0,$fiction,$turnterm,$startyear,$year,1678,  22,   "희지재",$img,1678,  0,    "-", 24,  5, 86, 0, 157, 194,    "-",    "-");
    }
}

function RegGeneral($connect,$init,$life,$fiction,$turnterm,$startyear,$year,$gencount,$npcmatch,$name,$img,$picture,$nation,
                    $city,$leader,$power,$intel,$level,
                    $bornyear,$deadyear,$personal,$special,
                    $msg="") {
    $name = "ⓝ".$name;
    $npc = 2;
    if($city == "-") {
        $city = rand() % 94 + 1;
    } else {
        $city = CityCall($city);
    }
    if($npcmatch == 0 || $fiction == 1) { $npcmatch = rand() % 150 + 1; }
    if($life == 1) { $bornyear = 160; $deadyear = 300; }
    if($init != 0 && $startyear > $bornyear+14 && $startyear <= $deadyear) {
        if($deadyear <= $startyear+3) $deadyear += 5;

        $genid = "gen{$gencount}";
        $turntime = getRandTurn($turnterm);
        if($personal != "-") { $personal = CharCall($personal); }
        else { $personal = rand() % 10; }
        if($fiction == 1) { $special = SpecCall("-"); }
        else { $special = SpecCall($special); }
        if($special >= 40) { $special2 = $special; $special = 0; }
        else { $special2 = 0; }
        if($picture == 0) { $picture = 1001 + rand() % 400; }
        if($picture == -1) { $picture = 'default'; }
        $picture = "{$picture}.jpg";
        if($img < 3) { $picture = 'default.jpg'; }
        $age = $startyear - $bornyear;
        $specage = $age + 1;
        $specage2 = $age + 1;
        $killturn = ($deadyear - $startyear) * 12 + (rand() % 12);
        $experience = $age * 100;
        $dedication = $age * 100;
        if($nation != 0 && $level == 0) $level = 1;
        $pw = md5("18071807");

        //장수
        @MYDB_query("
            insert into general (
                npcid,npc,npc_org,npcmatch,user_id,password,name,picture,nation,city,leader,power,intel,
                experience,dedication,level,gold,rice,crew,crewtype,train,atmos,
                weap,book,horse,turntime,killturn,age,belong,personal,special,specage,special2,specage2,npcmsg,
                makelimit,bornyear,deadyear
            ) values (
                '$gencount','$npc','$npc','$npcmatch','$genid','$pw','$name','$picture','$nation',
                '$city','$leader','$power','$intel','$experience','$dedication',
                '$level','1000','1000','0','0','0','0',
                '0','0','0','$turntime','$killturn','$age','1',
                '$personal','$special','$specage','$special2','$specage2','$msg',
                '0','$bornyear','$deadyear'
            )",
            $connect
        ) or Error(__LINE__.MYDB_error($connect),"");
    } elseif($year == $bornyear+14 && $year < $deadyear) {
        $genid = "gen{$gencount}";

        $query = "select no from general where npcid='$gencount'";
        $result = MYDB_query($query, $connect) or Error("func_npc ".MYDB_error($connect),"");
        $count = MYDB_num_rows($result);
        if($count == 0) {
            $turntime = getRandTurn($turnterm);
            if($personal != "-") { $personal = CharCall($personal); }
            else { $personal = rand() % 10; }
            if($fiction == 1) { $special = SpecCall("-"); }
            else { $special = SpecCall($special); }
            if($special >= 40) { $special2 = $special; $special = 0; }
            else { $special2 = 0; }
            if($picture == 0) { $picture = 1001 + rand() % 400; }
            $picture = "{$picture}.jpg";
            if($img < 3) { $picture = 'default.jpg'; }
            $age = $year - $bornyear;
            $specage = $age + 1;
            $specage2 = $age + 1;
            $killturn = ($deadyear - $year) * 12 + (rand() % 12);
            $experience = $age * 100;
            $dedication = $age * 100;
            $pw = md5("18071807");

            //장수
            @MYDB_query("
                insert into general (
                    npcid,npc,npc_org,npcmatch,user_id,password,name,picture,nation,city,leader,power,intel,
                    experience,dedication,level,gold,rice,crew,crewtype,train,atmos,
                    weap,book,horse,turntime,killturn,age,belong,personal,special,specage,special2,specage2,npcmsg,
                    makelimit,bornyear,deadyear
                ) values (
                    '$gencount','$npc','$npc','$npcmatch','$genid','$pw','$name','$picture','0',
                    '$city','$leader','$power','$intel','$experience','$dedication',
                    '0','1000','1000','0','0','0','0',
                    '0','0','0','$turntime','$killturn','$age','1',
                    '$personal','$special','$specage','$special2','$specage2','$msg',
                    '0','$bornyear','$deadyear'
                )",
                $connect
            ) or Error(__LINE__.MYDB_error($connect),"");

            $alllog[0] = "<C>●</>1월:<Y>$name</>(이)가 성인이 되어 <S>등장</>했습니다.";
            pushAllLog($alllog);
        }
    }
}

function SetDevelop($connect, $genType, $no, $city, $tech) {
    $query = "select rate,pop/pop2*100 as po,comm/comm2*100 as co,def/def2*100 as de,wall/wall2*100 as wa,secu/secu2*100 as se,agri/agri2*100 as ag from city where city='$city'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $city = MYDB_fetch_array($result);

    // 우선 선정
    if($city['rate'] < 95) {
        $command = EncodeCommand(0, 0, 0, 4);    // 우선 선정
        
        $query = "update general set turn0='$command' where no='$no'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        return;
    }
    
    $prob = rand() % 100;
    $command = EncodeCommand(0, 0, 0, 9); //조달
    switch($genType) {
    case 0: //무장
    case 2: //무내정장
        if($prob < 30) {
            if($city['de'] < 99) { $command = EncodeCommand(0, 0, 0, 5); } //수비
            elseif($city['po'] < 99) { $command = EncodeCommand(0, 0, 0, 7); } //정장
            else { $command = EncodeCommand(0, 0, 0, 9); } //조달
        } elseif($prob < 60) {
            if($city['wa'] < 99) { $command = EncodeCommand(0, 0, 0, 6); } //성벽
            elseif($city['po'] < 99) { $command = EncodeCommand(0, 0, 0, 7); } //정장
            else { $command = EncodeCommand(0, 0, 0, 9); } //조달
        } elseif($prob < 90) {
            if($city['se'] < 99) { $command = EncodeCommand(0, 0, 0, 8); } //치안
            elseif($city['po'] < 99) { $command = EncodeCommand(0, 0, 0, 7); } //정장
            else { $command = EncodeCommand(0, 0, 0, 9); } //조달
        } else {
            $command = EncodeCommand(0, 0, 0, 29);
        }
        break;
    case 1: //지장
    case 3: //지내정장
        if($prob < 40) {
            if($city['ag'] < 99) { $command = EncodeCommand(0, 0, 0, 1); } //농업
            elseif($tech < 10000) { $command = EncodeCommand(0, 0, 0, 3); } //기술
            elseif($city['po'] < 99) { $command = EncodeCommand(0, 0, 0, 7); } //정장
            else { $command = EncodeCommand(0, 0, 0, 9); } //조달
        } elseif($prob < 80) {
            if($city['co'] < 99) { $command = EncodeCommand(0, 0, 0, 2); } //상업
            elseif($tech < 10000) { $command = EncodeCommand(0, 0, 0, 3); } //기술
            elseif($city['po'] < 99) { $command = EncodeCommand(0, 0, 0, 7); } //정장
            else { $command = EncodeCommand(0, 0, 0, 9); } //조달
        } elseif($prob < 90) {
            if($tech < 10000) { $command = EncodeCommand(0, 0, 0, 3); } //기술
            elseif($city['po'] < 99) { $command = EncodeCommand(0, 0, 0, 7); } //정장
            else { $command = EncodeCommand(0, 0, 0, 9); } //조달
        } else {
            if($tech < 10000) { $command = EncodeCommand(0, 0, 0, 3 + (rand() % 2) * 6); } //기술, 조달
            else { $command = EncodeCommand(0, 0, 0, 29); }
        }
        break;
    }

    // 장수수가 너무 많으면 탐색 확률 감소
    if($command == EncodeCommand(0, 0, 0, 29)) {
        $query = "select no from general";
        $result = MYDB_query($query, $connect) or Error("processAI04 ".MYDB_error($connect),"");
        $genCount = MYDB_num_rows($result);

        $ratio = round($genCount / 600 * 100);

        if(rand() % 100 < $ratio) {
            $command = EncodeCommand(0, 0, 0, 9);
        }
    }
    
    $query = "update general set turn0='$command' where no='$no'";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    return;
}

function SetCrew($connect, $no, $personal, $gold, $leader, $genType, $tech, $region, $city, $dex0, $dex10, $dex20, $dex30, $dex40) {
    switch($genType) {
    case 0: //무장
    case 2: //무내정장
        $dex0 = $dex0 + rand()%1000;
        $dex10 = $dex10 + rand()%1000;
        $dex20 = $dex20 + rand()%1000;
        $sel = 0;
        // 보궁기 선택
        if($dex0 > $dex10) {
            if($dex0 > $dex20) {
                $sel = 0;
            } else {
                $sel = 2;
            }
        } else {
            if($dex10 > $dex20) {
                $sel = 1;
            } else {
                $sel = 2;
            }
        }

        switch($sel) {
        case 0:
            $type = 0; //보병
                if($tech >= 3000 && $city   ==  3) { $type =  4; } //근위병
            elseif($tech >= 2000 && $city   == 64) { $type =  3; } //자객병
            elseif($tech >= 1000 && $region ==  2) { $type =  1; } //청주병
            elseif($tech >= 1000 && $region ==  5) { $type =  5; } //등갑병
            elseif($tech >= 1000 && $region ==  7) { $type =  2; } //수병
            break;
        case 1:
            $type = 10; //궁병
                if($tech >= 3000 && $city   ==  7) { $type = 14; } //석궁병
            elseif($tech >= 3000 && $city   ==  6) { $type = 13; } //강궁병
            elseif($tech >= 1000 && $region ==  4) { $type = 12; } //연노병
            elseif($tech >= 1000 && $region ==  8) { $type = 11; } //궁기병
            break;
        case 2:
            $type = 20; //기병
                if($tech >= 3000 && $city   ==  2) { $type = 27; } //호표기병
            elseif($tech >= 2000 && $city   == 63) { $type = 24; } //철기병
            elseif($tech >= 2000 && $city   == 67) { $type = 25; } //수렵기병
            elseif($tech >= 2000 && $city   == 65) { $type = 23; } //돌격기병
            elseif($tech >= 2000 && $city   == 66) { $type = 26; } //맹수병
            elseif($tech >= 1000 && $region ==  1) { $type = 21; } //백마병
            elseif($tech >= 1000 && $region ==  3) { $type = 22; } //중장기병
            break;
        }
        break;
    case 1: //지장
    case 3: //지내정장
        $type = 30; //귀병
            if($tech >= 3000 && $city   ==  4) { $type = 34; } //악귀병
        elseif($tech >= 3000 && $city   ==  5) { $type = 37; } //천귀병
        elseif($tech >= 3000 && $city   ==  1) { $type = 38; } //마귀병
        elseif($tech >= 2000 && $city   == 69) { $type = 33; } //흑귀병
        elseif($tech >= 2000 && $city   == 68) { $type = 32; } //백귀병
        elseif($tech >= 1000 && $region ==  6) { $type = 31; } //신귀병
        elseif($tech >= 3000 && $city   ==  3) { $type = 36; } //황귀병
        elseif($tech >= 1000 && rand()%100 < 50) { $type = 35; } //남귀병
        break;
    }

    $gold -= 200;   // 사기비용

    $cost = getCost($connect, $type) * getTechCost($tech);
    $cost = CharCost($cost, $personal);

    $crew = floor($gold / $cost);
    if($leader < $crew) { $crew = $leader; }
    $command = EncodeCommand(0, $type, $crew, 11);

    $query = "update general set turn0='$command' where no='$no'";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    return;
}

function processAI($connect, $no) {
    global $_baserice;

    $query = "select startyear,year,month,turnterm,scenario,gold_rate,rice_rate from game where no='1'";
    $result = MYDB_query($query, $connect) or Error("processAI00 ".MYDB_error($connect),"");
    $admin = MYDB_fetch_array($result);
    // 초반 여부
    if($admin['startyear']+2 > $admin['year'] || ($admin['startyear']+2 == $admin['year'] && $admin['month'] < 5)) {
        $isStart = 1;
    } else {
        $isStart = 0;
    }

    $query = "select no,turn0,npcid,name,nation,nations,city,level,npcmsg,personal,leader,intel,power,gold,rice,crew,train,atmos,npc,npcmatch,mode,injury,picture,imgsvr,killturn,makelimit,dex0,dex10,dex20,dex30,dex40 from general where no='$no'";
    $result = MYDB_query($query, $connect) or Error("processAI01 ".MYDB_error($connect),"");
    $general = MYDB_fetch_array($result);

    // 입력된 턴이 있으면 그것 실행
    if($general['turn0'] != "00000000000000") {
        return;
    }

    $query = "select city,region,nation,level,path,rate,gen1,gen2,gen3,pop,supply,front from city where city='{$general['city']}'";
    $result = MYDB_query($query, $connect) or Error("processAI02 ".MYDB_error($connect),"");
    $city = MYDB_fetch_array($result);

    $query = "select nation,level,tech,gold,rice,rate,type,color,name,war from nation where nation='{$general['nation']}'";
    $result = MYDB_query($query, $connect) or Error("processAI03 ".MYDB_error($connect),"");
    $nation = MYDB_fetch_array($result);

    $coreCommand = array();
    if($general['level'] >= 5) {
        $query = "select l{$general['level']}turn0 from nation where nation='{$general['nation']}'";
        $result = MYDB_query($query, $connect) or Error("processAI03 ".MYDB_error($connect),"");
        $coreCommand = MYDB_fetch_array($result);
    }

    $attackable = 0;
    $query = "select city from city where nation='{$general['nation']}' and supply='1' and front=1";
    $result = MYDB_query($query, $connect) or Error("processAI10 ".MYDB_error($connect),"");
    $cityCount = MYDB_num_rows($result);
    // 공격가능도시 있으면 1
    if($cityCount > 0) { $attackable = 1; }

    $dipState = 0;
    $query = "select no from diplomacy where me='{$general['nation']}' and state=1 and term>8";
    $result = MYDB_query($query, $connect) or Error("processAI04 ".MYDB_error($connect),"");
    $dipCount = MYDB_num_rows($result);
    // 선포중이면 1상태
    if($dipCount > 0) { $dipState = 1; }

    $query = "select no from diplomacy where me='{$general['nation']}' and state=1 and term<=8";
    $result = MYDB_query($query, $connect) or Error("processAI04 ".MYDB_error($connect),"");
    $dipCount = MYDB_num_rows($result);
    // 전쟁준비 선포중이면 2상태
    if($dipCount > 0) { $dipState = 2; }

    $query = "select no from diplomacy where me='{$general['nation']}' and state=1 and term<=3";
    $result = MYDB_query($query, $connect) or Error("processAI04 ".MYDB_error($connect),"");
    $dipCount = MYDB_num_rows($result);
    // 교전 직전이면 3상태
    if($dipCount > 0) { $dipState = 3; }

    $query = "select no from diplomacy where me='{$general['nation']}' and state=0";
    $result = MYDB_query($query, $connect) or Error("processAI04 ".MYDB_error($connect),"");
    $dipCount = MYDB_num_rows($result);
    // 교전중이면 4상태
    if($dipCount > 0) { $dipState = 4; }

    //무장
    if($general['power'] >= $general['intel']) {
        $genType = 0;
        if($general['intel'] >= $general['power'] * 0.8) {  //무지장
            switch(rand() % 5) {
            case 0: case 1: case 2: case 3: $genType = 0; break;
            case 4:                         $genType = 1; break;
            }
        }
    //지장
    } else {
        $genType = 1;
        if($general['power'] >= $general['intel'] * 0.8) {  //지무장
            switch(rand() % 5) {
            case 0:                         $genType = 0; break;
            case 1: case 2: case 3: case 4: $genType = 1; break;
            }
        }
    }

    //내정장
    if($general['leader'] < 40) {
        $genType += 2;
        //$genType = 2; // 무내정장
        //$genType = 3; // 지내정장
    }

    $tech = getTechCost($nation['tech']);

    if($general['atmos'] >= 90 && $general['train'] >= 90) {
        if($general['mode'] == 0) {
            $query = "update general set mode=1 where no='{$general['no']}'";
            MYDB_query($query, $connect) or Error("processAI05 ".MYDB_error($connect),"");
        }
    } else {
        if($general['mode'] == 1) {
            $query = "update general set mode=0 where no='{$general['no']}'";
            MYDB_query($query, $connect) or Error("processAI05 ".MYDB_error($connect),"");
        }
    }

    //유기체메시지 출력 하루 6번
    //특별 메세지 있는 경우 출력 하루 4번
    switch($admin['turnterm']) {
    case 0: $term = 1; break;
    case 1: $term = 1; break;
    case 2: $term = 2; break;
    case 3: $term = 3; break;
    case 4: $term = 6; break;
    case 5: $term = 12; break;
    case 6: $term = 30; break;
    case 7: $term = 60; break;
    }
    if($general['npcid'] == 2000 && rand()%(24*$term) < 6) {
        PushMsg(1, 0, $general['picture'], $general['imgsvr'], "{$general['name']}:", $nation['color'], $nation['name'], $nation['color'], $general['npcmsg']);
    } elseif($general['npcmsg'] != "" && rand()%(24*$term) < 3) {
        PushMsg(1, 0, $general['picture'], $general['imgsvr'], "{$general['name']}:", $nation['color'], $nation['name'], $nation['color'], $general['npcmsg']);
    }

    //재야인경우
    if($general['npc'] == 5 && $general['level'] == 0) {
        // 오랑캐는 바로 임관
        $query = "select nation from general where level=12 and npc=5 and nation not in (0{$general['nations']}0) order by rand() limit 0,1";
        $result = MYDB_query($query, $connect) or Error("processAI06 ".MYDB_error($connect),"");
        $rulerCount = MYDB_num_rows($result);
        if($rulerCount > 0) {
            $ruler = MYDB_fetch_array($result);
            $command = EncodeCommand(0, 0, $ruler['nation'], 25); //임관
        } else {
            $command = EncodeCommand(0, 0, 0, 42); //견문
        }
        $query = "update general set turn0='$command' where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error("processAI07 ".MYDB_error($connect),"");
        return;
    } elseif($general['npc'] < 5 && $general['level'] == 0) {
        switch(rand()%5) {
        //임관 40%
        case 0: case 1:
            if($admin['scenario'] == 0 || $admin['scenario'] >= 20) {
                // 가상모드엔 랜덤임관, 초반엔 부상 적은 군주 우선 70%
                if($admin['startyear']+3 > $admin['year'] && rand()%100 < 70) {
                    $query = "select nation from general where level=12 and nation not in (0{$general['nations']}0) order by injury,rand() limit 0,1";
                } else {
                    $query = "select nation from general where level=12 and nation not in (0{$general['nations']}0) order by rand() limit 0,1";
                }
                $result = MYDB_query($query, $connect) or Error("processAI06 ".MYDB_error($connect),"");
                $rulerCount = MYDB_num_rows($result);
                if($rulerCount > 0 && $general['npcmatch'] != 999 && $general['makelimit'] == 0) {
                    $ruler = MYDB_fetch_array($result);
                    $command = EncodeCommand(0, 0, $ruler['nation'], 25); //임관
                } else {
                    $command = EncodeCommand(0, 0, 0, 42); //견문
                }
            } else {
                $query = "select nation from general where level=12 and npc=0";
                $result = MYDB_query($query, $connect) or Error("processAI06 ".MYDB_error($connect),"");
                $nonCount = MYDB_num_rows($result);
                $query = "select nation from general where level=12 and npc>0";
                $result = MYDB_query($query, $connect) or Error("processAI06 ".MYDB_error($connect),"");
                $npcCount = MYDB_num_rows($result);
                $ratio = round($npcCount / ($nonCount + $npcCount) * 100);
                $ratio = round($ratio * 1.0);
                //NPC우선임관
                $query = "select nation,ABS(IF(ABS(npcmatch-'{$general['npcmatch']}')>75,150-ABS(npcmatch-'{$general['npcmatch']}'),ABS(npcmatch-'{$general['npcmatch']}'))) as npcmatch2 from general where level=12 and npc>0 and nation not in (0{$general['nations']}0) order by npcmatch2,rand() limit 0,1";
                $result = MYDB_query($query, $connect) or Error("processAI06 ".MYDB_error($connect),"");
                $rulerCount = MYDB_num_rows($result);
                if($rulerCount > 0 && $general['npcmatch'] != 999 && rand()%100 < $ratio && $general['makelimit'] == 0) {  // 엔국 비율대로 임관(50% : 50%)
                    $ruler = MYDB_fetch_array($result);
                    $command = EncodeCommand(0, 0, $ruler['nation'], 25); //임관
                } elseif($general['npcmatch'] != 999 && $general['makelimit'] == 0) {  // NPC국가 없으면 유저국 임관
                    $query = "select nation from general where level=12 and npc=0 order by rand() limit 0,1";
                    $result = MYDB_query($query, $connect) or Error("processAI06 ".MYDB_error($connect),"");
                    $ruler = MYDB_fetch_array($result);
                    $command = EncodeCommand(0, 0, $ruler['nation'], 25); //임관
                } else {
                    $command = EncodeCommand(0, 0, 0, 42); //견문
                }
            }
            break;
        case 2: case 3: //거병이나 견문 40%
            // 초반이면서 능력이 좋은놈 위주로 1%확률로 거병 (300명 재야시 2년간 약 10개 거병 예상)
            $prop = rand() % 100;
            $ratio = round(($general['leader'] + $general['power'] + $general['intel']) / 3);
            if($admin['startyear']+2 > $admin['year'] && $prop < $ratio && rand()%100 < 1 && $general['makelimit'] == 0) {
                //거병
                $command = EncodeCommand(0, 0, 0, 55);
            } else {
                //견문
                $command = EncodeCommand(0, 0, 0, 42);
            }
            break;
        case 4: //이동 20%
            $paths = explode("|", $city['path']);
            $command = EncodeCommand(0, 0, $paths[rand()%count($paths)], 21);
            break;
        }
        $query = "update general set turn0='$command' where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error("processAI07 ".MYDB_error($connect),"");
        return;
    }

    $rulerCommand = 0;
    //군주가 할일
    if($general['level'] == 12) {
        //오랑캐인데 공격 못하면 바로 방랑/해산
        if($general['npc'] == 5 && $dipState == 0 && $attackable == 0) {
            //방랑군이냐 아니냐
            if($nation['level'] == 0) {
                // 해산
                $command = EncodeCommand(0, 0, 0, 56);
                $query = "update general set turn0='$command' where no='{$general['no']}'";
                MYDB_query($query, $connect) or Error("processAI09 ".MYDB_error($connect),"");
                return;
            } else {
                // 방랑
                $command = EncodeCommand(0, 0, 0, 47);
                $query = "update general set turn0='$command' where no='{$general['no']}'";
                MYDB_query($query, $connect) or Error("processAI09 ".MYDB_error($connect),"");
                return;
            }
        }
        //분기마다
        if($admin['month'] == 1 || $admin['month'] == 4 || $admin['month'] == 7 || $admin['month'] == 10) {
            //관직임명
            Promotion($connect, $general['nation'], $nation['level']);
        } elseif($admin['month'] == 12) {
            //세율
            $nation['rate'] = TaxRate($connect, $general['nation']);
            //지급율
            GoldBillRate($connect, $nation['nation'], $nation['rate'], $admin['gold_rate'], $nation['type'], $nation['gold']);
        } elseif($admin['month'] == 6) {
            //세율
            $nation['rate'] = TaxRate($connect, $general['nation']);
            //지급율
            RiceBillRate($connect, $nation['nation'], $nation['rate'], $admin['rice_rate'], $nation['type'], $nation['rice']);
        }

        //방랑군이냐 아니냐
        if($nation['level'] == 0) {
            if($admin['startyear']+2 <= $admin['year']) {
                // 해산
                $command = EncodeCommand(0, 0, 0, 56);
                $query = "update general set turn0='$command' where no='{$general['no']}'";
                MYDB_query($query, $connect) or Error("processAI09 ".MYDB_error($connect),"");
                return;
            } elseif($city['nation'] == 0 && ($city['level'] == 5 || $city['level'] == 6)) {
                $type = rand()%9 + 1;
                $colors = GetNationColors();
                $color = rand() % count($colors);
                $command = EncodeCommand(0, $type, $color, 46);
                $nationName = "㉿"._String::SubStr($general['name'], 1);
                //건국
                $query = "update general set turn0='$command',makenation='$nationName' where no='{$general['no']}'";
                MYDB_query($query, $connect) or Error("processAI08 ".MYDB_error($connect),"");
                return;
            } elseif(rand()%4 > 0) {
                //이동
                $paths = explode("|", $city['path']);
                $command = EncodeCommand(0, 0, $paths[rand()%count($paths)], 21);
                $query = "update general set turn0='$command' where no='{$general['no']}'";
                MYDB_query($query, $connect) or Error("processAI09 ".MYDB_error($connect),"");
                return;
            } else {
                //조달
                $command = EncodeCommand(0, 0, 0, 9);
                $query = "update general set turn0='$command' where no='{$general['no']}'";
                MYDB_query($query, $connect) or Error("processAI09 ".MYDB_error($connect),"");
                return;
            }
        } else {
            //외교 평시에 선포
            if($dipState == 0 && $attackable == 0) {
                //전방 체크 먼저
                SetNationFront($connect, $nation['nation']);

                $query = "select city from city where nation='{$general['nation']}' and front=1 limit 0,1";
                $result = MYDB_query($query, $connect) or Error("processAI02 ".MYDB_error($connect),"");
                $frontCount = MYDB_num_rows($result);
                //근접 공백지 없을때
                if($frontCount == 0) {
                    $query = "select (sum(pop/10)+sum(agri)+sum(comm)+sum(secu)+sum(def)+sum(wall))/(sum(pop2/10)+sum(agri2)+sum(comm2)+sum(secu2)+sum(def2)+sum(wall2))*100 as dev from city where nation='{$general['nation']}'";
                    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
                    $devRate = MYDB_fetch_array($result);
                    //내정이 80% 이상일때
                    if($devRate['dev'] > 80) {
                        $query = "select nation from nation where level>0 order by rand()";
                        $result = MYDB_query($query, $connect) or Error("processAI09 ".MYDB_error($connect),"");
                        $nationCount = MYDB_num_rows($result);
                        for($i=0; $i < $nationCount; $i++) {
                            $youNation = MYDB_fetch_array($result);

                            if(isClose($connect, $general['nation'], $youNation['nation'])) {
                                $command = EncodeCommand(0, 0, $youNation['nation'], 62);
                                $query = "update nation set l12turn0='$command' where nation='{$general['nation']}'";
                                MYDB_query($query, $connect) or Error("processAI09 ".MYDB_error($connect),"");
                                $rulerCommand = 1;
                                break;
                            }
                        }
                    }
                }
            }
        }
    }

    // 입력된 턴이 있으면
    if(!empty($coreCommand) && ($coreCommand["l{$general['level']}turn0"] != EncodeCommand(0, 0, 0, 99))) {
        $rulerCommand = 1;
    }

    //방랑군 아니고, 입력된 턴이 없을때 수뇌부가 할일
    if($nation['level'] != 0 && $general['level'] >= 5 && $rulerCommand == 0) {
        $query = "select A.no,A.name,A.nation,B.nation from general A, city B where A.city=B.city and A.nation='{$general['nation']}' and B.nation!='{$general['nation']}' and A.no!='{$general['no']}' order by rand() limit 0,1";
        $result = MYDB_query($query, $connect) or Error("processAI11 ".MYDB_error($connect),"");
        $curGen = MYDB_fetch_array($result);

        if($curGen['no'] != 0) {          // 타도시에 있는 경우 국내로 발령
            if($dipState >= 3) {
                $query = "select city from city where nation='{$general['nation']}' and front=1 and supply=1 order by rand() limit 0,1";
                $result = MYDB_query($query, $connect) or Error("processAI10 ".MYDB_error($connect),"");
                $selCity = MYDB_fetch_array($result);
                if($selCity['city'] > 0) {
                    // 발령
                    $command = EncodeCommand(0, $curGen['no'], $selCity['city'], 27);
                } else {
                    // 발령
                    $command = EncodeCommand(0, $curGen['no'], $city['city'], 27);
                }
            } else {
                // 발령
                $command = EncodeCommand(0, $curGen['no'], $city['city'], 27);
            }
            $query = "update nation set l{$general['level']}turn0='$command' where nation='{$general['nation']}'";
            MYDB_query($query, $connect) or Error("processAI09 ".MYDB_error($connect),"");
        } elseif($dipState <= 1) {      // 평시엔 균등 발령만
            //발령, 최소장수 도시 선택, 최다장수도시의 장수 선택
            $query = "select B.city,count(*) as cnt,((B.agri+B.comm+B.secu+B.def+B.wall)/(B.agri2+B.comm2+B.secu2+B.def2+B.wall2)+(B.pop/B.pop2))/2*100 as dev from general A, city B where A.city=B.city and A.nation='{$general['nation']}' and B.nation='{$general['nation']}' and B.supply=1 group by A.city";
            $result = MYDB_query($query, $connect) or Error("processAI10 ".MYDB_error($connect),"");
            $cityCount = MYDB_num_rows($result);
            //도시 2개 이상일때만
            if($cityCount > 1) {
                $min = 500; $minCity = 0;
                $max = 0;   $maxCity = 0;
                $devCity = 0;
                for($i=0; $i < $cityCount; $i++) {
                    $curCity = MYDB_fetch_array($result);
                    if($curCity['cnt'] >= $max) { $max = $curCity['cnt']; $maxCity = $curCity['city']; }
                    if($curCity['cnt'] <= $min) { $min = $curCity['cnt']; $minCity = $curCity['city']; }
                    if($curCity['dev'] < 70) { $devCity = $curCity['city']; }    // 개발이 안된 곳 우선
                }
                if($devCity != 0) { $minCity = $devCity; }
                if($maxCity != $minCity) {
                    $query = "select no from general where city='$maxCity' and nation='{$general['nation']}' and no!='{$general['no']}' and npc>=2 limit 0,1";
                    $result = MYDB_query($query, $connect) or Error("processAI11 ".MYDB_error($connect),"");
                    $curGen = MYDB_fetch_array($result);

                    if($curGen['no'] != 0) {
                        // 발령
                        $command = EncodeCommand(0, $curGen['no'], $minCity, 27);
                        $query = "update nation set l{$general['level']}turn0='$command' where nation='{$general['nation']}'";
                        MYDB_query($query, $connect) or Error("processAI09 ".MYDB_error($connect),"");
                    }
                    //계속 진행
                }
            }
        } else {
            // 병사있고 쌀있고 후방에 있는 장수
            $query = "select A.no from general A, city B where A.city=B.city and A.nation='{$general['nation']}' and B.nation='{$general['nation']}' and B.front=0 and A.crew>700 and A.rice>700*{$tech} order by A.npc,A.crew desc limit 0,1";
            $result = MYDB_query($query, $connect) or Error("processAI10 ".MYDB_error($connect),"");
            $selGen = MYDB_fetch_array($result);
            // 전방 도시, 30% 확률로 태수 있는 전방으로 발령
            if(rand()%100 < 30) {
                $query = "select city from city where nation='{$general['nation']}' and front=1 and supply=1 order by gen1 desc,rand() limit 0,1";
            } else {
                $query = "select city from city where nation='{$general['nation']}' and front=1 and supply=1 order by rand() limit 0,1";
            }
            $result = MYDB_query($query, $connect) or Error("processAI10 ".MYDB_error($connect),"");
            $selCity = MYDB_fetch_array($result);
            if($selGen['no'] > 0 && $selCity['city'] > 0 && rand() % 100 < 80) {    // 80% 확률
                // 발령
                $command = EncodeCommand(0, $selGen['no'], $selCity['city'], 27);
            } else {
                //병사 없고 인구없는 전방에 있는 장수
                $query = "select A.no from general A, city B where A.city=B.city and A.nation='{$general['nation']}' and B.nation='{$general['nation']}' and B.pop<40000 and B.front=1 and A.crew<700 order by A.npc,A.crew limit 0,1";
                $result = MYDB_query($query, $connect) or Error("processAI10 ".MYDB_error($connect),"");
                $selGen = MYDB_fetch_array($result);
                // 인구많은도시
                $query = "select city from city where nation='{$general['nation']}' and supply=1 order by pop desc limit 0,1";
                $result = MYDB_query($query, $connect) or Error("processAI10 ".MYDB_error($connect),"");
                $selCity = MYDB_fetch_array($result);
                if($selGen['no'] > 0 && $selCity['city'] > 0 && rand() % 100 < 80) {    // 80% 확률
                    // 발령
                    $command = EncodeCommand(0, $selGen['no'], $selCity['city'], 27);
                } else {
                    // 발령할 장수 없으면 몰포
                    if(rand() % 2 == 0) { $type = "gold"; $type2 = 1; }
                    else { $type = "rice"; $type2 = 2; }

                    if($nation[$type] < 1000) {  // 몰수
                        // 몰수 대상
                        $query = "select no,{$type} from general where nation='{$general['nation']}' and no!='{$general['no']}' and {$type}>3000 order by {$type} desc limit 0,1";
                        $result = MYDB_query($query, $connect) or Error("processAI10 ".MYDB_error($connect),"");
                        $SelGen = MYDB_fetch_array($result);
                        if($SelGen['no'] != 0) {
                            $amount = floor($SelGen[$type] / 5000)*10 + 10;
                            if($amount > 100) $amount = 100;
                            // 몰수
                            $command = EncodeCommand($type2, $SelGen['no'], $amount, 24);    // 금,쌀 1000단위 몰수
                        }
                    } else {    // 포상
                        // 포상 대상
                        $query = "select no from general where nation='{$general['nation']}' and no!='{$general['no']}' and killturn>=5 order by {$type} limit 0,1";
                        $result = MYDB_query($query, $connect) or Error("processAI10 ".MYDB_error($connect),"");
                        $SelGen = MYDB_fetch_array($result);
                        if($SelGen['no'] != 0) {
                            $amount = floor(($nation[$type]-$_baserice) / 5000)*10 + 10;
                            if($amount > 100) $amount = 100;
                            // 포상
                            $command = EncodeCommand($type2, $SelGen['no'], $amount, 23);    // 금 1000단위 포상
                        }
                    }
                }
            }
            $query = "update nation set l{$general['level']}turn0='$command' where nation='{$general['nation']}'";
            MYDB_query($query, $connect) or Error("processAI09 ".MYDB_error($connect),"");
        }
    }

    $command = EncodeCommand(0, 0, 0, 1);
    //일반 할일
    if($general['killturn'] < 5) {
        if($general['gold'] + $general['rice'] == 0) {
            $command = EncodeCommand(0, 0, 0, 9); //조달
        } elseif($general['gold'] > $general['rice']) {
            $command = EncodeCommand(0, 1, 100, 44); //헌납
        } else {
            $command = EncodeCommand(0, 2, 100, 44); //헌납
        }
    } elseif($general['injury'] > 10) {
    // 부상 2달 이상이면 요양
        $command = EncodeCommand(0, 0, 0, 50);  //요양
    } elseif($nation['level'] == 0) {
    //방랑군일때
        if($admin['startyear']+3 <= $admin['year']) {
            $command = EncodeCommand(0, 0, 0, 45); //하야
        } else {
            switch(rand()%5) {
            case 0:
                $command = EncodeCommand(0, 0, 0, 42); break; //견문 20%
            case 1: case 2: case 3: case 4:
                $command = EncodeCommand(0, 0, 0, 9); break; //조달 80%
            }
        }
    } else {
    //국가일때
        //아국땅 아니면 귀환
        if($general['nation'] != $city['nation'] || $city['supply'] == 0) {
            $command = EncodeCommand(0, 0, 0, 28);  //귀환
            $query = "update general set turn0='$command' where no='{$general['no']}'";
            MYDB_query($query, $connect) or Error("processAI23 ".MYDB_error($connect),"");
            return;
        }
        //국가 병량이 없을때 바로 헌납
        if($nation['rice'] < 2000 && $general['rice'] > 200) {
            $amount = floor(($general['rice'] - 200)/100) + 1;
            if($amount > 20) { $amount = 20; }
            $command = EncodeCommand(0, 2, $amount, 44);  //헌납
            $query = "update general set turn0='$command' where no='{$general['no']}'";
            MYDB_query($query, $connect) or Error("processAI23 ".MYDB_error($connect),"");
            return;
        }

//   R
//    ┃  ┃     공격/내정
// 700┃쌀┣━━━━┓
//    ┃팜┃        ┃
// 100┣━┫  내정  ┣━━━
//    ┃조┃        ┃쌀삼
//   0┗━┻━━━━┻━━━> G
//       100       700
        $resrc = $tech * 700;
        // 평시거나 초반아니면서 공격가능 없으면서 병사 있으면 해제(25%)
        if($dipState == 0 && $isStart == 0 && $attackable == 0 && $general['crew'] > 0 && rand()% 100 < 25) {
            $command = EncodeCommand(0, 0, 0, 17);    //소집해제
        } elseif($dipState <= 1 || $isStart == 1) {
        //평시이거나 선포있어도 초반이면
            if($general['gold'] + $general['rice'] < 200) { $command = EncodeCommand(0, 0, 0, 9); } //금쌀없으면 조달9
            elseif($general['rice'] > 100 && $city['rate'] < 95) { $command = EncodeCommand(0, 0, 0, 4); } //우선 선정
            elseif($general['gold'] < 100) {                                      //금없으면 쌀팜
                $amount = floor(($general['rice'] - $general['gold']) / 100 / 2);   // 100단위
                $command = EncodeCommand(0, 1, $amount, 49);                    //팜
            } elseif($general['gold'] < 700 && $general['rice'] < 700) { $command = EncodeCommand(0, 0, 0, 1); } //금쌀되면 내정
            elseif($general['rice'] < 100) {                                      //쌀없으면 쌀삼
                $amount = floor(($general['gold'] - $general['rice']) / 100 / 2);  // 100단위
                $command = EncodeCommand(0, 2, $amount, 49);                    //삼
            } elseif($genType >= 2) { $command = EncodeCommand(0, 0, 0, 1); } //내정장일때 내정
            else {
                //현도시가 전방이면 공격 가능성 체크
                if($city['front'] > 0) {
                    //주변도시 체크
                    $paths = explode("|", $city['path']);
                    for($i=0; $i < count($paths); $i++) {
                        $query = "select city,nation from city where city='$paths[$i]'";
                        $result = MYDB_query($query, $connect) or Error("processAI20 ".MYDB_error($connect),"");
                        $targetCity = MYDB_fetch_array($result);
                        //공백지이면 타겟에 포함
                        if($targetCity['nation'] == 0) { $target[count($target)] = $targetCity['city']; }
                    }
                    if(count($target) == 0 || $isStart == 1 || $nation['war'] == 1) { $command = EncodeCommand(0, 0, 0, 1); } //공격 가능도시가 없으면 내정
                    else { $command = EncodeCommand(0, 0, $target[rand()%count($target)], 16); }  //있으면 공격
                } else {
                    //전방 아니면 내정
                    $command = EncodeCommand(0, 0, 0, 1);
                }

                if($command == EncodeCommand(0, 0, 0, 1)) {     // 공격아닌 경우
                    $query = "select city,(pop/10+agri+comm+secu+def+wall)/(pop2/10+agri2+comm2+secu2+def2+wall2)*100 as dev from city where city='{$general['city']}'";
                    $result = MYDB_query($query, $connect) or Error("processAI19 ".MYDB_error($connect),"");
                    $selCity = MYDB_fetch_array($result);

                    $sel = rand() % 10;
                    if($selCity['dev'] > 95) { $sel = 9; }
                    elseif($selCity['dev'] < 70) { $sel = 0; }
                    switch($sel) {
                    case 0: case 1: case 2: case 3: case 4: case 5: case 6: case 7: // 그대로 내정 80 %
                        $command = EncodeCommand(0, 0, 0, 1);
                        break;
                    case 8: case 9: // 저개발 도시로 워프 20%
                        //도시 선택, 30% 확률로 군사 있는 곳으로 워프
                        if(rand()%100 < 30) {
                            $query = "select city,(pop/10+agri+comm+secu+def+wall)/(pop2/10+agri2+comm2+secu2+def2+wall2)*100 as dev from city where nation='{$general['nation']}' and supply='1' order by gen2 desc,dev limit 0,1";
                        } else {
                            $query = "select city,(pop/10+agri+comm+secu+def+wall)/(pop2/10+agri2+comm2+secu2+def2+wall2)*100 as dev from city where nation='{$general['nation']}' and supply='1' order by dev limit 0,1";
                        }
                        $result = MYDB_query($query, $connect) or Error("processAI16 ".MYDB_error($connect),"");
                        $selCity = MYDB_fetch_array($result);
                        //이미 그 도시이거나, 그 도시도 고개발이면 내정
                        if($selCity['city'] == $general['city'] || $selCity['dev'] > 95) {
                            $command = EncodeCommand(0, 0, 0, 1);
                        } else {
                            //워프
                            $query = "update general set city='{$selCity['city']}' where no='{$general['no']}'";
                            MYDB_query($query, $connect) or Error("processAI18 ".MYDB_error($connect),"");

                            $command = EncodeCommand(0, 0, 0, 50);  //요양
                            $query = "update general set turn0='$command' where no='{$general['no']}'";
                            MYDB_query($query, $connect) or Error("processAI23 ".MYDB_error($connect),"");
                            return;
                        }
                        break;
                    }

//     ┃        ┃
//     ┃  쌀팜  ┃ 공격
//     ┃        ┃
// 700t┣━━━━╋━━━━━━━━━
//     ┃내조  ↗┃
//     ┃    ↗  ┃
//     ┣━┓내조┃  쌀삼
//     ┃**┃    ┃
//   0 ┗━┻━━━━━━━> G
//              700t

                } else {                // 공격인 경우
                    if($general['crew'] < 700 && $general['gold'] >= $resrc && $general['rice'] >= $resrc) { //자원되고, 병사없을때
                        if($city['pop'] > 40000) { $command = EncodeCommand(0, 0, 0, 11); }
                        else { $command = EncodeCommand(0, 0, 0, 1); }
                    } elseif($general['rice'] < $resrc && $general['rice'] <= $general['gold']) {
                        //금이 더 많으면 매매
                        $amount = floor(($general['gold'] - $general['rice']) / 100 / 2);  // 100단위
                        if($amount > 0) { $command = EncodeCommand(0, 2, $amount, 49); }//삼
                        else { $command = EncodeCommand(0, 0, 0, (rand()%2)*8 + 1); }   // 내정, 조달
                    } elseif($general['gold'] < $resrc && $general['rice'] > $general['gold']) {
                        //쌀이 더 많으면 매매
                        $amount = floor(($general['rice'] - $general['gold']) / 100 / 2);  // 100단위
                        if($amount > 0) { $command = EncodeCommand(0, 1, $amount, 49); }//팜
                        else { $command = EncodeCommand(0, 0, 0, (rand()%2)*8 + 1); }   // 내정, 조달
                    //자원, 병사 모두 충족
                    } elseif($general['crew'] >= 700 && $general['train'] < 90) {
                        $command = EncodeCommand(0, 0, 0, 13);  //훈련
                    } elseif($general['crew'] >= 700 && $general['atmos'] < 90) {
                        $command = EncodeCommand(0, 0, 0, 14);  //사기진작
                    } else {
                        //공격
                        //$command = $target[rand()%count($target)] * 100 + 16;   //있으면 공격
                    }
                }
            }
        } else {
//     R
//     ┃  ┃
//     ┃쌀┃
//     ┃팜┃ 공격
// 700t┣━╋━━━━━
//     ┃조┃ 쌀삼
//    0┗━┻━━━━━> G
//        700t

        //전시일때
            if($general['gold'] + $general['rice'] < $resrc*2) { $command = EncodeCommand(0, 0, 0, 9); } //금쌀없으면 조달
            elseif($general['rice'] > $resrc && $city['rate'] < 95 && $city['front'] == 0) { $command = EncodeCommand(0, 0, 0, 4); }  // 우선 선정
            elseif($general['rice'] > $resrc && $city['rate'] < 50 && $city['front'] == 1) { $command = EncodeCommand(0, 0, 0, 4); }  // 우선 선정
            elseif($general['gold'] < $resrc) {                                   // 금없으면 쌀팜
                $amount = floor(($general['rice'] - $general['gold']) / 100 / 2);   // 100단위
                if($amount > 0) { $command = EncodeCommand(0, 1, $amount, 49); }// 팜
                else { $command = EncodeCommand(0, 0, 0, 9); }                  // 조달
            } elseif($general['rice'] < $resrc) {                                 // 쌀없으면 쌀삼
                $amount = floor(($general['gold'] - $general['rice']) / 100 / 2);   // 100단위
                if($amount > 0) { $command = EncodeCommand(0, 2, $amount, 49); }// 팜
                else { $command = EncodeCommand(0, 0, 0, 9); }                  // 조달
            } elseif($genType >= 2) { $command = EncodeCommand(0, 0, 0, 1); } //내정장일때 내정
            elseif($general['crew'] < 700 && $general['gold'] >= $resrc && $general['rice'] >= $resrc) {
                $query = "select no from general where nation='{$general['nation']}'";
                $result = MYDB_query($query, $connect) or Error("processAI16 ".MYDB_error($connect),"");
                $genCount = MYDB_num_rows($result);

                $query = "select no from general where nation='{$general['nation']}' and city='{$general['city']}'";
                $result = MYDB_query($query, $connect) or Error("processAI16 ".MYDB_error($connect),"");
                $genCount2 = MYDB_num_rows($result);

                $query = "select sum(pop) as sum from city where nation='{$general['nation']}' and supply='1'";
                $result = MYDB_query($query, $connect) or Error("processAI16 ".MYDB_error($connect),"");
                $sumCity = MYDB_fetch_array($result);
                // 현도시 인구 비율
                $ratio  = round($city['pop'] / $sumCity['sum'] * 100);
                // 현도시 장수 비율
                $ratio2 = round($genCount2 / $genCount * 100);
                $ratio3 = rand() % 100;
                // 전체 인구 대비 확률로 현지에서 징병
                if($city['pop'] > 40000 && 100 + $ratio - $ratio2 > $ratio3) {
                    $command = EncodeCommand(0, 0, 0, 11);  //인구 되면 징병
                } else {
                    // 인구 안되면 4만 이상인 도시로 워프
                    $query = "select city from city where nation='{$general['nation']}' and pop>40000 and supply='1' order by rand() limit 0,1";
                    $result = MYDB_query($query, $connect) or Error("processAI16 ".MYDB_error($connect),"");
                    $cityCount = MYDB_num_rows($result);
                    if($cityCount > 0) {
                        $selCity = MYDB_fetch_array($result);
                        //워프
                        $query = "update general set city='{$selCity['city']}' where no='{$general['no']}'";
                        MYDB_query($query, $connect) or Error("processAI18 ".MYDB_error($connect),"");

                        $command = EncodeCommand(0, 0, 0, 50);  //요양
                        $query = "update general set turn0='$command' where no='{$general['no']}'";
                        MYDB_query($query, $connect) or Error("processAI23 ".MYDB_error($connect),"");
                        return;
                    } else {
                        $command = EncodeCommand(0, 0, 0, 7);  //인구 안되면 정장
                    }
                }
            } elseif($general['crew'] >= 700 && $general['train'] < 90) {
                if($general['atmos'] >= 90 && $general['train'] >= 60 && $general['mode'] == 0) {
                    $query = "update general set mode=1 where no='{$general['no']}'";
                    MYDB_query($query, $connect) or Error("processAI05 ".MYDB_error($connect),"");
                }
                $command = EncodeCommand(0, 0, 0, 13);  //훈련
            } elseif($general['crew'] >= 700 && $general['atmos'] < 90) {
                if($general['atmos'] >= 60 && $general['train'] >= 90 && $general['mode'] == 0) {
                    $query = "update general set mode=1 where no='{$general['no']}'";
                    MYDB_query($query, $connect) or Error("processAI05 ".MYDB_error($connect),"");
                }
                $command = EncodeCommand(0, 0, 0, 14);  //사기진작
            } elseif($dipState <= 3) {
                $command = EncodeCommand(0, 0, 0, (rand()%2)*8 + 1);   // 준비는 됐으나 아직 선포중이면 내정, 조달
            } else {
                //공격 & 내정
                $paths = explode("|", $city['path']);
                for($i=0; $i < count($paths); $i++) {
                    $query = "select city,nation from city where city='$paths[$i]'";
                    $result = MYDB_query($query, $connect) or Error("processAI21 ".MYDB_error($connect),"");
                    $targetCity = MYDB_fetch_array($result);
                    //소유국이 있는 경우
                    if($targetCity['nation'] != 0 && $targetCity['nation'] != $general['nation']) {
                        $query = "select state from diplomacy where me='{$general['nation']}' and you='{$targetCity['nation']}'";
                        $dipResult = MYDB_query($query, $connect) or Error("processAI22 ".MYDB_error($connect),"");
                        $dip = MYDB_fetch_array($dipResult);
                        //전쟁중인 국가이면 타겟에 포함
                        if($dip['state'] == 0) $target[count($target)] = $targetCity['city'];
                    }
                }
                if(count($target) == 0) {
                    //전방 도시 선택, 30% 확률로 태수 있는 전방으로 워프
                    if(rand()%100 < 30) {
                        $query = "select city from city where nation='{$general['nation']}' and supply='1' and front=1 order by gen1 desc,rand() limit 0,1";
                    } else {
                        $query = "select city from city where nation='{$general['nation']}' and supply='1' and front=1 order by rand() limit 0,1";
                    }
                    $result = MYDB_query($query, $connect) or Error("processAI10 ".MYDB_error($connect),"");
                    $cityCount = MYDB_num_rows($result);
                    if($cityCount == 0) {
                        //도시 수, 랜덤(상위 20%) 선택, 저개발 도시 선택
                        $query = "select city from city where nation='{$general['nation']}' and supply='1'";
                        $result = MYDB_query($query, $connect) or Error("processAI10 ".MYDB_error($connect),"");
                        $cityCount = MYDB_num_rows($result);
                        $citySelect = rand() % (round($cityCount/5) + 1);

                        $query = "select city,(def+wall)/(def2+wall2) as dev from city where nation='{$general['nation']}' and supply='1' order by dev limit {$citySelect},1";
                        $result = MYDB_query($query, $connect) or Error("processAI10 ".MYDB_error($connect),"");
                        $selCity = MYDB_fetch_array($result);
                    } else {
                        $selCity = MYDB_fetch_array($result);
                    }

                    if($general['city'] != $selCity['city']) {
                        //워프
                        $query = "update general set city='{$selCity['city']}' where no='{$general['no']}'";
                        MYDB_query($query, $connect) or Error("processAI18 ".MYDB_error($connect),"");

                        $command = EncodeCommand(0, 0, 0, 50);  //요양
                        $query = "update general set turn0='$command' where no='{$general['no']}'";
                        MYDB_query($query, $connect) or Error("processAI23 ".MYDB_error($connect),"");
                        return;
                    } else {
                        $command = EncodeCommand(0, 0, 0, (rand()%2)*8 + 1); //공격 가능도시가 없고 워프도 안되면 내정, 조달
                    }
                } elseif($nation['war'] == 1) {
                    //전금이면 내정, 조달
                    $command = EncodeCommand(0, 0, 0, (rand()%2)*8 + 1);   //내정, 조달
                } else { $command = EncodeCommand(0, 0, $target[rand()%count($target)], 16); }  //있으면 공격
            }
        }
    }

    switch($command) {
    case "00000000000001": //내정
        SetDevelop($connect, $genType, $general['no'], $general['city'], $nation['tech']);
        return;
    case "00000000000011": //징병
        $query = "select region from city where nation='{$general['nation']}' order by rand() limit 0,1";
        $result = MYDB_query($query, $connect) or Error("processAI16 ".MYDB_error($connect),"");
        $selRegion = MYDB_fetch_array($result);

        $selCity['city'] = 0;
        // 90% 확률로 이민족 또는 특성병
        if(rand()%100 < 90) {
            $query = "select city from city where nation='{$general['nation']}' and (level='4' or level='8') order by rand() limit 0,1";
            $result = MYDB_query($query, $connect) or Error("processAI16 ".MYDB_error($connect),"");
            $selCity = MYDB_fetch_array($result);
        }
        // 특병 없으면 원래대로
        if($selCity['city'] == 0) {
            $selCity['city'] = $general['city'];
        }
        SetCrew($connect, $general['no'], $general['personal'], $general['gold'], $general['leader'], $genType, $nation['tech'], $selRegion['region'], $selCity['city'], $general[dex0], $general[dex10], $general[dex20], $general[dex30], $general[dex40]);
        return;
    default:
        $query = "update general set turn0='$command' where no='{$general['no']}'";
        MYDB_query($query, $connect) or Error("processAI23 ".MYDB_error($connect),"");
        return;
    }
}
//종전하기, 지급율
//$command = $fourth * 100000000 + $type * 100000 + $crew * 100 + 11;

function RegNation($connect,
        $name, $color, $gold, $rice, $scoutmsg, $tech, $gencount, $type, $level) {
    $type = NationCharCall($type);
    $totaltech = $tech * $gencount;

    @MYDB_query("
        insert into nation (
            name,color,gold,rice,bill,rate,scout,war,tricklimit,surlimit,
            scoutmsg,tech,totaltech,type,level,gennum
        ) values (
            '$name','$color','$gold','$rice','100','15','0','0','24','72',
            '$scoutmsg','$tech','$totaltech','$type','$level','$gencount'
        )", $connect
    ) or Error(__LINE__.MYDB_error($connect),"");
}

function RegCity($connect, $nation, $name, $cap=0) {
    $city = CityCall($name);
    @MYDB_query("update city set nation='$nation' where city='$city'",$connect) or Error(__LINE__.MYDB_error($connect),"");
    if($cap > 0) {
        @MYDB_query("update nation set capital='$city' where nation='$nation'",$connect) or Error(__LINE__.MYDB_error($connect),"");
    }
}

function Promotion($connect, $nation, $level) {
    $lv = getNationChiefLevel($level);

    $query = "select scenario,killturn from game where no='1'";
    $result = MYDB_query($query, $connect) or Error("processAI00 ".MYDB_error($connect),"");
    $admin = MYDB_fetch_array($result);

    //우선 수뇌 해제 (승상 뺴고)
    $query = "update general set level=1 where level<11 and level>4 and nation='$nation'";
    MYDB_query($query, $connect) or Error("Promotion_02 ".MYDB_error($connect),"");

    //유저 후보 선택
    $query = "select no from general where nation='$nation' and npc<2 and level=1 and belong>=3 and killturn>='{$admin['killturn']}' order by rand() limit 0,1";
    $result = MYDB_query($query, $connect) or Error("Promotion_00 ".MYDB_error($connect),"");
    $userCandidate = MYDB_fetch_array($result);
    // 유저수뇌 안함
    //$userCandidate['no'] = 0;
    
    //NPC 후보 선택
    $query = "select no from general where nation='$nation' and npc>=2 and level=1 order by intel desc limit 0,1";
    $result = MYDB_query($query, $connect) or Error("Promotion_00 ".MYDB_error($connect),"");
    $npcCandidate = MYDB_fetch_array($result);

    //현재 참모
    $query = "select no,intel,npc,killturn from general where nation='$nation' and level=11";
    $result = MYDB_query($query, $connect) or Error("Promotion_00 ".MYDB_error($connect),"");
    $level11 = MYDB_fetch_array($result);

    //공석이거나 삭턴 유저 참모인 경우
    if($level11['no'] == 0 || ($level11['npc'] < 2 && $level11['killturn'] < $admin['killturn'])) {
        if($userCandidate['no'] > 0) {
            //기존 참모 해임
            $query = "update general set level=1 where nation='$nation' and level=11";
            MYDB_query($query, $connect) or Error("Promotion_02 ".MYDB_error($connect),"");
            //유저 후보 있으면 임명
            $query = "update general set level=11 where no='{$userCandidate['no']}'";
            MYDB_query($query, $connect) or Error("Promotion_02 ".MYDB_error($connect),"");
        } elseif($npcCandidate['no'] > 0) {
            //기존 참모 해임
            $query = "update general set level=1 where nation='$nation' and level=11";
            MYDB_query($query, $connect) or Error("Promotion_02 ".MYDB_error($connect),"");
            //NPC 후보 있으면 임명
            $query = "update general set level=11 where no='{$npcCandidate['no']}'";
            MYDB_query($query, $connect) or Error("Promotion_02 ".MYDB_error($connect),"");
        }
    } elseif($level11['npc'] >= 2 && $userCandidate['no'] > 0) {
        //NPC 참모인데 삭턴 아닌 유저장이 있는 경우
        //기존 참모 해임
        $query = "update general set level=1 where nation='$nation' and level=11";
        MYDB_query($query, $connect) or Error("Promotion_02 ".MYDB_error($connect),"");
        //유저 후보 있으면 임명
        $query = "update general set level=11 where no='{$userCandidate['no']}'";
        MYDB_query($query, $connect) or Error("Promotion_02 ".MYDB_error($connect),"");
    }

    for($i=10; $i >= $lv; $i-=2) {
        $i1 = $i;   $i2 = $i - 1;
        //무관임명
        $query = "select no from general where nation='$nation' and level=1 order by power desc limit 0,1";
        $result = MYDB_query($query, $connect) or Error("Promotion_00 ".MYDB_error($connect),"");
        $level = MYDB_fetch_array($result);
        $query = "update general set level={$i1} where no='{$level['no']}'";
        MYDB_query($query, $connect) or Error("Promotion_02 ".MYDB_error($connect),"");
        //문관임명
        $query = "select no from general where nation='$nation' and level=1 order by intel desc limit 0,1";
        $result = MYDB_query($query, $connect) or Error("Promotion_00 ".MYDB_error($connect),"");
        $level = MYDB_fetch_array($result);
        $query = "update general set level={$i2} where no='{$level['no']}'";
        MYDB_query($query, $connect) or Error("Promotion_02 ".MYDB_error($connect),"");
    }
}

function TaxRate($connect, $nation) {
    //도시
    $query = "select city from city where nation='$nation'";
    $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
    $cityCount = MYDB_num_rows($result);

    if($cityCount == 0) {
        $query = "update nation set war=0,rate=15 where nation='$nation'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        return 15;
    } else {
        $query = "select sum(pop)/sum(pop2)*100 as rate,(sum(agri)+sum(comm)+sum(secu)+sum(def)+sum(wall))/(sum(agri2)+sum(comm2)+sum(secu2)+sum(def2)+sum(wall2))*100 as dev from city where nation='$nation'";
        $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        $devRate = MYDB_fetch_array($result);

        $avg = ($devRate['rate'] + $devRate['dev']) / 2;

        if($avg > 95) $rate = 25;
        elseif($avg > 70) $rate = 20;
        elseif($avg > 50) $rate = 15;
        else $rate = 10;

        $query = "update nation set war=0,rate='$rate' where nation='$nation'";
        MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
        return $rate;
    }
}

function GoldBillRate($connect, $nation, $rate, $gold_rate, $type, $gold) {
    $incomeList = getGoldIncome($connect, $nation, $rate, $gold_rate, $type);
    $income = $gold + $incomeList[0] + $incomeList[1];
    $outcome = getGoldOutcome($connect, $nation, 100);    // 100%의 지급량
    $bill = floor($income / $outcome * 90); // 수입의 90% 만 지급

    if($bill < 20)  { $bill = 20; }
    if($bill > 200) { $bill = 200; }

    $query = "update nation set bill='$bill' where nation='$nation'";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
}

function RiceBillRate($connect, $nation, $rate, $rice_rate, $type, $rice) {
    $incomeList = getRiceIncome($connect, $nation, $rate, $rice_rate, $type);
    $income = $rice + $incomeList[0] + $incomeList[1];
    $outcome = getRiceOutcome($connect, $nation, 100);    // 100%의 지급량
    $bill = floor($income / $outcome * 90); // 수입의 90% 만 지급

    if($bill < 20)  { $bill = 20; }
    if($bill > 200) { $bill = 200; }

    $query = "update nation set bill='$bill' where nation='$nation'";
    MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
}

