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
$result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$admin = MYDB_fetch_array($result);
$fiction = $admin['fiction'];    $turnterm = $admin['turnterm'];    $startyear = $admin['startyear'];    $year = $admin['year'];    $extend = $admin['extend'];
$img = $admin['img'];
//IF모드1 : 191년 백마장군의 위세

//국가1 원소 국가2 공손찬 국가3 동탁 국가4 원술 국가5 유언 국가6 마등 국가7 유표
//국가8 장로 국가9 유우 국가10 공손도 국가11 장양 국가12 공주 국가13 장연

//////////////////////////국가1/////////////////////////////////////////////////
RegNation($connect, "원소", "FFFF00", 10000, 10000, "4세 5공 명문 혈통 기주 패자 원소", 1000, 40, "법가", 2);
//////////////////////////국가2/////////////////////////////////////////////////
RegNation($connect, "공손찬", "FF00FF", 10000, 10000, "병주 패자 북평 태수 공손찬", 1000, 26, "병가", 3);
//////////////////////////국가3/////////////////////////////////////////////////
RegNation($connect, "동탁", "A9A9A9", 10000, 10000, "천하 권력을 노리는 동탁", 1500, 25, "법가", 5);
//////////////////////////국가4/////////////////////////////////////////////////
RegNation($connect, "원술", "FFC0CB", 10000, 10000, "4세 5공 명문 혈통 원소의 사촌 원술", 1000, 25, "병가", 2);
//////////////////////////국가5/////////////////////////////////////////////////
RegNation($connect, "유언", "483D8B", 10000, 10000, "익주 주자사 유언", 1000, 10, "유가", 3);
//////////////////////////국가6/////////////////////////////////////////////////
RegNation($connect, "마등", "808000", 10000, 10000, "서량 태수 마등", 1000, 9, "병가", 3);
//////////////////////////국가7/////////////////////////////////////////////////
RegNation($connect, "유표", "E0FFFF", 10000, 10000, "강하팔준 명성의 유표", 1000, 9, "유가", 3);
//////////////////////////국가8/////////////////////////////////////////////////
RegNation($connect, "장로", "20B2AA", 10000, 10000, "한중의 오두미도 장로", 1000, 7, "오두미도", 1);
//////////////////////////국가9/////////////////////////////////////////////////
RegNation($connect, "유우", "6495ED", 10000, 10000, "황실 혈통 유우", 1000, 4, "유가", 3);
//////////////////////////국가10////////////////////////////////////////////////
RegNation($connect, "공손도", "A0522D", 10000, 10000, "공손도", 1000, 3, "도적", 1);
//////////////////////////국가11////////////////////////////////////////////////
RegNation($connect, "장양", "483D8B", 10000, 10000, "장양", 1000, 3, "묵가", 3);
//////////////////////////국가12////////////////////////////////////////////////
RegNation($connect, "공주", "800080", 10000, 10000, "공주", 1000, 3, "도가", 3);
//////////////////////////국가13////////////////////////////////////////////////
RegNation($connect, "장연", "87CEEB", 10000, 10000, "장연", 1000, 3, "도가", 2);

//////////////////////////외교//////////////////////////////////////////////////
$query = "insert into diplomacy (me, you, state, term) values ('1', '2', '2', '0')";
MYDB_query($query, $connect) or Error("scenario_190B ".MYDB_error($connect),"");
$query = "insert into diplomacy (me, you, state, term) values ('1', '3', '2', '0')";
MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$query = "insert into diplomacy (me, you, state, term) values ('1', '4', '2', '0')";
MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$query = "insert into diplomacy (me, you, state, term) values ('1', '5', '2', '0')";
MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$query = "insert into diplomacy (me, you, state, term) values ('1', '6', '2', '0')";
MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$query = "insert into diplomacy (me, you, state, term) values ('1', '7', '2', '0')";
MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$query = "insert into diplomacy (me, you, state, term) values ('1', '8', '2', '0')";
MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$query = "insert into diplomacy (me, you, state, term) values ('1', '9', '2', '0')";
MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$query = "insert into diplomacy (me, you, state, term) values ('1', '10', '2', '0')";
MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$query = "insert into diplomacy (me, you, state, term) values ('1', '11', '2', '0')";
MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$query = "insert into diplomacy (me, you, state, term) values ('1', '12', '2', '0')";
MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$query = "insert into diplomacy (me, you, state, term) values ('1', '13', '2', '0')";
MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

$query = "insert into diplomacy (me, you, state, term) values ('2', '1', '2', '0')";
MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$query = "insert into diplomacy (me, you, state, term) values ('2', '3', '2', '0')";
MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$query = "insert into diplomacy (me, you, state, term) values ('2', '4', '2', '0')";
MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$query = "insert into diplomacy (me, you, state, term) values ('2', '5', '2', '0')";
MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$query = "insert into diplomacy (me, you, state, term) values ('2', '6', '2', '0')";
MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$query = "insert into diplomacy (me, you, state, term) values ('2', '7', '2', '0')";
MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$query = "insert into diplomacy (me, you, state, term) values ('2', '8', '2', '0')";
MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$query = "insert into diplomacy (me, you, state, term) values ('2', '9', '2', '0')";
MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$query = "insert into diplomacy (me, you, state, term) values ('2', '10', '2', '0')";
MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$query = "insert into diplomacy (me, you, state, term) values ('2', '11', '2', '0')";
MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$query = "insert into diplomacy (me, you, state, term) values ('2', '12', '2', '0')";
MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$query = "insert into diplomacy (me, you, state, term) values ('2', '13', '2', '0')";
MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

$query = "insert into diplomacy (me, you, state, term) values ('3', '1', '2', '0')";
MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$query = "insert into diplomacy (me, you, state, term) values ('3', '2', '2', '0')";
MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$query = "insert into diplomacy (me, you, state, term) values ('3', '4', '2', '0')";
MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$query = "insert into diplomacy (me, you, state, term) values ('3', '5', '2', '0')";
MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$query = "insert into diplomacy (me, you, state, term) values ('3', '6', '2', '0')";
MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$query = "insert into diplomacy (me, you, state, term) values ('3', '7', '2', '0')";
MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$query = "insert into diplomacy (me, you, state, term) values ('3', '8', '2', '0')";
MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$query = "insert into diplomacy (me, you, state, term) values ('3', '9', '2', '0')";
MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$query = "insert into diplomacy (me, you, state, term) values ('3', '10', '2', '0')";
MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$query = "insert into diplomacy (me, you, state, term) values ('3', '11', '2', '0')";
MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$query = "insert into diplomacy (me, you, state, term) values ('3', '12', '2', '0')";
MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$query = "insert into diplomacy (me, you, state, term) values ('3', '13', '2', '0')";
MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

$query = "insert into diplomacy (me, you, state, term) values ('4', '1', '2', '0')";
MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$query = "insert into diplomacy (me, you, state, term) values ('4', '2', '2', '0')";
MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$query = "insert into diplomacy (me, you, state, term) values ('4', '3', '2', '0')";
MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$query = "insert into diplomacy (me, you, state, term) values ('4', '5', '2', '0')";
MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$query = "insert into diplomacy (me, you, state, term) values ('4', '6', '2', '0')";
MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$query = "insert into diplomacy (me, you, state, term) values ('4', '7', '2', '0')";
MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$query = "insert into diplomacy (me, you, state, term) values ('4', '8', '2', '0')";
MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$query = "insert into diplomacy (me, you, state, term) values ('4', '9', '2', '0')";
MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$query = "insert into diplomacy (me, you, state, term) values ('4', '10', '2', '0')";
MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$query = "insert into diplomacy (me, you, state, term) values ('4', '11', '2', '0')";
MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$query = "insert into diplomacy (me, you, state, term) values ('4', '12', '2', '0')";
MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$query = "insert into diplomacy (me, you, state, term) values ('4', '13', '2', '0')";
MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

$query = "insert into diplomacy (me, you, state, term) values ('5', '1', '2', '0')";
MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$query = "insert into diplomacy (me, you, state, term) values ('5', '2', '2', '0')";
MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$query = "insert into diplomacy (me, you, state, term) values ('5', '3', '2', '0')";
MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$query = "insert into diplomacy (me, you, state, term) values ('5', '4', '2', '0')";
MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$query = "insert into diplomacy (me, you, state, term) values ('5', '6', '2', '0')";
MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$query = "insert into diplomacy (me, you, state, term) values ('5', '7', '2', '0')";
MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$query = "insert into diplomacy (me, you, state, term) values ('5', '8', '2', '0')";
MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$query = "insert into diplomacy (me, you, state, term) values ('5', '9', '2', '0')";
MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$query = "insert into diplomacy (me, you, state, term) values ('5', '10', '2', '0')";
MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$query = "insert into diplomacy (me, you, state, term) values ('5', '11', '2', '0')";
MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$query = "insert into diplomacy (me, you, state, term) values ('5', '12', '2', '0')";
MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$query = "insert into diplomacy (me, you, state, term) values ('5', '13', '2', '0')";
MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

$query = "insert into diplomacy (me, you, state, term) values ('6', '1', '2', '0')";
MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$query = "insert into diplomacy (me, you, state, term) values ('6', '2', '2', '0')";
MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$query = "insert into diplomacy (me, you, state, term) values ('6', '3', '2', '0')";
MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$query = "insert into diplomacy (me, you, state, term) values ('6', '4', '2', '0')";
MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$query = "insert into diplomacy (me, you, state, term) values ('6', '5', '2', '0')";
MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$query = "insert into diplomacy (me, you, state, term) values ('6', '7', '2', '0')";
MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$query = "insert into diplomacy (me, you, state, term) values ('6', '8', '2', '0')";
MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$query = "insert into diplomacy (me, you, state, term) values ('6', '9', '2', '0')";
MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$query = "insert into diplomacy (me, you, state, term) values ('6', '10', '2', '0')";
MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$query = "insert into diplomacy (me, you, state, term) values ('6', '11', '2', '0')";
MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$query = "insert into diplomacy (me, you, state, term) values ('6', '12', '2', '0')";
MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$query = "insert into diplomacy (me, you, state, term) values ('6', '13', '2', '0')";
MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

$query = "insert into diplomacy (me, you, state, term) values ('7', '1', '2', '0')";
MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$query = "insert into diplomacy (me, you, state, term) values ('7', '2', '2', '0')";
MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$query = "insert into diplomacy (me, you, state, term) values ('7', '3', '2', '0')";
MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$query = "insert into diplomacy (me, you, state, term) values ('7', '4', '2', '0')";
MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$query = "insert into diplomacy (me, you, state, term) values ('7', '5', '2', '0')";
MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$query = "insert into diplomacy (me, you, state, term) values ('7', '6', '2', '0')";
MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$query = "insert into diplomacy (me, you, state, term) values ('7', '8', '2', '0')";
MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$query = "insert into diplomacy (me, you, state, term) values ('7', '9', '2', '0')";
MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$query = "insert into diplomacy (me, you, state, term) values ('7', '10', '2', '0')";
MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$query = "insert into diplomacy (me, you, state, term) values ('7', '11', '2', '0')";
MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$query = "insert into diplomacy (me, you, state, term) values ('7', '12', '2', '0')";
MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$query = "insert into diplomacy (me, you, state, term) values ('7', '13', '2', '0')";
MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

$query = "insert into diplomacy (me, you, state, term) values ('8', '1', '2', '0')";
MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$query = "insert into diplomacy (me, you, state, term) values ('8', '2', '2', '0')";
MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$query = "insert into diplomacy (me, you, state, term) values ('8', '3', '2', '0')";
MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$query = "insert into diplomacy (me, you, state, term) values ('8', '4', '2', '0')";
MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$query = "insert into diplomacy (me, you, state, term) values ('8', '5', '2', '0')";
MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$query = "insert into diplomacy (me, you, state, term) values ('8', '6', '2', '0')";
MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$query = "insert into diplomacy (me, you, state, term) values ('8', '7', '2', '0')";
MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$query = "insert into diplomacy (me, you, state, term) values ('8', '9', '2', '0')";
MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$query = "insert into diplomacy (me, you, state, term) values ('8', '10', '2', '0')";
MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$query = "insert into diplomacy (me, you, state, term) values ('8', '11', '2', '0')";
MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$query = "insert into diplomacy (me, you, state, term) values ('8', '12', '2', '0')";
MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$query = "insert into diplomacy (me, you, state, term) values ('8', '13', '2', '0')";
MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

$query = "insert into diplomacy (me, you, state, term) values ('9', '1', '2', '0')";
MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$query = "insert into diplomacy (me, you, state, term) values ('9', '2', '2', '0')";
MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$query = "insert into diplomacy (me, you, state, term) values ('9', '3', '2', '0')";
MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$query = "insert into diplomacy (me, you, state, term) values ('9', '4', '2', '0')";
MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$query = "insert into diplomacy (me, you, state, term) values ('9', '5', '2', '0')";
MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$query = "insert into diplomacy (me, you, state, term) values ('9', '6', '2', '0')";
MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$query = "insert into diplomacy (me, you, state, term) values ('9', '7', '2', '0')";
MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$query = "insert into diplomacy (me, you, state, term) values ('9', '8', '2', '0')";
MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$query = "insert into diplomacy (me, you, state, term) values ('9', '10', '2', '0')";
MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$query = "insert into diplomacy (me, you, state, term) values ('9', '11', '2', '0')";
MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$query = "insert into diplomacy (me, you, state, term) values ('9', '12', '2', '0')";
MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$query = "insert into diplomacy (me, you, state, term) values ('9', '13', '2', '0')";
MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

$query = "insert into diplomacy (me, you, state, term) values ('10', '1', '2', '0')";
MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$query = "insert into diplomacy (me, you, state, term) values ('10', '2', '2', '0')";
MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$query = "insert into diplomacy (me, you, state, term) values ('10', '3', '2', '0')";
MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$query = "insert into diplomacy (me, you, state, term) values ('10', '4', '2', '0')";
MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$query = "insert into diplomacy (me, you, state, term) values ('10', '5', '2', '0')";
MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$query = "insert into diplomacy (me, you, state, term) values ('10', '6', '2', '0')";
MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$query = "insert into diplomacy (me, you, state, term) values ('10', '7', '2', '0')";
MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$query = "insert into diplomacy (me, you, state, term) values ('10', '8', '2', '0')";
MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$query = "insert into diplomacy (me, you, state, term) values ('10', '9', '2', '0')";
MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$query = "insert into diplomacy (me, you, state, term) values ('10', '11', '2', '0')";
MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$query = "insert into diplomacy (me, you, state, term) values ('10', '12', '2', '0')";
MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$query = "insert into diplomacy (me, you, state, term) values ('10', '13', '2', '0')";
MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

$query = "insert into diplomacy (me, you, state, term) values ('11', '1', '2', '0')";
MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$query = "insert into diplomacy (me, you, state, term) values ('11', '2', '2', '0')";
MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$query = "insert into diplomacy (me, you, state, term) values ('11', '3', '2', '0')";
MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$query = "insert into diplomacy (me, you, state, term) values ('11', '4', '2', '0')";
MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$query = "insert into diplomacy (me, you, state, term) values ('11', '5', '2', '0')";
MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$query = "insert into diplomacy (me, you, state, term) values ('11', '6', '2', '0')";
MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$query = "insert into diplomacy (me, you, state, term) values ('11', '7', '2', '0')";
MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$query = "insert into diplomacy (me, you, state, term) values ('11', '8', '2', '0')";
MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$query = "insert into diplomacy (me, you, state, term) values ('11', '9', '2', '0')";
MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$query = "insert into diplomacy (me, you, state, term) values ('11', '10', '2', '0')";
MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$query = "insert into diplomacy (me, you, state, term) values ('11', '12', '2', '0')";
MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$query = "insert into diplomacy (me, you, state, term) values ('11', '13', '2', '0')";
MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

$query = "insert into diplomacy (me, you, state, term) values ('12', '1', '2', '0')";
MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$query = "insert into diplomacy (me, you, state, term) values ('12', '2', '2', '0')";
MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$query = "insert into diplomacy (me, you, state, term) values ('12', '3', '2', '0')";
MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$query = "insert into diplomacy (me, you, state, term) values ('12', '4', '2', '0')";
MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$query = "insert into diplomacy (me, you, state, term) values ('12', '5', '2', '0')";
MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$query = "insert into diplomacy (me, you, state, term) values ('12', '6', '2', '0')";
MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$query = "insert into diplomacy (me, you, state, term) values ('12', '7', '2', '0')";
MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$query = "insert into diplomacy (me, you, state, term) values ('12', '8', '2', '0')";
MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$query = "insert into diplomacy (me, you, state, term) values ('12', '9', '2', '0')";
MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$query = "insert into diplomacy (me, you, state, term) values ('12', '10', '2', '0')";
MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$query = "insert into diplomacy (me, you, state, term) values ('12', '11', '2', '0')";
MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$query = "insert into diplomacy (me, you, state, term) values ('12', '13', '2', '0')";
MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

$query = "insert into diplomacy (me, you, state, term) values ('13', '1', '2', '0')";
MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$query = "insert into diplomacy (me, you, state, term) values ('13', '2', '2', '0')";
MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$query = "insert into diplomacy (me, you, state, term) values ('13', '3', '2', '0')";
MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$query = "insert into diplomacy (me, you, state, term) values ('13', '4', '2', '0')";
MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$query = "insert into diplomacy (me, you, state, term) values ('13', '5', '2', '0')";
MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$query = "insert into diplomacy (me, you, state, term) values ('13', '6', '2', '0')";
MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$query = "insert into diplomacy (me, you, state, term) values ('13', '7', '2', '0')";
MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$query = "insert into diplomacy (me, you, state, term) values ('13', '8', '2', '0')";
MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$query = "insert into diplomacy (me, you, state, term) values ('13', '9', '2', '0')";
MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$query = "insert into diplomacy (me, you, state, term) values ('13', '10', '2', '0')";
MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$query = "insert into diplomacy (me, you, state, term) values ('13', '11', '2', '0')";
MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$query = "insert into diplomacy (me, you, state, term) values ('13', '12', '2', '0')";
MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
//////////////////////////국가 끝///////////////////////////////////////////////

//////////////////////////장수//////////////////////////////////////////////////
//국가1 원소 국가2 공손찬 국가3 동탁 국가4 원술 국가5 유언 국가6 마등 국가7 유표
//국가8 장로 국가9 유우 국가10 공손도 국가11 장양 국가12 공주 국가13 장연
//                                                               상성       이름       사진 국가  도시   통  무  지 급 출생 사망    꿈     특기
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1001,   1,    "소제1",$img,1001,  0,    "-", 20, 11, 48, 0, 168, 190, "유지",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1002,   1,     "헌제",$img,1002,  0,    "-", 17, 13, 61, 0, 170, 250, "안전",    "-", "한 왕실을 구해줄 이는 진정 없는 것인가...");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1003, 999,   "사마휘",$img,1003,  0,    "-", 71, 11, 96, 0, 173, 234, "은둔", "신산", "좋지, 좋아~");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1004, 999,     "우길",$img,1004,  0,    "-", 17, 13, 83, 0, 131, 200, "은둔", "신산");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1005, 999,     "화타",$img,1005,  0,    "-", 53, 25, 70, 0, 151, 220, "은둔", "의술", "아픈 사람들은 모두 내게 오시오. 껄껄껄.");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1006, 999,     "길평",$img,1006,  0,    "-", 27, 15, 72, 0, 158, 200, "은둔", "의술");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1007,  29,     "가규",$img,1007,  0,    "-", 55, 55, 74, 0, 177, 231, "재간",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1008, 136,     "가범",$img,1008,  0,    "-", 58, 48, 73, 0, 202, 237, "유지",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1009,  49,   "가비능",$img,1009,  0,    "-", 58, 83, 32, 0, 172, 235, "정복",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1010,  31,     "가충",$img,1010,  0,    "-", 50, 25, 87, 0, 217, 282, "출세",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1011,  20,     "가후",$img,1011,  3,    "-", 69, 30, 94, 0, 147, 223, "할거", "귀병");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1012,  73,     "간옹",$img,1012,  0,    "-", 31, 33, 70, 0, 164, 225, "안전", "경작");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1013, 129,     "감녕",$img,1013,  0,    "-", 78, 95, 71, 0, 174, 222, "출세", "무쌍");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1014, 127,     "감택",$img,1014,  0,    "-", 62, 44, 79, 0, 182, 243, "유지",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1015,  60,     "강단",$img,1015,  0,    "-", 41, 73, 43, 0, 168, 230, "재간",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1016,  73,     "강유",$img,1016,  0,    "-", 95, 90, 94, 0, 202, 264, "왕좌", "집중");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1017, 102,     "고간",$img,1017,  1,    "-", 60, 57, 51, 0, 168, 206, "유지",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1018,  27,     "고람",$img,1018,  1,    "-", 72, 67, 59, 0, 159, 201, "출세", "기병");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1019,  69,     "고상",$img,1019,  0,    "-", 41, 40, 38, 0, 194, 252, "재간",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1020, 144,     "고순",$img,1020,  3,    "-", 79, 82, 65, 0, 162, 198, "의협", "기병");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1021,   7,     "고승",$img,1021,  0,    "-", 42, 73, 24, 0, 145, 185, "출세",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1022, 120,     "고옹",$img,1022,  0,    "-", 57, 21, 79, 0, 168, 243, "출세",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1023,  63,     "고정",$img,1023,  0,    "-", 67, 65, 55, 0, 190, 251, "정복",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1024,  53,     "고패",$img,1024,  5,    "-", 53, 56, 28, 0, 170, 212, "유지",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1025,  74,     "공도",$img,1025,  0,    "-", 26, 73, 19, 0, 164, 200, "재간",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1026, 142,   "공손강",$img,1026, 10,    "-", 64, 72, 61, 0, 172, 210, "정복",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1027, 142,   "공손공",$img,1027, 10,    "-", 68, 41, 75, 0, 174, 238, "정복",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1028, 142,   "공손도",$img,1028, 10,    "-", 62, 72, 41,12, 154, 204, "정복", "기병");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1029,  65,   "공손범",$img,1029,  2,    "-", 61, 67, 61, 0, 158, 199, "출세",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1030,  65,   "공손속",$img,1030,  2,    "-", 60, 76, 41, 0, 176, 199, "정복",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1031,  10,   "공손연",$img,1031,  0,    "-", 74, 79, 64, 0, 205, 238, "패권", "돌격");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1032,  65,   "공손월",$img,1032,  2,    "-", 47, 63, 46, 0, 160, 192, "정복",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1033,  65,   "공손찬",$img,1033,  2,    "-", 61, 87, 67,12, 152, 199, "패권", "기병");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1034,  43,     "공융",$img,1034,  2,    "-", 63, 48, 85, 0, 153, 208, "왕좌", "경작");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1035,  35,     "공주",$img,1035, 12,    "-", 64, 35, 78,12, 151, 194, "재간",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1036,  83,     "공지",$img,1036,  0,    "-", 57, 54, 64, 0, 178, 242, "대의",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1037,  26,     "곽가",$img,1037,  0,    "-", 47, 23, 99, 0, 170, 207, "패권", "귀모");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1038, 111,     "곽도",$img,1038,  1,    "-", 63, 67, 81, 0, 155, 205, "재간",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1039,   2,     "곽사",$img,1039,  3,    "-", 58, 67, 31, 0, 146, 197, "패권",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1040,  80,   "곽유지",$img,1040,  0,    "-", 37, 22, 71, 0, 190, 259, "재간", "상재");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1041,  67,     "곽익",$img,1041,  0,    "-", 67, 60, 67, 0, 207, 270, "재간",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1042,  67,     "곽준",$img,1042,  0,    "-", 76, 69, 73, 0, 178, 217, "출세",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1043,  27,     "곽혁",$img,1043,  0,    "-", 40, 29, 80, 0, 187, 228, "안전",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1044,  20,     "곽회",$img,1044,  0,    "-", 77, 75, 71, 0, 187, 255, "정복",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1045,  39,   "관구검",$img,1045,  0,    "-", 72, 68, 77, 0, 202, 255, "대의",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1046,  76,     "관색",$img,1046,  0,    "-", 69, 85, 67, 0, 200, 239, "의협", "징병");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1047,  76,     "관우",$img,1047,  2,    "-", 96, 98, 80, 0, 162, 219, "의협", "위압");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1048,  76,     "관이",$img,1048,  0,    "-", 48, 60, 58, 0, 219, 263, "안전",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1049,  76,     "관통",$img,1049,  0,    "-", 49, 63, 60, 0, 218, 259, "대의",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1050,  76,     "관평",$img,1050,  0,    "-", 77, 80, 70, 0, 186, 219, "의협", "보병");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1051,   7,     "관해",$img,1051,  0,    "-", 66, 90, 35, 0, 160, 193, "출세",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1052,  76,     "관흥",$img,1052,  0,    "-", 69, 84, 72, 0, 199, 234, "의협", "돌격");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1053,  40,     "괴량",$img,1053,  7,    "-", 41, 28, 81, 0, 155, 204, "안전", "신중");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1054,  40,     "괴월",$img,1054,  7,    "-", 26, 30, 84, 0, 157, 214, "유지", "귀병");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1055,  98,     "교모",$img,1055,  0,    "-", 59, 58, 61, 0, 150, 191, "안전",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1056,  98,     "교현",$img,1056,  0,    "-", 50, 18, 60, 0, 158, 210, "재간",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1057,   6,   "구역거",$img,1057,  0,    "-", 51, 72, 49, 0, 152, 193, "안전",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1058,  80,     "극정",$img,1058,  0,    "-", 38, 25, 75, 0, 208, 278, "유지",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1059,  46,     "금선",$img,1059,  0,    "-", 55, 49, 36, 0, 155, 208, "할거",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1060,  62, "금환삼결",$img,1060,  0,    "-", 46, 76, 17, 0, 192, 225, "출세",    "-");
//국가1 원소 국가2 공손찬 국가3 동탁 국가4 원술 국가5 유언 국가6 마등 국가7 유표
//국가8 장로 국가9 유우 국가10 공손도 국가11 장양 국가12 공주 국가13 장연
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1061, 141,     "기령",$img,1061,  4,    "-", 76, 81, 33, 0, 155, 199, "대의", "무쌍");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1062, 122,     "낙통",$img,1062,  0,    "-", 57, 44, 69, 0, 193, 228, "유지",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1063, 124,    "노숙1",$img,1063,  0,    "-", 90, 42, 94, 0, 172, 217, "왕좌", "상재");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1064,  75,     "노식",$img,1064,  0,    "-", 91, 54, 80, 0, 139, 192, "왕좌", "징병", "한 황실의 앞날이 심히 걱정되는구나...");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1065,  59,     "뇌동",$img,1065,  0,    "-", 70, 77, 45, 0, 172, 218, "출세", "궁병");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1066, 142,     "뇌박",$img,1066,  4,    "-", 54, 54, 33, 0, 157, 206, "유지",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1067, 127,     "능조",$img,1067,  0,    "-", 67, 80, 44, 0, 165, 203, "재간", "공성");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1068, 127,     "능통",$img,1068,  0,    "-", 71, 78, 58, 0, 189, 237, "의협", "궁병");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1069,  64,     "단경",$img,1069,  0,    "-", 68, 61, 68, 0, 156, 199, "정복",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1070, 132,     "담웅",$img,1070,  0,    "-", 52, 77, 19, 0, 188, 221, "출세",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1071,  99,     "답둔",$img,1071,  0,    "-", 59, 71, 31, 0, 158, 207, "유지",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1072, 132,     "당자",$img,1072,  0,    "-", 59, 56, 45, 0, 196, 265, "안전",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1073,  60, "대래동주",$img,1073,  0,    "-", 40, 65, 24, 0, 195, 249, "안전",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1074,  82,     "도겸",$img,1074,  2,    "-", 51, 32, 61, 0, 132, 194, "할거", "인덕");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1075, 120,     "도준",$img,1075,  0,    "-", 64, 57, 50, 0, 238, 285, "안전",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1076,  73,     "동궐",$img,1076,  0,    "-", 66, 50, 76, 0, 204, 271, "대의",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1077,  62,   "동다나",$img,1077,  0,    "-", 51, 71, 27, 0, 189, 225, "유지",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1078,   2,     "동민",$img,1078,  3,    "-", 52, 65, 49, 0, 149, 192, "안전",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1079,  21,     "동소",$img,1079,  1,    "-", 46, 46, 62, 0, 156, 236, "패권",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1080, 127,     "동습",$img,1080,  0,    "-", 53, 64, 32, 0, 169, 215, "출세",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1081,  89,     "동승",$img,1081,  3,    "-", 75, 66, 65, 0, 154, 200, "왕좌",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1082,  78,     "동윤",$img,1082,  0,    "-", 64, 26, 78, 0, 192, 246, "왕좌",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1083,   2,     "동탁",$img,1083,  3,    "-", 87, 91, 54,12, 139, 192, "패권", "기병", "곧 기회가 올 것이다...");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1084,  66,     "동화",$img,1084,  0,    "-", 48, 64, 53, 0, 168, 219, "대의",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1085,  32,     "두예",$img,1085,  0,    "-", 88, 80, 84, 0, 222, 284, "정복",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1086,   7,     "등무",$img,1086,  0,    "-", 43, 74, 19, 0, 147, 185, "정복",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1087,  41,     "등애",$img,1087,  0,    "-", 94, 82, 92, 0, 197, 264, "패권", "신산");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1088, 116,     "등윤",$img,1088,  0,    "-", 34, 42, 68, 0, 194, 256, "안전",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1089,  73,     "등지",$img,1089,  0,    "-", 74, 51, 80, 0, 182, 251, "할거", "경작");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1090,  41,     "등충",$img,1090,  0,    "-", 60, 82, 55, 0, 230, 264, "출세",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1091,  54,     "등현",$img,1091,  0,    "-", 65, 59, 61, 0, 188, 248, "출세",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1092,  29,     "마균",$img,1092,  0,    "-", 33, 38, 80, 0, 200, 259, "안전",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1093,  71,     "마대",$img,1093,  0,    "-", 77, 79, 49, 0, 183, 246, "대의", "기병");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1094,  70,     "마등",$img,1094,  6,    "-", 80, 87, 56,12, 149, 211, "왕좌", "기병");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1095,  80,     "마속",$img,1095,  0,    "-", 73, 64, 82, 0, 190, 228, "패권", "집중");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1096,  77,     "마량",$img,1096,  0,    "-", 57, 25, 87, 0, 187, 225, "왕좌",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1097,  48,     "마완",$img,1097,  6,    "-", 49, 64, 26, 0, 170, 211, "안전", "기병");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1098,  19,     "마준",$img,1098,  0,    "-", 45, 63, 62, 0, 196, 260, "안전",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1099,  71,     "마철",$img,1099,  0,    "-", 71, 60, 31, 0, 179, 211, "대의", "기병");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1100,  70,     "마초",$img,1100,  6,    "-", 78, 97, 40, 0, 176, 226, "대의", "기병");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1101, 131,    "마충1",$img,1101,  0,    "-", 67, 62, 51, 0, 186, 222, "재간",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1102,  69,    "마충2",$img,1102,  0,    "-", 61, 68, 51, 0, 187, 249, "출세",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1103,  71,     "마휴",$img,1103,  0,    "-", 71, 60, 32, 0, 178, 211, "대의", "기병");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1104, 116,     "만총",$img,1104,  0,    "-", 79, 40, 78, 0, 170, 242, "할거", "신중");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1105,  51,   "망아장",$img,1105,  0,    "-", 29, 64, 20, 0, 191, 225, "출세",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1106,  44,     "맹달",$img,1106,  0,    "-", 70, 66, 72, 0, 172, 228, "할거", "귀병");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1107,  60,     "맹우",$img,1107,  0,    "-", 63, 79, 26, 0, 190, 251, "정복",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1108,  60,     "맹획",$img,1108,  0,    "-", 78, 92, 50, 0, 186, 245, "왕좌", "격노");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1109,  29,     "모개",$img,1109,  0,    "-", 46, 56, 56, 0, 161, 216, "대의",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1110,  51, "목록대왕",$img,1110,  0,    "-", 58, 71, 65, 0, 184, 225, "재간", "척사");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1111,  96,     "목순",$img,1111,  0,    "-", 17, 21, 68, 0, 157, 191, "왕좌",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1112,  43,   "무안국",$img,1112,  0,    "-", 51, 73, 18, 0, 156, 191, "재간",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1113,  28,     "문빙",$img,1113,  0,    "-", 70, 77, 43, 0, 178, 237, "유지",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1114,  38,     "문앙",$img,1114,  0,    "-", 71, 91, 46, 0, 222, 285, "대의",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1115, 102,     "문추",$img,1115,  1,    "-", 72, 94, 25, 0, 161, 200, "출세", "무쌍");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1116,  38,     "문흠",$img,1116,  0,    "-", 76, 77, 43, 0, 200, 258, "대의",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1117,  49, "미당대왕",$img,1117,  0,    "-", 64, 75, 32, 0, 202, 260, "유지",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1118, 108,     "미방",$img,1118,  2,    "-", 58, 65, 37, 0, 169, 222, "패권", "징병");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1119,  77,     "미축",$img,1119,  2,    "-", 26, 30, 65, 0, 165, 220, "왕좌", "상재");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1120,  94,     "반봉",$img,1120,  0,    "-", 61, 75, 17, 0, 155, 191, "출세",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1121,  44,     "반준",$img,1121,  0,    "-", 41, 21, 67, 0, 174, 239, "안전",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1122,  65,     "방덕",$img,1122,  6,    "-", 76, 90, 67, 0, 170, 219, "의협", "기병");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1123,  73,     "방통",$img,1123,  0,    "-", 86, 41, 97, 0, 179, 214, "패권", "반계");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1124,  65,     "방회",$img,1124,  0,    "-", 25, 33, 59, 0, 205, 272, "안전",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1125,  68,   "배원소",$img,1125,  0,    "-", 45, 69, 33, 0, 169, 200, "재간",    "-");
//국가1 원소 국가2 공손찬 국가3 동탁 국가4 원술 국가5 유언 국가6 마등 국가7 유표
//국가8 장로 국가9 유우 국가10 공손도 국가11 장양 국가12 공주 국가13 장연
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1126,  78,     "번건",$img,1126,  0,    "-", 28, 31, 68, 0, 205, 270, "대의",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1127, 149,     "번주",$img,1127,  3,    "-", 67, 77, 21, 0, 149, 192, "할거",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1128,  72,     "법정",$img,1128,  0,    "-", 81, 29, 93, 0, 176, 220, "패권", "신산");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1129,   8,     "변희",$img,1129, 13,    "-", 65, 65, 27, 0, 169, 200, "안전",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1130, 121,     "보질",$img,1130,  0,    "-", 58, 28, 77, 0, 177, 247, "유지",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1131, 113,   "복양흥",$img,1131,  0,    "-", 58, 51, 71, 0, 224, 264, "유지",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1132, 110,     "봉기",$img,1132,  1,    "-", 68, 52, 80, 0, 153, 202, "패권", "집중");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1133,  74,     "부동",$img,1133,  0,    "-", 58, 69, 69, 0, 183, 222, "대의",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1134, 108,   "부사인",$img,1134,  0,    "-", 54, 59, 51, 0, 182, 222, "출세",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1135,  38,     "부손",$img,1135,  0,    "-", 24, 43, 68, 0, 162, 230, "안전",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1136,  74,     "부첨",$img,1136,  0,    "-", 61, 74, 45, 0, 216, 263, "대의",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1137,  66,     "비시",$img,1137,  0,    "-", 18, 36, 61, 0, 176, 240, "재간",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1138, 141,     "비연",$img,1138,  0,    "-", 66, 65, 53, 0, 196, 238, "출세",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1139,  77,     "비위",$img,1139,  0,    "-", 72, 26, 73, 0, 193, 253, "대의",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1140, 144,     "사광",$img,1140,  0,    "-", 57, 49, 66, 0, 175, 235, "안전",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1141,  71,   "사마가",$img,1141,  0,    "-", 61, 85, 18, 0, 167, 222, "정복", "돌격");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1142,  20,   "사마랑",$img,1142,  0,    "-", 25, 32, 63, 0, 171, 217, "대의",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1143,  24,   "사마망",$img,1143,  0,    "-", 71, 61, 65, 0, 205, 271, "정복",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1144,  24,   "사마부",$img,1144,  0,    "-", 55, 31, 73, 0, 180, 272, "대의",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1145,  31,   "사마사",$img,1145,  0,    "-", 87, 64, 91, 0, 208, 255, "정복",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1146,  31,   "사마소",$img,1146,  0,    "-", 93, 63, 84, 0, 211, 265, "정복",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1147,  31,   "사마염",$img,1147,  0,    "-", 92, 78, 72, 0, 236, 290, "패권",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1148,  30,   "사마유",$img,1148,  0,    "-", 62, 45, 79, 0, 248, 283, "왕좌",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1149,  31,   "사마의",$img,1149,  0,    "-", 98, 67, 98, 0, 179, 251, "패권", "반계");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1150, 139,     "사섭",$img,1150,  0,    "-", 63, 61, 71, 0, 137, 226, "안전",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1151, 139,     "사일",$img,1151,  0,    "-", 59, 44, 68, 0, 153, 230, "유지",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1152, 132,     "사정",$img,1152,  0,    "-", 67, 71, 20, 0, 178, 221, "안전",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1153, 146,     "사지",$img,1153,  0,    "-", 61, 49, 70, 0, 163, 227, "안전",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1154, 144,     "사휘",$img,1154,  0,    "-", 67, 71, 61, 0, 165, 227, "유지",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1155,  35,     "서막",$img,1155,  0,    "-", 56, 41, 72, 0, 171, 249, "유지",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1156,  76,     "서서",$img,1156,  0,    "-", 90, 70, 96, 0, 178, 232, "의협", "귀병");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1157, 124,     "서성",$img,1157,  0,    "-", 83, 76, 83, 0, 177, 234, "대의",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1158, 142,     "서영",$img,1158,  0,    "-", 47, 63, 33, 0, 147, 191, "안전",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1159,  23,     "서질",$img,1159,  0,    "-", 55, 73, 34, 0, 207, 253, "재간",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1160,  23,     "서황",$img,1160,  0,    "-", 79, 89, 68, 0, 165, 228, "의협", "필살");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1161,  32,     "석포",$img,1161,  0,    "-", 71, 63, 59, 0, 214, 272, "정복",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1162, 131,     "설영",$img,1162,  0,    "-", 46, 23, 64, 0, 223, 282, "유지",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1163, 128,     "설종",$img,1163,  0,    "-", 27, 33, 67, 0, 187, 243, "유지",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1164,  69,     "성의",$img,1164,  6,    "-", 45, 64, 22, 0, 168, 211, "재간",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1165, 129,     "소비",$img,1165,  0,    "-", 67, 63, 49, 0, 172, 221, "대의",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1166,  76,     "손건",$img,1166,  0,    "-", 42, 33, 73, 0, 165, 215, "대의", "거상");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1167, 125,     "손견",$img,1167,  4,    "-", 96, 95, 76, 0, 156, 192, "왕좌", "무쌍", "나는 강동의 호랑이 손견이올씨다!");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1168, 126,     "손광",$img,1168,  0,    "-", 63, 54, 58, 0, 186, 207, "유지",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1169, 125,     "손권",$img,1169,  0,    "-", 90, 77, 83, 0, 182, 252, "할거", "수비");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1170, 126,     "손랑",$img,1170,  0,    "-", 27, 54, 28, 0, 187, 226, "안전",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1171, 126,     "손등",$img,1171,  0,    "-", 52, 39, 77, 0, 209, 241, "할거",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1172, 125,     "손량",$img,1172,  0,    "-", 24, 23, 79, 0, 243, 260, "할거",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1173,  20,     "손례",$img,1173,  0,    "-", 64, 64, 69, 0, 180, 250, "안전",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1174, 124,     "손소",$img,1174,  0,    "-", 76, 80, 68, 0, 188, 241, "정복",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1175, 130,     "손수",$img,1175,  0,    "-", 67, 57, 59, 0, 235, 299, "안전",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1176, 126,     "손유",$img,1176,  4,    "-", 77, 60, 67, 0, 177, 215, "정복",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1177, 122,     "손이",$img,1177,  0,    "-", 57, 62, 57, 0, 223, 272, "안전",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1178, 126,     "손정",$img,1178,  4,    "-", 59, 56, 62, 0, 160, 206, "유지",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1179, 115,     "손준",$img,1179,  0,    "-", 59, 69, 51, 0, 219, 256, "정복",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1180,   7,     "손중",$img,1180,  0,    "-", 53, 63, 24, 0, 154, 185, "안전",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1181, 125,     "손책",$img,1181,  4,    "-", 96, 96, 78, 0, 175, 200, "패권", "필살");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1182, 115,     "손침",$img,1182,  0,    "-", 49, 71, 40, 0, 231, 258, "패권",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1183, 114,     "손호",$img,1183,  0,    "-", 20, 78, 67, 0, 242, 284, "패권",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1184, 126,     "손화",$img,1184,  0,    "-", 35, 25, 71, 0, 224, 253, "왕좌",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1185, 127,     "손환",$img,1185,  0,    "-", 79, 65, 70, 0, 197, 228, "정복",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1186, 126,     "손휴",$img,1186,  0,    "-", 63, 43, 64, 0, 235, 264, "정복",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1187, 117,     "손흠",$img,1187,  0,    "-", 66, 63, 33, 0, 235, 280, "정복",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1188, 147,     "송헌",$img,1188,  3,    "-", 42, 63, 41, 0, 157, 200, "안전",    "-");
//국가1 원소 국가2 공손찬 국가3 동탁 국가4 원술 국가5 유언 국가6 마등 국가7 유표
//국가8 장로 국가9 유우 국가10 공손도 국가11 장양 국가12 공주 국가13 장연
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1189,  95,   "순우경",$img,1189,  1,    "-", 72, 67, 60, 0, 146, 200, "할거",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1190,  22,    "순욱1",$img,1190,  0,    "-", 54, 29, 97, 0, 163, 212, "왕좌", "집중");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1191,  22,     "순유",$img,1191,  3,    "-", 73, 41, 90, 0, 157, 214, "대의", "신중");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1192,  29,     "신비",$img,1192,  1,    "-", 47, 28, 74, 0, 171, 240, "재간",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1193,  37,     "신의",$img,1193,  0,    "-", 55, 61, 51, 0, 190, 252, "출세",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1194,  37,     "신탐",$img,1194,  0,    "-", 56, 58, 57, 0, 188, 254, "출세",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1195,  85,     "신평",$img,1195,  1,    "-", 68, 51, 75, 0, 165, 204, "왕좌",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1196, 102,     "심배",$img,1196,  1,    "-", 75, 66, 68, 0, 156, 204, "패권", "귀병");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1197, 126,     "심영",$img,1197,  0,    "-", 53, 72, 51, 0, 235, 280, "재간",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1198,  47, "아하소과",$img,1198,  0,    "-", 53, 75, 15, 0, 204, 253, "안전", "척사");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1199,  62,   "아회남",$img,1199,  0,    "-", 50, 74, 30, 0, 190, 225, "출세", "척사");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1200,  23,     "악진",$img,1200,  1,    "-", 73, 67, 56, 0, 159, 218, "대의", "돌격");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1201,  23,     "악침",$img,1201,  0,    "-", 45, 52, 33, 0, 196, 257, "대의",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1202,  63,     "악환",$img,1202,  0,    "-", 54, 82, 55, 0, 196, 251, "재간",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1203, 102,     "안량",$img,1203,  1,    "-", 73, 93, 36, 0, 160, 200, "출세", "위압");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1204,  14,     "양백",$img,1204,  8,    "-", 55, 54, 53, 0, 171, 214, "안전",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1205,  92,    "양봉1",$img,1205,  0,    "-", 57, 64, 36, 0, 153, 197, "유지",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1206,  91,    "양봉2",$img,1206,  0,    "-", 62, 78, 61, 0, 191, 252, "정복",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1207,  13,     "양송",$img,1207,  8,    "-", 15, 35, 34, 0, 167, 215, "안전",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1208,  43,     "양수",$img,1208,  0,    "-", 18, 31, 91, 0, 175, 219, "재간", "귀병");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1209,  61,     "양의",$img,1209,  0,    "-", 67, 56, 71, 0, 190, 235, "유지",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1210, 141,    "양조1",$img,1210,  0,    "-", 68, 54, 60, 0, 202, 256, "출세",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1211,   9,    "양추1",$img,1211, 11,    "-", 51, 67, 16, 0, 159, 199, "정복",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1212,  31,     "양호",$img,1212,  0,    "-", 91, 69, 80, 0, 221, 278, "패권",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1213,  53,     "양회",$img,1213,  5,    "-", 60, 67, 40, 0, 167, 212, "출세",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1214, 141,     "양흥",$img,1214,  6,    "-", 52, 68, 17, 0, 169, 211, "안전",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1215,   6,   "어부라",$img,1215,  0,    "-", 78, 80, 61, 0, 150, 195, "정복",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1216,  64,     "엄강",$img,1216,  2,    "-", 57, 65, 44, 0, 163, 192, "재간",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1217,  10,   "엄백호",$img,1217,  0,    "-", 48, 68, 30, 0, 150, 197, "할거",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1218,  10,     "엄여",$img,1218,  0,    "-", 35, 66, 24, 0, 153, 197, "할거",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1219,  69,     "엄안",$img,1219,  5,    "-", 72, 84, 67, 0, 151, 222, "왕좌",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1220,   7,     "엄정",$img,1220,  0,    "-", 31, 68, 49, 0, 151, 189, "출세",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1221, 121,     "엄준",$img,1221,  0,    "-", 44, 24, 71, 0, 169, 246, "재간",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1222,  71,     "여개",$img,1222,  0,    "-", 51, 42, 67, 0, 194, 227, "유지",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1223,  29,     "여건",$img,1223,  0,    "-", 44, 68, 29, 0, 173, 238, "출세",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1224, 107,     "여광",$img,1224,  0,    "-", 60, 67, 27, 0, 162, 207, "유지",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1225, 124,     "여몽",$img,1225,  0,    "-", 92, 78, 93, 0, 178, 219, "패권", "궁병");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1226, 123,     "여범",$img,1226,  0,    "-", 43, 34, 71, 0, 169, 228, "할거",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1227, 107,     "여상",$img,1227,  0,    "-", 62, 68, 26, 0, 164, 207, "유지",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1228, 105,   "여위황",$img,1228,  1,    "-", 42, 62, 38, 0, 159, 200, "정복",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1229, 145,     "여포",$img,1229,  3,    "-", 74,100, 29, 0, 156, 198, "패권", "돌격");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1230,  50,     "염우",$img,1230,  0,    "-", 58, 51, 18, 0, 209, 264, "출세",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1231,  40,     "염유",$img,1231,  9,    "-", 59, 75, 51, 0, 168, 227, "출세",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1232,  18,     "염포",$img,1232,  8,    "-", 33, 35, 77, 0, 163, 231, "유지",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1233,  40,     "예형",$img,1233,  0,    "-", 77, 31, 95, 0, 173, 209, "은둔", "통찰");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1234, 133,     "오강",$img,1234,  0,    "-", 47, 37, 61, 0, 216, 275, "안전",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1235,  59,     "오란",$img,1235,  0,    "-", 67, 75, 42, 0, 170, 218, "재간",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1236,  72,     "오반",$img,1236,  5,    "-", 70, 66, 45, 0, 171, 234, "재간",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1237, 128,     "오언",$img,1237,  0,    "-", 71, 60, 52, 0, 235, 297, "재간",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1238, 126,     "오연",$img,1238,  0,    "-", 36, 70, 31, 0, 234, 280, "정복",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1239,  69,     "오의",$img,1239,  5,    "-", 75, 72, 74, 0, 165, 237, "유지",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1240,  19,     "오질",$img,1240,  0,    "-", 43, 37, 69, 0, 177, 230, "출세",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1241,  51,   "올돌골",$img,1241,  0,    "-", 77, 92, 15, 0, 186, 225, "출세", "척사");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1242,  52,     "옹개",$img,1242,  0,    "-", 58, 67, 51, 0, 188, 225, "안전",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1243,  20,     "왕경",$img,1243,  0,    "-", 55, 47, 65, 0, 206, 260, "재간",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1244,  97,     "왕광",$img,1244,  0,    "-", 72, 67, 54, 0, 150, 190, "유지",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1245,  30,    "왕기1",$img,1245,  0,    "-", 76, 62, 70, 0, 190, 261, "유지",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1246,  34,     "왕랑",$img,1246,  2,    "-", 49, 29, 51, 0, 162, 228, "할거",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1247,  57,     "왕루",$img,1247,  0,    "-", 40, 28, 76, 0, 173, 211, "유지",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1248,  76,     "왕보",$img,1248,  0,    "-", 47, 34, 75, 0, 171, 219, "대의",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1249,  86,     "왕수",$img,1249,  2,    "-", 34, 34, 67, 0, 168, 218, "재간",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1250,  26,     "왕쌍",$img,1250,  0,    "-", 58, 89, 15, 0, 195, 228, "정복", "보병");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1251,  46,     "왕위",$img,1251,  0,    "-", 59, 60, 68, 0, 163, 208, "안전",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1252,  92,     "왕윤",$img,1252,  3,    "-", 16, 18, 77, 0, 137, 192, "왕좌",    "-");
//국가1 원소 국가2 공손찬 국가3 동탁 국가4 원술 국가5 유언 국가6 마등 국가7 유표
//국가8 장로 국가9 유우 국가10 공손도 국가11 장양 국가12 공주 국가13 장연
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1253,  40,     "왕융",$img,1253,  0,    "-", 62, 41, 77, 0, 234, 305, "재간",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1254,  32,     "왕준",$img,1254,  0,    "-", 81, 83, 76, 0, 206, 285, "대의",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1255,  30,     "왕찬",$img,1255,  0,    "-", 28, 28, 78, 0, 177, 217, "안전",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1256,  33,     "왕창",$img,1256,  0,    "-", 74, 57, 52, 0, 188, 259, "정복",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1257,  69,     "왕평",$img,1257,  0,    "-", 77, 76, 71, 0, 192, 248, "재간",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1258,  71,     "왕항",$img,1258,  0,    "-", 51, 43, 60, 0, 184, 254, "대의",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1259,  33,     "왕혼",$img,1259,  0,    "-", 69, 32, 59, 0, 223, 297, "정복",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1260,  71,     "요립",$img,1260,  0,    "-", 65, 41, 84, 0, 181, 250, "출세",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1261,  74,     "요화",$img,1261,  0,    "-", 67, 58, 60, 0, 170, 264, "의협",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1262,  22,    "우금1",$img,1262,  1,    "-", 80, 74, 71, 0, 159, 221, "정복",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1263,  27,    "우금2",$img,1263,  0,    "-", 63, 77, 37, 0, 173, 226, "대의",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1264, 122,     "우번",$img,1264,  0,    "-", 23, 42, 73, 0, 164, 233, "유지",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1265, 126,     "우전",$img,1265,  0,    "-", 63, 55, 41, 0, 204, 258, "안전",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1266,  86,     "원담",$img,1266,  1,    "-", 67, 59, 55, 0, 173, 205, "왕좌",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1267, 101,     "원상",$img,1267,  0,    "-", 54, 72, 68, 0, 179, 207, "패권",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1268, 101,     "원소",$img,1268,  1,    "-", 85, 67, 76,12, 154, 202, "패권", "위압", "4세 5공 명문집안 출신 원소라 하오!");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1269, 140,     "원술",$img,1269,  4,    "-", 77, 59, 71,12, 155, 199, "패권", "축성");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1270, 140,     "원윤",$img,1270,  4,    "-", 41, 34, 60, 0, 163, 199, "패권",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1271, 101,     "원희",$img,1271,  1,    "-", 69, 57, 72, 0, 176, 207, "할거",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1272, 131,     "위소",$img,1272,  0,    "-", 39, 24, 82, 0, 204, 273, "재간",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1273, 147,     "위속",$img,1273,  3,    "-", 57, 59, 41, 0, 156, 200, "안전",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1274,  81,     "위연",$img,1274,  0,    "-", 78, 94, 62, 0, 175, 234, "패권", "보병");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1275,  96,     "위유",$img,1275,  9,    "-", 53, 69, 71, 0, 151, 193, "재간",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1276,  76,     "유기",$img,1276,  7,    "-", 57, 19, 73, 0, 174, 209, "왕좌",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1277,  36,     "유대",$img,1277,  1,    "-", 61, 57, 62, 0, 147, 202, "재간",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1278, 134,     "유도",$img,1278,  0,    "-", 35, 33, 68, 0, 168, 214, "재간",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1279,  46,     "유벽",$img,1279,  0,    "-", 63, 71, 23, 0, 168, 210, "출세",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1280,  75,     "유봉",$img,1280,  0,    "-", 60, 65, 62, 0, 188, 220, "출세",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1281,  75,     "유비",$img,1281,  2,    "-", 85, 75, 70, 0, 161, 223, "왕좌", "인덕");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1282,  75,    "유선1",$img,1282,  0,    "-", 24, 17, 21, 0, 207, 271, "대의",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1283,  55,     "유순",$img,1283,  0,    "-", 67, 61, 54, 0, 184, 239, "출세",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1284,  75,     "유심",$img,1284,  0,    "-", 63, 46, 70, 0, 238, 263, "왕좌",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1285, 129,     "유약",$img,1285,  0,    "-", 67, 63, 61, 0, 206, 260, "유지",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1286,  55,     "유언",$img,1286,  5,    "-", 60, 40, 74,12, 132, 194, "할거",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1287,  27,     "유엽",$img,1287,  0,    "-", 40, 29, 79, 0, 176, 235, "유지",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1288,  11,     "유요",$img,1288,  0,    "-", 23, 22, 48, 0, 156, 195, "안전", "발명");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1289,  96,     "유우",$img,1289,  9,    "-", 68, 34, 72,12, 145, 193, "왕좌",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1290,  55,     "유장",$img,1290,  5,    "-", 38, 31, 63, 0, 162, 219, "할거", "수비");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1291,  45,     "유종",$img,1291,  0,    "-", 22, 26, 61, 0, 191, 208, "할거",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1292,  56,     "유파",$img,1292,  0,    "-", 47, 32, 70, 0, 186, 222, "대의",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1293,  45,    "유표1",$img,1293,  7,    "-", 71, 57, 71,12, 142, 208, "할거",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1294,  27,    "유표2",$img,1294,  0,    "-", 76, 55, 71, 0, 173, 229, "안전",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1295, 134,     "유현",$img,1295,  0,    "-", 32, 56, 55, 0, 188, 252, "안전",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1296, 122,     "육개",$img,1296,  0,    "-", 66, 30, 72, 0, 198, 269, "재간",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1297, 122,     "육손",$img,1297,  0,    "-", 98, 68, 98, 0, 183, 245, "왕좌", "귀병");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1298, 121,     "육적",$img,1298,  0,    "-", 44, 29, 73, 0, 187, 219, "안전",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1299, 122,     "육항",$img,1299,  0,    "-", 95, 69, 94, 0, 226, 274, "왕좌",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1300,  38,   "윤대목",$img,1300,  0,    "-", 62, 49, 69, 0, 211, 270, "재간",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1301,  80,     "윤묵",$img,1301,  0,    "-", 19, 28, 73, 0, 183, 239, "대의",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1302,  72,     "윤상",$img,1302,  0,    "-", 30, 32, 42, 0, 194, 260, "대의",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1303, 136,     "윤직",$img,1303,  0,    "-", 44, 58, 63, 0, 197, 237, "재간",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1304,   2,     "이각",$img,1304,  3,    "-", 56, 77, 43, 0, 148, 198, "패권",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1305, 146,     "이숙",$img,1305,  3,    "-", 27, 45, 67, 0, 156, 192, "재간",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1306,  71,     "이엄",$img,1306,  0,    "-", 80, 84, 81, 0, 172, 234, "패권",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1307,   2,     "이유",$img,1307,  3,    "-", 64, 22, 90, 0, 150, 192, "패권", "귀모");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1308, 132,     "이이",$img,1308,  0,    "-", 55, 75, 20, 0, 187, 222, "정복",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1309,  77,     "이적",$img,1309,  0,    "-", 55, 27, 77, 0, 162, 226, "할거",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1310,  22,     "이전",$img,1310,  1,    "-", 75, 68, 82, 0, 174, 216, "왕좌",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1311,  71,    "이풍1",$img,1311,  0,    "-", 59, 56, 62, 0, 206, 260, "재간",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1312,  66,     "이회",$img,1312,  0,    "-", 67, 50, 79, 0, 175, 231, "유지",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1313, 114,     "잠혼",$img,1313,  0,    "-", 15, 16, 44, 0, 239, 280, "할거",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1314,   7,     "장각",$img,1314,  0,    "-", 93, 25, 93, 0, 140, 185, "패권", "환술", "푸른 하늘은 가고 누런 하늘이 다가온다!");
//국가1 원소 국가2 공손찬 국가3 동탁 국가4 원술 국가5 유언 국가6 마등 국가7 유표
//국가8 장로 국가9 유우 국가10 공손도 국가11 장양 국가12 공주 국가13 장연
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1315,  34,     "장간",$img,1315,  0,    "-", 19, 20, 70, 0, 175, 239, "재간",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1316,   7,     "장개",$img,1316,  0,    "-", 48, 69, 19, 0, 155, 202, "안전",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1317, 122,     "장굉",$img,1317,  0,    "-", 25, 21, 85, 0, 153, 212, "왕좌",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1318,   7,     "장량",$img,1318,  0,    "-", 68, 81, 68, 0, 153, 185, "정복", "환술");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1319,  15,     "장로",$img,1319,  8,    "-", 76, 44, 80,12, 163, 237, "유지", "축성");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1320,  23,     "장료",$img,1320,  3,    "-", 89, 93, 83, 0, 169, 222, "의협", "견고");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1321,   7,     "장보",$img,1321,  0,    "-", 78, 81, 76, 0, 148, 185, "패권", "환술");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1322,  76,     "장비",$img,1322,  2,    "-", 79, 99, 48, 0, 167, 221, "의협", "무쌍");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1323,  62,     "장서",$img,1323,  0,    "-", 44, 48, 35, 0, 225, 290, "출세",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1324, 120,    "장소1",$img,1324,  0,    "-", 42, 24, 91, 0, 156, 236, "안전",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1325,  78,    "장소2",$img,1325,  0,    "-", 51, 44, 71, 0, 202, 264, "재간",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1326,  72,     "장송",$img,1326,  0,    "-", 49, 28, 93, 0, 170, 212, "할거", "통찰");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1327, 148,     "장수",$img,1327,  3,    "-", 71, 72, 69, 0, 154, 207, "할거",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1328, 145,    "장양1",$img,1328, 11,    "-", 62, 66, 65,12, 150, 199, "출세",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1329,   8,     "장연",$img,1329, 13,    "-", 78, 66, 47,12, 153, 210, "안전",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1330,  11,     "장영",$img,1330,  0,    "-", 55, 65, 40, 0, 154, 195, "재간",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1331, 118,     "장온",$img,1331,  0,    "-", 21, 30, 69, 0, 193, 231, "할거",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1332,  78,     "장완",$img,1332,  0,    "-", 70, 55, 86, 0, 188, 246, "할거", "상재");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1333,  15,     "장위",$img,1333,  8,    "-", 65, 70, 29, 0, 172, 215, "대의",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1334,  36,     "장윤",$img,1334,  0,    "-", 67, 59, 60, 0, 163, 208, "정복",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1335, 104,   "장의거",$img,1335,  1,    "-", 68, 59, 34, 0, 159, 205, "대의",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1336,  68,     "장익",$img,1336,  0,    "-", 75, 68, 63, 0, 188, 264, "유지",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1337,  56,     "장임",$img,1337,  0,    "-", 83, 82, 74, 0, 169, 214, "대의", "견고");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1338, 148,    "장제1",$img,1338,  3,    "-", 70, 65, 59, 0, 144, 196, "할거",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1339,  32,    "장제2",$img,1339,  0,    "-", 30, 33, 84, 0, 188, 249, "유지",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1340, 126,    "장제3",$img,1340,  0,    "-", 74, 49, 61, 0, 236, 280, "할거",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1341,  17,     "장패",$img,1341,  2,    "-", 44, 78, 43, 0, 165, 231, "할거",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1342,  76,    "장포1",$img,1342,  0,    "-", 69, 85, 49, 0, 198, 229, "재간", "징병");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1343, 113,    "장포2",$img,1343,  0,    "-", 63, 66, 51, 0, 225, 264, "유지",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1344,  27,     "장합",$img,1344,  1,    "-", 83, 91, 63, 0, 167, 231, "출세", "궁병");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1345,  23,     "장호",$img,1345,  0,    "-", 56, 62, 54, 0, 195, 240, "정복",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1346,  31,     "장화",$img,1346,  0,    "-", 49, 24, 86, 0, 232, 300, "유지",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1347,  48,     "장횡",$img,1347,  0,    "-", 53, 67, 25, 0, 178, 211, "재간",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1348, 141,     "장훈",$img,1348,  4,    "-", 67, 61, 60, 0, 156, 206, "안전",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1349, 120,     "장휴",$img,1349,  0,    "-", 42, 35, 70, 0, 204, 244, "재간",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1350, 126,     "장흠",$img,1350,  0,    "-", 64, 66, 67, 0, 168, 219, "대의", "저격");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1351,  96,     "저수",$img,1351,  1,    "-", 82, 54, 88, 0, 156, 201, "할거", "반계");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1352, 130,     "전단",$img,1352,  0,    "-", 64, 73, 61, 0, 204, 261, "출세",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1353,  42,     "전속",$img,1353,  0,    "-", 66, 57, 49, 0, 218, 272, "안전",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1354,  26,    "전위1",$img,1354,  0,    "-", 61, 96, 34, 0, 160, 197, "의협", "필살");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1355, 130,    "전위2",$img,1355,  0,    "-", 74, 69, 62, 0, 230, 274, "출세",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1356, 128,     "전종",$img,1356,  0,    "-", 79, 77, 74, 0, 183, 249, "패권",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1357,  42,     "전주",$img,1357,  9,    "-", 69, 67, 51, 0, 169, 214, "의협",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1358,  96,     "전풍",$img,1358,  1,    "-", 81, 41, 96, 0, 162, 200, "왕좌",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1359,  24,     "정무",$img,1359,  0,    "-", 54, 38, 74, 0, 201, 265, "출세",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1360, 119,     "정병",$img,1360,  0,    "-", 22, 25, 67, 0, 172, 226, "유지",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1361, 126,     "정보",$img,1361,  4,    "-", 81, 64, 76, 0, 151, 216, "패권",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1362, 123,    "정봉1",$img,1362,  0,    "-", 70, 77, 64, 0, 190, 271, "패권",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1363,  24,     "정욱",$img,1363,  0,    "-", 80, 39, 90, 0, 141, 220, "패권", "신중");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1364,  88,     "정원",$img,1364,  0,    "-", 64, 77, 58, 0, 137, 190, "왕좌", "기병");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1365,   7,   "정원지",$img,1365,  0,    "-", 41, 74, 38, 0, 145, 185, "출세",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1366,  69,     "정은",$img,1366,  6,    "-", 53, 62, 26, 0, 169, 211, "재간",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1367, 121,   "제갈각",$img,1367,  0,    "-", 61, 53, 92, 0, 203, 253, "왕좌",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1368,  76,   "제갈균",$img,1368,  0,    "-", 59, 45, 74, 0, 185, 252, "안전", "상재");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1369, 121,   "제갈근",$img,1369,  0,    "-", 60, 42, 88, 0, 174, 241, "왕좌", "경작");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1370,  76,   "제갈량",$img,1370,  0,    "-", 97, 55,100, 0, 181, 234, "왕좌", "집중");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1371,  76,   "제갈상",$img,1371,  0,    "-", 52, 75, 71, 0, 246, 263, "대의",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1372, 135,   "제갈정",$img,1372,  0,    "-", 56, 57, 54, 0, 241, 300, "안전",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1373,  76,   "제갈첨",$img,1373,  0,    "-", 73, 52, 76, 0, 227, 263, "대의",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1374, 135,   "제갈탄",$img,1374,  0,    "-", 79, 79, 73, 0, 206, 258, "패권",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1375,  76,     "조광",$img,1375,  0,    "-", 65, 67, 54, 0, 210, 263, "재간",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1376,  74,     "조루",$img,1376,  0,    "-", 49, 37, 60, 0, 183, 219, "대의",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1377,  24,     "조모",$img,1377,  0,    "-", 53, 32, 30, 0, 241, 260, "할거",    "-");
//국가1 원소 국가2 공손찬 국가3 동탁 국가4 원술 국가5 유언 국가6 마등 국가7 유표
//국가8 장로 국가9 유우 국가10 공손도 국가11 장양 국가12 공주 국가13 장연
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1378, 127,     "조무",$img,1378,  4,    "-", 71, 68, 71, 0, 155, 191, "의협",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1379,  26,     "조방",$img,1379,  0,    "-", 50, 20, 31, 0, 232, 274, "정복",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1380,  83,     "조범",$img,1380,  0,    "-", 58, 40, 63, 0, 168, 218, "유지",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1381,  26,     "조비",$img,1381,  0,    "-", 72, 69, 75, 0, 187, 226, "패권", "징병");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1382,  19,     "조상",$img,1382,  0,    "-", 68, 62, 31, 0, 207, 249, "패권",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1383, 147,     "조성",$img,1383,  3,    "-", 44, 69, 51, 0, 163, 198, "안전",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1384,  26,     "조순",$img,1384,  1,    "-", 66, 57, 72, 0, 170, 210, "정복",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1385,  25,     "조식",$img,1385,  0,    "-", 19, 19, 90, 0, 192, 232, "왕좌", "귀모");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1386,  25,     "조앙",$img,1386,  1,    "-", 44, 65, 62, 0, 175, 197, "왕좌",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1387,  25,     "조예",$img,1387,  0,    "-", 57, 55, 82, 0, 205, 239, "정복",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1388,  26,     "조우",$img,1388,  0,    "-", 67, 55, 67, 0, 199, 260, "대의",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1389,  76,     "조운",$img,1389,  2,    "-", 95, 98, 87, 0, 168, 229, "왕좌", "무쌍");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1390,  25,     "조웅",$img,1390,  0,    "-", 59, 27, 44, 0, 194, 220, "안전",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1391,  26,     "조인",$img,1391,  1,    "-", 74, 79, 62, 0, 168, 223, "패권", "보병");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1392,  25,     "조조",$img,1392,  1,    "-",100, 80, 95, 0, 155, 220, "패권", "반계", "내 이름을 떨칠 때가 왔군.");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1393,  26,     "조진",$img,1393,  0,    "-", 82, 67, 65, 0, 185, 231, "정복",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1394,  25,     "조창",$img,1394,  0,    "-", 75, 88, 37, 0, 190, 223, "정복", "돌격");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1395,  76,     "조통",$img,1395,  0,    "-", 65, 64, 55, 0, 209, 260, "재간",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1396,  84,     "조표",$img,1396,  2,    "-", 34, 70, 16, 0, 151, 196, "안전",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1397,   7,    "조홍1",$img,1397,  0,    "-", 52, 66, 42, 0, 156, 185, "유지",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1398,  24,    "조홍2",$img,1398,  1,    "-", 72, 69, 44, 0, 169, 232, "패권",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1399,  26,     "조환",$img,1399,  0,    "-", 34, 24, 42, 0, 246, 302, "정복",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1400,  19,     "조훈",$img,1400,  0,    "-", 67, 63, 30, 0, 212, 249, "출세",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1401,  26,     "조휴",$img,1401,  1,    "-", 75, 71, 70, 0, 174, 228, "패권",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1402,  19,     "조희",$img,1402,  0,    "-", 64, 57, 71, 0, 210, 249, "대의",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1403,  22,     "종요",$img,1403,  0,    "-", 16, 20, 74, 0, 151, 230, "유지",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1404,  20,     "종회",$img,1404,  0,    "-", 84, 58, 93, 0, 225, 264, "패권",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1405, 128,     "주거",$img,1405,  0,    "-", 73, 71, 72, 0, 190, 246, "유지",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1406, 118,     "주방",$img,1406,  0,    "-", 56, 36, 76, 0, 200, 240, "할거",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1407, 126,     "주연",$img,1407,  0,    "-", 73, 72, 51, 0, 182, 249, "할거",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1408, 126,     "주유",$img,1408,  4,    "-", 97, 73, 97, 0, 175, 210, "패권", "신산");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1409, 128,     "주이",$img,1409,  0,    "-", 61, 55, 61, 0, 201, 257, "안전",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1410,  88,     "주준",$img,1410,  3,    "-", 82, 75, 65, 0, 149, 195, "왕좌",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1411,  32,     "주지",$img,1411,  0,    "-", 52, 77, 47, 0, 233, 295, "안전",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1412,  76,     "주창",$img,1412,  0,    "-", 42, 79, 30, 0, 164, 219, "의협", "궁병");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1413, 126,     "주치",$img,1413,  4,    "-", 58, 55, 56, 0, 156, 224, "안전",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1414,  41,    "주태1",$img,1414,  0,    "-", 62, 55, 61, 0, 207, 261, "안전",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1415, 126,    "주태2",$img,1415,  0,    "-", 74, 88, 60, 0, 171, 225, "정복", "필살");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1416, 128,     "주환",$img,1416,  0,    "-", 84, 86, 74, 0, 177, 238, "정복",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1417,  29,     "진건",$img,1417,  0,    "-", 62, 70, 62, 0, 214, 292, "정복",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1418,  29,     "진교",$img,1418,  0,    "-", 21, 25, 67, 0, 175, 237, "유지",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1419,  28,     "진군",$img,1419,  0,    "-", 60, 38, 87, 0, 167, 235, "대의",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1420, 143,     "진궁",$img,1420,  1,    "-", 77, 51, 90, 0, 154, 198, "할거", "신중");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1421,  79,     "진규",$img,1421,  2,    "-", 22, 19, 71, 0, 132, 206, "할거",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1422,  72,     "진도",$img,1422,  0,    "-", 71, 85, 70, 0, 171, 237, "대의",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1423,  79,     "진등",$img,1423,  2,    "-", 64, 62, 71, 0, 169, 207, "할거",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1424,  37,     "진림",$img,1424,  1,    "-", 50, 28, 82, 0, 160, 217, "재간",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1425, 124,     "진무",$img,1425,  0,    "-", 62, 74, 59, 0, 176, 215, "재간",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1426,  66,     "진복",$img,1426,  0,    "-", 36, 27, 76, 0, 160, 226, "재간",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1427,  50,     "진수",$img,1427,  0,    "-", 25, 29, 83, 0, 233, 297, "대의",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1428,  81,     "진식",$img,1428,  0,    "-", 47, 68, 52, 0, 191, 230, "출세",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1429,  79,     "진진",$img,1429,  0,    "-", 58, 38, 64, 0, 170, 235, "재간",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1430,  28,     "진태",$img,1430,  0,    "-", 79, 76, 70, 0, 210, 260, "대의",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1431,  11,     "진횡",$img,1431,  0,    "-", 38, 58, 47, 0, 161, 195, "출세",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1432,  21,     "차주",$img,1432,  0,    "-", 55, 66, 62, 0, 164, 199, "안전",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1433,  11,     "착융",$img,1433,  2,    "-", 62, 59, 21, 0, 161, 194, "출세",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1434,  36,     "채모",$img,1434,  7,    "-", 79, 69, 68, 0, 155, 208, "정복", "궁병");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1435,  36,     "채중",$img,1435,  7,    "-", 58, 43, 55, 0, 168, 208, "출세",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1436,  36,     "채화",$img,1436,  7,    "-", 56, 47, 49, 0, 166, 208, "안전",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1437,  66,     "초주",$img,1437,  0,    "-", 22, 26, 81, 0, 201, 270, "대의",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1438,  20,     "최염",$img,1438,  0,    "-", 43, 54, 67, 0, 162, 216, "대의",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1439,  60,     "축융",$img,1439,  0,    "-", 59, 87, 25, 0, 193, 246, "정복", "척사");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1440,  60, "타사대왕",$img,1440,  0,    "-", 61, 72, 67, 0, 186, 225, "출세", "척사");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1441, 124,   "태사자",$img,1441,  0,    "-", 71, 97, 65, 0, 166, 209, "대의", "무쌍");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1442, 124,   "태사향",$img,1442,  0,    "-", 51, 69, 50, 0, 189, 246, "재간",    "-");
//국가1 원소 국가2 공손찬 국가3 동탁 국가4 원술 국가5 유언 국가6 마등 국가7 유표
//국가8 장로 국가9 유우 국가10 공손도 국가11 장양 국가12 공주 국가13 장연
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1443,  12,     "포륭",$img,1443,  0,    "-", 53, 74, 20, 0, 174, 208, "안전",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1444,  74,     "풍습",$img,1444,  0,    "-", 36, 64, 44, 0, 182, 222, "재간",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1445,   7,     "하의",$img,1445,  0,    "-", 49, 68, 25, 0, 161, 195, "출세",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1446, 121,     "하제",$img,1446,  0,    "-", 74, 73, 64, 0, 171, 227, "재간",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1447,  90,     "하진",$img,1447,  0,    "-", 49, 69, 37, 0, 135, 189, "왕좌",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1448,  24,   "하후덕",$img,1448,  0,    "-", 67, 64, 39, 0, 178, 218, "출세",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1449,  26,   "하후돈",$img,1449,  1,    "-", 88, 92, 71, 0, 156, 220, "의협", "돌격");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1450,  19,   "하후무",$img,1450,  0,    "-", 38, 33, 37, 0, 201, 259, "안전",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1451,  24,   "하후상",$img,1451,  0,    "-", 67, 62, 71, 0, 181, 225, "출세",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1452,  26,   "하후연",$img,1452,  1,    "-", 79, 90, 58, 0, 162, 219, "패권", "궁병");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1453,  26,   "하후위",$img,1453,  0,    "-", 73, 76, 71, 0, 204, 254, "정복",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1454,  26,   "하후은",$img,1454,  0,    "-", 49, 51, 39, 0, 180, 208, "안전",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1455,  23,   "하후패",$img,1455,  0,    "-", 78, 88, 69, 0, 202, 262, "대의",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1456,  20,   "하후현",$img,1456,  0,    "-", 57, 23, 75, 0, 208, 254, "유지",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1457,  26,   "하후혜",$img,1457,  0,    "-", 76, 66, 78, 0, 206, 242, "정복",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1458,  26,   "하후화",$img,1458,  0,    "-", 77, 61, 80, 0, 207, 265, "정복",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1459,  22,     "학소",$img,1459,  0,    "-", 89, 81, 86, 0, 185, 229, "대의", "견고");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1460, 126,     "한당",$img,1460,  4,    "-", 68, 67, 64, 0, 156, 225, "할거",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1461,  93,     "한복",$img,1461,  0,    "-", 66, 59, 42, 0, 149, 191, "안전",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1462,   8,     "한섬",$img,1462,  0,    "-", 39, 62, 35, 0, 159, 196, "출세",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1463,  48,     "한수",$img,1463,  6,    "-", 66, 76, 77, 0, 142, 215, "대의", "기병");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1464,  40,     "한숭",$img,1464,  0,    "-", 21, 25, 70, 0, 154, 210, "재간",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1465,   7,     "한충",$img,1465,  0,    "-", 41, 66, 29, 0, 151, 185, "안전",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1466,   9,     "한현",$img,1466,  0,    "-", 43, 61, 20, 0, 163, 208, "유지",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1467,  27,     "한호",$img,1467,  4,    "-", 60, 73, 45, 0, 164, 218, "유지",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1468,  79,     "향랑",$img,1468,  0,    "-", 51, 21, 77, 0, 167, 247, "대의",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1469,  79,     "향총",$img,1469,  0,    "-", 76, 42, 73, 0, 195, 240, "왕좌",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1470, 139,     "허공",$img,1470,  0,    "-", 65, 63, 59, 0, 155, 200, "유지",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1471,  21,     "허유",$img,1471,  1,    "-", 47, 47, 57, 0, 155, 204, "재간",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1472,  23,     "허의",$img,1472,  0,    "-", 31, 74, 47, 0, 213, 263, "출세",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1473,  26,     "허저",$img,1473,  0,    "-", 57, 98, 27, 0, 169, 226, "정복", "무쌍");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1474,  87,     "허정",$img,1474, 12,    "-", 18, 29, 74, 0, 152, 222, "재간",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1475,  12,   "형도영",$img,1475,  0,    "-", 49, 78, 23, 0, 174, 208, "출세",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1476, 148,   "호거아",$img,1476,  3,    "-", 35, 76, 61, 0, 164, 206, "안전",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1477,  76,     "호반",$img,1477,  0,    "-", 61, 58, 46, 0, 179, 233, "재간",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1478,  20,     "호분",$img,1478,  0,    "-", 71, 60, 61, 0, 222, 288, "할거",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1479,  27,   "호주천",$img,1479,  0,    "-", 77, 75, 65, 0, 169, 230, "정복",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1480,  20,     "호준",$img,1480,  0,    "-", 67, 60, 46, 0, 200, 256, "재간",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1481,   2,     "화웅",$img,1481,  0,    "-", 68, 88, 24, 0, 155, 191, "출세", "돌격");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1482, 131,     "화핵",$img,1482,  0,    "-", 37, 27, 75, 0, 217, 278, "안전",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1483,  10,     "화흠",$img,1483,  3,    "-", 18, 43, 75, 0, 157, 231, "출세",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1484,  21,     "환범",$img,1484,  0,    "-", 20, 25, 81, 0, 199, 249, "유지",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1485, 127,     "황개",$img,1485,  4,    "-", 78, 85, 69, 0, 154, 218, "왕좌", "징병");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1486,  56,     "황권",$img,1486,  5,    "-", 76, 46, 77, 0, 167, 240, "대의",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1487,  41,     "황란",$img,1487,  0,    "-", 29, 70, 25, 0, 200, 264, "재간",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1488,  88,   "황보숭",$img,1488,  3,    "-", 83, 63, 73, 0, 132, 195, "왕좌",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1489,  72,     "황충",$img,1489,  0,    "-", 84, 94, 67, 0, 148, 222, "왕좌", "궁병");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1490,  50,     "황호",$img,1490,  0,    "-", 15, 17, 48, 0, 226, 263, "안전",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1491, 147,     "후성",$img,1491,  3,    "-", 56, 62, 33, 0, 158, 199, "정복",    "-");
//국가1 원소 국가2 공손찬 국가3 동탁 국가4 원술 국가5 유언 국가6 마등 국가7 유표
//국가8 장로 국가9 유우 국가10 공손도 국가11 장양 국가12 공주 국가13 장연

if($extend == 1) {
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1492, 123,     "가화",$img,1492,  0,    "-", 50, 66, 40, 0, 176, 224,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1493, 999,     "건석",$img,1493,  0,    "-", 21, 12, 61, 0, 155, 189,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1494, 999,     "견씨",$img,1494,  0,    "-", 35, 24, 58, 0, 182, 221,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1495,  40,     "견홍",$img,1495,  0,    "-", 76, 72, 66, 0, 224, 272,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1496, 120,     "고담",$img,1496,  0,    "-", 33, 21, 69, 0, 203, 244,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1497, 101,     "고유",$img,1497,  0,    "-", 56, 44, 73, 0, 174, 263,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1498, 132,     "곽마",$img,1498,  0,    "-", 68, 71, 49, 0, 239, 280,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1499,  39,   "관구수",$img,1499,  0,    "-", 58, 63, 35, 0, 206, 265,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1500,  39,   "관구전",$img,1500,  0,    "-", 63, 58, 68, 0, 224, 255,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1501, 999,     "관로",$img,1501,  0,    "-", 62, 21, 75, 0, 191, 256,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1502,  65,     "관정",$img,1502,  2,    "-", 35, 50, 73, 0, 158, 199,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1503, 137,     "교수",$img,1503,  4,    "-", 67, 69, 39, 0, 143, 195,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1504,  33,     "구건",$img,1504,  0,    "-", 43, 56, 69, 0, 239, 272,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1505,  41,     "구본",$img,1505,  0,    "-", 52, 41, 70, 0, 232, 269,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1506,  12,     "구성",$img,1506,  0,    "-", 56, 71, 31, 0, 157, 187,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1507,  21,     "국연",$img,1507,  0,    "-", 52, 21, 71, 0, 160, 219,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1508,  99,     "국의",$img,1508,  1,    "-", 83, 79, 50, 0, 146, 191,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1509,  34,     "금의",$img,1509,  0,    "-", 18, 40, 63, 0, 177, 218,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1510,  76,     "나헌",$img,1510,  0,    "-", 86, 67, 75, 0, 218, 270,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1511, 999,     "남두",$img,1511,  0,    "-", 35, 25, 54, 0, 130, 200,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1512,  54,     "냉포",$img,1512,  0,    "-", 70, 82, 69, 0, 176, 214,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1513, 124,    "노숙2",$img,1513,  0,    "-", 70, 55, 76, 0, 208, 274,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1514,  42,     "누규",$img,1514,  0,    "-", 54, 19, 88, 0, 143, 212,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1515,   6,     "누반",$img,1515,  0,    "-", 65, 76, 39, 0, 178, 207,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1516, 130,     "누현",$img,1516,  0,    "-", 23, 20, 68, 0, 223, 275,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1517,  40,     "당균",$img,1517,  0,    "-", 33, 19, 81, 0, 229, 264,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1518,  32,     "당빈",$img,1518,  0,    "-", 70, 74, 62, 0, 235, 294,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1519, 999,     "대교",$img,1519,  0,    "-", 42, 10, 54, 0, 177, 235,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1520,  28,     "대릉",$img,1520,  0,    "-", 64, 75, 45, 0, 199, 258,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1521, 129,     "동조",$img,1521,  0,    "-", 16, 15, 51, 0, 221, 281,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1522, 114,     "등수",$img,1522,  0,    "-", 35, 20, 44, 0, 228, 288,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1523,  50,     "마막",$img,1523,  0,    "-", 22, 17,  5, 0, 221, 265,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1524, 116,     "만욱",$img,1524,  0,    "-", 20, 18, 66, 0, 240, 272,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1525, 120,     "맹종",$img,1525,  0,    "-", 48, 48, 67, 0, 216, 271,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1526,  38,     "문호",$img,1526,  0,    "-", 65, 74, 45, 0, 227, 279,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1527, 999,     "미씨",$img,1527,  0,    "-", 59, 15, 68, 0, 176, 208,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1528,  39,     "반림",$img,1528,  0,    "-", 66, 79,  8, 0, 168, 225,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1529, 129,     "반장",$img,1529,  0,    "-", 77, 78, 69, 0, 177, 222,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1530,  94,     "방열",$img,1530,  0,    "-", 58, 82, 28, 0, 153, 190,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1531,  55,     "방희",$img,1531,  0,    "-", 59, 38, 69, 0, 153, 218,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1532,  30,     "배수",$img,1532,  0,    "-", 10, 11, 77, 0, 223, 271,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1533,   8,     "번능",$img,1533,  0,    "-", 70, 61, 47, 0, 158, 194,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1534, 999,     "번씨",$img,1534,  0,    "-", 32, 17, 45, 0, 176, 220,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1535,  49,   "보도근",$img,1535,  0,    "-", 64, 73, 50, 0, 170, 233,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1536, 129,     "보천",$img,1536,  0,    "-", 68, 60, 72, 0, 222, 272,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1537, 121,     "보협",$img,1537,  0,    "-", 73, 53, 75, 0, 216, 264,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1538,  28,     "부하",$img,1538,  0,    "-", 44, 36, 85, 0, 209, 255,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1539,  26,     "비요",$img,1539,  0,    "-", 70, 65, 73, 0, 192, 228,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1540,  31,   "사마주",$img,1540,  0,    "-", 63, 53, 62, 0, 227, 283,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1541,  41,     "사찬",$img,1541,  0,    "-", 61, 71, 54, 0, 215, 264,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1542, 128,     "설후",$img,1542,  0,    "-", 16, 14, 71, 0, 221, 271,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1543,  36,   "성공영",$img,1543,  0,    "-", 73, 58, 80, 0, 172, 220,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1544, 126,     "성만",$img,1544,  0,    "-", 61, 69, 66, 0, 225, 276,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1545, 999,     "소교",$img,1545,  0,    "-", 57, 23, 66, 0, 178, 218,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1546,  46,     "소유",$img,1546,  0,    "-", 51, 61, 48, 0, 164, 210,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1547,  31,    "소제2",$img,1547,  0,    "-", 22, 16, 78, 0, 224, 268,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1548, 126,     "손교",$img,1548,  0,    "-", 77, 60, 69, 0, 181, 219,    "-",    "-");
//국가1 원소 국가2 공손찬 국가3 동탁 국가4 원술 국가5 유언 국가6 마등 국가7 유표
//국가8 장로 국가9 유우 국가10 공손도 국가11 장양 국가12 공주 국가13 장연
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1549, 116,     "손기",$img,1549,  0,    "-", 62, 65, 52, 0, 227, 276,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1550, 999,   "손상향",$img,1550,  0,    "-", 72, 62, 42, 0, 193, 244,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1551, 125,     "손익",$img,1551,  0,    "-", 69, 75, 26, 0, 184, 204,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1552, 125,     "손진",$img,1552,  0,    "-", 64, 71, 48, 0, 234, 280,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1553, 123,     "송겸",$img,1553,  0,    "-", 61, 48, 44, 0, 175, 215,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1554,  85,     "순심",$img,1554,  1,    "-", 20, 21, 79, 0, 164, 208,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1555,  31,    "순욱2",$img,1555,  0,    "-", 10, 16, 77, 0, 225, 289,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1556,  31,     "순의",$img,1556,  0,    "-", 16, 11, 73, 0, 207, 281,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1557, 129,     "시삭",$img,1557,  0,    "-", 36, 66, 44, 0, 226, 268,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1558,  29,     "신창",$img,1558,  0,    "-", 51, 29, 46, 0, 210, 272,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1559, 999,     "악신",$img,1559,  0,    "-", 53, 12, 46, 0, 175, 228,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1560, 138,     "악취",$img,1560,  0,    "-", 56, 68, 58, 0, 157, 195,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1561, 138,     "양강",$img,1561,  0,    "-", 62, 70, 42, 0, 160, 199,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1562,  21,     "양부",$img,1562,  0,    "-", 68, 55, 85, 0, 178, 239,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1563,  72,     "양서",$img,1563,  0,    "-", 56, 62, 66, 0, 198, 260,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1564,  34,     "양습",$img,1564,  0,    "-", 67, 49, 73, 0, 168, 230,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1565,  16,     "양앙",$img,1565,  8,    "-", 65, 70, 39, 0, 172, 215,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1566,  17,     "양임",$img,1566,  8,    "-", 71, 78, 56, 0, 170, 215,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1567,  32,     "양제",$img,1567,  0,    "-", 69, 63, 71, 0, 226, 291,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1568,  33,    "양조2",$img,1568,  0,    "-", 65, 61, 67, 0, 223, 286,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1569,  48,    "양추2",$img,1569,  0,    "-", 66, 67, 61, 0, 172, 238,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1570, 140,     "양홍",$img,1570,  4,    "-", 19, 17, 76, 0, 152, 199,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1571,  31,     "양혼",$img,1571,  0,    "-", 60, 67, 63, 0, 220, 278,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1572, 123,     "여거",$img,1572,  0,    "-", 71, 58, 69, 0, 196, 256,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1573, 130,     "여대",$img,1573,  0,    "-", 83, 72, 70, 0, 161, 256,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1574, 136,     "염상",$img,1574,  0,    "-", 29, 27, 69, 0, 158, 199,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1575,  36,     "염행",$img,1575,  0,    "-", 70, 86, 38, 0, 159, 222,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1576,  73,     "영수",$img,1576,  0,    "-", 69, 70, 74, 0, 234, 264,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1577,  39,     "오거",$img,1577,  0,    "-", 49, 63, 32, 0, 151, 211,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1578, 126,     "오경",$img,1578,  0,    "-", 73, 60, 57, 0, 159, 203,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1579, 999,   "오국태",$img,1579,  0,    "-", 31, 11, 60, 0, 161, 222,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1580, 126,     "오찬",$img,1580,  0,    "-", 69, 41, 78, 0, 181, 245,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1581,  23,     "온회",$img,1581,  0,    "-", 42, 40, 78, 0, 178, 222,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1582,  21,    "왕기2",$img,1582,  0,    "-", 70, 66, 63, 0, 217, 281,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1583,  32,     "왕도",$img,1583,  0,    "-", 48, 44, 70, 0, 210, 269,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1584, 123,     "왕돈",$img,1584,  0,    "-", 60, 65, 41, 0, 198, 256,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1585,  34,     "왕릉",$img,1585,  0,    "-", 73, 60, 71, 0, 172, 251,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1586,  63,     "왕문",$img,1586,  0,    "-", 64, 67, 32, 0, 162, 205,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1587,  32,     "왕상",$img,1587,  0,    "-", 25, 19, 65, 0, 180, 268,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1588,  34,     "왕숙",$img,1588,  0,    "-", 35, 21, 80, 0, 195, 256,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1589,  33,     "왕업",$img,1589,  0,    "-", 32,  6, 46, 0, 220, 280,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1590,  35,     "왕충",$img,1590,  0,    "-", 42, 58, 21, 0, 152, 214,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1591,   1,     "우보",$img,1591,  3,    "-", 43, 63, 12, 0, 159, 192,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1592, 122,     "우사",$img,1592,  0,    "-", 70, 33, 79, 0, 217, 273,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1593, 138,     "원요",$img,1593,  4,    "-", 44, 42, 45, 0, 177, 206,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1594,  95,     "원유",$img,1594,  1,    "-", 57, 38, 73, 0, 150, 193,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1595,  31,     "위관",$img,1595,  0,    "-", 69, 45, 81, 0, 220, 291,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1596, 129,     "위막",$img,1596,  0,    "-", 58, 62, 60, 0, 221, 268,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1597,  55,     "유괴",$img,1597,  5,    "-", 75, 72, 66, 0, 165, 214,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1598, 129,     "유략",$img,1598,  0,    "-", 72, 68, 59, 0, 206, 260,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1599,  45,     "유반",$img,1599,  0,    "-", 74, 79, 48, 0, 168, 210,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1600,  29,     "유복",$img,1600,  0,    "-", 54, 50, 73, 0, 164, 208,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1601,  78,    "유선2",$img,1601,  0,    "-",  9, 21, 39, 0, 224, 264,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1602, 139,     "유섭",$img,1602,  0,    "-", 62, 79, 26, 0, 158, 190,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1603,  28,     "유소",$img,1603,  0,    "-", 66, 51, 73, 0, 195, 264,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1604, 128,     "유승",$img,1604,  0,    "-", 46, 69, 29, 0, 215, 258,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1605, 129,     "유찬",$img,1605,  0,    "-", 74, 75, 66, 0, 172, 255,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1606, 129,     "유평",$img,1606,  0,    "-", 65, 70, 67, 0, 218, 272,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1607, 138,     "유훈",$img,1607,  4,    "-", 51, 64, 50, 0, 163, 216,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1608,  48,     "이감",$img,1608,  0,    "-", 59, 67, 33, 0, 176, 211,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1609,  19,     "이승",$img,1609,  0,    "-", 13, 26, 32, 0, 201, 249,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1610,  22,     "이통",$img,1610,  0,    "-", 75, 84, 52, 0, 168, 211,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1611, 138,    "이풍2",$img,1611,  0,    "-", 72, 77, 50, 0, 158, 199,    "-",    "-");
//국가1 원소 국가2 공손찬 국가3 동탁 국가4 원술 국가5 유언 국가6 마등 국가7 유표
//국가8 장로 국가9 유우 국가10 공손도 국가11 장양 국가12 공주 국가13 장연
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1612,  20,    "이풍3",$img,1612,  0,    "-", 23, 25, 71, 0, 204, 254,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1613,  29,     "장구",$img,1613,  0,    "-", 69, 71, 47, 0, 201, 263,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1614,  21,     "장기",$img,1614,  0,    "-", 77, 35, 79, 0, 170, 223,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1615,  74,     "장남",$img,1615,  0,    "-", 71, 64, 38, 0, 187, 222,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1616, 100,     "장막",$img,1616,  1,    "-", 53, 52, 70, 0, 155, 195,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1617,   7,   "장만성",$img,1617,  0,    "-", 73, 83, 47, 0, 143, 184,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1618, 135,     "장반",$img,1618,  0,    "-", 56, 73, 66, 0, 227, 282,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1619,  78,     "장빈",$img,1619,  0,    "-", 30, 28, 67, 0, 216, 263,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1620, 124,     "장승",$img,1620,  0,    "-", 75, 68, 75, 0, 178, 244,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1621, 999,    "장양2",$img,1621,  0,    "-", 58, 50, 47, 0, 130, 184,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1622,  68,     "장억",$img,1622,  0,    "-", 82, 80, 54, 0, 190, 254,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1623,  75,     "장준",$img,1623,  0,    "-", 65, 67, 66, 0, 224, 263,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1624,  20,     "장집",$img,1624,  0,    "-", 31, 27, 74, 0, 196, 254,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1625,  39,     "장특",$img,1625,  0,    "-", 71, 53, 74, 0, 209, 265,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1626,  96,     "저곡",$img,1626,  0,    "-", 57, 53, 67, 0, 184, 204,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1627, 128,     "전기",$img,1627,  0,    "-", 51, 69, 55, 0, 231, 258,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1628,  23,     "전만",$img,1628,  0,    "-", 52, 74, 38, 0, 181, 235,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1629, 128,     "전상",$img,1629,  0,    "-",  5,  6, 11, 0, 208, 258,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1630, 130,     "전역",$img,1630,  0,    "-", 60, 62, 37, 0, 212, 265,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1631,  75,     "전예",$img,1631,  2,    "-", 80, 62, 83, 0, 171, 252,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1632,  64,     "전해",$img,1632,  0,    "-", 71, 63, 57, 0, 154, 199,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1633, 123,    "정봉2",$img,1633,  0,    "-", 67, 68, 52, 0, 198, 266,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1634,  22,     "정의",$img,1634,  0,    "-", 17,  3, 66, 0, 184, 220,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1635,  81,   "제갈교",$img,1635,  0,    "-", 55, 17, 77, 0, 204, 228,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1636,  40,   "제갈서",$img,1636,  0,    "-", 45, 43, 27, 0, 218, 286,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1637,  25,     "조충",$img,1637,  0,    "-", 14,  7, 80, 0, 196, 208,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1638, 121,   "종리목",$img,1638,  0,    "-", 84, 68, 75, 0, 214, 269,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1639,  22,     "종육",$img,1639,  0,    "-", 27, 11, 71, 0, 223, 263,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1640, 122,     "좌혁",$img,1640,  0,    "-", 60, 66, 51, 0, 232, 280,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1641,  36,     "주령",$img,1641,  1,    "-", 77, 70, 69, 0, 170, 236,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1642, 115,     "주앙",$img,1642,  0,    "-", 75, 64, 64, 0, 162, 195,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1643,  52,     "주포",$img,1643,  0,    "-", 59, 72, 12, 0, 191, 225,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1644,  11,     "주흔",$img,1644,  0,    "-", 67, 53, 77, 0, 159, 196,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1645, 140,     "진기",$img,1645,  4,    "-", 58, 67, 46, 0, 165, 198,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1646, 142,     "진란",$img,1646,  4,    "-", 65, 70, 43, 0, 157, 204,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1647,  25,     "진랑",$img,1647,  0,    "-", 57, 70, 38, 0, 192, 234,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1648,  12,     "진응",$img,1648,  0,    "-", 62, 69, 49, 0, 172, 208,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1649, 124,     "진표",$img,1649,  0,    "-", 62, 49, 74, 0, 204, 237,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1650, 999,     "채염",$img,1650,  0,    "-", 40, 22, 64, 0, 168, 237,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1651, 999,     "초선",$img,1651,  0,    "-", 66, 15, 72, 0, 176, 211,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1652, 135,     "초이",$img,1652,  0,    "-", 54, 65, 55, 0, 219, 266,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1653,  65,     "추단",$img,1653,  2,    "-", 63, 71, 36, 0, 148, 193,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1654, 999,     "추씨",$img,1654,  0,    "-", 36, 13, 54, 0, 165, 225,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1655,  71,     "추정",$img,1655,  0,    "-", 67, 65, 66, 0, 144, 193,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1656, 145,     "파재",$img,1656,  0,    "-", 69, 75, 52, 0, 145, 184,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1657,  22,     "포신",$img,1657,  1,    "-", 78, 60, 83, 0, 152, 192,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1658, 114,     "하식",$img,1658,  0,    "-", 18, 38, 29, 0, 230, 284,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1659,  36,     "하안",$img,1659,  0,    "-",  6, 27, 72, 0, 190, 249,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1660, 999,   "하후씨",$img,1660,  0,    "-", 29, 16, 47, 0, 186, 249,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1661,   5,     "학맹",$img,1661,  0,    "-", 57, 66, 41, 0, 156, 197,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1662,  98,   "한거자",$img,1662,  0,    "-", 53, 59, 30, 0, 158, 200,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1663,  19,     "한덕",$img,1663,  0,    "-", 62, 79, 24, 0, 171, 228,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1664, 140,     "한윤",$img,1664,  4,    "-", 27, 24, 68, 0, 155, 197,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1665, 999,     "허소",$img,1665,  0,    "-", 53, 27, 60, 0, 150, 195,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1666,  30,     "호열",$img,1666,  0,    "-", 77, 69, 76, 0, 225, 272,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1667,  76,     "호제",$img,1667,  0,    "-", 58, 42, 68, 0, 207, 264,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1668, 149,     "호진",$img,1668,  0,    "-", 65, 77, 13, 0, 146, 190,    "-",    "-");
//국가1 원소 국가2 공손찬 국가3 동탁 국가4 원술 국가5 유언 국가6 마등 국가7 유표
//국가8 장로 국가9 유우 국가10 공손도 국가11 장양 국가12 공주 국가13 장연
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1669,  29,     "호질",$img,1669,  0,    "-", 73, 50, 75, 0, 192, 250,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1670,   7,     "환계",$img,1670,  0,    "-", 12, 25, 67, 0, 156, 221,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1671,  56,     "황숭",$img,1671,  0,    "-", 68, 64, 74, 0, 208, 263,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1672, 999,   "황승언",$img,1672,  0,    "-", 68, 17, 81, 0, 165, 222,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1673, 999,   "황월영",$img,1673,  0,    "-", 58, 14, 75, 0, 186, 235,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1674,  45,     "황조",$img,1674,  7,    "-", 74, 65, 57, 0, 148, 208,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1675,  48,     "후선",$img,1675,  0,    "-", 56, 66, 35, 0, 175, 228,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1676,   8,     "휴고",$img,1676, 11,    "-", 61, 72, 40, 0, 151, 199,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1677,  98,   "휴원진",$img,1677,  0,    "-", 53, 63, 38, 0, 155, 200,    "-",    "-");
RegGeneral($connect,1,0,$fiction,$turnterm,$startyear,$year,1678,  22,   "희지재",$img,1678,  0,    "-", 24,  5, 86, 0, 157, 194,    "-",    "-");
}

//////////////////////////장수 끝///////////////////////////////////////////////

//////////////////////////도시 소속/////////////////////////////////////////////
//국가1 원소 국가2 공손찬 국가3 동탁 국가4 원술 국가5 유언 국가6 마등 국가7 유표
//국가8 장로 국가9 유우 국가10 공손도 국가11 장양 국가12 공주 국가13 장연
RegCity($connect, 1, "업", 1);
RegCity($connect, 12, "허창", 1);
RegCity($connect, 4, "낙양");
RegCity($connect, 3, "장안", 1);
RegCity($connect, 5, "성도", 1);
RegCity($connect, 7, "양양", 1);

RegCity($connect, 2, "북평", 1);
RegCity($connect, 2, "남피");
RegCity($connect, 4, "완", 1);
RegCity($connect, 2, "서주");

RegCity($connect, 9, "계", 1);
RegCity($connect, 1, "복양");
RegCity($connect, 1, "진류");
RegCity($connect, 6, "서량", 1);
RegCity($connect, 11, "하내", 1);
RegCity($connect, 8, "한중", 1);
RegCity($connect, 5, "강주");

RegCity($connect, 10, "안평", 1);
RegCity($connect, 13, "진양", 1);
RegCity($connect, 2, "평원");
RegCity($connect, 2, "북해");
RegCity($connect, 2, "패");
RegCity($connect, 6, "천수");
RegCity($connect, 3, "안정");
RegCity($connect, 5, "자동");
RegCity($connect, 7, "강하");

RegCity($connect, 4, "호로");
RegCity($connect, 2, "역경");
RegCity($connect, 2, "계교");
RegCity($connect, 1, "관도");
RegCity($connect, 1, "정도");
RegCity($connect, 6, "적도");
RegCity($connect, 5, "면죽");
RegCity($connect, 7, "장판");

$query = "update city set pop=pop2*0.7,agri=agri2*0.7,comm=comm2*0.7,secu=secu2*0.7,rate=80,def=def2*0.7,wall=wall2*0.7 where nation>0";
MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
$query = "update city set pop=pop2*0.7,agri=agri2*0.7,comm=comm2*0.7,secu=secu2*0.7,rate=80 where nation=0";
MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
//전방설정
$query = "select nation from nation";
$result = MYDB_query($query, $connect) or Error("scenario_194A ".MYDB_error($connect),"");
$count = MYDB_num_rows($result);
for($i=0; $i < $count; $i++) {
    $nation = MYDB_fetch_array($result);
    SetNationFront($connect, $nation['nation']);
}

//////////////////////////도시 끝///////////////////////////////////////////////

//////////////////////////이벤트///////////////////////////////////////////////
$history[count($history)] = "<C>●</>191년 1월:<L><b>【IF모드1】</b></>백마장군의 위세";
$history[count($history)] = "<C>●</>191년 1월:<L><b>【시나리오】</b></><Y>동탁</>은 <G><b>장안</b></>으로 후퇴합니다!";
$history[count($history)] = "<C>●</>191년 1월:<L><b>【시나리오】</b></><G><b>낙양</b></>은 <Y>손견</>과 결탁한 <Y>원술</>이 차지합니다!";
$history[count($history)] = "<C>●</>191년 1월:<L><b>【시나리오】</b></><G><b>기주</b></>에는 <Y>조조</>와 의기투합한 <Y>원소</>가 힘을 비축합니다!";
$history[count($history)] = "<C>●</>191년 1월:<L><b>【시나리오】</b></><G><b>병주</b></>에는 <Y>유비</>가 합세한 <Y>공손찬</>이 위용을 뽐냅니다!";
pushHistory($connect, $history);

//echo "<script>location.replace('install3_ok.php');</script>";
echo 'install3_ok.php';//TODO:debug all and replace
