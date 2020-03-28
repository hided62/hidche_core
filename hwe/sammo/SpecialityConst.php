<?php
namespace sammo;

//TODO: 나중에는 각 내특, 전특이 클래스로 가야함
class SpecialityConst{
    //const GENERIC = 0x0;
    const DISABLED = 0x1;

    const STAT_LEADERSHIP = 0x2;
    const STAT_STRENGTH = 0x4;
    const STAT_INTEL = 0x8;

    const ARMY_FOOTMAN = 0x100;
    const ARMY_ARCHER = 0x200;
    const ARMY_CAVALRY = 0x400;
    const ARMY_WIZARD = 0x800;
    const ARMY_SIEGE = 0x1000;

    const REQ_DEXTERITY = 0x4000;

    const WEIGHT_NORM = 1;
    const WEIGHT_PERCENT = 2;

    private $invDomestic = null;
    private $invWar = null;

    private function __construct(){
    }

    //음수 : 절대값 %, 양수 : 상대적 비중
    const DOMESTIC = [
        'che_경작' => ['경작', 1, [self::STAT_INTEL]],
        'che_상재' => ['상재', 1, [self::STAT_INTEL]],
        'che_발명' => ['발명', 1, [self::STAT_INTEL]],

        'che_축성' => ['축성', 1, [self::STAT_STRENGTH]],
        'che_수비' => ['수비', 1, [self::STAT_STRENGTH]],
        'che_통찰' => ['통찰', 1, [self::STAT_STRENGTH]],

        'che_인덕' => ['인덕', 1, [self::STAT_LEADERSHIP]],

        'che_귀모' => ['귀모', -2.5, [self::STAT_LEADERSHIP, self::STAT_STRENGTH, self::STAT_INTEL]],
    ];

    const WAR = [
        'che_귀병' => ['귀병', 1, [self::STAT_INTEL | self::ARMY_WIZARD | self::REQ_DEXTERITY]],

        'che_신산' => ['신산', 1, [self::STAT_INTEL]],
        'che_환술' => ['환술', -5, [self::STAT_INTEL]],
        'che_집중' => ['집중', 1, [self::STAT_INTEL]],
        'che_신중' => ['신중', 1, [self::STAT_INTEL]],
        'che_반계' => ['반계', 1, [self::STAT_INTEL]],

        'che_보병' => ['보병', 1, [
            self::STAT_LEADERSHIP | self::REQ_DEXTERITY | self::ARMY_FOOTMAN,
            self::STAT_STRENGTH | self::REQ_DEXTERITY | self::ARMY_FOOTMAN
        ]],
        'che_궁병' => ['궁병', 1, [
            self::STAT_LEADERSHIP | self::REQ_DEXTERITY | self::ARMY_ARCHER,
            self::STAT_STRENGTH | self::REQ_DEXTERITY | self::ARMY_ARCHER
            ]],
        'che_기병' => ['기병', 1, [
            self::STAT_LEADERSHIP | self::REQ_DEXTERITY | self::ARMY_CAVALRY,
            self::STAT_STRENGTH | self::REQ_DEXTERITY | self::ARMY_CAVALRY
        ]],
        'che_공성' => ['공성', 1, [
            self::STAT_LEADERSHIP | self::REQ_DEXTERITY | self::ARMY_SIEGE,
            self::STAT_STRENGTH | self::REQ_DEXTERITY | self::ARMY_SIEGE,
            self::STAT_INTEL | self::REQ_DEXTERITY | self::ARMY_SIEGE
        ]],

        'che_돌격' => ['돌격', 1, [self::STAT_LEADERSHIP, self::STAT_STRENGTH]],
        'che_무쌍' => ['무쌍', 1, [self::STAT_LEADERSHIP, self::STAT_STRENGTH]],
        'che_견고' => ['견고', 1, [self::STAT_LEADERSHIP, self::STAT_STRENGTH]],
        'che_위압' => ['위압', 1, [self::STAT_LEADERSHIP, self::STAT_STRENGTH]],

        'che_저격' => ['저격', 1, [self::STAT_LEADERSHIP, self::STAT_STRENGTH, self::STAT_INTEL]],
        'che_필살' => ['필살', 1, [self::STAT_LEADERSHIP, self::STAT_STRENGTH, self::STAT_INTEL]],
        'che_징병' => ['징병', 1, [self::STAT_LEADERSHIP, self::STAT_STRENGTH, self::STAT_INTEL]],
        'che_의술' => ['의술', -2, [self::STAT_LEADERSHIP, self::STAT_STRENGTH, self::STAT_INTEL]],
        'che_격노' => ['격노', 1, [self::STAT_LEADERSHIP, self::STAT_STRENGTH, self::STAT_INTEL]],
        'che_척사' => ['척사', 1, [self::STAT_LEADERSHIP, self::STAT_STRENGTH, self::STAT_INTEL]],
    ];

    public static function getInvDomestic(string $name){
        if(static::$invDomestic !== null){
            return static::$invDomestic[$name]??null;
        }

        $invDomestic = [];
        foreach(static::DOMESTIC as $key=>$val){
            $nameKey = $val[0];
            $val[0] = $key;
            $invDomestic[$nameKey] = $val;
        }
        static::$invDomestic = $invDomestic;

        return static::$invDomestic[$name]??null;
    }

    public static function getInvWar(string $name){
        if(static::$invWar !== null){
            return static::$invWar[$name]??null;
        }

        $invWar = [];
        foreach(static::WAR as $key=>$val){
            $nameKey = $val[0];
            $val[0] = $key;
            $invWar[$nameKey] = $val;
        }
        static::$invWar = $invWar;

        return static::$invWar[$name]??null;
    }

    private static function calcCondGeneric(array $general) : int {
        $myCond = 0;

        $leadership = $general['leadership']??50;
        $strength = $general['strength']??50;
        $intel = $general['intel']??50;
        
        if ($leadership * 0.95 > $strength && $leadership * 0.95 > $intel) {
            $myCond |= self::STAT_LEADERSHIP;
        }
        else if($strength >= $intel){
            $myCond |= self::STAT_STRENGTH;
        }
        else {
            $myCond |= self::STAT_INTEL;
        }

        return $myCond;
    }

    private static function calcCondDexterity(array $general) : int {
        $dex = [
            static::ARMY_FOOTMAN => $general['dex0']??0,
            static::ARMY_ARCHER => $general['dex10']??0,
            static::ARMY_CAVALRY => $general['dex20']??0,
            static::ARMY_WIZARD => $general['dex30']??0,
            static::ARMY_SIEGE => $general['dex40']??0,
        ];

        $dexSum = array_sum($dex);
        $dexBase = Util::round(sqrt($dexSum) / 4);

        if(Util::randBool(0.8)){
            return 0;
        }

        if(mt_rand(0, 99) < $dexBase){
            return 0;
        }

        if(!$dexSum){
            return array_rand($dex);
        }

        return array_keys($dex, max($dex))[0];
    }

    public static function pickSpecialDomestic(array $general) : string{
        $pAbs = [];
        $pRel = [];

        $myCond = static::calcCondGeneric($general);

        foreach(self::DOMESTIC as $id=>list($name, $weight, $conds)){
            $valid = false;
            foreach($conds as $cond){
                if($cond === ($cond & $myCond)){
                    $valid = true;
                    break;
                }
            }
            if(!$valid){
                continue;
            }
            

            if($weight < 0){
                $pAbs[$id] = -$weight;
            }
            else{
                $pRel[$id] = $weight;
            }
        }
        
        if($pAbs){
            if($pRel){
                $pAbs[0] = max(0, 100 - array_sum($pAbs));
            }
            $id = Util::choiceRandomUsingWeight($pAbs);
            if($id){
                return $id;
            }
        }

        $id = Util::choiceRandomUsingWeight($pRel);
        if($id){
            return $id;
        }

        throw new MustNotBeReachedException();
    }

    public static function pickSpecialWar(array $general) : string{
        $reqDex = [];
        $pAbs = [];
        $pRel = [];

        $myCond = static::calcCondGeneric($general);
        $myCond |= static::calcCondDexterity($general);

        foreach(self::WAR as $id=>list($name, $weight, $conds)){
            $valid = false;
            foreach($conds as $cond){
                if($cond === ($cond & $myCond)){
                    $valid = true;
                    break;
                }
            }
            if(!$valid){
                continue;
            }

            if($cond & self::REQ_DEXTERITY){
                $reqDex[$id] = $weight;
            }
            else if($weight < 0){
                $pAbs[$id] = -$weight;
            }
            else{
                $pRel[$id] = $weight;
            }
        }

        if($reqDex){
            return Util::choiceRandomUsingWeight($reqDex);
        }

        if($pAbs){
            if($pRel){
                $pAbs[0] = max(0, 100 - array_sum($pAbs));
            }
            
            $id = Util::choiceRandomUsingWeight($pAbs);
            if($id){
                return $id;
            }
        }

        $id = Util::choiceRandomUsingWeight($pRel);
        if($id){
            return $id;
        }

        throw new MustNotBeReachedException();
    }
}