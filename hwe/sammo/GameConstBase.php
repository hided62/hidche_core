<?php
namespace sammo;

class GameConstBase
{
     /** @var string 게임명 */
     public static $title = "삼국지 모의전투 PHP HiDCHe";
     /** @var string 코드 아래에 붙는 설명 코드 */
     public static $banner = "KOEI의 이미지를 사용, 응용하였습니다 / 제작 : HideD(hided62@gmail.com) / <a href='https://sam.hided.net/wiki/hidche/credit' target='_blank' style='color:white;text-decoration: underline;'>Credit</a>";
     /** @var string 사용중인 지도명 */
     public static $mapName = 'che';
     /** @var string 사용중인 유닛셋 */
    public static $unitSet = 'che';
    /** @var int 내정시 최하 민심 설정*/
    public static $develrate = 50;
    /** @var int 능력치 상승 경험치*/
    public static $upgradeLimit = 30;
    /** @var int 숙련도 제한치*/
    public static $dexLimit = 1000000;
    /** @var int 초기 징병 사기치*/
    public static $defaultAtmosLow = 40;
    /** @var int 초기 징병 훈련치*/
    public static $defaultTrainLow = 40;
    /** @var int 초기 모병 사기치*/
    public static $defaultAtmosHigh = 70;
    /** @var int 초기 모병 훈련치*/
    public static $defaultTrainHigh = 70;
    /** @var int 사기진작으로 올릴 수 있는 최대 사기치*/
    public static $maxAtmosByCommand = 100;
    /** @var int 훈련으로 올릴 수 있는 최대 사기치*/
    public static $maxTrainByCommand = 100;
    /** @var int 전투로 올릴 수 있는 최대 사기치*/
    public static $maxAtmosByWar = 150;
    /** @var int 전투로 올릴 수 훈련치*/
    public static $maxTrainByWar = 110;
    /** @var int 풀징병시 훈련 1회 상승량*/
    public static $trainDelta = 30;
    /** @var int 풀징병시 훈련 1회 상승량*/
    public static $atmosDelta = 30;
    /** @var float 훈련시 사기 감소율*/
    public static $atmosSideEffectByTraining = 1;
    /** @var float 사기시 훈련 감소율*/
    public static $trainSideEffectByAtmosTurn = 1;
    /** @var float 계략 기본 성공률*/
    public static $sabotageDefaultProb = 0.35;
    /** @var int 계략시 확률 가중치(수치가 클수록 변화가 적음 : (지력차/$firing + $basefiring)*/
    public static $sabotageProbCoefByStat = 300;
    /** @var int 계략시 최소 수치 감소량*/
    public static $sabotageDamageMin = 100;
    /** @var int 계략시 최대 수치 감소량*/
    public static $sabotageDamageMax = 500;
    /** @var string 기본 배경색깔 푸른색*/
    public static $basecolor = "#000044";
    /** @var string 기본 배경색깔 초록색*/
    public static $basecolor2 = "#225500";
    /** @var string 기본 배경색깔 붉은색*/
    public static $basecolor3 = "#660000";
    /** @var string 기본 배경색깔 검붉은색*/
    public static $basecolor4 = "#330000";
    /** @var int 페이즈당 표준 감소 병사 수*/
    public static $armperphase = 500;
    /** @var int 기본 국고*/
    public static $basegold = 0;
    /** @var int 기본 병량*/
    public static $baserice = 2000;
    /** @var int 최저 국고(긴급시) */
    public static $minNationalGold = 0;
    /** @var int 최저 병량(긴급시) */
    public static $minNationalRice = 0;
    /** @var float 군량 매매시 세율*/
    public static $exchangeFee = 0.01;
    /** @var float 성인 연령 */
    public static $adultAge = 14;
    /** @var int 최대 계급 */
    public static $maxDedLevel = 30;
    /** @var int 최대 기술 레벨 */
    public static $maxTechLevel = 12;

    /** @var int 초기 제한시 장수 제한 */
    public static $initialNationGenLimitForRandInit = 3;

    /** @var int 초기 제한시 장수 제한 */
    public static $initialNationGenLimit = 10;

    /** @var int 초기 최대 장수수 */
    public static $defaultMaxGeneral = 500;
    /** @var int 초기 최대 국가 수 */
    public static $defaultMaxNation = 55;
    /** @var int 초기 최대 천재 수 */
    public static $defaultMaxGenius = 5;
    /** @var int 초기 시작 년도. 실제 값은 시나리오에서 정해지므로 딱히 의미는 없음. */
    public static $defaultStartYear = 180;

    /** @var int 최대 턴 */
    public static $maxTurn = 30;
    public static $maxNationTurn = 12;

    public static $statGradeLevel = 5;
      
    /** @var int 초반 제한 기간 */
    public static $openingPartYear = 3;
    /** @var int 거병,임관 제한 기간 */
    public static $joinActionLimit = 12;

    /** @var array 선택 가능한 국가 성향 */
    public static $availableNationType = [
        'che_도적', 'che_명가', 'che_음양가', 'che_종횡가', 'che_불가', 'che_오두미도', 'che_태평도', 'che_도가',
        'che_묵가', 'che_덕가', 'che_병가', 'che_유가', 'che_법가'
    ];
    /** @var array 기본 국가 성향 */
    public static $neutralNationType = 'che_중립';

    /** @var array 기본 내정 특기 */
    public static $defaultSpecialDomestic = 'None';
    /** @var array 선택 가능한 장수 내정 특기 */
    public static $availableSpecialDomestic = [
        'che_경작', 'che_상재', 'che_발명', 'che_축성', 'che_수비', 'che_통찰', 'che_인덕', 'che_귀모',
    ];

    /** @var array 기본 전투 특기 */
    public static $defaultSpecialWar = 'None';
    /** @var array 선택 가능한 장수 내정 특기 */
    public static $availableSpecialWar = [
        'che_귀병', 'che_신산', 'che_환술', 'che_집중', 'che_신중', 'che_반계', 
        'che_보병', 'che_궁병', 'che_기병', 'che_공성',
        'che_돌격', 'che_무쌍', 'che_견고', 'che_위압', 
        'che_저격', 'che_필살', 'che_징병', 'che_의술', 'che_격노', 'che_척사',
    ];
    

    /** @var array 기본 성향(공용) */
    public static $neutralPersonality = 'None';
    /** @var array 선택 가능한 성향 */
    public static $availablePersonality = [
        'che_안전', 'che_유지', 'che_재간', 'che_출세', 'che_할거', 'che_정복',
        'che_패권', 'che_의협', 'che_대의', 'che_왕좌'    
    ];
}
