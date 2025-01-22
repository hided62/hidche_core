<?php
namespace sammo;

use sammo\GameUnitConstraint\Impossible;
use sammo\GameUnitConstraint\ReqCities;
use sammo\GameUnitConstraint\ReqMinRelYear;
use sammo\GameUnitConstraint\ReqRegions;
use sammo\GameUnitConstraint\ReqTech;

class GameUnitConst extends GameUnitConstBase
{
    protected static function getBuildData(): array {
        return [
            [
                1000, self::T_CASTLE, '성벽',
                100, 100, 7, 0, 0,   99,  9,
                [new Impossible()],
                [], // 성벽은 공격할 수 없다.
                [self::T_FOOTMAN=>1.2],
                ['성벽입니다.','생성할 수 없습니다.'],
                null, ['che_선제사격시도', 'che_선제사격발동'], null
            ],

            [
                1100, self::T_FOOTMAN, '보병',
                100, 150, 7, 10, 0,   9,  9,
                [],
                [self::T_ARCHER=>1.2, self::T_CAVALRY=>0.8, self::T_SIEGE=>1.2],
                [self::T_ARCHER=>0.8, self::T_CAVALRY=>1.2, self::T_SIEGE=>0.8],
                ['표준적인 보병입니다.','보병은 방어특화이며,','상대가 회피하기 어렵습니다.'],
                null, ['che_방어력증가5p'], null
            ],
            [
                1101, self::T_FOOTMAN, '청주병',
                100, 200, 7, 10, 0,  10, 11,
                [new ReqTech(1000), new ReqRegions('중원')],
                [self::T_ARCHER=>1.2, self::T_CAVALRY=>0.8, self::T_SIEGE=>1.2],
                [self::T_ARCHER=>0.8, self::T_CAVALRY=>1.2, self::T_SIEGE=>0.8],
                ['저렴하고 튼튼합니다.'],
                null, ['che_방어력증가5p'], null
            ],
            [
                1102, self::T_FOOTMAN, '수병',
                150, 150, 7, 10, 0,  11, 10,
                [new ReqTech(1000), new ReqRegions('오월')],
                [self::T_ARCHER=>1.2, self::T_CAVALRY=>0.8, self::T_SIEGE=>1.2],
                [self::T_ARCHER=>0.8, self::T_CAVALRY=>1.2, self::T_SIEGE=>0.8],
                ['저렴하고 강력합니다.'],
                null, ['che_방어력증가5p'], null
            ],
            [
                1103, self::T_FOOTMAN, '자객병',
                100, 150, 8, 20, 0,  10, 10,
                [new ReqTech(2000), new ReqCities('저')],
                [self::T_ARCHER=>1.2, self::T_CAVALRY=>0.8, self::T_SIEGE=>1.2],
                [self::T_ARCHER=>0.8, self::T_CAVALRY=>1.2, self::T_SIEGE=>0.8],
                ['은밀하고 날쌥니다.'],
                null, ['che_방어력증가5p'], null
            ],
            [
                1104, self::T_FOOTMAN, '근위병',
                150, 200, 7, 10, 0,  12, 12,
                [new ReqTech(3000), new ReqCities('낙양')],
                [self::T_ARCHER=>1.2, self::T_CAVALRY=>0.8, self::T_SIEGE=>1.2],
                [self::T_ARCHER=>0.8, self::T_CAVALRY=>1.2, self::T_SIEGE=>0.8],
                ['최강의 보병입니다.'],
                null, ['che_방어력증가5p'], null
            ],
            [
                1105, self::T_FOOTMAN, '등갑병',
                100, 225, 7,  5, 0,  13, 10,
                [new ReqTech(1000), new ReqRegions('남중')],
                [self::T_ARCHER=>1.2, self::T_CAVALRY=>0.8, self::T_SIEGE=>1.2],
                [self::T_ARCHER=>0.8, self::T_CAVALRY=>1.2, self::T_SIEGE=>0.8],
                ['등갑을 두른 보병입니다.'],
                null, ['che_방어력증가5p'], null
            ],
            [
                1106, self::T_FOOTMAN, '백이병',
                175, 175, 7, 5, 0,  13, 11,
                [new ReqTech(3000), new ReqCities('성도')],
                [self::T_ARCHER=>1.1, self::T_CAVALRY=>0.9, self::T_SIEGE=>1.1],
                [self::T_ARCHER=>0.9, self::T_CAVALRY=>1.1, self::T_SIEGE=>0.9],
                ['정예 보병입니다. 불리한 싸움도 버텨냅니다.'],
                null, ['che_방어력증가5p'], null
            ],

            [
                1200, self::T_ARCHER, '궁병',
                100, 100, 7, 10, 0,  10, 10,
                [],
                [self::T_CAVALRY=>1.2, self::T_FOOTMAN=>0.8, self::T_SIEGE=>1.2],
                [self::T_CAVALRY=>0.8, self::T_FOOTMAN=>1.2, self::T_SIEGE=>0.8],
                ['표준적인 궁병입니다.','궁병은 선제사격을 하는 병종입니다.'],
                null, ['che_선제사격시도', 'che_선제사격발동'], null
            ],
            [
                1201, self::T_ARCHER, '궁기병',
                100, 100, 8, 20, 0,  11, 12,
                [new ReqTech(1000), new ReqRegions('동이')],
                [self::T_CAVALRY=>1.2, self::T_FOOTMAN=>0.9, self::T_SIEGE=>1.2],
                [self::T_CAVALRY=>0.8, self::T_FOOTMAN=>1.1, self::T_SIEGE=>0.8],
                ['말을 타고 잘 피합니다. 특히 다른 궁병보다 보병에게 조금 더 강합니다.'],
                null, ['che_선제사격시도', 'che_선제사격발동'], null
            ],
            [
                1202, self::T_ARCHER, '연노병',
                150, 100, 8, 10, 0,  12, 11,
                [new ReqTech(1000), new ReqRegions('서촉')],
                [self::T_CAVALRY=>1.2, self::T_FOOTMAN=>0.8, self::T_SIEGE=>1.2],
                [self::T_CAVALRY=>0.8, self::T_FOOTMAN=>1.2, self::T_SIEGE=>0.8],
                ['화살을 연사합니다.'],
                null, ['che_선제사격시도', 'che_선제사격발동'], null
            ],
            [
                1203, self::T_ARCHER, '강궁병',
                150, 150, 7, 10, 0,  13, 13,
                [new ReqTech(3000), new ReqCities('양양')],
                [self::T_CAVALRY=>1.2, self::T_FOOTMAN=>0.8, self::T_SIEGE=>1.2],
                [self::T_CAVALRY=>0.8, self::T_FOOTMAN=>1.2, self::T_SIEGE=>0.8],
                ['강건한 궁병입니다.'],
                null, ['che_선제사격시도', 'che_선제사격발동'], null
            ],
            [
                1204, self::T_ARCHER, '석궁병',
                200, 100, 7, 10, 0,  13, 13,
                [new ReqTech(3000), new ReqCities('건업')],
                [self::T_CAVALRY=>1.2, self::T_FOOTMAN=>0.8, self::T_SIEGE=>1.2],
                [self::T_CAVALRY=>0.8, self::T_FOOTMAN=>1.2, self::T_SIEGE=>0.8],
                ['강력한 화살을 쏩니다.'],
                null, ['che_선제사격시도', 'che_선제사격발동'], null
            ],

            [
                1300, self::T_CAVALRY, '기병',
                150, 100, 7,  5, 0,  11, 11,
                [],
                [self::T_FOOTMAN=>1.2, self::T_ARCHER=>0.8, self::T_SIEGE=>1.2],
                [self::T_FOOTMAN=>0.8, self::T_ARCHER=>1.2, self::T_SIEGE=>0.8],
                ['표준적인 기병입니다.','기병은 공격특화입니다.'],
                null, ['che_기병병종전투'], null
            ],
            [
                1301, self::T_CAVALRY, '백마병',
                200, 100, 7,  5, 0,  12, 13,
                [new ReqTech(1000), new ReqRegions('하북')],
                [self::T_FOOTMAN=>1.2, self::T_ARCHER=>0.8, self::T_SIEGE=>1.2],
                [self::T_FOOTMAN=>0.8, self::T_ARCHER=>1.2, self::T_SIEGE=>0.8],
                ['백마의 위용을 보여줍니다.'],
                null, ['che_기병병종전투'], null
            ],
            [
                1302, self::T_CAVALRY, '중장기병',
                150, 150, 7,  5, 0,  13, 12,
                [new ReqTech(1000), new ReqRegions('서북')],
                [self::T_FOOTMAN=>1.2, self::T_ARCHER=>0.8, self::T_SIEGE=>1.2],
                [self::T_FOOTMAN=>0.8, self::T_ARCHER=>1.2, self::T_SIEGE=>0.8],
                ['갑주를 두른 기병입니다.'],
                null, ['che_기병병종전투'], null
            ],
            [
                1303, self::T_CAVALRY, '돌격기병',
                200, 100, 8,  5, 0,  13, 11,
                [new ReqTech(2000), new ReqCities('흉노')],
                [self::T_FOOTMAN=>1.2, self::T_ARCHER=>0.8, self::T_SIEGE=>1.2],
                [self::T_FOOTMAN=>0.8, self::T_ARCHER=>1.2, self::T_SIEGE=>0.8],
                ['저돌적으로 공격합니다.'],
                null, ['che_기병병종전투'], null
            ],
            [
                1304, self::T_CAVALRY, '철기병',
                100, 200, 7,  5, 0,  11, 13,
                [new ReqTech(2000), new ReqCities('강')],
                [self::T_FOOTMAN=>1.2, self::T_ARCHER=>0.8, self::T_SIEGE=>1.2],
                [self::T_FOOTMAN=>0.8, self::T_ARCHER=>1.2, self::T_SIEGE=>0.8],
                ['철갑을 두른 기병입니다.'],
                null, ['che_기병병종전투'], null
            ],
            [
                1305, self::T_CAVALRY, '수렵기병',
                150, 100, 8, 15, 0,  12, 12,
                [new ReqTech(2000), new ReqCities('산월')],
                [self::T_FOOTMAN=>1.2, self::T_ARCHER=>0.8, self::T_SIEGE=>1.2],
                [self::T_FOOTMAN=>0.8, self::T_ARCHER=>1.2, self::T_SIEGE=>0.8],
                ['날쎄고 빠른 기병입니다.'],
                null, ['che_기병병종전투'], null
            ],
            [
                1306, self::T_CAVALRY, '맹수병',
                250, 175, 6,  0, 0,  16, 16,
                [new ReqTech(2000), new ReqCities('남만')],
                [self::T_FOOTMAN=>1.2, self::T_ARCHER=>0.8, self::T_SIEGE=>1.2],
                [self::T_FOOTMAN=>0.8, self::T_ARCHER=>1.2, self::T_SIEGE=>0.8],
                ['어느 누구보다 강력합니다.'],
                null, ['che_기병병종전투'], null
            ],
            [
                1307, self::T_CAVALRY, '호표기병',
                200, 150, 7,  5, 0,  14, 14,
                [new ReqTech(3000), new ReqCities('허창')],
                [self::T_FOOTMAN=>1.2, self::T_ARCHER=>0.8, self::T_SIEGE=>1.2],
                [self::T_FOOTMAN=>0.8, self::T_ARCHER=>1.2, self::T_SIEGE=>0.8],
                ['정예 기병입니다.'],
                null, ['che_기병병종전투'], null
            ],

            [
                1400, self::T_WIZARD, '귀병',
                80,  80, 7,  5, 0.5,  9,  9,
                [],
                [self::T_SIEGE=>1.2],
                [self::T_SIEGE=>0.8],
                ['계략을 사용하는 병종입니다.'],
                null, null, null
            ],
            [
                1401, self::T_WIZARD, '신귀병',
                80,  80, 7, 20, 0.6, 10, 10,
                [new ReqTech(1000), new ReqRegions('초')],
                [self::T_SIEGE=>1.2],
                [self::T_SIEGE=>0.8],
                ['신출귀몰한 귀병입니다.'],
                null, null, null
            ],
            [
                1402, self::T_WIZARD, '백귀병',
                80, 130, 7,  5, 0.6,  9, 11,
                [new ReqTech(2000), new ReqCities('오환')],
                [self::T_SIEGE=>1.2],
                [self::T_SIEGE=>0.8],
                ['저렴하고 튼튼합니다.'],
                null, null, null
            ],
            [
                1403, self::T_WIZARD, '흑귀병',
                130,  80, 7,  5, 0.6, 11,  9,
                [new ReqTech(2000), new ReqCities('왜')],
                [self::T_SIEGE=>1.2],
                [self::T_SIEGE=>0.8],
                ['저렴하고 강력합니다.'],
                null, null, null
            ],
            [
                1404, self::T_WIZARD, '악귀병',
                130, 130, 7,  0, 0.6, 12, 12,
                [new ReqTech(3000), new ReqCities('장안')],
                [self::T_SIEGE=>1.2],
                [self::T_SIEGE=>0.8],
                ['백병전에도 능숙합니다.'],
                null, null, null
            ],
            [
                1405, self::T_WIZARD, '남귀병',
                60,  60, 7, 10, 0.8,  8,  8,
                [new ReqTech(1000)],
                [self::T_SIEGE=>1.2],
                [self::T_SIEGE=>0.8],
                ['전투를 포기하고 계략에 몰두합니다.'],
                null, null, null
            ],
            [
                1406, self::T_WIZARD, '황귀병',
                110, 110, 7,  0, 0.8, 13, 10,
                [new ReqTech(3000), new ReqCities('낙양')],
                [self::T_SIEGE=>1.2],
                [self::T_SIEGE=>0.8],
                ['고도로 훈련된 귀병입니다.'],
                null, null, null
            ],
            [
                1407, self::T_WIZARD, '천귀병',
                80, 130, 7, 15, 0.6, 11, 12,
                [new ReqTech(3000), new ReqCities('성도')],
                [self::T_SIEGE=>1.2],
                [self::T_SIEGE=>0.8],
                ['갑주를 두른 귀병입니다.'],
                null, null, null
            ],
            [
                1408, self::T_WIZARD, '마귀병',
                130,  80, 7, 15, 0.6, 12, 11,
                [new ReqTech(3000), new ReqCities('업')],
                [self::T_SIEGE=>1.2],
                [self::T_SIEGE=>0.8],
                ['날카로운 무기를 가진 귀병입니다.'],
                null, null, null
            ],

            [
                1500, self::T_SIEGE, '정란',
                100, 100, 6,  0, 0,  14,  5,
                [new ReqMinRelYear(3)],
                [self::T_FOOTMAN=>1.25, self::T_ARCHER=>1.25, self::T_CAVALRY=>1.25, self::T_WIZARD=>1.25, self::T_CASTLE=>1.2, 1106=>1.112],
                [self::T_FOOTMAN=>1.2, self::T_ARCHER=>1.2, self::T_CAVALRY=>1.2, self::T_WIZARD=>1.2, 1106=>1.067],
                ['높은 구조물 위에서 공격합니다. 첫 공격은 성벽을 향합니다.'],
                ['che_성벽부상무효'], ['che_선제사격시도', 'che_선제사격발동'], ['che_성벽선제']
            ],
            [
                1502, self::T_SIEGE, '벽력거',
                150, 100, 6,  5, 0,  20,  5,
                [new ReqTech(3000), new ReqCities('업')],
                [self::T_FOOTMAN=>1.25, self::T_ARCHER=>1.25, self::T_CAVALRY=>1.25, self::T_WIZARD=>1.25, self::T_CASTLE=>1.2, 1106=>1.112],
                [self::T_FOOTMAN=>1.2, self::T_ARCHER=>1.2, self::T_CAVALRY=>1.2, self::T_WIZARD=>1.2, 1106=>1.067],
                ['상대에게 돌덩이를 날립니다. 첫 공격은 성벽을 향합니다.'],
                ['che_성벽부상무효'], ['che_선제사격시도', 'che_선제사격발동'], ['che_성벽선제']
            ],
            [
                1503, self::T_SIEGE, '목우',
                50, 200, 5,  0, 0,  15,  5,
                [new ReqTech(3000), new ReqCities('성도')],
                [self::T_FOOTMAN=>1, self::T_ARCHER=>1, self::T_CAVALRY=>1, self::T_WIZARD=>1, self::T_CASTLE=>1.2],
                [self::T_FOOTMAN=>1, self::T_ARCHER=>1, self::T_CAVALRY=>1, self::T_WIZARD=>1, 1106=>1],
                ['상대를 저지하는 특수병기입니다.'],
                ['che_성벽부상무효'], ['che_저지시도', 'che_저지발동'], null
            ]
        ];
    }
}