<?php
namespace sammo;

class GameConstBase
{
    /** @var string 버전 */
    public static $title = "삼국지 모의전투 PHP HiDCHe";
    /** @var string 코드 아래에 붙는 설명 코드 */
    public static $banner = "KOEI의 이미지를 사용, 응용하였습니다 / 제작 : HideD(hided62@gmail.com) / <a href='https://sam.hided.net/wiki/hidche/credit' target='_blank' style='color:white;text-decoration: underline;'>Credit</a>";
    /** @var string mapName 사용중인 지도명 */
    public static $mapName = 'che';
    /** @var string unitSet 사용중인 유닛셋 */
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
    /** @var float 계략 기본 성공률*/
    public static $sabotageDefaultProb = 0.15;
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
    public static $initialNationGenLimit = 10;

    /** @var int 초기 최대 장수수 */
    public static $defaultMaxGeneral = 500;
    /** @var int 초기 최대 국가 수 */
    public static $defaultMaxNation = 55;
    /** @var int 초기 최대 천재 수 */
    public static $defaultMaxGenius = 5;
    /** @var int 초기 시작 년도. 실제 값은 시나리오에서 정해지므로 딱히 의미는 없음. */
    public static $defaultStartYear = 180;

    /** @var int 최대 턴(현재는 DB상 24턴으로 고정) */
    public static $maxTurn = 24;

    public static $statGradeLevel = 5;
}
