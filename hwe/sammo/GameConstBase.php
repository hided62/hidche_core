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
    /** @var float 인원수 별 계략 방어 가중치 */
    public static $sabotageDefenceCoefByGeneralCnt = 0.04;
    /** @var int 계략시 최소 수치 감소량*/
    public static $sabotageDamageMin = 100;
    /** @var int 계략시 최대 수치 감소량*/
    public static $sabotageDamageMax = 800;
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
    /** @var float 명전 등록 가능 연령 */
    public static $minPushHallAge = 40;
    /** @var int 최대 계급 */
    public static $maxDedLevel = 30;
    /** @var int 최대 기술 레벨 */
    public static $maxTechLevel = 12;
    /** @var int 최대 하야 패널티 수 */
    public static $maxBetrayCnt = 9;

    /** @var int 최대 레벨 */
    public static $maxLevel = 255;

    /** @var int 최소 인구 증가량 */
    public static $basePopIncreaseAmount = 5000;
    /** @var int 증축시 인구 증가량 */
    public static $expandCityPopIncreaseAmount = 100000;
    /** @var int 증축시 내정 증가량 */
    public static $expandCityDevelIncreaseAmount = 2000;
    /** @var int 증축시 성벽 증가량 */
    public static $expandCityWallIncreaseAmount = 2000;
    /** @var int 증축시 최소 비용 */
    public static $expandCityDefaultCost = 60000;
    /** @var int 증축시 비용 계수 */
    public static $expandCityCostCoef = 500;
    /** @var int 징병 허용 최소 인구 */
    public static $minAvailableRecruitPop = 30000;

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

    /** @var float 멸망한 NPC 장수의 임관 확률 */
    public static $joinRuinedNPCProp = 0.1;

    /** @var int 시작시 금 */
    public static $defaultGold = 1000;
    /** @var int 시작시 쌀 */
    public static $defaultRice = 1000;

    /** @var int 원조 계수 */
    public static $coefAidAmount = 10000;

    /** @var int 최대 개별 자원 금액 */
    public static $maxResourceActionAmount = 10000;
    /** @var int[] 포상/몰수 가이드 금액 */
    public static $resourceActionAmountGuide = [
        100, 200, 300, 400, 500, 600, 700, 800, 900, 1000,
        1200, 1500, 2000, 2500, 3000, 4000, 5000, 6000, 7000, 8000, 9000, 10000
    ];

    public static $generalMinimumGold = 0;
    public static $generalMinimumRice = 500;

    /** @var int 최대 턴 */
    public static $maxTurn = 30;
    public static $maxChiefTurn = 12;

    public static $statGradeLevel = 5;

    /** @var int 초반 제한 기간 */
    public static $openingPartYear = 3;
    /** @var int 거병,임관 제한 기간 */
    public static $joinActionLimit = 12;

    /** @var int 장수 생성시 능력치 최소 보너스 */
    public static $bornMinStatBonus = 3;
    /** @var int 장수 생성시 능력치 최대 보너스 */
    public static $bornMaxStatBonus = 5;

    /** @var array 선택 가능한 국가 성향 */
    public static $availableNationType = [
        'che_도적', 'che_명가', 'che_음양가', 'che_종횡가', 'che_불가', 'che_오두미도', 'che_태평도', 'che_도가',
        'che_묵가', 'che_덕가', 'che_병가', 'che_유가', 'che_법가'
    ];
    /** @var string 기본 국가 성향 */
    public static $neutralNationType = 'che_중립';

    /** @var string 기본 내정 특기 */
    public static $defaultSpecialDomestic = 'None';
    /** @var array 선택 가능한 장수 내정 특기 */
    public static $availableSpecialDomestic = [
        'che_경작', 'che_상재', 'che_발명', 'che_축성', 'che_수비', 'che_통찰', 'che_인덕', 'che_귀모',
    ];
    /** @var array 선택할 수 없으나 게임 내에 유효한 장수 내정 특기 */
    public static $optionalSpecialDomestic = [
        'None',
    ];

    /** @var string 기본 전투 특기 */
    public static $defaultSpecialWar = 'None';
    /** @var array 선택 가능한 장수 전투 특기 */
    public static $availableSpecialWar = [
        'che_귀병', 'che_신산', 'che_환술', 'che_집중', 'che_신중', 'che_반계',
        'che_보병', 'che_궁병', 'che_기병', 'che_공성',
        'che_돌격', 'che_무쌍', 'che_견고', 'che_위압',
        'che_저격', 'che_필살', 'che_징병', 'che_의술', 'che_격노', 'che_척사',
    ];
    /** @var array 선택할 수 없으나 게임 내에 유효한 장수 전투 특기 */
    public static $optionalSpecialWar = [
        'None',
    ];


    /** @var string 기본 성향(공용) */
    public static $neutralPersonality = 'None';
    /** @var array 선택 가능한 성향 */
    public static $availablePersonality = [
        'che_안전', 'che_유지', 'che_재간', 'che_출세', 'che_할거', 'che_정복',
        'che_패권', 'che_의협', 'che_대의', 'che_왕좌'
    ];
    /** @var array 존재하는 모든 성향 */
    public static $optionalPersonality = [
        'che_은둔', 'None'
    ];

    public static $maxUniqueItemLimit = [
        [-1, 1],
        [3, 2],
        [10, 3],
        [20, 4]
    ];

    public static $minTurnDieOnPrestart = 2;

    public static $uniqueTrialCoef = 1;
    public static $maxUniqueTrialProb = 0.25;

    public static $maxAvailableWarSettingCnt = 10;
    public static $incAvailableWarSettingCnt = 2;

    public static $minGoldRequiredWhenBetting = 500;

    public static $minMonthToAllowInheritItem = 4;
    public static $inheritBornSpecialPoint = 6000;
    public static $inheritBornTurntimePoint = 3000;
    public static $inheritBornCityPoint = 1000;
    public static $inheritBornStatPoint = 1000;
    public static $inheritItemUniqueMinPoint = 5000;
    public static $inheritItemRandomPoint = 3000;
    public static $inheritBuffPoints = [0, 200, 600, 1200, 2000, 3000];
    public static $inheritSpecificSpecialPoint = 4000;
    public static $inheritResetAttrPointBase = [1000, 1000, 2000, 3000]; //필요하면 늘려서 쓰기

    /** @var ?string */
    public static $scenarioEffect = null;

    public static $defaultInstantAction = [
        'dieOnPrestart' => true,
        'buildNationCandidate' => true,
    ];

    public static $availableInstantAction = [
        'dieOnPrestart' => true,
        'buildNationCandidate' => true,
    ];

    public static $allItems = [
        'horse' => [
            'che_명마_01_노기' => 0, 'che_명마_02_조랑' => 0, 'che_명마_03_노새' => 0,
            'che_명마_04_나귀' => 0, 'che_명마_05_갈색마' => 0, 'che_명마_06_흑색마' => 0,

            'che_명마_07_백마' => 2, 'che_명마_07_기주마' => 2, 'che_명마_07_오환마' => 2, 'che_명마_07_백상' => 2,
            'che_명마_08_양주마' => 2, 'che_명마_08_흉노마' => 2, 'che_명마_09_과하마' => 2, 'che_명마_09_의남백마' => 2,
            'che_명마_10_대완마' => 2, 'che_명마_10_옥추마' => 2, 'che_명마_11_서량마' => 2, 'che_명마_11_화종마' => 2,
            'che_명마_12_사륜거' => 2, 'che_명마_12_옥란백용구' => 2, 'che_명마_13_절영' => 2, 'che_명마_13_적로' => 2,
            'che_명마_14_적란마' => 2, 'che_명마_14_조황비전' => 2, 'che_명마_15_한혈마' => 2, 'che_명마_15_적토마' => 2,
        ],
        'weapon' => [
            'che_무기_01_단도' => 0, 'che_무기_02_단궁' => 0, 'che_무기_03_단극' => 0,
            'che_무기_04_목검' => 0, 'che_무기_05_죽창' => 0, 'che_무기_06_소부' => 0,

            'che_무기_07_동추' => 2, 'che_무기_07_철편' => 2, 'che_무기_07_철쇄' => 2, 'che_무기_07_맥궁' => 2,
            'che_무기_08_유성추' => 2, 'che_무기_08_철질여골' => 2, 'che_무기_09_쌍철극' => 2, 'che_무기_09_동호비궁' => 2,
            'che_무기_10_삼첨도' => 2, 'che_무기_10_대부' => 2, 'che_무기_11_고정도' => 2, 'che_무기_11_이광궁' => 2,
            'che_무기_12_철척사모' => 2, 'che_무기_12_칠성검' => 2, 'che_무기_13_사모' => 2, 'che_무기_13_양유기궁' => 2,
            'che_무기_14_언월도' => 2, 'che_무기_14_방천화극' => 2, 'che_무기_15_청홍검' => 2, 'che_무기_15_의천검' => 2
        ],
        'book' => [
            'che_서적_01_효경전' => 0, 'che_서적_02_회남자' => 0, 'che_서적_03_변도론' => 0,
            'che_서적_04_건상역주' => 0, 'che_서적_05_여씨춘추' => 0, 'che_서적_06_사민월령' => 0,

            'che_서적_07_위료자' => 2, 'che_서적_07_사마법' => 2, 'che_서적_07_한서' => 2, 'che_서적_07_논어' => 2,
            'che_서적_08_전론' => 2, 'che_서적_08_사기' => 2, 'che_서적_09_장자' => 2, 'che_서적_09_역경' => 2,
            'che_서적_10_시경' => 2, 'che_서적_10_구국론' => 2, 'che_서적_11_상군서' => 2, 'che_서적_11_춘추전' => 2,
            'che_서적_12_산해경' => 2, 'che_서적_12_맹덕신서' => 2, 'che_서적_13_관자' => 2, 'che_서적_13_병법24편' => 2,
            'che_서적_14_한비자' => 2, 'che_서적_14_오자병법' => 2, 'che_서적_15_노자' => 2, 'che_서적_15_손자병법' => 2,
        ],
        'item' => [
            'che_치료_환약' => 0, 'che_저격_수극' => 0, 'che_사기_탁주' => 0,
            'che_훈련_청주' => 0, 'che_계략_이추' => 0, 'che_계략_향낭' => 0,

            'che_의술_정력견혈산' => 1, 'che_의술_청낭서' => 1, 'che_의술_태평청령' => 1, 'che_의술_상한잡병론' => 1,
            'che_보물_도기' => 1, 'che_조달_주판' => 1,
            'che_내정_납금박산로' => 1, 'che_전략_평만지장도' => 1, 'che_숙련_동작' => 1, 'che_명성_구석' => 1,

            'che_척사_오악진형도' => 1, 'che_격노_구정신단경' => 1, 'che_징병_낙주' => 1,
            'che_저격_매화수전' => 1, 'che_저격_비도' => 1, 'che_위압_조목삭' => 1, 'che_공성_묵자' => 1,
            'che_집중_전국책' => 1, 'che_환술_논어집해' => 1,

            'che_진압_박혁론' => 1, 'che_부적_태현청생부' => 1, 'che_저지_삼황내문' => 1,
            'che_행동_서촉지형도' => 1, 'che_간파_노군입산부' => 1, 'che_불굴_상편' => 1,
            'che_약탈_옥벽' => 1,

            'che_농성_주서음부' => 1, 'che_농성_위공자병법' => 1,
            'che_계략_육도' => 1, 'che_계략_삼략' => 1,

            'che_상성보정_과실주' => 1,
            'che_능력치_지력_이강주' => 1, 'che_능력치_무력_두강주' => 1, 'che_능력치_통솔_보령압주' => 1,
            'che_훈련_철벽서' => 1, 'che_훈련_단결도' => 1, 'che_사기_춘화첩' => 1, 'che_사기_초선화' => 1,
            'che_회피_태평요술' => 1, 'che_필살_둔갑천서' => 1,
        ]
    ];

    /** @var array 선택 가능한 커맨드 */
    public static $availableGeneralCommand = [
        '개인' => [
            '휴식',
            'che_요양',
            'che_단련',
            'che_숙련전환',
            'che_견문',
            'che_은퇴',
            'che_장비매매',
            'che_군량매매',
            'che_내정특기초기화',
            'che_전투특기초기화',
        ],
        '내정' => [
            'che_농지개간',
            'che_상업투자',
            'che_기술연구',
            'che_수비강화',
            'che_성벽보수',
            'che_치안강화',
            'che_정착장려',
            'che_주민선정',
            'che_물자조달',
        ],
        '군사' => [
            'che_징병',
            'che_모병',
            'che_훈련',
            'che_사기진작',
            'che_출병',
            'che_집합',
            'che_소집해제',
            'che_첩보',
        ],
        '인사' => [
            'che_이동',
            'che_강행',
            'che_인재탐색',
            'che_등용',
            'che_귀환',
            'che_임관',
            'che_랜덤임관',
            'che_장수대상임관',
        ],
        '계략' => [
            'che_선동',
            'che_탈취',
            'che_파괴',
            'che_화계',
        ],
        '국가' => [
            'che_증여',
            'che_헌납',
            'che_하야',
            'che_거병',
            'che_건국',
            'che_선양',
            'che_해산',
        ]
    ];

    /** @var array 선택 가능한 커맨드 */
    public static $availableChiefCommand = [
        '휴식' => [
            '휴식',
        ],
        '인사' => [
            'che_발령',
            'che_포상',
            'che_몰수',
        ],
        '외교' => [
            'che_물자원조',
            'che_불가침제의',
            'che_선전포고',
            'che_종전제의',
            'che_불가침파기제의',
        ],
        '특수' => [
            'che_초토화',
            'che_천도',
            'che_증축',
            'che_감축',
        ],
        '전략' => [
            'che_필사즉생',
            'che_백성동원',
            'che_수몰',
            'che_허보',
            'che_의병모집',
            'che_이호경식',
            'che_급습',
            'che_피장파장',
        ],
        '기타' => [
            'che_국기변경',
            'che_국호변경',
        ]
    ];

    /** @var array 개인 전략 커맨드 */
    public static $availableUserActionCommand = [
        '개인 전략' => [
            '휴식',
            'g65_의원소환',
            'g65_철야내정',
            'g65_군량급매',
            'g65_접경귀환',
            'g65_필중계략',
            'g65_병장기지원',
            'g65_입대독려',
            'g65_병사연회',
            'g65_약점간파',
        ]
    ];

    public static $retirementYear = 80;

    public static $targetGeneralPool = 'RandomNameGeneral';
    public static $generalPoolAllowOption = ['stat', 'ego', 'picture'];

    public static $randGenFirstName = [
        '가', '간', '감', '강', '고', '공', '공손', '곽', '관', '괴', '교', '금', '노', '뇌', '능', '도', '동', '두',
        '등', '마', '맹', '문', '미', '반', '방', '부', '비', '사', '사마', '서', '설', '성', '소', '손', '송', '순',
        '신', '심', '악', '안', '양', '엄', '여', '염', '오', '왕', '요', '우', '원', '위', '유', '육', '윤', '이',
        '장', '저', '전', '정', '제갈', '조', '종', '주', '진', '채', '태사', '하', '하후', '학', '한', '향', '허',
        '호', '화', '황', '공손', '손', '왕', '유', '장', '조'
    ];
    public static $randGenMiddleName = [''];
    public static $randGenLastName = [
        '가', '간', '강', '거', '건', '검', '견', '경', '공', '광', '권', '규', '녕', '단', '대', '도', '등', '람',
        '량', '례', '로', '료', '모', '민', '박', '범', '보', '비', '사', '상', '색', '서', '소', '속', '송', '수',
        '순', '습', '승', '양', '연', '영', '온', '옹', '완', '우', '웅', '월', '위', '유', '윤', '융', '이', '익',
        '임', '정', '제', '조', '주', '준', '지', '찬', '책', '충', '탁', '택', '통', '패', '평', '포', '합', '해',
        '혁', '현', '화', '환', '회', '횡', '후', '훈', '휴', '흠', '흥'
    ];

    public static $npcBanMessageProb = 0.01;
    public static $npcSeizureMessageProb = 0.01;
    public static $npcMessageFreqByDay = 4;

    public static $defaultInitialEvents = [
        [
            true,
            ["NoticeToHistoryLog", "<S>2년간 거병 및 건국이 가능합니다.</>", ActionLogger::EVENT_YEAR_MONTH]
        ]
    ];
    public static $defaultEvents = [
        [
            "pre_month", 9000,
            true,
            ["UpdateCitySupply"],
            ["ProcessWarIncome"]
        ],
        [
            "month", 9000,
            ["Date", "==", null, 1],
            ["MergeInheritPointRank"],
            ["ProcessSemiAnnual", "gold"],
            ["ProcessIncome", "gold"],
            ["ResetOfficerLock"],
            ["RaiseDisaster"],
            ["RandomizeCityTradeRate"],
            ["NewYear"],
            ["AssignGeneralSpeciality"],
        ],
        [
            "month", 9000,
            ["Date", "==", null, 4],
            ["ResetOfficerLock"],
            ["RaiseDisaster"],
        ],
        [
            "month", 9000,
            ["Date", "==", null, 7],
            ["MergeInheritPointRank"],
            ["ProcessSemiAnnual", "rice"],
            ["ProcessIncome", "rice"],
            ["ResetOfficerLock"],
            ["RaiseDisaster"],
            ["RandomizeCityTradeRate"],
        ],
        [
            "month", 9000,
            ["Date", "==", null, 10],
            ["ResetOfficerLock"],
            ["RaiseDisaster"],
        ],
        [
            "month", 2000,
            ["DateRelative", "==", 1, 1],
            ["NoticeToHistoryLog", "<S>2년 뒤 출병 제한이 풀립니다.</>", ActionLogger::EVENT_YEAR_MONTH],
            ["DeleteEvent"]
        ],
        [
            "month", 2000,
            ["DateRelative", "==", 2, 1],
            ["NoticeToHistoryLog", "<S>1년 뒤 출병 제한이 풀립니다.</>", ActionLogger::EVENT_YEAR_MONTH],
            ["DeleteEvent"]
        ],
        [
            "month", 2000,
            ["DateRelative", "==", 2, 7],
            ["NoticeToHistoryLog", "<S>6개월 뒤 출병 제한이 풀립니다. 병력을 준비해주세요.</>", ActionLogger::EVENT_YEAR_MONTH],
            ["DeleteEvent"]
        ],
        [
            "month", 2000,
            ["DateRelative", "==", 3, 1],
            ["NoticeToHistoryLog", "<S>출병 제한이 풀렸습니다.</>", ActionLogger::EVENT_YEAR_MONTH],
            ["DeleteEvent"]
        ],
        [
            "month", 2000,
            ["DateRelative", "==", 4, 1],
            ["NoticeToHistoryLog", "<S>이제부터 하야, 망명시 패널티가 적용됩니다.</>", ActionLogger::EVENT_YEAR_MONTH],
            ["AddGlobalBetray", 1, 0],
            ["AddGlobalBetray", 1, 1],
            ["DeleteEvent"]
        ],
        [
            "month", 1000,
            true,
            ["UpdateNationLevel"],
            ["ProvideNPCTroopLeader"]
        ],
        [
            "united", 5000,
            true,
            ["MergeInheritPointRank"],
        ]
    ];
}
