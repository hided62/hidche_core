<?php
namespace sammo;

class GameUnitConst extends GameUnitConstBase
{
    const DEFAULT_CREWTYPE = 1500;

    protected static $_buildData = [
        [
            1000, self::T_CASTLE, '성벽',
            100, 100, 7, 0, 0,   99,  9,    
            999999, null,     null,     999999, 
            [],//성벽은 공격할 수 없다.
            [self::T_FOOTMAN=>1.2],
            ['성벽입니다.','생성할 수 없습니다.'],
            null, null
        ],
        [
            1100, self::T_FOOTMAN, '보병',
            100, 150, 7, 10, 0,   18,  18,    
            0, null,     null,     0, 
            [self::T_ARCHER=>1.2, self::T_CAVALRY=>0.8],
            [self::T_ARCHER=>0.8, self::T_CAVALRY=>1.2],
            ['표준적인 보병입니다.','보병은 방어특화이며, 상대가 회피하기 어렵습니다.'],
            null, null
        ],

        [
            1200, self::T_ARCHER, '궁병',     
            100, 100, 7, 20, 0,  20, 20,    
            0, null,     null,     0, 
            [self::T_CAVALRY=>1.2, self::T_FOOTMAN=>0.8],
            [self::T_CAVALRY=>0.8, self::T_FOOTMAN=>1.2],
            ['표준적인 궁병입니다.','궁병은 회피특화입니다.'],
            null, null
        ],

        [
            1300, self::T_CAVALRY, '기병',     
            150, 100, 7,  5, 0,  22, 22,    
            0, null,     null,     0, 
            [self::T_FOOTMAN=>1.2, self::T_ARCHER=>0.8],
            [self::T_FOOTMAN=>0.8, self::T_ARCHER=>1.2],
            ['표준적인 기병입니다.','기병은 공격특화입니다.'],
            null, null
        ],

        [
            1400, self::T_WIZARD, '귀병',      
            80,  80, 7,  5, 0.5,  18,  18,    
            0, null,     null,     0, 
            [],
            [],
            ['계략을 사용하는 병종입니다.'],
            null, null
        ],
        [
            1405, self::T_WIZARD, '남귀병',    
            60,  60, 7, 10, 0.8,  16,  16, 
            1000, null,     null,     0, 
            [],
            [],
            ['전투를 포기하고 계략에 몰두합니다.'],
            null, null
        ],

        [
            1500, self::T_SIEGE, '정란',     
            100, 100, 6,  0, 0,  7,  3,    
            0, null,     null,     0, 
            [self::T_CASTLE=>1.8],
            [],
            ['높은 구조물 위에서 공격합니다.'],
            ['che_성벽부상무효'], null
        ],
        [
            1501, self::T_SIEGE, '충차',     
            150, 100, 6,  0, 0,  10,  3, 
            1000, null,     null,     3, 
            [self::T_CASTLE=>2.4],
            [],
            ['엄청난 위력으로 성벽을 부수어버립니다.'],
            ['che_성벽부상무효'], null
        ],
        [
            1502, self::T_SIEGE, '벽력거',   
            200, 100, 6,  0, 0,  20,  4, 
            3000, ['업'],   null,     0, 
            [self::T_CASTLE=>1.8],
            [],
            ['상대에게 돌덩이를 날립니다.'],
            ['che_성벽부상무효'], null
        ],
        [
            1503, self::T_SIEGE, '목우',      
            50, 200, 5,  0, 0,  15,  3, 
            3000, ['성도'], null,     0, 
            [],
            [],
            ['상대를 저지하는 특수병기입니다.'],
            ['che_성벽부상무효'], null
        ]
    ];
}