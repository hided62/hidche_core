<?php
namespace sammo;

class GameConst{
    /** @var string 버전 */
    public static $version = "삼국지 모의전투 PHP HideD v0.1";
    /** @var string 코드 아래에 붙는 설명 코드 */
    public static $banner = "KOEI의 이미지를 사용, 응용하였습니다 / 제작 : 유기체(jwh1807@gmail.com), HideD(hided62@gmail.com)";
    /** @var string 코드 아래에 붙는 설명 코드 */
    public static $helper = "도움 주신 분들";
    /** @var int 내정시 최하 민심 설정*/
    public static $develrate = 50;    
    /** @var int 능력치 상승 경험치*/
    public static $upgradeLimit = 30;     
    /** @var int 숙련도 제한치*/
    public static $dexLimit = 1000000;     
    /** @var int 초기 사기치*/
    public static $defaultatmos = 40;   
    /** @var int 초기 훈련치*/
    public static $defaulttrain = 40;   
    /** @var int 초기 사기치*/
    public static $defaultatmos2 = 70;   
    /** @var int 초기 훈련치*/
    public static $defaulttrain2 = 70;   
    /** @var int 최대 훈련치*/
    public static $maxtrain = 100;    
    /** @var int 인위적으로 올릴 수 있는 최대 사기치*/
    public static $maxatmos = 100;    
    /** @var int 최대 사기치*/
    public static $maximumatmos = 150;    
    /** @var int 최대 훈련치*/
    public static $maximumtrain = 110;    
    /** @var int 풀징병시 훈련 1회 상승량*/
    public static $training = 30;   
    /** @var float 훈련시 사기 감소율*/
    public static $atmosing = 0.98;   
    /** @var float 계략 기본 성공률*/
    public static $basefiring = 0.25;  
    /** @var int 계략시 확률 가중치(수치가 클수록 변화가 적음 : (지력차/public static $firing + public static $basefiring)*/
    public static $firing = 300;     
    /** @var int 계략시 기본 수치 감소량*/
    public static $firingbase = 100;  
    /** @var int 계략시 수치 감소량(public static $firingbase ~ public static $firingpower)*/
    public static $firingpower = 400;  
    /** @var int 명장,지장에 사용될 통솔 제한*/
    public static $goodgenleader = 65;   
    /** @var int 명장에 사용될 무력 제한*/
    public static $goodgenpower = 65;   
    /** @var int 지장에 사용될 지력 제한*/
    public static $goodgenintel = 65;   
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
    public static $minNationalgold = 0;
    /** @var int 최저 병량(긴급시) */
    public static $minNationalRice = 0;
    /** @var float 군량 매매시 세율*/
    public static $taxrate = 0.01;    
}