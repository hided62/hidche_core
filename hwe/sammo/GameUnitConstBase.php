<?php
namespace sammo;

class GameUnitConstBase{
    protected function __construct(){

    }    

    const T_CASTLE = -1;
    const T_MISC = -2;
    const T_FOOTMAN = 0;
    const T_ARCHER = 1;
    const T_CAVALRY = 2;
    const T_WIZARD = 3;
    const T_SIEGE = 4;

    const DEFAULT_CREWTYPE = 0;
    
    

    protected static $constID = [];
    protected static $constName = [];
    protected static $constCity = [];
    protected static $constRegion = [];
    protected static $constType = [];

    protected static $typeData = [
        self::T_FOOTMAN => '보병',
        self::T_ARCHER => '궁병',
        self::T_CAVALRY => '기병',
        self::T_WIZARD => '귀병',
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
            0, self::T_FOOTMAN, '보병',
            100, 150, 7, 10, 0,   9,  9,    
            0, null,     null,     0, 
            [self::T_ARCHER=>1.2, self::T_CAVALRY=>0.8, self::T_SIEGE=>1.2],
            [self::T_ARCHER=>0.8, self::T_CAVALRY=>1.2, self::T_SIEGE=>0.8],
            ['표준적인 보병입니다.','보병은 방어특화이며, 상대가 회피하기 어렵습니다.'],
            null, null
        ],
        [
            1, self::T_FOOTMAN, '청주병',   
            100, 200, 7, 10, 0,  10, 11, 
            1000, null,     ['중원'], 0, 
            [self::T_ARCHER=>1.2, self::T_CAVALRY=>0.8, self::T_SIEGE=>1.2],
            [self::T_ARCHER=>0.8, self::T_CAVALRY=>1.2, self::T_SIEGE=>0.8],
            ['저렴하고 튼튼합니다.'],
            null, null
        ],
        [
            2, self::T_FOOTMAN, '수병',     
            150, 150, 7, 10, 0,  11, 10, 
            1000, null,     ['오월'], 0, 
            [self::T_ARCHER=>1.2, self::T_CAVALRY=>0.8, self::T_SIEGE=>1.2],
            [self::T_ARCHER=>0.8, self::T_CAVALRY=>1.2, self::T_SIEGE=>0.8],
            ['저렴하고 강력합니다.'],
            null, null
        ],
        [
            3, self::T_FOOTMAN, '자객병',   
            100, 150, 8, 20, 0,  10, 10, 
            2000, ['저'],   null,     0, 
            [self::T_ARCHER=>1.2, self::T_CAVALRY=>0.8, self::T_SIEGE=>1.2],
            [self::T_ARCHER=>0.8, self::T_CAVALRY=>1.2, self::T_SIEGE=>0.8],
            ['은밀하고 날쌥니다.'],
            null, null
        ],
        [
            4, self::T_FOOTMAN, '근위병',   
            150, 200, 7, 10, 0,  12, 12, 
            3000, ['낙양'], null,     0, 
            [self::T_ARCHER=>1.2, self::T_CAVALRY=>0.8, self::T_SIEGE=>1.2],
            [self::T_ARCHER=>0.8, self::T_CAVALRY=>1.2, self::T_SIEGE=>0.8],
            ['최강의 보병입니다.'],
            null, null
        ],
        [
            5, self::T_FOOTMAN, '등갑병',   
            100, 225, 7,  5, 0,  13, 10, 
            1000, null,     ['남중'], 0, 
            [self::T_ARCHER=>1.2, self::T_CAVALRY=>0.8, self::T_SIEGE=>1.2],
            [self::T_ARCHER=>0.8, self::T_CAVALRY=>1.2, self::T_SIEGE=>0.8],
            ['등갑을 두른 보병입니다.'],
            null, null
        ],

        [
            10, self::T_ARCHER, '궁병',     
            100, 100, 7, 20, 0,  10, 10,    
            0, null,     null,     0, 
            [self::T_CAVALRY=>1.2, self::T_FOOTMAN=>0.8, self::T_SIEGE=>1.2],
            [self::T_CAVALRY=>0.8, self::T_FOOTMAN=>1.2, self::T_SIEGE=>0.8],
            ['표준적인 궁병입니다.','궁병은 회피특화입니다.'],
            null, null
        ],
        [
            11, self::T_ARCHER, '궁기병',   
            100, 100, 8, 30, 0,  11, 12, 
            1000, null,     ['동이'], 0, 
            [self::T_CAVALRY=>1.2, self::T_FOOTMAN=>0.8, self::T_SIEGE=>1.2],
            [self::T_CAVALRY=>0.8, self::T_FOOTMAN=>1.2, self::T_SIEGE=>0.8],
            ['말을 타고 잘 피합니다.'],
            null, null
        ],
        [
            12, self::T_ARCHER, '연노병',   
            150, 100, 8, 20, 0,  12, 11, 
            1000, null,     ['서촉'], 0, 
            [self::T_CAVALRY=>1.2, self::T_FOOTMAN=>0.8, self::T_SIEGE=>1.2],
            [self::T_CAVALRY=>0.8, self::T_FOOTMAN=>1.2, self::T_SIEGE=>0.8],
            ['화살을 연사합니다.'],
            null, null
        ],
        [
            13, self::T_ARCHER, '강궁병',   
            150, 150, 7, 20, 0,  13, 13, 
            3000, ['양양'], null,     0, 
            [self::T_CAVALRY=>1.2, self::T_FOOTMAN=>0.8, self::T_SIEGE=>1.2],
            [self::T_CAVALRY=>0.8, self::T_FOOTMAN=>1.2, self::T_SIEGE=>0.8],
            ['강건한 궁병입니다.'],
            null, null
        ],
        [
            14, self::T_ARCHER, '석궁병',   
            200, 100, 7, 20, 0,  13, 13, 
            3000, ['건업'], null,     0, 
            [self::T_CAVALRY=>1.2, self::T_FOOTMAN=>0.8, self::T_SIEGE=>1.2],
            [self::T_CAVALRY=>0.8, self::T_FOOTMAN=>1.2, self::T_SIEGE=>0.8],
            ['강력한 화살을 쏩니다.'],
            null, null
        ],

        [
            20, self::T_CAVALRY, '기병',     
            150, 100, 7,  5, 0,  11, 11,    
            0, null,     null,     0, 
            [self::T_FOOTMAN=>1.2, self::T_ARCHER=>0.8, self::T_SIEGE=>1.2],
            [self::T_FOOTMAN=>0.8, self::T_ARCHER=>1.2, self::T_SIEGE=>0.8],
            ['표준적인 기병입니다.','기병은 공격특화입니다.'],
            null, null
        ],
        [
            21, self::T_CAVALRY, '백마병',   
            200, 100, 7,  5, 0,  12, 13, 
            1000, null,     ['하북'], 0, 
            [self::T_FOOTMAN=>1.2, self::T_ARCHER=>0.8, self::T_SIEGE=>1.2],
            [self::T_FOOTMAN=>0.8, self::T_ARCHER=>1.2, self::T_SIEGE=>0.8],
            ['백마의 위용을 보여줍니다.'],
            null, null
        ],
        [
            22, self::T_CAVALRY, '중장기병', 
            150, 150, 7,  5, 0,  13, 12, 
            1000, null,     ['서북'], 0, 
            [self::T_FOOTMAN=>1.2, self::T_ARCHER=>0.8, self::T_SIEGE=>1.2],
            [self::T_FOOTMAN=>0.8, self::T_ARCHER=>1.2, self::T_SIEGE=>0.8],
            ['갑주를 두른 기병입니다.'],
            null, null
        ],
        [
            23, self::T_CAVALRY, '돌격기병', 
            200, 100, 8,  5, 0,  13, 11, 
            2000, ['흉노'], null,     0, 
            [self::T_FOOTMAN=>1.2, self::T_ARCHER=>0.8, self::T_SIEGE=>1.2],
            [self::T_FOOTMAN=>0.8, self::T_ARCHER=>1.2, self::T_SIEGE=>0.8],
            ['저돌적으로 공격합니다.'],
            null, null
        ],
        [
            24, self::T_CAVALRY, '철기병',  
            100, 200, 7,  5, 0,  11, 13, 
            2000, ['강'],   null,     0, 
            [self::T_FOOTMAN=>1.2, self::T_ARCHER=>0.8, self::T_SIEGE=>1.2],
            [self::T_FOOTMAN=>0.8, self::T_ARCHER=>1.2, self::T_SIEGE=>0.8],
            ['철갑을 두른 기병입니다.'],
            null, null
        ],
        [
            25, self::T_CAVALRY, '수렵기병', 
            150, 100, 8, 15, 0,  12, 12, 
            2000, ['산월'], null,     0, 
            [self::T_FOOTMAN=>1.2, self::T_ARCHER=>0.8, self::T_SIEGE=>1.2],
            [self::T_FOOTMAN=>0.8, self::T_ARCHER=>1.2, self::T_SIEGE=>0.8],
            ['날쎄고 빠른 기병입니다.'],
            null, null
        ],
        [
            26, self::T_CAVALRY, '맹수병',   
            250, 175, 6,  0, 0,  16, 16, 
            2000, ['남만'], null,     0, 
            [self::T_FOOTMAN=>1.2, self::T_ARCHER=>0.8, self::T_SIEGE=>1.2],
            [self::T_FOOTMAN=>0.8, self::T_ARCHER=>1.2, self::T_SIEGE=>0.8],
            ['어느 누구보다 강력합니다.'],
            null, null
        ],
        [
            27, self::T_CAVALRY, '호표기병', 
            200, 150, 7,  5, 0,  14, 14, 
            3000, ['허창'], null,     0, 
            [self::T_FOOTMAN=>1.2, self::T_ARCHER=>0.8, self::T_SIEGE=>1.2],
            [self::T_FOOTMAN=>0.8, self::T_ARCHER=>1.2, self::T_SIEGE=>0.8],
            ['정예 기병입니다.'],
            null, null
        ],

        [
            30, self::T_WIZARD, '귀병',      
            80,  80, 7,  5, 0.5,  9,  9,    
            0, null,     null,     0, 
            [self::T_SIEGE=>1.2],
            [self::T_SIEGE=>0.8],
            ['계략을 사용하는 병종입니다.'],
            null, null
        ],
        [
            31, self::T_WIZARD, '신귀병',    
            80,  80, 7, 20, 0.6, 10, 10, 
            1000, null,     ['초'],   0, 
            [self::T_SIEGE=>1.2],
            [self::T_SIEGE=>0.8],
            ['신출귀몰한 귀병입니다.'],
            null, null
        ],
        [
            32, self::T_WIZARD, '백귀병',    
            80, 130, 7,  5, 0.6,  9, 11, 
            2000, ['오환'], null,     0, 
            [self::T_SIEGE=>1.2],
            [self::T_SIEGE=>0.8],
            ['저렴하고 튼튼합니다.'],
            null, null
        ],
        [
            33, self::T_WIZARD, '흑귀병',   
            130,  80, 7,  5, 0.6, 11,  9, 
            2000, ['왜'],   null,     0, 
            [self::T_SIEGE=>1.2],
            [self::T_SIEGE=>0.8],
            ['저렴하고 강력합니다.'],
            null, null
        ],
        [
            34, self::T_WIZARD, '악귀병',   
            130, 130, 7,  0, 0.6, 12, 12, 
            3000, ['장안'], null,     0, 
            [self::T_SIEGE=>1.2],
            [self::T_SIEGE=>0.8],
            ['백병전에도 능숙합니다.'],
            null, null
        ],
        [
            35, self::T_WIZARD, '남귀병',    
            60,  60, 7, 10, 0.8,  8,  8, 
            1000, null,     null,     0, 
            [self::T_SIEGE=>1.2],
            [self::T_SIEGE=>0.8],
            ['전투를 포기하고 계략에 몰두합니다.'],
            null, null
        ],
        [
            36, self::T_WIZARD, '황귀병',   
            110, 110, 7,  0, 0.8, 13, 10, 
            3000, ['낙양'], null,     0, 
            [self::T_SIEGE=>1.2],
            [self::T_SIEGE=>0.8],
            ['고도로 훈련된 귀병입니다.'],
            null, null
        ],
        [
            37, self::T_WIZARD, '천귀병',    
            80, 130, 7, 15, 0.6, 11, 12, 
            3000, ['성도'], null,     0, 
            [self::T_SIEGE=>1.2],
            [self::T_SIEGE=>0.8],
            ['갑주를 두른 귀병입니다.'],
            null, null
        ],
        [
            38, self::T_WIZARD, '마귀병',   
            130,  80, 7, 15, 0.6, 12, 11, 
            3000, ['업'],   null,     0, 
            [self::T_SIEGE=>1.2],
            [self::T_SIEGE=>0.8],
            ['날카로운 무기를 가진 귀병입니다.'],
            null, null
        ],

        [
            40, self::T_SIEGE, '정란',     
            100, 100, 6,  0, 0,  15,  5,    
            0, null,     null,     3, 
            [self::T_FOOTMAN=>0.8, self::T_ARCHER=>0.8, self::T_CAVALRY=>0.8, self::T_WIZARD=>0.8, self::T_CASTLE=>1.8],
            [self::T_FOOTMAN=>1.2, self::T_ARCHER=>1.2, self::T_CAVALRY=>1.2, self::T_WIZARD=>1.2],
            ['높은 구조물 위에서 공격합니다.'],
            ['che_성벽부상무효'], null
        ],
        [
            41, self::T_SIEGE, '충차',     
            150, 100, 6,  0, 0,  20,  5, 
            1000, null,     null,     3, 
            [self::T_FOOTMAN=>0.8, self::T_ARCHER=>0.8, self::T_CAVALRY=>0.8, self::T_WIZARD=>0.8, self::T_CASTLE=>2.4],
            [self::T_FOOTMAN=>1.2, self::T_ARCHER=>1.2, self::T_CAVALRY=>1.2, self::T_WIZARD=>1.2],
            ['엄청난 위력으로 성벽을 부수어버립니다.'],
            ['che_성벽부상무효'], null
        ],
        [
            42, self::T_SIEGE, '벽력거',   
            200, 100, 6,  0, 0,  25,  5, 
            3000, ['업'],   null,     0, 
            [self::T_FOOTMAN=>0.8, self::T_ARCHER=>0.8, self::T_CAVALRY=>0.8, self::T_WIZARD=>0.8, self::T_CASTLE=>1.8],
            [self::T_FOOTMAN=>1.2, self::T_ARCHER=>1.2, self::T_CAVALRY=>1.2, self::T_WIZARD=>1.2],
            ['상대에게 돌덩이를 날립니다.'],
            ['che_성벽부상무효'], null
        ],
        [
            43, self::T_SIEGE, '목우',      
            50, 200, 5,  0, 0,  30,  5, 
            3000, ['성도'], null,     0, 
            [self::T_FOOTMAN=>0.8, self::T_ARCHER=>0.8, self::T_CAVALRY=>0.8, self::T_WIZARD=>0.8],
            [self::T_FOOTMAN=>1.2, self::T_ARCHER=>1.2, self::T_CAVALRY=>1.2, self::T_WIZARD=>1.2],
            ['상대를 저지하는 특수병기입니다.'],
            ['che_성벽부상무효'], ['che_저지시도', 'che_저지발동']
        ]
    ];

    public static function addGameUnit(GameUnitDetail $unitType){
        static::_generate();

        static::$constID[$unitType->id] = $unitType;
        static::$constName[$unitType->name] = $unitType;

        if(!key_exists($unitType->armType, static::$constType)){
            static::$constType[$unitType->armType] = [];
        }
        static::$constType[$unitType->armType][] = $unitType;

        foreach($unitType->reqCities as $reqCity){
            if(!key_exists($reqCity, static::$constCity)){
                static::$constCity[$reqCity] = [];
            }
            static::$constCity[$reqCity][] = $unitType;
        }

        if($unitType->reqRegions){
            foreach($unitType->reqRegions as $reqRegion){
                if(!key_exists($reqRegion, static::$constRegion)){
                    static::$constRegion[$reqRegion] = [];
                }
                static::$constRegion[$reqRegion][] = $unitType;
            }
        }
    }

    /**
     * @return \sammo\GameUnitDetail[]
     */
    public static function all(): array{
        static::_generate();
        return static::$constID;
    }

    public static function allType(): array{
        return static::$typeData;
    }

    public static function byID(int $id): ?GameUnitDetail{
        static::_generate();
        return static::$constID[$id]??null;
    }

    public static function byName(string $name): ?GameUnitDetail{
        static::_generate();
        return static::$constName[$name]??null;
    }

    /**
     * @return \sammo\GameUnitDetail[]
     */
    public static function byCity(int $city): array{
        static::_generate();
        return static::$constCity[$city]??[];
    }

    /**
     * @return \sammo\GameUnitDetail[]
     */
    public static function byRegion(int $region): array{
        static::_generate();
        return static::$constRegion[$region]??[];
    }

    
    /**
     * @return \sammo\GameUnitDetail[]
     */
    public static function byType(int $type): array{
        static::_generate();
        if(!key_exists($type, static::$constType)){
            return [];
        }

        return static::$constType[$type];
    }

    protected static function _generateOptional(){
        //for inheritance
    }

    protected static function _generate(){

        if(static::$constID || static::$constName || static::$constCity || static::$constRegion || static::$constType){
            return;
        }

        $constID = [];
        $constName = [];
        $constCity = [];
        $constRegion = [];
        $constType = [];

        foreach(static::$_buildData as $rawUnit){
            [
                $id,
                $armType,
                $name, 
                $attack,
                $defence,
                $speed,
                $avoid,
                $magicCoef,
                $cost,
                $rice,
                $reqTech,
                $reqCities,
                $reqRegions,
                $reqYear,
                $attackCoef,
                $defenceCoef,
                $info,
                $initSkillTrigger,
                $phaseSkillTrigger,
             ] = $rawUnit;

            if($reqYear > 0){
                $info[] = "{$reqYear}년 경과 후 사용 가능";
            }

            if($reqTech > 0){
                $info[] = "기술력 {$reqTech} 이상 필요";
            }

            if($reqCities !== null){
                $reqCities = array_map(function($reqCity) use (&$info){
                    $info[] = "{$reqCity} 소유시 가능";
                    return CityConst::byName($reqCity)->id;
                }, $reqCities);
            }

            if($reqRegions !== null){
                $reqRegions = array_map(function($reqRegion) use (&$info){
                    $info[] = "{$reqRegion} 지역 소유시 가능";
                    return CityConst::$regionMap[$reqRegion];
                }, $reqRegions);
            }
            
            $unit = new GameUnitDetail(
                $id,
                $armType,
                $name, 
                $attack,
                $defence,
                $speed,
                $avoid,
                $magicCoef,
                $cost,
                $rice,
                $reqTech,
                $reqCities,
                $reqRegions,
                $reqYear,
                $attackCoef,
                $defenceCoef,
                $info,
                $initSkillTrigger,
                $phaseSkillTrigger
            );

            $constID[$id] = $unit;
            $constName[$name] = $unit;
            if(!key_exists($armType, $constType)){
                $constType[$armType] = [];
            }
            $constType[$armType][] = $unit;

            if($unit->reqCities){
                foreach($unit->reqCities as $reqCity){
                    if(!key_exists($reqCity, $constCity)){
                        $constCity[$reqCity] = [];
                    }
                    $constCity[$reqCity][] = $unit;
                }
            }
            

            if($unit->reqRegions){
                foreach($unit->reqRegions as $reqRegion){
                    if(!key_exists($reqRegion, $constRegion)){
                        $constRegion[$reqRegion] = [];
                    }
                    $constRegion[$reqRegion][] = $unit;
                }
            }

        }

        static::$constID = $constID;
        static::$constName = $constName;
        static::$constCity = $constCity;
        static::$constRegion = $constRegion;
        static::$constType = $constType;

        static::_generateOptional();
    }
}