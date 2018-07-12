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

    protected static $_buildData = [
        [ 0, T_FOOTMAN, '보병',     100, 150, 7, 10,  9,  9, 0, 0, true, ['표준적인 보병입니다.','보병은 방어특화입니다.']],
        [ 1, T_FOOTMAN, '청주병',   100, 200, 7, 10, 10, 11, 1, '중원', true, ['저렴하고 튼튼합니다.']],
        [ 2, T_FOOTMAN, '수병',     150, 150, 7, 10, 11, 10, 1, '오월', true, ['저렴하고 강력합니다.']],
        [ 3, T_FOOTMAN, '자객병',   100, 150, 7, 20, 10, 10, 2, '저', true, ['은밀하고 날쌥니다.']],
        [ 4, T_FOOTMAN, '근위병',   150, 200, 7, 10, 12, 12, 2, '낙양', true, ['최강의 보병입니다.']],
        [ 5, T_FOOTMAN, '등갑병',   100, 250, 7,  5, 13, 10, 1, '남중', true, ['등갑을 두른 보병입니다.']],

        [10, T_ARCHER, '궁병',     100, 100, 7, 20, 10, 10, 0, 0, true, ['표준적인 궁병입니다.','궁병은 회피특화입니다.']],
        [11, T_ARCHER, '궁기병',   100, 100, 8, 30, 11, 12, 1, '동이', true, ['말을 타고 잘 피합니다.']],
        [12, T_ARCHER, '연노병',   150, 100, 8, 20, 12, 11, 1, '서촉', true, ['화살을 연사합니다.']],
        [13, T_ARCHER, '강궁병',   150, 150, 7, 20, 13, 13, 2, '양양', true, ['강건한 궁병입니다.']],
        [14, T_ARCHER, '석궁병',   200, 100, 7, 20, 13, 13, 2, '건업', true, ['강력한 화살을 쏩니다.']],

        [20, T_CAVALRY, '기병',     150, 100, 7,  5, 11, 11, 0, 0, true, ['표준적인 기병입니다.','기병은 공격특화입니다.']],
        [21, T_CAVALRY, '백마병',   200, 100, 7,  5, 12, 13, 1, '하북', true, ['백마의 위용을 보여줍니다.']],
        [22, T_CAVALRY, '중장기병', 150, 150, 7,  5, 13, 12, 1, '서북', true, ['갑주를 두른 기병입니다.']],
        [23, T_CAVALRY, '돌격기병', 200, 100, 8,  5, 13, 11, 2,'흉노', true, ['저돌적으로 공격합니다.']],
        [24, T_CAVALRY, '철기병',   100, 200, 7,  5, 11, 13, 2, '강', true, ['철갑을 두른 기병입니다.']],
        [25, T_CAVALRY, '수렵기병', 150, 100, 8, 15, 12, 12, 2, '산월', true, ['날쎄고 빠른 기병입니다.']],
        [26, T_CAVALRY, '맹수병',   250, 200, 6,  0, 16, 16, 2, '남만', true, ['어느 누구보다 강력합니다.']],
        [27, T_CAVALRY, '호표기병', 200, 150, 7,  5, 14, 14, 2, '허창', true, ['정예 기병입니다.']],

        [30, T_WIZARD, '귀병',      80,  80, 7,  5,  9,  9, 0, 0, true, ['계략을 사용하는 병종입니다.']],
        [31, T_WIZARD, '신귀병',    80,  80, 7, 20, 10, 10, 1,'초', true, ['신출귀몰한 귀병입니다.']],
        [32, T_WIZARD, '백귀병',    80, 130, 7,  5,  9, 11, 2, '오환', true, ['저렴하고 튼튼합니다.']],
        [33, T_WIZARD, '흑귀병',   130,  80, 7,  5, 11,  9, 2, '왜', true, ['저렴하고 강력합니다.']],
        [34, T_WIZARD, '악귀병',   130, 130, 7,  0, 12, 12, 2, '장안', true, ['백병전에도 능숙합니다.']],
        [35, T_WIZARD, '남귀병',    60,  60, 7, 10,  8,  8, 0, 1000, false, ['전투를 포기하고 계략에 몰두합니다.']],
        [36, T_WIZARD, '황귀병',   110, 110, 7,  0, 13, 10, 2, '낙양', true, ['고도로 훈련된 귀병입니다.']],
        [37, T_WIZARD, '천귀병',    80, 130, 7, 15, 11, 12, 2, '성도', true, ['갑주를 두른 귀병입니다.']],
        [38, T_WIZARD, '마귀병',   130,  80, 7, 15, 12, 11, 2, '업', true, ['날카로운 무기를 가진 귀병입니다.']],

        [40, T_SIEGE, '정란',     100, 100, 6,  0, 15,  5, 0, 0, false, ['높은 구조물 위에서 공격합니다.']],
        [41, T_SIEGE, '충차',     150, 100, 6,  0, 20,  5, 0, 1000, false, ['엄청난 위력으로 성벽을 부수어버립니다.']],
        [42, T_SIEGE, '벽력거',   200, 100, 6,  0, 25,  5, 2, '업', true, ['상대에게 돌덩이를 날립니다.']],
        [43, T_SIEGE, '목우',      50, 200, 5,  0, 30,  5, 2, '성도', true, ['상대를 저지하는 특수병기입니다.']]
    ];

    /**
     * @return \sammo\GameUnitDetail[]
     */
    public static function all(): array{
        static::_generate();
        return static::$constID;
    }

    public static function byID(int $id): GameUnitDetail{
        static::_generate();
        return static::$constID[$id];
    }

    public static function byName(string $name): GameUnitDetail{
        static::_generate();
        return static::$constName[$name];
    }

    public static function byCity(int $city): array{
        static::_generate();
        return static::$constCity[$city];
    }

    public static function byRegion(int $region): array{
        static::_generate();
        return static::$constRegion[$region];
    }

    public static function byType(int $type): array{
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
                $recruitType,
                $recruitCondition,
                $recruitFirst,
                $info
             ] = $rawUnit;

            //0인 경우는 기술치이다.
            if(!$recruitFirst){
                $info[] = "일정 시간이 지나야 사용 가능";
            }

            if($recruitType == 1){
                $info[] = "{$recruitCondition}지역 소유시 가능";
                $recruitCondition = CityConst::$regionMap[$recruitCondition];
            }
            else if($recruitType == 2){
                $info[] = "{$recruitCondition} 소유시 가능";
                $recruitCondition = CityConst::byName($recruitCondition)->id;
            }

            

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
                $recruitType,
                $recruitCondition,
                $recruitFirst,
                $info
            );

            $constID[$id] = $unit;
            $constName[$name] = $unit;
            if(!key_exists($armType, $constType)){
                $constType[$armType] = [];
            }
            $constType[$armType][] = $unit;

            if($recruitType == 1){
                if(!key_exists($recruitCondition, $constRegion)){
                    $constRegion[$recruitCondition] = [];
                }
                $constRegion[$recruitCondition][] = $unit;
            }
            if($recruitType == 2){
                if(!key_exists($recruitCondition, $constCity)){
                    $constCity[$recruitCondition] = [];
                }
                $constCity[$recruitCondition][] = $unit;
            }
        }

        static::$constID = $constID;
        static::$constName = $constName;
        static::$constCity = $constCity;
        static::$constRegion = $constRegion;
        
    }
}