<?php
namespace sammo;

class SpecialityHelper{
    //const GENERIC = 0x0;
    const DISABLED = 0x1;

    const STAT_LEADERSHIP = 0x2;
    const STAT_STRENGTH = 0x4;
    const STAT_INTEL = 0x8;

    const STAT_NOT_LEADERSHIP = 0x20;
    const STAT_NOT_STRENGTH = 0x40;
    const STAT_NOT_INTEL = 0x80;

    const ARMY_FOOTMAN = 0x100;
    const ARMY_ARCHER = 0x200;
    const ARMY_CAVALRY = 0x400;
    const ARMY_WIZARD = 0x800;
    const ARMY_SIEGE = 0x1000;

    const REQ_DEXTERITY = 0x4000;

    const WEIGHT_NORM = 1;
    const WEIGHT_PERCENT = 2;

    static $domesticInv = null;
    static $warInv = null;

    private function __construct(){
    }

    public static function getDomesticClassByName(?string $type):string{
        if($type === null || $type === '' || $type == 'None'){
            return GameConst::$defaultSpecialDomestic;
        }

        if(static::$domesticInv){
            if(!key_exists($type, static::$domesticInv)){
                throw new \InvalidArgumentException('올바르지 않은 특기명:'.$type);
            }
            return static::$domesticInv[$type];
        }

        $cache = [];
        foreach(static::getSpecialDomesticList(false) as $className=>$classObj){
            $cache[$classObj->getName()] = $className;
        }
        static::$domesticInv = $cache;
        if(!key_exists($type, $cache)){
            throw new \InvalidArgumentException('올바르지 않은 특기명:'.$type);
        }
        return $cache[$type];
    }

    public static function getWarClassByName(?string $type):string{
        if($type === null || $type === '' || $type == 'None'){
            return GameConst::$defaultSpecialWar;
        }

        if(static::$warInv){
            if(!key_exists($type, static::$warInv)){
                throw new \InvalidArgumentException('올바르지 않은 특기명:'.$type);
            }
            return static::$warInv[$type];
        }

        $cache = [];
        foreach(static::getSpecialWarList(false) as $className=>$classObj){
            $cache[$classObj->getName()] = $className;
        }
        if(!key_exists($type, $cache)){
            throw new \InvalidArgumentException('올바르지 않은 특기명:'.$type);
        }
        static::$warInv = $cache;
        return $cache[$type];
    }

    private static function calcCondGeneric(array $general) : int {
        $myCond = 0;

        $leadership = $general['leadership']??50;
        $strength = $general['strength']??50;
        $intel = $general['intel']??50;

        if($leadership > GameConst::$chiefStatMin){
            $myCond |= self::STAT_LEADERSHIP;
        }
        
        if($strength >= $intel * 0.95 && $strength > GameConst::$chiefStatMin){
            $myCond |= self::STAT_STRENGTH;
        }
        if($strength < GameConst::$chiefStatMin){
            $myCond |= self::STAT_NOT_STRENGTH;
        }

        if($intel >= $strength * 0.95 && $intel > GameConst::$chiefStatMin){
            $myCond |= self::STAT_INTEL;
        }

        if($myCond){
            if($leadership < GameConst::$chiefStatMin){
                $myCond |= self::STAT_NOT_LEADERSHIP;
            }
            if($strength < GameConst::$chiefStatMin){
                $myCond |= self::STAT_NOT_STRENGTH;
            }   
            if($intel < GameConst::$chiefStatMin){
                $myCond |= self::STAT_NOT_INTEL;
            }
        }

        if($myCond === 0){
            if ($leadership * 0.9 > $strength && $leadership * 0.9 > $intel) {
                $myCond |= self::STAT_LEADERSHIP;
            }
            else if($strength >= $intel){
                $myCond |= self::STAT_STRENGTH;
            }
            else {
                $myCond |= self::STAT_INTEL;
            }
        }

        if(!$myCond){
            $myCond = self::STAT_INTEL;
        }
        
        return $myCond;
    }

    private static function calcCondDexterity(array $general) : int {
        $dex = [
            static::ARMY_FOOTMAN => $general['dex1']??0,
            static::ARMY_ARCHER => $general['dex2']??0,
            static::ARMY_CAVALRY => $general['dex3']??0,
            static::ARMY_WIZARD => $general['dex4']??0,
            static::ARMY_SIEGE => $general['dex5']??0,
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

        return Util::choiceRandom(array_keys($dex, max($dex)));
    }

    /** @return BaseSpecial[] */
    public static function getSpecialDomesticList(bool $onlyAvailable=true):array{
        $result = [];
        if(!$onlyAvailable){
            $specialObj = buildGeneralSpecialDomesticClass(GameConst::$defaultSpecialDomestic);
            $result[GameConst::$defaultSpecialDomestic] = $specialObj;
        }

        foreach(GameConst::$availableSpecialDomestic as $specialID){
            $specialObj = buildGeneralSpecialDomesticClass($specialID);
            $result[$specialID] = $specialObj;
        }

        if(!$onlyAvailable){
            foreach(GameConst::$optionalSpecialDomestic as $specialID){
                $specialObj = buildGeneralSpecialDomesticClass($specialID);
                $result[$specialID] = $specialObj;
            }
        }

        return $result;
    }

    /** @return BaseSpecial[] */
    public static function getSpecialWarList(bool $onlyAvailable=true):array{
        $result = [];
        if(!$onlyAvailable){
            $specialObj = buildGeneralSpecialWarClass(GameConst::$defaultSpecialWar);
            $result[GameConst::$defaultSpecialWar] = $specialObj;
        }
        
        foreach(GameConst::$availableSpecialWar as $specialID){
            $specialObj = buildGeneralSpecialWarClass($specialID);
            $result[$specialID] = $specialObj;
        }

        if(!$onlyAvailable){
            foreach(GameConst::$optionalSpecialWar as $specialID){
                $specialObj = buildGeneralSpecialWarClass($specialID);
                $result[$specialID] = $specialObj;
            }
        }

        return $result;
    }

    public static function pickSpecialDomestic(array $general, array $prevSpecials=[]) : string{
        $pAbs = [];
        $pRel = [];

        $myCond = static::calcCondGeneric($general);

        foreach(static::getSpecialDomesticList() as $specialID=>$specialObj){
            $conds = $specialObj::$type;
            $name = $specialObj->getName();
            $weightType = $specialObj::$selectWeightType;
            $weight = $specialObj::$selectWeight;

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
            if(in_array($specialID, $prevSpecials)){
                continue;
            }
            

            if($weightType === static::WEIGHT_PERCENT){
                $pAbs[$specialID] = $weight;
            }
            else{
                $pRel[$specialID] = $weight;
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

        if($prevSpecials){
            return static::pickSpecialDomestic($general, []);
        }

        throw new MustNotBeReachedException();
    }

    public static function pickSpecialWar(array $general, array $prevSpecials=[]) : string{
        $reqDex = [];
        $pAbs = [];
        $pRel = [];

        $myCond = static::calcCondGeneric($general);
        $myCond |= static::calcCondDexterity($general);
        $myCond |= static::REQ_DEXTERITY;

        foreach(static::getSpecialWarList() as $specialID=>$specialObj){
            $conds = $specialObj::$type;
            $name = $specialObj->getName();
            $weightType = $specialObj::$selectWeightType;
            $weight = $specialObj::$selectWeight;

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
            if(in_array($specialID, $prevSpecials)){
                continue;
            }
            

            if($cond & self::REQ_DEXTERITY){
                $reqDex[$specialID] = $weight;
            }
            else if($weightType === static::WEIGHT_PERCENT){
                $pAbs[$specialID] = $weight;
            }
            else{
                $pRel[$specialID] = $weight;
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

        if($prevSpecials){
            return static::pickSpecialWar($general, []);
        }

        throw new MustNotBeReachedException();
    }
}