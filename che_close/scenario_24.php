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

$query = "select startyear,year,month,turnterm,scenario,extend,fiction from game where no='1'";
$result = MYDB_query($query, $connect) or Error("scenario_194A ".MYDB_error($connect),"");
$admin = MYDB_fetch_array($result);

//가상모드4 : 180년 결사항전

//////////////////////////국가1/////////////////////////////////////////////////
RegNation($connect, "유저", "FF0000", 10000, 10000, "오랑캐를 막아내라", 0, 1, "유가", 7);
//////////////////////////국가2/////////////////////////////////////////////////
RegNation($connect, "오랑캐", "0000FF", 10000, 10000, "중원을 정복하라", 0, 1, "병가", 7);

$query = "update nation set scout=1 where nation='2'";
MYDB_query($query, $connect) or Error("scenario_194A ".MYDB_error($connect),"");

//////////////////////////외교//////////////////////////////////////////////////
$query = "insert into diplomacy (me, you, state, term) values ('1', '2', '2', '0')";
MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");

$query = "insert into diplomacy (me, you, state, term) values ('2', '1', '2', '0')";
MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
//////////////////////////외교 끝//////////////////////////////////////////////////

//////////////////////////장수//////////////////////////////////////////////////
//                                                      이름   통  무  지    꿈   특기
$gencount = 1679;
RegGeneral2($connect,$admin[turnterm],$gencount,"6글자 이내", 63, 46, 62,"안전","징병", "장수명에 6글자 이내로 입력하라고 해서 6글자 이내를 입력했습니다."); $gencount++;
RegGeneral2($connect,$admin[turnterm],$gencount,      "Cato", 44, 41, 67,"재간","저격", "★☆ 승리의 카토 ★☆"); $gencount++;
RegGeneral2($connect,$admin[turnterm],$gencount,       "hui", 42, 61, 53,"재간","저격", "모반으로 천통군주된 먹튀 후이에요~"); $gencount++;
RegGeneral2($connect,$admin[turnterm],$gencount,     "Misty", 63, 71, 25,"패권","격노", ""); $gencount++;
RegGeneral2($connect,$admin[turnterm],$gencount,       "yhp", 34, 53, 75,"재간","신산", ""); $gencount++;
RegGeneral2($connect,$admin[turnterm],$gencount,    "갈매기", 46, 32, 70,"재간","귀병", "끼룩 끼룩 끼끼룩~"); $gencount++;
RegGeneral2($connect,$admin[turnterm],$gencount,      "강유", 75, 18, 74,"왕좌","무쌍", "제대할때까지 NPC가 대신 활동함..."); $gencount++;
RegGeneral2($connect,$admin[turnterm],$gencount,    "개수작", 74, 72, 19,"출세","돌격", ""); $gencount++;
RegGeneral2($connect,$admin[turnterm],$gencount,      "견희", 62, 76, 13,"정복","무쌍", "피리로 다 쓸어버리겠다!"); $gencount++;
RegGeneral2($connect,$admin[turnterm],$gencount,  "계백의난", 82, 65, 18,"패권","공성", "이거 이름 잘못됐네요. 훼백의난이에요."); $gencount++;
RegGeneral2($connect,$admin[turnterm],$gencount,    "김동팔", 52, 40, 64,"왕좌","상재", "띠용~"); $gencount++;
RegGeneral2($connect,$admin[turnterm],$gencount,      "낭군", 66, 25, 73,"대의","보병", ""); $gencount++;
RegGeneral2($connect,$admin[turnterm],$gencount,"내동생고기", 55, 75, 31,"유지","신중", ""); $gencount++;
RegGeneral2($connect,$admin[turnterm],$gencount,      "네시", 75, 85, 11,"의협","무쌍", "내가 군대에 있어도 분신이 움직임. ㄳ"); $gencount++;
RegGeneral2($connect,$admin[turnterm],$gencount,      "독우", 32, 60, 65,"출세","경작", "장비한테 쳐맞은 독우올시다."); $gencount++;
RegGeneral2($connect,$admin[turnterm],$gencount,"뒷집할머니", 63, 60, 31,"의협","축성", "꺄훌~"); $gencount++;
RegGeneral2($connect,$admin[turnterm],$gencount,    "듀라한", 74, 75, 10,"패권","격노", "훼라셀 강림이요."); $gencount++;
RegGeneral2($connect,$admin[turnterm],$gencount,      "땅꾼", 64, 85, 10,"왕좌","위압", "숨은 굇수인 나를 모름?"); $gencount++;
RegGeneral2($connect,$admin[turnterm],$gencount,  "라디에르", 76, 65, 14,"대의","수비", "안냐셍. 훼디에요."); $gencount++;
RegGeneral2($connect,$admin[turnterm],$gencount,  "람슈타인", 74, 66, 20,"출세","견고", "&#48561;.."); $gencount++;
RegGeneral2($connect,$admin[turnterm],$gencount,    "러블리", 50, 72, 63,"재간","발명", ""); $gencount++;
RegGeneral2($connect,$admin[turnterm],$gencount,    "레스텐", 52, 36, 73,"패권","신산", ""); $gencount++;
RegGeneral2($connect,$admin[turnterm],$gencount,    "류비연", 70, 10, 62,"대의","인덕", "쌀풀러 왔어요^ㅇ^"); $gencount++;
RegGeneral2($connect,$admin[turnterm],$gencount,      "리칼", 64, 68, 22,"유지","의술", "난 휏신이 아님. 그냥 오덕일뿐..."); $gencount++;
RegGeneral2($connect,$admin[turnterm],$gencount,    "마법사", 31, 59, 88,"출세","발명", ""); $gencount++;
RegGeneral2($connect,$admin[turnterm],$gencount,  "명예회장", 54, 73, 18,"정복","필살", "양민학살 명훼훼장임."); $gencount++;
RegGeneral2($connect,$admin[turnterm],$gencount,    "모모리", 75, 20, 70,"패권","통찰", "몹몰러 왔다능~"); $gencount++;
RegGeneral2($connect,$admin[turnterm],$gencount,      "묵왕", 70, 70, 10,"정복","궁병", "훼신의 귀환."); $gencount++;
RegGeneral2($connect,$admin[turnterm],$gencount,  "반사대선", 70, 15, 70,"재간","신중", "자소유님 샤릉해요 ㅎ_ㅎ"); $gencount++;
RegGeneral2($connect,$admin[turnterm],$gencount,      "불곰", 69, 71, 10,"유지","징병", ""); $gencount++;
RegGeneral2($connect,$admin[turnterm],$gencount,    "블래싱", 59, 61, 30,"유지","수비", "유... 유리야!"); $gencount++;
RegGeneral2($connect,$admin[turnterm],$gencount,  "사나에♡", 69, 13, 63,"안전","귀모", "러브호텔에 어서오세요 >ㅁ< ♡"); $gencount++;
RegGeneral2($connect,$admin[turnterm],$gencount,      "사푸", 59, 33, 63,"안전","상재", "이게 다 사푸때문이다."); $gencount++;
RegGeneral2($connect,$admin[turnterm],$gencount,  "살수묵랑", 69, 85, 13,"정복","무쌍", "남자라믄 고저 근성 열혈이라요!!!"); $gencount++;
RegGeneral2($connect,$admin[turnterm],$gencount,      "성야", 77, 62, 19,"출세","집중", "훼야 훼야"); $gencount++;
RegGeneral2($connect,$admin[turnterm],$gencount,      "소마", 73, 22, 69,"대의","신중", "오늘 항가해요 (.. *)"); $gencount++;
RegGeneral2($connect,$admin[turnterm],$gencount,    "소용녀", 64, 22, 84,"대의","신중", ""); $gencount++;
RegGeneral2($connect,$admin[turnterm],$gencount,    "소지로", 70, 71, 20,"의협","무쌍", ""); $gencount++;
RegGeneral2($connect,$admin[turnterm],$gencount,        "수", 41, 16, 68,"안전","견고", "흠... 그럼 어쩔수 없이 흉노로 가야겠군요..."); $gencount++;
RegGeneral2($connect,$admin[turnterm],$gencount,    "슈이쯔", 75, 76, 18,"패권","격노", ""); $gencount++;
RegGeneral2($connect,$admin[turnterm],$gencount,    "스노곰", 70, 75, 10,"안전","궁병", ""); $gencount++;
RegGeneral2($connect,$admin[turnterm],$gencount,    "시그리", 81, 63, 14,"왕좌","징병", "어이쿠야 월척이다. 보람 있구나~"); $gencount++;
RegGeneral2($connect,$admin[turnterm],$gencount,    "심심해", 60, 85, 11,"의협","무쌍", ""); $gencount++;
RegGeneral2($connect,$admin[turnterm],$gencount,  "실바누스", 83, 61, 15,"대의","신산", "살려주셍쇼ㅠ"); $gencount++;
RegGeneral2($connect,$admin[turnterm],$gencount,  "아시루스", 56, 27, 75,"패권","집중", "왼손은 거들뿐..."); $gencount++;
RegGeneral2($connect,$admin[turnterm],$gencount,    "얀웬리", 66, 17, 82,"재간","반계", ""); $gencount++;
RegGeneral2($connect,$admin[turnterm],$gencount,      "여휘", 67, 20, 70,"재간","공성", ""); $gencount++;
RegGeneral2($connect,$admin[turnterm],$gencount,  "에드워드", 68, 70, 12,"안전","인덕", "훼드훼드에염"); $gencount++;
RegGeneral2($connect,$admin[turnterm],$gencount,  "에레니아", 70, 19, 63,"정복","척사", "에레냐에염 ㅇㅅㅇ"); $gencount++;
RegGeneral2($connect,$admin[turnterm],$gencount,"온몸이근육", 58, 81, 10,"패권","징병", "불끈!"); $gencount++;
RegGeneral2($connect,$admin[turnterm],$gencount,    "유기체", 70, 21, 75,"출세","환술", "5분 후에 리셋합니다. ㄳ ㅇ_ㅇ"); $gencount++;
RegGeneral2($connect,$admin[turnterm],$gencount,    "윤석민", 73, 20, 66,"패권","집중", ""); $gencount++;
RegGeneral2($connect,$admin[turnterm],$gencount,        "율", 68, 10, 78,"패권","반계", "율!"); $gencount++;
RegGeneral2($connect,$admin[turnterm],$gencount,"이노베이션", 83, 65, 14,"패권","돌격", ""); $gencount++;
RegGeneral2($connect,$admin[turnterm],$gencount,    "임사영", 75, 75, 18,"출세","필살", "평민왔셈 ㅇㅇ"); $gencount++;
RegGeneral2($connect,$admin[turnterm],$gencount,    "자소유", 77, 70, 19,"대의","기병", "ㅇ_ㅇ"); $gencount++;
RegGeneral2($connect,$admin[turnterm],$gencount,    "장군보", 67, 82, 21,"의협","견고", ""); $gencount++;
RegGeneral2($connect,$admin[turnterm],$gencount,    "장수명", 66, 62, 18,"유지","견고", "장수명에 장수명을 입력한 장수명입니다."); $gencount++;
RegGeneral2($connect,$admin[turnterm],$gencount,    "장수왕", 77, 34, 43,"왕좌","위압", "장수왕섭 다음주 오픈해요. 기대해주셍."); $gencount++;
RegGeneral2($connect,$admin[turnterm],$gencount,      "장임", 50, 70, 75,"출세","저격", "훼력짱임."); $gencount++;
RegGeneral2($connect,$admin[turnterm],$gencount,      "장자", 66, 27, 80,"할거","신산", ""); $gencount++;
RegGeneral2($connect,$admin[turnterm],$gencount,    "제로스", 71, 79, 24,"패권","필살", ""); $gencount++;
RegGeneral2($connect,$admin[turnterm],$gencount,    "제이토", 76, 72, 14,"정복","보병", "훼이토 ㅇㅇ"); $gencount++;
RegGeneral2($connect,$admin[turnterm],$gencount,      "조용", 58, 62, 45,"할거","궁병", "조용..."); $gencount++;
RegGeneral2($connect,$admin[turnterm],$gencount,      "중달", 70, 73, 15,"대의","귀모", "내가 바로 그 훼달임."); $gencount++;
RegGeneral2($connect,$admin[turnterm],$gencount,      "지장", 58, 84, 21,"정복","무쌍", "췌퉤훼섭 모두 가리지 않는 훼력 ㅇㅇ"); $gencount++;
RegGeneral2($connect,$admin[turnterm],$gencount,    "지천영", 65, 84, 11,"왕좌","위압", ""); $gencount++;
RegGeneral2($connect,$admin[turnterm],$gencount,  "카이사르", 74, 75, 16,"정복","돌격", ""); $gencount++;
RegGeneral2($connect,$admin[turnterm],$gencount,    "카이토", 63, 15, 78,"유지","집중", ""); $gencount++;
RegGeneral2($connect,$admin[turnterm],$gencount,    "캐양민", 67, 18, 71,"패권","반계", "캐양민임...ㅠㅠ"); $gencount++;
RegGeneral2($connect,$admin[turnterm],$gencount,    "쿄타로", 75, 21, 66,"정복","신산", ""); $gencount++;
RegGeneral2($connect,$admin[turnterm],$gencount,"크리스티앙", 60, 83, 16,"유지","견고", "끌턍 꿀사턍 꾸린스탈 ㅇㅇ"); $gencount++;
RegGeneral2($connect,$admin[turnterm],$gencount,      "통키", 79, 68, 14,"대의","통찰", "불꽃슛~"); $gencount++;
RegGeneral2($connect,$admin[turnterm],$gencount,  "철혈대신", 72, 14, 85,"안전","견고", ""); $gencount++;
RegGeneral2($connect,$admin[turnterm],$gencount,"초절정미녀", 68, 19, 81,"출세","환술", "<- 초미녀임 ㅇㅇ;"); $gencount++;
RegGeneral2($connect,$admin[turnterm],$gencount,  "페르난도", 68, 69, 21,"대의","징병", ""); $gencount++;
RegGeneral2($connect,$admin[turnterm],$gencount,    "펠퍼틴", 58, 29, 61,"안전","의술", "짝퉁 화타가 바로 나!"); $gencount++;
RegGeneral2($connect,$admin[turnterm],$gencount,    "하누리", 55, 22, 85,"패권","귀병", "하루 종일 누르리!"); $gencount++;
RegGeneral2($connect,$admin[turnterm],$gencount,      "하리", 80, 63, 12,"왕좌","위압", "열전 쓰고 있는중..."); $gencount++;
RegGeneral2($connect,$admin[turnterm],$gencount,  "하오마루", 65, 85, 12,"정복","위압", "사무라이 쇼다운 막판왕 깨고 왔음."); $gencount++;
RegGeneral2($connect,$admin[turnterm],$gencount,"하후연묘재", 65, 15, 67,"유지","반계", "묘섭 베타서버 운영중입니다. 많이 찾아주세욤~"); $gencount++;
RegGeneral2($connect,$admin[turnterm],$gencount,      "환선", 60, 76, 24,"패권","돌격", ""); $gencount++;
RegGeneral2($connect,$admin[turnterm],$gencount,    "황월영", 72, 10, 68,"대의","인덕", ""); $gencount++;
RegGeneral2($connect,$admin[turnterm],$gencount,      "허기", 64, 12, 77,"출세","거상", ""); $gencount++;
RegGeneral2($connect,$admin[turnterm],$gencount,    "형왔다", 64, 72, 27,"의협","기병", "형왔다~"); $gencount++;
RegGeneral2($connect,$admin[turnterm],$gencount,      "헹이", 75, 16, 73,"의협","환술", "다 헹궈버리겠다~ ㅇ<-<"); $gencount++;
RegGeneral2($connect,$admin[turnterm],$gencount,    "효주곰", 54, 32, 77,"유지","경작", "순수한 효주곰이에여 >_<"); $gencount++;
RegGeneral2($connect,$admin[turnterm],$gencount,      "후랴", 56, 79, 20,"의협","기병", "후랴 후랴~"); $gencount++;

//군주임명
$no = rand() % 79 + 2;
$query = "update general set level=12 where no='$no'";
MYDB_query($query, $connect) or Error("scenario_194A ".MYDB_error($connect),"");

RegGeneral3($connect,$admin[turnterm],$gencount, 2,12,"오랑캐대왕", 95, 95, 95,"패권","돌격", "오랑캐들이여 일어서라!"); $gencount++;
RegGeneral3($connect,$admin[turnterm],$gencount, 2,11,  "오환대왕", 85, 55, 85,"패권","환술", ""); $gencount++;
RegGeneral3($connect,$admin[turnterm],$gencount, 2,10,    "저대왕", 75, 75, 45,"패권","돌격", ""); $gencount++;
RegGeneral3($connect,$admin[turnterm],$gencount, 2, 9,  "흉노대왕", 65, 50, 50,"패권","돌격", ""); $gencount++;
RegGeneral3($connect,$admin[turnterm],$gencount, 2, 8,  "남만대왕", 45, 15, 45,"패권","환술", ""); $gencount++;
RegGeneral3($connect,$admin[turnterm],$gencount, 2, 7,  "산월대왕", 45, 15, 45,"패권","환술", ""); $gencount++;
RegGeneral3($connect,$admin[turnterm],$gencount, 2, 6,    "왜대왕", 45, 15, 45,"패권","환술", ""); $gencount++;
RegGeneral3($connect,$admin[turnterm],$gencount, 2, 5,    "강대왕", 45, 45, 15,"패권","돌격", ""); $gencount++;

RegGeneral3($connect,$admin[turnterm],$gencount, 2, 1,  "북적대왕", 45, 45, 15,"패권","돌격", ""); $gencount++;
RegGeneral3($connect,$admin[turnterm],$gencount, 2, 1,  "서융대왕", 45, 15, 45,"패권","돌격", ""); $gencount++;
RegGeneral3($connect,$admin[turnterm],$gencount, 2, 1,  "동이대왕", 45, 45, 15,"패권","돌격", ""); $gencount++;

for($k=0; $k <= 99; $k++) {
    if(rand()%2) { $l = rand()%10 + 10; $p = rand()%10 + 15; $i = rand()%10 + 10; }
    else         { $l = rand()%10 + 10; $p = rand()%10 + 10; $i = rand()%10 + 15; }
    RegGeneral3($connect,$admin[turnterm],$gencount, 2, 1,"북적장수{$k}", $l, $p, $i,"패권","", ""); $gencount++;
}

for($k=0; $k <= 99; $k++) {
    if(rand()%2) { $l = rand()%10 + 10; $p = rand()%10 + 15; $i = rand()%10 + 10; }
    else         { $l = rand()%10 + 10; $p = rand()%10 + 10; $i = rand()%10 + 15; }
    RegGeneral3($connect,$admin[turnterm],$gencount, 2, 1,"서융장수{$k}", $l, $p, $i,"패권","", ""); $gencount++;
}

for($k=0; $k <= 99; $k++) {
    if(rand()%2) { $l = rand()%10 + 10; $p = rand()%10 + 15; $i = rand()%10 + 10; }
    else         { $l = rand()%10 + 10; $p = rand()%10 + 10; $i = rand()%10 + 15; }
    RegGeneral3($connect,$admin[turnterm],$gencount, 2, 1,"동이장수{$k}", $l, $p, $i,"패권","", ""); $gencount++;
}

for($k=0; $k <= 99; $k++) {
    if(rand()%2) { $l = rand()%10 + 10; $p = rand()%10 + 15; $i = rand()%10 + 10; }
    else         { $l = rand()%10 + 10; $p = rand()%10 + 10; $i = rand()%10 + 15; }
    RegGeneral3($connect,$admin[turnterm],$gencount, 2, 1,"강장수{$k}", $l, $p, $i,"패권","", ""); $gencount++;
}

for($k=0; $k <= 99; $k++) {
    if(rand()%2) { $l = rand()%10 + 10; $p = rand()%10 + 15; $i = rand()%10 + 10; }
    else         { $l = rand()%10 + 10; $p = rand()%10 + 10; $i = rand()%10 + 15; }
    RegGeneral3($connect,$admin[turnterm],$gencount, 2, 1,"저장수{$k}", $l, $p, $i,"패권","", ""); $gencount++;
}

for($k=0; $k <= 99; $k++) {
    if(rand()%2) { $l = rand()%10 + 10; $p = rand()%10 + 15; $i = rand()%10 + 10; }
    else         { $l = rand()%10 + 10; $p = rand()%10 + 10; $i = rand()%10 + 15; }
    RegGeneral3($connect,$admin[turnterm],$gencount, 2, 1,"흉노장수{$k}", $l, $p, $i,"패권","", ""); $gencount++;
}

for($k=0; $k <= 99; $k++) {
    if(rand()%2) { $l = rand()%10 + 10; $p = rand()%10 + 15; $i = rand()%10 + 10; }
    else         { $l = rand()%10 + 10; $p = rand()%10 + 10; $i = rand()%10 + 15; }
    RegGeneral3($connect,$admin[turnterm],$gencount, 2, 1,"오환장수{$k}", $l, $p, $i,"패권","", ""); $gencount++;
}

for($k=0; $k <= 99; $k++) {
    if(rand()%2) { $l = rand()%10 + 10; $p = rand()%10 + 15; $i = rand()%10 + 10; }
    else         { $l = rand()%10 + 10; $p = rand()%10 + 10; $i = rand()%10 + 15; }
    RegGeneral3($connect,$admin[turnterm],$gencount, 2, 1,"남만장수{$k}", $l, $p, $i,"패권","", ""); $gencount++;
}

for($k=0; $k <= 99; $k++) {
    if(rand()%2) { $l = rand()%10 + 10; $p = rand()%10 + 15; $i = rand()%10 + 10; }
    else         { $l = rand()%10 + 10; $p = rand()%10 + 10; $i = rand()%10 + 15; }
    RegGeneral3($connect,$admin[turnterm],$gencount, 2, 1,"산월장수{$k}", $l, $p, $i,"패권","", ""); $gencount++;
}

for($k=0; $k <= 99; $k++) {
    if(rand()%2) { $l = rand()%10 + 10; $p = rand()%10 + 15; $i = rand()%10 + 10; }
    else         { $l = rand()%10 + 10; $p = rand()%10 + 10; $i = rand()%10 + 15; }
    RegGeneral3($connect,$admin[turnterm],$gencount, 2, 1,"왜장수{$k}", $l, $p, $i,"패권","", ""); $gencount++;
}

//////////////////////////장수 끝///////////////////////////////////////////////

//////////////////////////도시 소속/////////////////////////////////////////////
RegCity($connect, 1, "업", 1);
RegCity($connect, 2, "양양", 1);

//////////////////////////도시 끝///////////////////////////////////////////////

//////////////////////////이벤트///////////////////////////////////////////////
$history[count($history)] = "<C>●</>180년 1월:<L><b>【가상모드4】</b>결사항전</>";
$history[count($history)] = "<C>●</>180년 1월:<L><b>【이벤트】</b></>오랑캐에 맞서 버텨내야 합니다!";
pushHistory($connect, $history);

echo "<script>location.replace('install3_ok.php');</script>";

function RegGeneral2($connect,$turnterm,$gencount,$name,$leader,$power,$intel,$personal,$special,$msg="") {
    $name = "ⓝ".$name;
    $genid      = "gen{$gencount}";
    $turntime = getRandTurn($turnterm);
    $personal = CharCall($personal);
    $special = SpecCall($special);
    if($special >= 40) { $special2 = $special; $special = 0; }
    else { $special2 = 0; }
    $city = rand()%61 + 1;
    $age = 15;
    $specage = $age;
    $specage2 = $age;
    $killturn = 9999;
    $experience = $age * 100;
    $dedication = $age * 100;
    $npc = 2;
    $npcmatch = rand()%150 + 1;
    $picture = 'default.jpg';
    $pw = md5("18071807");
    //장수
    @MYDB_query("
        insert into general (
            npcid,npc,npc_org,npcmatch,user_id,password,name,picture,nation,level,city,
            leader,power,intel,experience,dedication,
            gold,rice,crew,crewtype,train,atmos,
            weap,book,horse,turntime,killturn,age,belong,personal,special,specage,special2,specage2,npcmsg,
            makelimit
        ) values (
            '$gencount','$npc','$npc','$npcmatch','$genid','$pw','$name','$picture','1','1','$city',
            '$leader','$power','$intel','$experience','$dedication',
            '2000','2000','0','0','0','0',
            '0','0','0','$turntime','$killturn','$age','1',
            '$personal','$special','$specage','$special2','$specage2','$msg',
            '0'
        )",
        $connect
    ) or Error(__LINE__.MYDB_error($connect),"");
}

function RegGeneral3($connect,$turnterm,$gencount,$nation,$level,$name,$leader,$power,$intel,$personal,$special,$msg="") {
    $name = "ⓝ".$name;
    $genid      = "npc{$gencount}";
    $turntime = getRandTurn($turnterm);
    $personal = CharCall($personal);
    $special = SpecCall($special);
    if($special >= 40) { $special2 = $special; $special = 0; }
    else { $special2 = 0; }
    $city = rand()%61 + 1;
    $age = 15;
    $specage = $age+1;
    $specage2 = $age+1;
    $killturn = 9999;
    $experience = $age * 100;
    $dedication = $age * 100;
    $npc = 2;
    $npcmatch = 999;
    $picture = 'default.jpg';
    $pw = md5("18071807");
    //장수
    @MYDB_query("
        insert into general (
            npcid,npc,npc_org,npcmatch,user_id,password,name,picture,nation,city,
            leader,power,intel,experience,dedication,
            level,gold,rice,crew,crewtype,train,atmos,
            weap,book,horse,turntime,killturn,age,belong,personal,special,specage,special2,specage2,npcmsg,
            makelimit
        ) values (
            '$gencount','$npc','$npc','$npcmatch','$genid','$pw','$name','$picture','$nation','$city',
            '$leader','$power','$intel','$experience','$dedication',
            '$level','2000','2000','0','0','0','0',
            '0','0','0','$turntime','$killturn','$age','1',
            '$personal','$special','$specage','$special2','$specage2','$msg',
            '0'
        )",
        $connect
    ) or Error(__LINE__.MYDB_error($connect),"");
}

?>
