<?php

namespace sammo;

class GameUnitConst extends GameUnitConstBase
{
  const DEFAULT_CREWTYPE = 1100;

  protected static $_buildData = [
    [
      1000, self::T_CASTLE, '성벽',
      100, 100, 7, 0, 0,   99,  9,
      999999, null,     null,     999999,
      [], //성벽은 공격할 수 없다.
      [self::T_FOOTMAN => 1.2],
      ['성벽입니다.', '생성할 수 없습니다.'],
      null, null
    ],

    [
      1100, self::T_FOOTMAN, '보병',
      100, 150, 7, 10, 0,   9,  9,
      0, null,     null,     0,
      [self::T_ARCHER => 1.2, self::T_CAVALRY => 0.8, self::T_SIEGE => 1.2],
      [self::T_ARCHER => 0.8, self::T_CAVALRY => 1.2, self::T_SIEGE => 0.8],
      ['표준적인 보병입니다.', '보병은 방어특화이며,', '상대가 회피하기 어렵습니다.'],
      null, null
    ],
    [
      1200, self::T_ARCHER, '궁병',
      100, 100, 7, 20, 0,  10, 10,
      0, null,     null,     0,
      [self::T_CAVALRY => 1.2, self::T_FOOTMAN => 0.8, self::T_SIEGE => 1.2],
      [self::T_CAVALRY => 0.8, self::T_FOOTMAN => 1.2, self::T_SIEGE => 0.8],
      ['표준적인 궁병입니다.', '궁병은 회피특화입니다.'],
      null, null
    ],
    [
      1300, self::T_CAVALRY, '기병',
      150, 100, 7,  5, 0,  11, 11,
      0, null,     null,     0,
      [self::T_FOOTMAN => 1.2, self::T_ARCHER => 0.8, self::T_SIEGE => 1.2],
      [self::T_FOOTMAN => 0.8, self::T_ARCHER => 1.2, self::T_SIEGE => 0.8],
      ['표준적인 기병입니다.', '기병은 공격특화입니다.'],
      null, null
    ],
    [
      1104, self::T_FOOTMAN, '근위병',
      150, 200, 7, 10, 0,  12, 12,
      2000, ['낙양'], null,     0,
      [self::T_ARCHER => 1.2, self::T_CAVALRY => 0.8, self::T_SIEGE => 1.2],
      [self::T_ARCHER => 0.8, self::T_CAVALRY => 1.2, self::T_SIEGE => 0.8],
      ['최강의 보병입니다.'],
      null, ['che_방어력증가5p']
    ],
    [
      1106, self::T_FOOTMAN, '백이병',
      175, 175, 7, 5, 0,  13, 11,
      2000, ['성도'], null,     0,
      [self::T_ARCHER => 1.1, self::T_CAVALRY => 0.9, self::T_SIEGE => 1.1],
      [self::T_ARCHER => 0.9, self::T_CAVALRY => 1.1, self::T_SIEGE => 0.9],
      ['정예 보병입니다. 불리한 싸움도 버텨냅니다.'],
      null, ['che_방어력증가5p']
    ],
    [
      97201, self::T_ARCHER, '화랑',
      175, 150, 8, 15, 0.05,  13, 13,
      2000, ['한', '서라벌'], null, 0,
      [self::T_CAVALRY => 1.2, self::T_FOOTMAN => 0.9, self::T_SIEGE => 1.2],
      [self::T_CAVALRY => 0.8, self::T_FOOTMAN => 1.1, self::T_SIEGE => 0.8],
      ['특수한 궁병입니다.'],
      null, ['che_선제사격시도', 'che_선제사격발동']
    ],
    [
      1204, self::T_ARCHER, '석궁병',
      200, 125, 7, 10, 0,  13, 13,
      2000, ['건업'], null,     0,
      [self::T_CAVALRY => 1.2, self::T_FOOTMAN => 0.8, self::T_SIEGE => 1.2],
      [self::T_CAVALRY => 0.8, self::T_FOOTMAN => 1.2, self::T_SIEGE => 0.8],
      ['강력한 화살을 쏩니다.'],
      null, ['che_선제사격시도', 'che_선제사격발동']
    ],
    [
      1303, self::T_CAVALRY, '돌격기병',
      200, 125, 8,  10, 0,  13, 12,
      2000, ['서량', '안정'], null,     0,
      [self::T_FOOTMAN => 1.2, self::T_ARCHER => 0.8, self::T_SIEGE => 1.2],
      [self::T_FOOTMAN => 0.8, self::T_ARCHER => 1.2, self::T_SIEGE => 0.8],
      ['저돌적으로 공격합니다.'],
      null, ['che_기병병종전투']
    ],
    [
      1307, self::T_CAVALRY, '호표기병',
      200, 150, 7,  5, 0,  14, 14,
      2000, ['허창'], null,     0,
      [self::T_FOOTMAN => 1.2, self::T_ARCHER => 0.8, self::T_SIEGE => 1.2],
      [self::T_FOOTMAN => 0.8, self::T_ARCHER => 1.2, self::T_SIEGE => 0.8],
      ['정예 기병입니다.'],
      null, ['che_기병병종전투']
    ],
    [
      1306, self::T_CAVALRY, '맹수병',
      250, 175, 6,  0, 0,  16, 16,
      2000, ['운남'], null,     0,
      [self::T_FOOTMAN => 1.2, self::T_ARCHER => 0.8, self::T_SIEGE => 1.2],
      [self::T_FOOTMAN => 0.8, self::T_ARCHER => 1.2, self::T_SIEGE => 0.8],
      ['어느 누구보다 강력합니다.'],
      null, ['che_기병병종전투']
    ],
    [
      1400, self::T_WIZARD, '귀병',
      80,  80, 7,  5, 0.5,  9,  9,
      0, null,     null,     0,
      [self::T_SIEGE => 1.2],
      [self::T_SIEGE => 0.8],
      ['계략을 사용하는 병종입니다.'],
      null, null
    ],
    [
      1405, self::T_WIZARD, '남귀병',
      60,  60, 7, 10, 0.8,  8,  8,
      1000, null,     null,     0,
      [self::T_SIEGE => 1.2],
      [self::T_SIEGE => 0.8],
      ['전투를 포기하고 계략에 몰두합니다.'],
      null, null
    ],
    [
      1404, self::T_WIZARD, '악귀병',
      130, 130, 7,  0, 0.6, 12, 12,
      2000, ['장안'], null,     0,
      [self::T_SIEGE => 1.2],
      [self::T_SIEGE => 0.8],
      ['백병전에도 능숙합니다.'],
      null, null
    ],
    [
      1407, self::T_WIZARD, '천귀병',
      90, 130, 7, 15, 0.6, 11, 12,
      2000, ['성도'], null,     0,
      [self::T_SIEGE => 1.2],
      [self::T_SIEGE => 0.8],
      ['갑주를 두른 귀병입니다.'],
      null, null
    ],
    [
      1500, self::T_SIEGE, '정란',
      100, 100, 6,  0, 0,  15,  5,
      0, null,     null,     3,
      [self::T_FOOTMAN => 0.8, self::T_ARCHER => 0.8, self::T_CAVALRY => 0.8, self::T_WIZARD => 0.8, self::T_CASTLE => 1.8],
      [self::T_FOOTMAN => 1.2, self::T_ARCHER => 1.2, self::T_CAVALRY => 1.2, self::T_WIZARD => 1.2],
      ['높은 구조물 위에서 공격합니다.'],
      ['che_성벽부상무효'], null
    ],
    [
      1501, self::T_SIEGE, '충차',
      150, 100, 6,  0, 0,  20,  5,
      1000, null,     null,     3,
      [self::T_FOOTMAN => 0.8, self::T_ARCHER => 0.8, self::T_CAVALRY => 0.8, self::T_WIZARD => 0.8, self::T_CASTLE => 2.4],
      [self::T_FOOTMAN => 1.2, self::T_ARCHER => 1.2, self::T_CAVALRY => 1.2, self::T_WIZARD => 1.2],
      ['엄청난 위력으로 성벽을 부수어버립니다.'],
      ['che_성벽부상무효'], null
    ],
    [
      1502, self::T_SIEGE, '벽력거',
      135, 100, 6,  5, 0,  20,  5,
      2000, ['업'],   null,     0,
      [self::T_FOOTMAN => 1.25, self::T_ARCHER => 1.25, self::T_CAVALRY => 1.25, self::T_WIZARD => 1.25, self::T_CASTLE => 1.8, 1106 => 1.112],
      [self::T_FOOTMAN => 0.833, self::T_ARCHER => 0.833, self::T_CAVALRY => 0.833, self::T_WIZARD => 0.833, 1106 => 0.909],
      ['상대에게 돌덩이를 날립니다.'],
      ['che_성벽부상무효'], ['che_선제사격시도', 'che_선제사격발동']
    ],
    [
      97101, self::T_FOOTMAN, '중장보병',
      150,  175, 7, 10, 0, 12, 12,
      3000, null,     null,   0,
      [self::T_ARCHER => 1.2, self::T_CAVALRY => 0.8, self::T_SIEGE => 1.2],
      [self::T_ARCHER => 0.8, self::T_CAVALRY => 1.2, self::T_SIEGE => 0.8],
      ['전천후 보병입니다.'],
      null, ['che_방어력증가5p']
    ],
    [
      1202, self::T_ARCHER, '연노병',
      150, 175, 8, 10, 0,  12, 12,
      3000, null,     null, 0,
      [self::T_CAVALRY => 1.2, self::T_FOOTMAN => 0.8, self::T_SIEGE => 1.2],
      [self::T_CAVALRY => 0.8, self::T_FOOTMAN => 1.2, self::T_SIEGE => 0.8],
      ['화살을 연사합니다.'],
      null, ['che_선제사격시도', 'che_선제사격발동']
    ],
    [
      1304, self::T_CAVALRY, '철기병',
      175, 175, 7,  5, 0,  13, 13,
      3000, null,   null,     0,
      [self::T_FOOTMAN => 1.2, self::T_ARCHER => 0.8, self::T_SIEGE => 1.2],
      [self::T_FOOTMAN => 0.8, self::T_ARCHER => 1.2, self::T_SIEGE => 0.8],
      ['철갑을 두른 기병입니다.'],
      null, ['che_기병병종전투']
    ],
    [
      1401, self::T_WIZARD, '신귀병',
      100,  100, 7, 20, 0.6, 12, 12,
      3000, null,     null,   0,
      [self::T_SIEGE => 1.2],
      [self::T_SIEGE => 0.8],
      ['신출귀몰한 귀병입니다.'],
      null, null
    ],
  ];
}
