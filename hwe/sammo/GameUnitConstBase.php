<?php
namespace sammo;

class GameUnitConstBase{
    protected function __construct(){

    }

    const T_CASTLE = 0;
    const T_FOOTMAN = 1;
    const T_ARCHER = 2;
    const T_CAVALRY = 3;
    const T_WIZARD = 4;
    const T_SIEGE = 5;
    const T_MISC = 6;

    protected static $constID = null;
    protected static $constName = null;
    protected static $constCity = null;
    protected static $constRegion = null;
    protected static $constType = null;

    protected static $typeData = [
        GameUnitConstBase::T_FOOTMAN => '보병',
        GameUnitConstBase::T_ARCHER => '궁병',
        GameUnitConstBase::T_CAVALRY => '기병',
        GameUnitConstBase::T_WIZARD => '귀병',
        GameUnitConstBase::T_SIEGE => '차병',
    ];

    protected static $_buildData = [
        [ 0, GameUnitConstBase::T_FOOTMAN, '보병',     100, 150, 7, 10,  9,  9,    0, null,     null,     0, ['표준적인 보병입니다.','보병은 방어특화입니다.']],
        [ 1, GameUnitConstBase::T_FOOTMAN, '청주병',   100, 200, 7, 10, 10, 11, 1000, null,     ['중원'], 0, ['저렴하고 튼튼합니다.']],
        [ 2, GameUnitConstBase::T_FOOTMAN, '수병',     150, 150, 7, 10, 11, 10, 1000, null,     ['오월'], 0, ['저렴하고 강력합니다.']],
        [ 3, GameUnitConstBase::T_FOOTMAN, '자객병',   100, 150, 7, 20, 10, 10, 2000, ['저'],   null,     0, ['은밀하고 날쌥니다.']],
        [ 4, GameUnitConstBase::T_FOOTMAN, '근위병',   150, 200, 7, 10, 12, 12, 3000, ['낙양'], null,     0, ['최강의 보병입니다.']],
        [ 5, GameUnitConstBase::T_FOOTMAN, '등갑병',   100, 250, 7,  5, 13, 10, 1000, null,     ['남중'], 0, ['등갑을 두른 보병입니다.']],

        [10, GameUnitConstBase::T_ARCHER, '궁병',     100, 100, 7, 20, 10, 10,    0, null,     null,     0, ['표준적인 궁병입니다.','궁병은 회피특화입니다.']],
        [11, GameUnitConstBase::T_ARCHER, '궁기병',   100, 100, 8, 30, 11, 12, 1000, null,     ['동이'], 0, ['말을 타고 잘 피합니다.']],
        [12, GameUnitConstBase::T_ARCHER, '연노병',   150, 100, 8, 20, 12, 11, 1000, null,     ['서촉'], 0, ['화살을 연사합니다.']],
        [13, GameUnitConstBase::T_ARCHER, '강궁병',   150, 150, 7, 20, 13, 13, 3000, ['양양'], null,     0, ['강건한 궁병입니다.']],
        [14, GameUnitConstBase::T_ARCHER, '석궁병',   200, 100, 7, 20, 13, 13, 3000, ['건업'], null,     0, ['강력한 화살을 쏩니다.']],

        [20, GameUnitConstBase::T_CAVALRY, '기병',     150, 100, 7,  5, 11, 11,    0, null,     null,     0, ['표준적인 기병입니다.','기병은 공격특화입니다.']],
        [21, GameUnitConstBase::T_CAVALRY, '백마병',   200, 100, 7,  5, 12, 13, 1000, null,     ['하북'], 0, ['백마의 위용을 보여줍니다.']],
        [22, GameUnitConstBase::T_CAVALRY, '중장기병', 150, 150, 7,  5, 13, 12, 1000, null,     ['서북'], 0, ['갑주를 두른 기병입니다.']],
        [23, GameUnitConstBase::T_CAVALRY, '돌격기병', 200, 100, 8,  5, 13, 11, 2000, ['흉노'], null,     0, ['저돌적으로 공격합니다.']],
        [24, GameUnitConstBase::T_CAVALRY, '철기병',   100, 200, 7,  5, 11, 13, 2000, ['강'],   null,     0, ['철갑을 두른 기병입니다.']],
        [25, GameUnitConstBase::T_CAVALRY, '수렵기병', 150, 100, 8, 15, 12, 12, 2000, ['산월'], null,     0, ['날쎄고 빠른 기병입니다.']],
        [26, GameUnitConstBase::T_CAVALRY, '맹수병',   250, 200, 6,  0, 16, 16, 2000, ['남만'], null,     0, ['어느 누구보다 강력합니다.']],
        [27, GameUnitConstBase::T_CAVALRY, '호표기병', 200, 150, 7,  5, 14, 14, 3000, ['허창'], null,     0, ['정예 기병입니다.']],

        [30, GameUnitConstBase::T_WIZARD, '귀병',      80,  80, 7,  5,  9,  9,    0, null,     null,     0, ['계략을 사용하는 병종입니다.']],
        [31, GameUnitConstBase::T_WIZARD, '신귀병',    80,  80, 7, 20, 10, 10, 1000, null,     ['초'],   0, ['신출귀몰한 귀병입니다.']],
        [32, GameUnitConstBase::T_WIZARD, '백귀병',    80, 130, 7,  5,  9, 11, 2000, ['오환'], null,     0, ['저렴하고 튼튼합니다.']],
        [33, GameUnitConstBase::T_WIZARD, '흑귀병',   130,  80, 7,  5, 11,  9, 2000, ['왜'],   null,     0, ['저렴하고 강력합니다.']],
        [34, GameUnitConstBase::T_WIZARD, '악귀병',   130, 130, 7,  0, 12, 12, 3000, ['장안'], null,     0, ['백병전에도 능숙합니다.']],
        [35, GameUnitConstBase::T_WIZARD, '남귀병',    60,  60, 7, 10,  8,  8, 1000, null,     null,     0, ['전투를 포기하고 계략에 몰두합니다.']],
        [36, GameUnitConstBase::T_WIZARD, '황귀병',   110, 110, 7,  0, 13, 10, 3000, ['낙양'], null,     0, ['고도로 훈련된 귀병입니다.']],
        [37, GameUnitConstBase::T_WIZARD, '천귀병',    80, 130, 7, 15, 11, 12, 3000, ['성도'], null,     0, ['갑주를 두른 귀병입니다.']],
        [38, GameUnitConstBase::T_WIZARD, '마귀병',   130,  80, 7, 15, 12, 11, 3000, ['업'],   null,     0, ['날카로운 무기를 가진 귀병입니다.']],

        [40, GameUnitConstBase::T_SIEGE, '정란',     100, 100, 6,  0, 15,  5,    0, null,     null,     3, ['높은 구조물 위에서 공격합니다.']],
        [41, GameUnitConstBase::T_SIEGE, '충차',     150, 100, 6,  0, 20,  5, 1000, null,     null,     0, ['엄청난 위력으로 성벽을 부수어버립니다.']],
        [42, GameUnitConstBase::T_SIEGE, '벽력거',   200, 100, 6,  0, 25,  5, 3000, ['업'],   null,     0, ['상대에게 돌덩이를 날립니다.']],
        [43, GameUnitConstBase::T_SIEGE, '목우',      50, 200, 5,  0, 30,  5, 3000, ['성도'], null,     0, ['상대를 저지하는 특수병기입니다.']]
    ];

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

    public static function byCity(int $city): array{
        static::_generate();
        return static::$constCity[$city]??[];
    }

    public static function byRegion(int $region): array{
        static::_generate();
        return static::$constRegion[$region]??[];
    }

    

    public static function byType(int $type): array{
        static::_generate();
        if(!key_exists($type, static::$constType)){
            return [];
        }

        return static::$constType[$type];
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
                $cost,
                $rice,
                $reqTech,
                $reqCities,
                $reqRegions,
                $reqYear,
                $info
             ] = $rawUnit;

            if($reqYear > 0){
                $info[] = "{$reqYear}년 경과 후 사용 가능";
            }

            if($reqTech > 0){
                $info[] = "기술력 {$reqTech} 이상 필요";
            }

            $reqCities = array_map(function($reqCity) use (&$info){
                $info[] = "{$reqCity} 소유시 가능";
                return CityConst::byName($reqCity)->id;
            }, $reqCities);

            $reqRegions = array_map(function($reqRegion) use (&$info){
                $info[] = "{$reqRegion} 지역 소유시 가능";
                return CityConst::$regionMap[$reqRegion];
            }, $reqRegions);
            
            $unit = new GameUnitDetail(
                $id,
                $armType,
                $name, 
                $attack,
                $defence,
                $speed,
                $avoid,
                $cost,
                $rice,
                $reqTech,
                $reqCities,
                $reqRegions,
                $reqYear,
                $info
            );

            $constID[$id] = $unit;
            $constName[$name] = $unit;
            if(!key_exists($armType, $constType)){
                $constType[$armType] = [];
            }
            $constType[$armType][] = $unit;

            foreach($unit->reqCities as $reqCity){
                if(!key_exists($reqCity, $constCity)){
                    $constCity[$reqCity] = [];
                }
                $constCity[$reqCity][] = $unit;
            }

            foreach($unit->reqRegions as $reqRegion){
                if(!key_exists($reqRegion, $constRegion)){
                    $constRegion[$reqRegion] = [];
                }
                $constRegion[$reqRegion][] = $unit;
            }

        }

        static::$constID = $constID;
        static::$constName = $constName;
        static::$constCity = $constCity;
        static::$constRegion = $constRegion;
        
    }
}