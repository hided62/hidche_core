<?php
namespace sammo;

class GameUnitConst{
    private function __construct(){

    }

    private static $constID = null;
    private static $constName = null;
    private static $constCity = null;
    private static $constRegion = null;

    /**
     * @return \sammo\GameUnitDetail[]
     */
    public static function all(): array{
        static::_generate();
        return static::$constID;
    }
    public static function byID($id): GameUnitDetail{
        static::_generate();
        return static::$constID[$id];
    }

    public static function byName($name): GameUnitDetail{
        static::_generate();
        return static::$constName[$name];
    }

    public static function byCity($city): GameUnitDetail{
        static::_generate();
        return static::$constCity[$city];
    }

    public static function byRegion($region): GameUnitDetail{
        static::_generate();
        return static::$constRegion[$region];
    }

    private static function _generate(){

        if(static::$constID || static::$constName || static::$constCity || static::$constRegion){
            return;
        }

        $list = [
            [ 0, '보병',     100, 150, 7, 10,  9,  9, 0, 0, true],
            [ 1, '청주병',   100, 200, 7, 10, 10, 11, 1, '중원', true],
            [ 2, '수병',     150, 150, 7, 10, 11, 10, 1, '오월', true],
            [ 3, '자객병',   100, 150, 7, 20, 10, 10, 2, '저', true],
            [ 4, '근위병',   150, 200, 7, 10, 12, 12, 2, '낙양', true],
            [ 5, '등갑병',   100, 250, 7,  5, 13, 10, 1, '남중', true],

            [10, '궁병',     100, 100, 7, 20, 10, 10, 0, 0, true],
            [11, '궁기병',   100, 100, 8, 30, 11, 12, 1, '동이', true],
            [12, '연노병',   150, 100, 8, 20, 12, 11, 1, '서촉', true],
            [13, '강궁병',   150, 150, 7, 20, 13, 13, 2, '양양', true],
            [14, '석궁병',   200, 100, 7, 20, 13, 13, 2, '건업', true],

            [20, '기병',     150, 100, 7,  5, 11, 11, 0, 0, true],
            [21, '백마병',   200, 100, 7,  5, 12, 13, 1, '하북', true],
            [22, '중장기병', 150, 150, 7,  5, 13, 12, 1, '서북', true],
            [23, '돌격기병', 200, 100, 8,  5, 13, 11, 2,'흉노', true],
            [24, '철기병',   100, 200, 7,  5, 11, 13, 2, '강', true],
            [25, '수렵기병', 150, 100, 8, 15, 12, 12, 2, '산월', true],
            [26, '맹수병',   250, 200, 6,  0, 16, 16, 2, '남만', true],
            [27, '호표기병', 200, 150, 7,  5, 14, 14, 2, '허창', true],

            [30, '귀병',      80,  80, 7,  5,  9,  9, 0, 0, true],
            [31, '신귀병',    80,  80, 7, 20, 10, 10, 1,'초', true],
            [32, '백귀병',    80, 130, 7,  5,  9, 11, 2, '오환', true],
            [33, '흑귀병',   130,  80, 7,  5, 11,  9, 2, '왜', true],
            [34, '악귀병',   130, 130, 7,  0, 12, 12, 2, '장안', true],
            [35, '남귀병',    60,  60, 7, 10,  8,  8, 0, 0, false],
            [36, '황귀병',   110, 110, 7,  0, 13, 10, 2, '낙양', true],
            [37, '천귀병',    80, 130, 7, 15, 11, 12, 2, '성도', true],
            [38, '마귀병',   130,  80, 7, 15, 12, 11, 2, '업', true],

            [40, '정란',     100, 100, 6,  0, 15,  5, 0, 0, false],
            [41, '충차',     150, 100, 6,  0, 20,  5, 0, 1000, false],
            [42, '벽력거',   200, 100, 6,  0, 25,  5, 2, '업', false],
            [43, '목우',      50, 200, 5,  0, 30,  5, 2, '성도', false]
        ];

        $constID = [];
        $constName = [];
        $constCity = [];
        $constRegion = [];

        foreach($list as $rawUnit){
            list(
                $id,
                $name, 
                $attack,
                $defence,
                $speed,
                $avoid,
                $cost,
                $rice,
                $recruitType,
                $recruitCondition,
                $recruitFirst
            ) = $rawUnit;

            //0인 경우는 기술치이다.
            if($recruitType == 1){
                $recruitCondition = CityConst::byName($recruitCondition)->id;
            }
            else if($recruitType == 2){
                $recruitCondition = CityConst::$regionMap[$recruitCondition]->id;
            }

            $unit = new GameUnitDetail(
                $id,
                $name, 
                $attack,
                $defence,
                $speed,
                $avoid,
                $cost,
                $rice,
                $recruitType,
                $recruitCondition,
                $recruitFirst
            );

            $constID[$id] = $unit;
            $constName[$name] = $unit;
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