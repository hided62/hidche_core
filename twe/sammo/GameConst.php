<?php
namespace sammo;

class GameConst{
    /** @var string 버전 */
    const version = "삼국지 모의전투 PHP HideD v0.1";
    /** @var string 코드 아래에 붙는 설명 코드 */
    const banner = "KOEI의 이미지를 사용, 응용하였습니다 / 제작 : 유기체(jwh1807@gmail.com), HideD(hided62@gmail.com)";
    /** @var string 코드 아래에 붙는 설명 코드 */
    const helper = "도움 주신 분들";
    /** @var int 내정시 최하 민심 설정*/
    const develrate = 50;    
    /** @var int 능력치 상승 경험치*/
    const upgradeLimit = 30;     
    /** @var int 숙련도 제한치*/
    const dexLimit = 1000000;     
    /** @var int 초기 사기치*/
    const defaultatmos = 40;   
    /** @var int 초기 훈련치*/
    const defaulttrain = 40;   
    /** @var int 초기 사기치*/
    const defaultatmos2 = 70;   
    /** @var int 초기 훈련치*/
    const defaulttrain2 = 70;   
    /** @var int 최대 훈련치*/
    const maxtrain = 100;    
    /** @var int 인위적으로 올릴 수 있는 최대 사기치*/
    const maxatmos = 100;    
    /** @var int 최대 사기치*/
    const maximumatmos = 150;    
    /** @var int 최대 훈련치*/
    const maximumtrain = 110;    
    /** @var int 풀징병시 훈련 1회 상승량*/
    const training = 30;   
    /** @var float 훈련시 사기 감소율*/
    const atmosing = 0.98;   
    /** @var float 계략 기본 성공률*/
    const basefiring = 0.25;  
    /** @var int 계략시 확률 가중치(수치가 클수록 변화가 적음 : (지력차/const firing + const basefiring)*/
    const firing = 300;     
    /** @var int 계략시 기본 수치 감소량*/
    const firingbase = 100;  
    /** @var int 계략시 수치 감소량(const firingbase ~ const firingpower)*/
    const firingpower = 400;  
    /** @var int 명장,지장에 사용될 통솔 제한*/
    const goodgenleader = 65;   
    /** @var int 명장에 사용될 무력 제한*/
    const goodgenpower = 65;   
    /** @var int 지장에 사용될 지력 제한*/
    const goodgenintel = 65;   
    /** @var string 기본 배경색깔 푸른색*/
    const basecolor = "#000044";  
    /** @var string 기본 배경색깔 초록색*/
    const basecolor2 = "#225500";  
    /** @var string 기본 배경색깔 붉은색*/
    const basecolor3 = "#660000";  
    /** @var string 기본 배경색깔 검붉은색*/
    const basecolor4 = "#330000";  
    /** @var int 페이즈당 표준 감소 병사 수*/
    const armperphase = 500;     
    /** @var int 기본 국고*/
    const basegold = 0;   
    /** @var int 기본 병량*/
    const baserice = 2000;   
    /** @var int 최저 국고(긴급시) */
    const minNationalgold = 0;
    /** @var int 최저 병량(긴급시) */
    const minNationalRice = 0;
    /** @var float 군량 매매시 세율*/
    const taxrate = 0.01;    
}