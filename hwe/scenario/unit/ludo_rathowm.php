<?php
namespace sammo;

class GameUnitConst extends GameUnitConstBase
{
    const DEFAULT_CREWTYPE = 217001;

    protected static $typeData = [
        self::T_FOOTMAN => '보병',
        self::T_ARCHER => '궁병',
        self::T_CAVALRY => '기동병',
        self::T_WIZARD => '마법병',
        self::T_SIEGE => '차병',
    ];

    protected static $_buildData = [
        [
            -1, self::T_CASTLE, '성벽',
            100, 100, 7, 0, 0,   99,  9,    
            999999, null,     null,     999999, 
            [],//성벽은 공격할 수 없다.
            [self::T_FOOTMAN=>1.2],
            ['성벽입니다.','생성할 수 없습니다.'],
            null, null
        ],
        
        [ 
            217001, self::T_FOOTMAN, '보병', 
            100, 150, 7, 10, 0, 9, 9, 
            0, null, null, 0, 
            [self::T_ARCHER=>1.2, self::T_CAVALRY=>0.8, self::T_SIEGE=>1.2], 
            [self::T_ARCHER=>0.8, self::T_CAVALRY=>1.2, self::T_SIEGE=>0.8], 
            ["표준적인 보병입니다. 보병은 방어특화입니다."],
            null, null
        ],
        [ 
            217002, self::T_FOOTMAN, '마물병', 
            110, 160, 7, 10, 0, 9, 10, 
            0, null, ["호넷 마인령","케이브리스 마인령"], 0, 
            [self::T_ARCHER=>1.2, self::T_CAVALRY=>0.8, self::T_SIEGE=>1.2], 
            [self::T_ARCHER=>0.8, self::T_CAVALRY=>1.2, self::T_SIEGE=>0.8], 
            ["마군 지역 기본병종입니다. 조금더 강하지만 쌀을 많이 소비합니다."],
            null, null
        ],
        [ 
            217003, self::T_FOOTMAN, '중장보병', 
            100, 250, 7, 5, 0, 13, 10, 
            1000, null, ["헬만 동부"], 0, 
            [self::T_ARCHER=>1.2, self::T_CAVALRY=>0.8, self::T_SIEGE=>1.2], 
            [self::T_ARCHER=>0.8, self::T_CAVALRY=>1.2, self::T_SIEGE=>0.8], 
            ["헬만 특유의 견고한 보병입니다."],
            null, null
        ],
        [ 
            217004, self::T_FOOTMAN, '흑의 군 보병', 
            150, 150, 7, 10, 0, 12, 11, 
            1000, null, ["리자스 남부","리자스 북부"], 0, 
            [self::T_ARCHER=>1.2, self::T_CAVALRY=>0.8, self::T_SIEGE=>1.2], 
            [self::T_ARCHER=>0.8, self::T_CAVALRY=>1.2, self::T_SIEGE=>0.8], 
            ["흑의 군 전통의 체계적인 훈련으로 공격력을 보완했습니다."],
            null, null
        ],
        [ 
            217005, self::T_FOOTMAN, '용병', 
            125, 175, 7, 10, 0, 12, 11, 
            1000, null, ["자유도시 동부"], 0, 
            [self::T_ARCHER=>1.2, self::T_CAVALRY=>0.8, self::T_SIEGE=>1.2], 
            [self::T_ARCHER=>0.8, self::T_CAVALRY=>1.2, self::T_SIEGE=>0.8], 
            ["일반 보병보다 비싸지만 받은 만큼은 일해줍니다."],
            null, null
        ],
        [ 
            217006, self::T_FOOTMAN, '사메라이', 
            175, 150, 7, 0, 0, 11, 10, 
            1000, null, ["제스 남부"], 0, 
            [self::T_ARCHER=>1.2, self::T_CAVALRY=>0.8, self::T_SIEGE=>1.2], 
            [self::T_ARCHER=>0.8, self::T_CAVALRY=>1.2, self::T_SIEGE=>0.8], 
            ["검으로 난무를 가하는 몬스터 보병입니다."],
            null, null
        ],
        [ 
            217007, self::T_FOOTMAN, '템플나이트병', 
            75, 275, 7, 0, 0, 11, 12, 
            1000, null, ["AL교"], 0, 
            [self::T_ARCHER=>1.2, self::T_CAVALRY=>0.8, self::T_SIEGE=>1.2], 
            [self::T_ARCHER=>0.8, self::T_CAVALRY=>1.2, self::T_SIEGE=>0.8], 
            ["공격을 포기하고 오직 방어에만 집중합니다."],
            null, null
        ],
        [ 
            217008, self::T_FOOTMAN, '메이드병', 
            150, 150, 7, 20, 0, 13, 11, 
            2000, ["메이드의 묘지"], null, 0, 
            [self::T_ARCHER=>1.2, self::T_CAVALRY=>0.8, self::T_SIEGE=>1.2], 
            [self::T_ARCHER=>0.8, self::T_CAVALRY=>1.2, self::T_SIEGE=>0.8], 
            ["켓셀링크 휘하의 날렵한 전투메이드입니다."],
            null, null
        ],
        [ 
            217009, self::T_FOOTMAN, '요괴병', 
            150, 150, 8, 0, 0, 11, 13, 
            2000, ["오슈"], null, 0, 
            [self::T_ARCHER=>1.2, self::T_CAVALRY=>0.8, self::T_SIEGE=>1.2], 
            [self::T_ARCHER=>0.8, self::T_CAVALRY=>1.2, self::T_SIEGE=>0.8], 
            ["단단한 육체를 믿고 적진을 돌파합니다."],
            null, null
        ],
        [ 
            217010, self::T_FOOTMAN, '리자스 친위병', 
            150, 200, 7, 10, 0, 12, 12, 
            3000, ["리자스성"], null, 0, 
            [self::T_ARCHER=>1.2, self::T_CAVALRY=>0.8, self::T_SIEGE=>1.2], 
            [self::T_ARCHER=>0.8, self::T_CAVALRY=>1.2, self::T_SIEGE=>0.8], 
            ["여왕을 수호하는 리자스 최강의 보병 정예보병입니다."],
            null, null
        ],
        [ 
            217011, self::T_FOOTMAN, '케이브리스 마물병', 
            200, 150, 7, 5, 0, 13, 14, 
            3000, ["케이브리스의 성"], null, 0, 
            [self::T_ARCHER=>1.2, self::T_CAVALRY=>0.8, self::T_SIEGE=>1.2], 
            [self::T_ARCHER=>0.8, self::T_CAVALRY=>1.2, self::T_SIEGE=>0.8], 
            ["케이브리스 직속의 마물 정예 전투보병입니다."],
            null, null
        ],
        [ 
            217012, self::T_FOOTMAN, '투신', 
            200, 300, 7, 5, 0, 28, 23, 
            5000, ["투신도시"], null, 0, 
            [self::T_ARCHER=>1.2, self::T_CAVALRY=>0.8, self::T_SIEGE=>1.2], 
            [self::T_ARCHER=>0.8, self::T_CAVALRY=>1.2, self::T_SIEGE=>0.8], 
            ["성마교단 유적에서 발굴된 사상 최강의 보병입니다."],
            null, null
        ],

        [ 
            217101, self::T_ARCHER, '궁병', 
            100, 100, 7, 20, 0, 10, 10, 
            0, null, null, 0, 
            [ self::T_FOOTMAN=>0.8, self::T_CAVALRY=>1.2, self::T_SIEGE=>1.2], 
            [ self::T_FOOTMAN=>1.2, self::T_CAVALRY=>0.8, self::T_SIEGE=>0.8], 
            ["표준적인 궁병입니다. 궁병은 회피특화입니다."],
            null, null
        ],
        [ 
            217102, self::T_ARCHER, '투척마물병', 
            150, 100, 7, 10, 0, 11, 12, 
            1000, null, ["호넷 마인령"], 0, 
            [ self::T_FOOTMAN=>0.8, self::T_CAVALRY=>1.2, self::T_SIEGE=>1.2], 
            [ self::T_FOOTMAN=>1.2, self::T_CAVALRY=>0.8, self::T_SIEGE=>0.8], 
            ["날렵함을 포기하고 도끼를 투척합니다."],
            null, null
        ],
        [ 
            217103, self::T_ARCHER, '백의 군 궁병', 
            100, 100, 8, 30, 0, 11, 11, 
            1000, null, ["리자스 남부"], 0, 
            [ self::T_FOOTMAN=>0.8, self::T_CAVALRY=>1.2, self::T_SIEGE=>1.2], 
            [ self::T_FOOTMAN=>1.2, self::T_CAVALRY=>0.8, self::T_SIEGE=>0.8], 
            ["전략을 활용하여 치고 빠지기에 능합니다."],
            null, null
        ],
        [ 
            217104, self::T_ARCHER, '벌레술사병', 
            100, 150, 7, 20, 0, 12, 12, 
            2000, ["호박성"], null, 0, 
            [ self::T_FOOTMAN=>0.8, self::T_CAVALRY=>1.2, self::T_SIEGE=>1.2], 
            [ self::T_FOOTMAN=>1.2, self::T_CAVALRY=>0.8, self::T_SIEGE=>0.8], 
            ["벌레들을 부려 몸을 보호하는 궁병입니다."],
            null, null
        ],
        [ 
            217105, self::T_ARCHER, '저격암살병', 
            225, 75, 5, 10, 0, 14, 11, 
            2000, ["블라디보스토크"], null, 0, 
            [ self::T_FOOTMAN=>0.8, self::T_CAVALRY=>1.2, self::T_SIEGE=>1.2], 
            [ self::T_FOOTMAN=>1.2, self::T_CAVALRY=>0.8, self::T_SIEGE=>0.8], 
            ["독을 바른 탄환으로 적의 목숨을 앗아갑니다."],
            null, null
        ],
        [ 
            217106, self::T_ARCHER, '호루스병', 
            150, 100, 8, 20, 0, 11, 12, 
            2000, ["거대전함"], null, 0, 
            [ self::T_FOOTMAN=>0.8, self::T_CAVALRY=>1.2, self::T_SIEGE=>1.2], 
            [ self::T_FOOTMAN=>1.2, self::T_CAVALRY=>0.8, self::T_SIEGE=>0.8], 
            ["민첩하게 파고드는 호루스족 궁병입니다."],
            null, null
        ],
        [ 
            217107, self::T_ARCHER, '튤립병', 
            200, 100, 7, 20, 0, 13, 13, 
            3000, ["커스텀"], null, 0, 
            [ self::T_FOOTMAN=>0.8, self::T_CAVALRY=>1.2, self::T_SIEGE=>1.2], 
            [ self::T_FOOTMAN=>1.2, self::T_CAVALRY=>0.8, self::T_SIEGE=>0.8], 
            ["최첨단병기 튤립으로 적을 폭격합니다."],
            null, null
        ],
        [ 
            217108, self::T_ARCHER, '아이스플레임 궁병', 
            150, 150, 7, 20, 0, 13, 13, 
            3000, ["라그나로크 아크"], null, 0, 
            [ self::T_FOOTMAN=>0.8, self::T_CAVALRY=>1.2, self::T_SIEGE=>1.2], 
            [ self::T_FOOTMAN=>1.2, self::T_CAVALRY=>0.8, self::T_SIEGE=>0.8], 
            ["혁명을 성공으로 이끈 정예 게릴라 궁병입니다."],
            null, null
        ],
        [ 
            217109, self::T_ARCHER, '카라 궁병', 
            225, 225, 7, 30, 0, 25, 25, 
            5000, ["펜실 카우"], null, 0, 
            [ self::T_FOOTMAN=>0.8, self::T_CAVALRY=>1.2, self::T_SIEGE=>1.2], 
            [ self::T_FOOTMAN=>1.2, self::T_CAVALRY=>0.8, self::T_SIEGE=>0.8], 
            ["선조의 힘을 이어받은 최강의 카라 정예 궁병입니다."],
            null, null
        ],

        [ 
            217201, self::T_CAVALRY, '기동병', 
            150, 100, 7, 5, 0, 11, 11, 
            0, null, null, 0, 
            [ self::T_FOOTMAN=>1.2, self::T_ARCHER=>0.8, self::T_SIEGE=>1.2], 
            [ self::T_FOOTMAN=>0.8, self::T_ARCHER=>1.2, self::T_SIEGE=>0.8], 
            ["표준적인 기동병입니다. 기동병은 공격특화입니다."],
            null, null
        ],
        [ 
            217202, self::T_CAVALRY, '하치온나', 
            175, 100, 7, 15, 0, 12, 12, 
            1000, null, ["자유도시 서부"], 0, 
            [ self::T_FOOTMAN=>1.2, self::T_ARCHER=>0.8, self::T_SIEGE=>1.2], 
            [ self::T_FOOTMAN=>0.8, self::T_ARCHER=>1.2, self::T_SIEGE=>0.8], 
            ["작은 몸으로 회피하며 따끔한 일격을 먹이는 몬스터 기동병입니다."],
            null, null
        ],
        [ 
            217203, self::T_CAVALRY, '적의 군 기동병', 
            200, 100, 7, 5, 0, 12, 13, 
            1000, null, ["리자스 북부"], 0, 
            [ self::T_FOOTMAN=>1.2, self::T_ARCHER=>0.8, self::T_SIEGE=>1.2], 
            [ self::T_FOOTMAN=>0.8, self::T_ARCHER=>1.2, self::T_SIEGE=>0.8], 
            ["리자스군의 자랑인 기동부대입니다."],
            null, null
        ],
        [ 
            217204, self::T_CAVALRY, '안드로이드 기동병', 
            175, 150, 7, 10, 0, 13, 12, 
            2000, ["악의 탑"], null, 0, 
            [ self::T_FOOTMAN=>1.2, self::T_ARCHER=>0.8, self::T_SIEGE=>1.2], 
            [ self::T_FOOTMAN=>0.8, self::T_ARCHER=>1.2, self::T_SIEGE=>0.8], 
            ["파이아르가 개발한 최첨단 안드로이드 기동병입니다."],
            null, null
        ],
        [ 
            217205, self::T_CAVALRY, '파란쵸 기동병', 
            200, 100, 8, 5, 0, 13, 11, 
            2000, ["파란쵸 왕국"], null, 0, 
            [ self::T_FOOTMAN=>1.2, self::T_ARCHER=>0.8, self::T_SIEGE=>1.2], 
            [ self::T_FOOTMAN=>0.8, self::T_ARCHER=>1.2, self::T_SIEGE=>0.8], 
            ["적진을 일점돌파하는 파란쵸왕국의 돌격 기동병입니다. "],
            null, null
        ],
        [ 
            217206, self::T_CAVALRY, '비행마물병', 
            150, 100, 8, 15, 0, 12, 12, 
            2000, ["죽음의 대지"], null, 0, 
            [ self::T_FOOTMAN=>1.2, self::T_ARCHER=>0.8, self::T_SIEGE=>1.2], 
            [ self::T_FOOTMAN=>0.8, self::T_ARCHER=>1.2, self::T_SIEGE=>0.8], 
            ["비행마물에 올라탄 기동마물병입니다."],
            null, null
        ],
        [ 
            217207, self::T_CAVALRY, '마물조련사병', 
            250, 200, 6, 0, 0, 16, 16, 
            3000, ["랑그바우"], null, 0, 
            [ self::T_FOOTMAN=>1.2, self::T_ARCHER=>0.8, self::T_SIEGE=>1.2], 
            [ self::T_FOOTMAN=>0.8, self::T_ARCHER=>1.2, self::T_SIEGE=>0.8], 
            ["무시무시한 몬스터에 올라타서 싸우는 극강의 기동병입니다."],
            null, null
        ],
        [ 
            217208, self::T_CAVALRY, '기마병', 
            200, 150, 7, 5, 0, 14, 14, 
            3000, ["오와리"], null, 0, 
            [ self::T_FOOTMAN=>1.2, self::T_ARCHER=>0.8, self::T_SIEGE=>1.2], 
            [ self::T_FOOTMAN=>0.8, self::T_ARCHER=>1.2, self::T_SIEGE=>0.8], 
            ["JAPAN 특유의 기마에 올라타 적을 짓밟는 기동병입니다."],
            null, null
        ],
        [ 
            217209, self::T_CAVALRY, '엔젤나이트', 
            300, 200, 7, 5, 0, 25, 25, 
            5000, ["떨어진 궁도"], null, 0, 
            [ self::T_FOOTMAN=>1.2, self::T_ARCHER=>0.8, self::T_SIEGE=>1.2], 
            [ self::T_FOOTMAN=>0.8, self::T_ARCHER=>1.2, self::T_SIEGE=>0.8], 
            ["신의 명령으로 파멸을 내리기 위해 강림했습니다."],
            null, null
        ],

        [ 
            217301, self::T_WIZARD, '마법병', 
            80, 80, 7, 5, 0.6, 9, 9, 
            0, null, null, 0, 
            [self::T_SIEGE=>1.2], 
            [self::T_SIEGE=>0.8], 
            ["마법을 사용하는 병종입니다."],
            null, null
        ],
        [ 
            217302, self::T_WIZARD, '마물 마법병', 
            90, 90, 7, 0, 0.6, 11, 9, 
            0, null, ["호넷 마인령","케이브리스 마인령"], 0, 
            [self::T_SIEGE=>1.2], 
            [self::T_SIEGE=>0.8], 
            ["마군 지역 기본병종입니다. 조금더 강하지만 금을 많이 소비합니다."],
            null, null
        ],
        [ 
            217303, self::T_WIZARD, '카라 마법병', 
            80, 80, 7, 20, 0.6, 10, 10, 
            1000, null, ["카라의 숲"], 0, 
            [self::T_SIEGE=>1.2], 
            [self::T_SIEGE=>0.8], 
            ["신속히 움직이며 카라의 저주로 적을 공격합니다."],
            null, null
        ],
        [ 
            217304, self::T_WIZARD, '제스 마법병', 
            100, 100, 7, 0, 0.6, 10, 10, 
            1000, null, ["제스 북부"], 0, 
            [self::T_SIEGE=>1.2], 
            [self::T_SIEGE=>0.8], 
            ["마법국가 제스의 전통있는 마법병입니다."],
            null, null
        ],
        [ 
            217305, self::T_WIZARD, '무녀', 
            80, 80, 8, 10, 0.6, 10, 10, 
            1000, null, ["JAPAN"], 0, 
            [self::T_SIEGE=>1.2], 
            [self::T_SIEGE=>0.8], 
            ["신마법과 함께 신통한 춤으로 활력을 불어넣는 마법병입니다."],
            null, null
        ],
        [ 
            217306, self::T_WIZARD, '프로즌', 
            90, 70, 7, 10, 0.8, 10, 10, 
            1000, null, ["헬만 서부"], 0, 
            [self::T_SIEGE=>1.2], 
            [self::T_SIEGE=>0.8], 
            ["백병전에 불리한 연약한 몸으로 마법 사용에 집중하는 몬스터입니다."],
            null, null
        ],
        [ 
            217307, self::T_WIZARD, '마소한 마법병', 
            70, 90, 7, 10, 0.8, 11, 10, 
            1000, null, ["케이브리스 마인령"], 0, 
            [self::T_SIEGE=>1.2], 
            [self::T_SIEGE=>0.8], 
            ["로브를 여러겹 둘러입은 마물마법병입니다. 직접 전투보단 마법에 집중합니다."],
            null, null
        ],
        [ 
            217308, self::T_WIZARD, '신관병', 
            80, 130, 7, 5, 0.6, 9, 11, 
            2000, ["카이즈"], null, 0, 
            [self::T_SIEGE=>1.2], 
            [self::T_SIEGE=>0.8], 
            ["AL교의 전투신관입니다. 몸을 보호하는 성스러운 마법을 사용합니다."],
            null, null
        ],
        [ 
            217309, self::T_WIZARD, '중장마법병', 
            80, 130, 7, 15, 0.6, 11, 12, 
            3000, ["로제스그라드"], null, 0, 
            [self::T_SIEGE=>1.2], 
            [self::T_SIEGE=>0.8], 
            ["두터운 로브를 입고 불길한 주문을 읊습니다."],
            null, null
        ],
        [ 
            217310, self::T_WIZARD, '악마병', 
            130, 80, 7, 15, 0.6, 11, 12, 
            3000, ["올드제스"], null, 0, 
            [self::T_SIEGE=>1.2], 
            [self::T_SIEGE=>0.8], 
            ["고대 제스 왕가의 계약에 따라 소환된 흑마법병입니다."],
            null, null
        ],
        [ 
            217311, self::T_WIZARD, '호넷 마물병', 
            130, 130, 7, 0, 0.6, 12, 12, 
            3000, ["구 마왕성"], null, 0, 
            [self::T_SIEGE=>1.2], 
            [self::T_SIEGE=>0.8], 
            ["호넷 직속의 최강 마물 마법병입니다."],
            null, null
        ],
        [ 
            217312, self::T_WIZARD, 'Z가디언', 
            180, 180, 7, 0, 0.7, 28, 20, 
            5000, ["라그나로크 아크"], null, 0, 
            [self::T_SIEGE=>1.2], 
            [self::T_SIEGE=>0.8], 
            ["제스의 기술력이 집약된 최고의 마법병기입니다."],
            null, null
        ],
        
        [ 
            217401, self::T_SIEGE, '정란', 
            150, 150, 6, 0, 0, 15, 5, 
            0, null, null, 3, 
            [ self::T_FOOTMAN=>0.8, self::T_ARCHER=>0.8, self::T_CAVALRY=>0.8, self::T_WIZARD=>0.8, self::T_CASTLE=>1.8], 
            [ self::T_FOOTMAN=>1.2, self::T_ARCHER=>1.2, self::T_CAVALRY=>1.2, self::T_WIZARD=>1.2], 
            ["높은 구조물 위에서 공격합니다."],
            ['che_성벽부상무효'], null
        ],
        [ 
            217402, self::T_SIEGE, '충차', 
            150, 100, 6, 0, 0, 20, 5, 
            1000, null, null, 3, 
            [ self::T_FOOTMAN=>0.8, self::T_ARCHER=>0.8, self::T_CAVALRY=>0.8, self::T_WIZARD=>0.8, self::T_CASTLE=>2.4], 
            [ self::T_FOOTMAN=>1.2, self::T_ARCHER=>1.2, self::T_CAVALRY=>1.2, self::T_WIZARD=>1.2], 
            ["엄청난 위력으로 성벽을 부수어버립니다."],
            ['che_성벽부상무효'], null
        ],
        [ 
            217403, self::T_SIEGE, '튤립3호', 
            275, 300, 6, 0, 0, 35, 15, 
            5000, ["커스텀"], null, 0, 
            [ ], 
            [ ], 
            ["파괴적인 위력과 정말 파괴적인 비용을 자랑하는 전차입니다. "],
            ['che_성벽부상무효'], null
        ],


    ];
}