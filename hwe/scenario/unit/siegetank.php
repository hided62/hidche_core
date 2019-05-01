<?php
namespace sammo;

class GameUnitConst extends GameUnitConstBase
{
    const DEFAULT_CREWTYPE = 9040;

    protected static $_buildData = [
        [
            -1, self::T_CASTLE, '성벽',
            100, 100, 7, 0, 0,   99,  9,    
            999999, null,     null,     999999, 
            [],//성벽은 공격할 수 없다.
            [self::T_FOOTMAN=>1.2],
            ['성벽입니다.','생성할 수 없습니다.']
        ],
        [
            9000, self::T_FOOTMAN, '보병',
            100, 150, 7, 10, 0,   18,  18,    
            0, null,     null,     0, 
            [self::T_ARCHER=>1.2, self::T_CAVALRY=>0.8],
            [self::T_ARCHER=>0.8, self::T_CAVALRY=>1.2],
            ['표준적인 보병입니다.','보병은 방어특화이며, 상대가 회피하기 어렵습니다.']
        ],
        [
            9001, self::T_FOOTMAN, '청주병',   
            100, 200, 7, 10, 0,  20, 22, 
            1000, null,     ['중원'], 0, 
            [self::T_ARCHER=>1.2, self::T_CAVALRY=>0.8],
            [self::T_ARCHER=>0.8, self::T_CAVALRY=>1.2],
            ['저렴하고 튼튼합니다.']
        ],
        [
            9002, self::T_FOOTMAN, '수병',     
            150, 150, 7, 10, 0,  22, 20, 
            1000, null,     ['오월'], 0, 
            [self::T_ARCHER=>1.2, self::T_CAVALRY=>0.8],
            [self::T_ARCHER=>0.8, self::T_CAVALRY=>1.2],
            ['저렴하고 강력합니다.']
        ],
        [
            9003, self::T_FOOTMAN, '자객병',   
            100, 150, 8, 20, 0,  20, 20, 
            2000, ['저'],   null,     0, 
            [self::T_ARCHER=>1.2, self::T_CAVALRY=>0.8],
            [self::T_ARCHER=>0.8, self::T_CAVALRY=>1.2],
            ['은밀하고 날쌥니다.']
        ],
        [
            9004, self::T_FOOTMAN, '근위병',   
            150, 200, 7, 10, 0,  24, 24, 
            3000, ['낙양'], null,     0, 
            [self::T_ARCHER=>1.2, self::T_CAVALRY=>0.8],
            [self::T_ARCHER=>0.8, self::T_CAVALRY=>1.2],
            ['최강의 보병입니다.']
        ],
        [
            9005, self::T_FOOTMAN, '등갑병',   
            100, 225, 7,  5, 0,  26, 20, 
            1000, null,     ['남중'], 0, 
            [self::T_ARCHER=>1.2, self::T_CAVALRY=>0.8],
            [self::T_ARCHER=>0.8, self::T_CAVALRY=>1.2],
            ['등갑을 두른 보병입니다.']
        ],

        [
            9010, self::T_ARCHER, '궁병',     
            100, 100, 7, 20, 0,  20, 20,    
            0, null,     null,     0, 
            [self::T_CAVALRY=>1.2, self::T_FOOTMAN=>0.8],
            [self::T_CAVALRY=>0.8, self::T_FOOTMAN=>1.2],
            ['표준적인 궁병입니다.','궁병은 회피특화입니다.']
        ],
        [
            9011, self::T_ARCHER, '궁기병',   
            100, 100, 8, 30, 0,  22, 24, 
            1000, null,     ['동이'], 0, 
            [self::T_CAVALRY=>1.2, self::T_FOOTMAN=>0.8],
            [self::T_CAVALRY=>0.8, self::T_FOOTMAN=>1.2],
            ['말을 타고 잘 피합니다.']
        ],
        [
            9012, self::T_ARCHER, '연노병',   
            150, 100, 8, 20, 0,  24, 22, 
            1000, null,     ['서촉'], 0, 
            [self::T_CAVALRY=>1.2, self::T_FOOTMAN=>0.8],
            [self::T_CAVALRY=>0.8, self::T_FOOTMAN=>1.2],
            ['화살을 연사합니다.']
        ],
        [
            9013, self::T_ARCHER, '강궁병',   
            150, 150, 7, 20, 0,  26, 26, 
            3000, ['양양'], null,     0, 
            [self::T_CAVALRY=>1.2, self::T_FOOTMAN=>0.8],
            [self::T_CAVALRY=>0.8, self::T_FOOTMAN=>1.2],
            ['강건한 궁병입니다.']
        ],
        [
            9014, self::T_ARCHER, '석궁병',   
            200, 100, 7, 20, 0,  26, 26, 
            3000, ['건업'], null,     0, 
            [self::T_CAVALRY=>1.2, self::T_FOOTMAN=>0.8],
            [self::T_CAVALRY=>0.8, self::T_FOOTMAN=>1.2],
            ['강력한 화살을 쏩니다.']
        ],

        [
            9020, self::T_CAVALRY, '기병',     
            150, 100, 7,  5, 0,  22, 22,    
            0, null,     null,     0, 
            [self::T_FOOTMAN=>1.2, self::T_ARCHER=>0.8],
            [self::T_FOOTMAN=>0.8, self::T_ARCHER=>1.2],
            ['표준적인 기병입니다.','기병은 공격특화입니다.']
        ],
        [
            9021, self::T_CAVALRY, '백마병',   
            200, 100, 7,  5, 0,  24, 26, 
            1000, null,     ['하북'], 0, 
            [self::T_FOOTMAN=>1.2, self::T_ARCHER=>0.8],
            [self::T_FOOTMAN=>0.8, self::T_ARCHER=>1.2],
            ['백마의 위용을 보여줍니다.']
        ],
        [
            9022, self::T_CAVALRY, '중장기병', 
            150, 150, 7,  5, 0,  26, 24, 
            1000, null,     ['서북'], 0, 
            [self::T_FOOTMAN=>1.2, self::T_ARCHER=>0.8],
            [self::T_FOOTMAN=>0.8, self::T_ARCHER=>1.2],
            ['갑주를 두른 기병입니다.']
        ],
        [
            9023, self::T_CAVALRY, '돌격기병', 
            200, 100, 8,  5, 0,  26, 22, 
            2000, ['흉노'], null,     0, 
            [self::T_FOOTMAN=>1.2, self::T_ARCHER=>0.8],
            [self::T_FOOTMAN=>0.8, self::T_ARCHER=>1.2],
            ['저돌적으로 공격합니다.']
        ],
        [
            9024, self::T_CAVALRY, '철기병',  
            100, 200, 7,  5, 0,  22, 26, 
            2000, ['강'],   null,     0, 
            [self::T_FOOTMAN=>1.2, self::T_ARCHER=>0.8],
            [self::T_FOOTMAN=>0.8, self::T_ARCHER=>1.2],
            ['철갑을 두른 기병입니다.']],
        [
            9025, self::T_CAVALRY, '수렵기병', 
            150, 100, 8, 15, 0,  24, 24, 
            2000, ['산월'], null,     0, 
            [self::T_FOOTMAN=>1.2, self::T_ARCHER=>0.8],
            [self::T_FOOTMAN=>0.8, self::T_ARCHER=>1.2],
            ['날쎄고 빠른 기병입니다.']
        ],
        [
            9026, self::T_CAVALRY, '맹수병',   
            250, 175, 6,  0, 0,  32, 32, 
            2000, ['남만'], null,     0, 
            [self::T_FOOTMAN=>1.2, self::T_ARCHER=>0.8],
            [self::T_FOOTMAN=>0.8, self::T_ARCHER=>1.2],
            ['어느 누구보다 강력합니다.']
        ],
        [
            9027, self::T_CAVALRY, '호표기병', 
            200, 150, 7,  5, 0,  28, 28, 
            3000, ['허창'], null,     0, 
            [self::T_FOOTMAN=>1.2, self::T_ARCHER=>0.8],
            [self::T_FOOTMAN=>0.8, self::T_ARCHER=>1.2],
            ['정예 기병입니다.']
        ],

        [
            9030, self::T_WIZARD, '귀병',      
            80,  80, 7,  5, 0.5,  18,  18,    
            0, null,     null,     0, 
            [],
            [],
            ['계략을 사용하는 병종입니다.']
        ],
        [
            9031, self::T_WIZARD, '신귀병',    
            80,  80, 7, 20, 0.6, 20, 20, 
            1000, null,     ['초'],   0, 
            [],
            [],
            ['신출귀몰한 귀병입니다.']
        ],
        [
            9032, self::T_WIZARD, '백귀병',    
            80, 130, 7,  5, 0.6,  18, 22, 
            2000, ['오환'], null,     0, 
            [],
            [],
            ['저렴하고 튼튼합니다.']
        ],
        [
            9033, self::T_WIZARD, '흑귀병',   
            130,  80, 7,  5, 0.6, 22,  18, 
            2000, ['왜'],   null,     0, 
            [],
            [],
            ['저렴하고 강력합니다.']
        ],
        [
            9034, self::T_WIZARD, '악귀병',   
            130, 130, 7,  0, 0.6, 24, 24, 
            3000, ['장안'], null,     0, 
            [],
            [],
            ['백병전에도 능숙합니다.']
        ],
        [
            9035, self::T_WIZARD, '남귀병',    
            60,  60, 7, 10, 0.8,  16,  16, 
            1000, null,     null,     0, 
            [],
            [],
            ['전투를 포기하고 계략에 몰두합니다.']
        ],
        [
            9036, self::T_WIZARD, '황귀병',   
            110, 110, 7,  0, 0.8, 26, 20, 
            3000, ['낙양'], null,     0, 
            [],
            [],
            ['고도로 훈련된 귀병입니다.']
        ],
        [
            9037, self::T_WIZARD, '천귀병',    
            80, 130, 7, 15, 0.6, 22, 24, 
            3000, ['성도'], null,     0, 
            [],
            [],
            ['갑주를 두른 귀병입니다.']
        ],
        [
            9038, self::T_WIZARD, '마귀병',   
            130,  80, 7, 15, 0.6, 24, 22, 
            3000, ['업'],   null,     0, 
            [],
            [],
            ['날카로운 무기를 가진 귀병입니다.']
        ],
        [
            9040, self::T_SIEGE, '정란',     
            100, 100, 6,  0, 0,  7,  3,    
            0, null,     null,     0, 
            [self::T_CASTLE=>1.8],
            [],
            ['높은 구조물 위에서 공격합니다.']
        ],
        [
            9041, self::T_SIEGE, '충차',     
            150, 100, 6,  0, 0,  10,  3, 
            1000, null,     null,     3, 
            [self::T_CASTLE=>2.4],
            [],
            ['엄청난 위력으로 성벽을 부수어버립니다.']
        ],
        [
            9042, self::T_SIEGE, '벽력거',   
            200, 100, 6,  0, 0,  20,  4, 
            3000, ['업'],   null,     0, 
            [self::T_CASTLE=>1.8],
            [],
            ['상대에게 돌덩이를 날립니다.']
        ],
        [
            9043, self::T_SIEGE, '목우',      
            50, 200, 5,  0, 0,  15,  3, 
            3000, ['성도'], null,     0, 
            [],
            [],
            ['상대를 저지하는 특수병기입니다.']
        ]
    ];
}