<?php
namespace sammo\Scenario;
use \sammo\Util;
use \sammo\JosaUtil;
use \sammo\DB;
use \sammo\CityHelper;
use \sammo\GameUnitConst;
use \sammo\CityConst;
use \sammo\GameConst;
use \sammo\SpecialityHelper;
use sammo\TimeUtil;

use function sammo\buildGeneralSpecialClass;

class GeneralBuilder{

    protected $generalID = null;
    protected $realName = null;

    protected $owner = 0;
    protected $ownerName = null;
    protected $affinity=null; 
    protected $nameCustomPrefix=null;
    protected $name; 
    protected $imgsvr = 0;
    protected $picturePath; 
    protected $nationID; 
    protected $cityID; 
    protected $leadership=null; 
    protected $strength=null; 
    protected $intel=null; 
    protected $officerLevel;
    protected $birth=null; 
    protected $death=null; 
    protected $ego=null;
    protected $specialDomestic=null; 
    protected $specialWar=null; 
    protected $npc = 2;
    protected $text;
    static $prefixList = [
        0 => '', //기본
        1 => 'ⓝ', //빙의 NPC
        2 => 'ⓝ', //NPC
        3 => 'ⓜ', //인탐 장수
        4 => 'ⓖ', //의병장(전략)
        5 => '㉥', //부대장
        6 => 'ⓤ', //unselectable npc, 빙의 불가 npc
        
        9 => 'ⓞ', //오랑캐?
    ];

    protected $gold=1000;
    protected $rice=1000;

    protected $specAge = null;
    protected $specAge2 = null;
    protected $experience = null;
    protected $dedication = null;

    protected $killturn = null;

    //XXX: 코드 못 바꾸나?
    protected $dex1 = 0;
    protected $dex2 = 0;
    protected $dex3 = 0;
    protected $dex4 = 0;
    protected $dex5 = 0;

    protected $aux = [];

    public function __construct(
        string $name, 
        bool $isDynamicImageSvr,
        $picturePath, 
        int $nationID
    ){
        
        $this->name = $name;
        $this->imgsvr = $isDynamicImageSvr;
        $this->picturePath = $picturePath;
        $this->nationID = $nationID;
    }

    public function setPicture(int $imgsvr, $picturePath){
        $this->imgsvr = $imgsvr;
        $this->picturePath = $picturePath;
    }

    public function setOwner(int $owner):self{
        if($owner <= 0){
            throw new \InvalidArgumentException();
        }
        $this->owner = $owner;
        return $this;
    }

    public function setOwnerName(string $ownerName):self{
        $this->ownerName = $ownerName;
        return $this;
    }

    public function setNationID(int $nationID):self{
        $this->nationID = $nationID;
        return $this;
    }

    public function setSpecialOption(?string $option):self{
        $general = [
            'leadership'=>$this->leadership,
            'strength'=>$this->strength,
            'intel'=>$this->intel
        ];
        if($option === '랜덤전특'){
            $this->specialWar = SpecialityHelper::pickSpecialWar($general);
        }
        else if($option === '랜덤내특'){
            $this->specialDomestic = SpecialityHelper::pickSpecialDomestic($general);
        }
        else if($option === '랜덤'){
            if(Util::randBool(2/3)){
                $this->specialWar = SpecialityHelper::pickSpecialWar($general);
            }
            else{
                $this->specialDomestic = SpecialityHelper::pickSpecialDomestic($general);
            }
        }
        return $this;
    }

    public function setSpecialSingle(?string $special):self{
        if($special === null){
            $this->specialDomestic = GameConst::$defaultSpecialDomestic;
            $this->specialWar = GameConst::$defaultSpecialWar;
        }
        try{
            $this->specialDomestic = SpecialityHelper::getDomesticClassByName($special);
            $this->specialWar = GameConst::$defaultSpecialWar;
        }
        catch (\Exception $e){
            $this->specialDomestic = GameConst::$defaultSpecialDomestic;
            $this->specialWar = SpecialityHelper::getWarClassByName($special);
        }
        return $this;
    }

    public function setSpecial(string $specialDomestic, string $specialWar):self{
        $this->specialDomestic = $specialDomestic;
        $this->specialWar = $specialWar;
        return $this;
        
    }

    public function setOfficerLevel(int $level):self{
        $this->officerLevel = $level;
        return $this;
    }

    public function setNPCText(?string $text):self{
        $this->text = $text;
        return $this;
    }

    public function setEgo(?string $ego):self{
        $this->ego = $ego;
        return $this;
    }

    public function setAffinity(int $affinity):self{
        if($affinity < 1){
            $this->affinity = Util::randRangeInt(1, 150);
        }
        else if($affinity >= 900){
            $this->affinity = 999;
        }
        else if(1 <= $affinity && $affinity <= 150){
            $this->affinity = $affinity;
        }
        else{
            throw new \InvalidArgumentException();
        }
        return $this;
    }

    public function setNPCType(int $npcType):self{
        if(!key_exists($npcType, static::$prefixList)){
            throw new \InvalidArgumentException();
        }
        $this->npc = $npcType;
        return $this;
    }

    public function setCustomPrefix(?string $prefix):self{
        $this->nameCustomPrefix = $prefix;
        return $this;
    }

    public function setStat(int $leadership, int $strength, int $intel):self{
        $this->leadership = $leadership;
        $this->strength = $strength;
        $this->intel = $intel;
        return $this;
    }

    public function setSpecYear(?int $specAge, ?int $specAge2):self{
        $this->specAge = $specAge;
        $this->specAge2 = $specAge2;
        return $this;
    }

    public function setExpDed(?int $experience, ?int $dedication):self{
        $this->experience = $experience;
        $this->dedication = $dedication;
        return $this;
    }

    public function setMoney(int $gold, int $rice):self{
        $this->gold = $gold;
        $this->rice = $rice;
        return $this;
    }

    public function setCityID(int $cityID):self{
        if(!key_exists($cityID, \sammo\CityConst::all())){
            throw new \InvalidArgumentException();
        }
        $this->cityID = $cityID;
        return $this;
    }

    public function setCity($city, bool $ignoreError=false):self{
        if($city===null){
            $this->cityID = null;
            return $this;
        }
        if(is_int($city)){
            if(!key_exists($city, \sammo\CityConst::all())){
                if(!$ignoreError){
                    throw new \InvalidArgumentException();
                }
                else{
                    return $this;
                }
            }
            $this->cityID = $city;
        }
        else{
            try{
                $obj = \sammo\CityConst::byName($city);
                $this->cityID = $obj->id;
            }
            catch(\TypeError $e){
                if($ignoreError){
                    return $this;
                }
                else{
                    throw $e;
                }
            }
        }
        return $this;
    }

    public function getStat():array{
        return [
            $this->leadership,
            $this->strength,
            $this->intel
        ];
    }

    public function setAuxVar(string $key, $value):self{
        if($value === null){
            unset($this->aux[$key]);
            return $this;
        }
        $this->aux[$key]=$value;
        return $this;
    }

    public function setKillturn(?int $killturn):self{
        $this->killturn = $killturn;
        return $this;
    }

    public function setDex(int $footman, int $archer, int $cavalry, int $wizard, int $siege):self{
        $this->dex1 = $footman;
        $this->dex2 = $archer;
        $this->dex3 = $cavalry;
        $this->dex4 = $wizard;
        $this->dex5 = $siege;
        return $this;
    }

    public function setLifeSpan(int $birth, int $death):self{
        $this->birth = $birth;
        $this->death = $death;
        return $this;
    }

    public function setGoldRice(int $gold, int $rice):self{
        $this->gold = $gold;
        $this->rice = $rice;
        return $this;
    }

    public function fillRandomStat(array $pickTypeList, &$pickedType=null):self{
        $pickType = Util::choiceRandomUsingWeight($pickTypeList);
        $totalStat = GameConst::$defaultStatNPCTotal;
        $minStat = GameConst::$defaultStatNPCMin;
        $mainStat = GameConst::$defaultStatNPCMax - Util::randRangeInt(0, GameConst::$defaultStatNPCMin);
        $otherStat = $minStat + Util::randRangeInt(0, Util::toInt(GameConst::$defaultStatNPCMin/2));
        $subStat = $totalStat - $mainStat - $otherStat;
        if ($subStat < $minStat) {
            $subStat = $otherStat;
            $otherStat = $minStat;
            $mainStat = $totalStat - $subStat - $otherStat;
            if ($mainStat) {
                throw new \LogicException('기본 스탯 설정값이 잘못되어 있음');
            }
        }

        if ($pickType == '무') {
            $leadership = $subStat;
            $strength = $mainStat;
            $intel = $otherStat;
        } else if ($pickType == '지') {
            $leadership = $subStat;
            $strength = $otherStat;
            $intel = $mainStat;
        } else {
            $leadership = $otherStat;
            $strength = $subStat;
            $intel = $mainStat;
        }
        $this->setStat($leadership, $strength, $intel);
        $pickedType = $pickType;
        return $this;
    }

    public function fillRemainSpecAsZero(array $env=[]):self{
        if($this->leadership===null){
            throw new \RuntimeException('stat이 설정되어 있지 않음');
        }

        if($this->affinity === null || $this->affinity === 0){
            $this->affinity = Util::randRangeInt(1, 150);
        }

        if($this->birth === null){
            $this->birth = $env['year']-20;
            $this->death = $env['year']+60;
        }

        if($this->specAge===null){
            $age = $env['year']-$this->birth;
            $relYear = Util::valueFit($env['year'] - $env['startyear'], 0);
            $this->specAge = Util::valueFit(Util::round((GameConst::$retirementYear - $age)/12 - $relYear / 2), 3) + $age;
        }

        if($this->specAge2===null){
            $age = $env['year']-$this->birth;
            $relYear = Util::valueFit($env['year'] - $env['startyear'], 0);
            $this->specAge2 = Util::valueFit(Util::round((GameConst::$retirementYear - $age)/6 - $relYear / 2), 3) + $age;
        }

        if($this->officerLevel === null){
            if($this->nationID){
                $this->officerLevel = 1;
            }
            else{
                $this->officerLevel = 0;
            }
        }

        if($this->ego === null){
            $this->ego = Util::choiceRandom(GameConst::$availablePersonality);
        }

        if($this->specialDomestic === null){
            $this->specialDomestic = GameConst::$defaultSpecialDomestic;
        }

        if($this->specialWar === null){
            $this->specialWar = GameConst::$defaultSpecialWar;
        }
        
        if($this->killturn === null && $this->owner){
            $this->killturn = 5;
        }
        return $this;
    }

    public function fillRemainSpecAsRandom(array $pickTypeList, array $avgGen, array $env=[]):self{

        $isFictionMode = (Util::array_get($env['fiction'], 0)!=0);

        if($isFictionMode || $this->specialWar === null){
            $this->specialWar = GameConst::$defaultSpecialWar;
        }

        if($isFictionMode || $this->specialDomestic === null){
            $this->specialDomestic = GameConst::$defaultSpecialDomestic;
        }

        if($this->affinity === null || $this->affinity === 0 || $isFictionMode){
            $this->affinity = Util::randRangeInt(1, 150);
        }

        if($this->birth === null){
            $this->birth = $env['year']+Util::randRange(-5, 5);
            $this->death = $this->birth+Util::randRangeInt(60, 80);
        }

        
        if($this->specAge===null){
            $age = $env['year']-$this->birth;
            $relYear = Util::valueFit($env['year'] - $env['startyear'], 0);
            $this->specAge = Util::valueFit(Util::round((GameConst::$retirementYear - $age)/12 - $relYear / 2), 3) + $age;
        }

        if($this->specAge2===null){
            $age = $env['year']-$this->birth;
            $relYear = Util::valueFit($env['year'] - $env['startyear'], 0);
            $this->specAge2 = Util::valueFit(Util::round((GameConst::$retirementYear - $age)/6 - $relYear / 2), 3) + $age;
        }

        if ($this->leadership===null || $this->strength===null || $this->intel === null){
            $this->fillRandomStat($pickTypeList, $pickType);
        }else{
            $leadership = $this->leadership;
            $strength = $this->strength;
            $intel = $this->intel;
            //getCall()과 같이 가야하는가?

            do{
                if($leadership < 40){
                    $pickType = '무지';
                    break;
                }
                if($intel * 0.8 > $strength){
                    $pickType = '지';
                    break;
                }
                if($strength * 0.8 > $intel){
                    $pickType = '무';
                    break;
                }
                $pickType = Util::choiceRandomUsingWeight([
                    '무'=>$strength,
                    '지'=>$intel
                ]);

            }while(0);
        }

        if($this->officerLevel === null){
            if($this->nationID){
                $this->officerLevel = 1;
            }
            else{
                $this->officerLevel = 0;
            }
        }

        if($this->dex1 === null){
            $dexTotal = $avgGen['dex_t'];
            if ($pickType == '무') {
                $dexVal = Util::choiceRandom([
                    [$dexTotal * 5 / 8, $dexTotal / 8, $dexTotal / 8, $dexTotal / 8],
                    [$dexTotal / 8, $dexTotal * 5 / 8, $dexTotal / 8, $dexTotal / 8],
                    [$dexTotal / 8, $dexTotal / 8, $dexTotal * 5 / 8, $dexTotal / 8],
                ]);
            } else if ($pickType == '지') {
                $dexVal = [$dexTotal / 8, $dexTotal / 8, $dexTotal / 8, $dexTotal * 5 / 8];
            } else {
                $dexVal = [$dexTotal / 4, $dexTotal / 4, $dexTotal / 4, $dexTotal / 4];
            }
            $this->setDex($dexVal[0], $dexVal[1], $dexVal[2], $dexVal[3], $avgGen['dex5']);
        }

        if($this->ego === null || $isFictionMode){
            $this->ego = Util::choiceRandom(GameConst::$availablePersonality);
        }

        if($this->experience === null){
            $this->experience = 0;
        }

        if($this->dedication === null){
            $this->dedication = 0;
        }
        return $this;
    }

    public function getCityID():?int{
        return $this->cityID;
    }

    public function getNationID():int{
        return $this->nationID;
    }

    public function getGeneralID():int{
        if($this->generalID === null){
            throw new \RuntimeException('build()되지 않음');
        }
        return $this->generalID;
    }

    public function getGeneralRawName():string{
        return $this->name;
    }

    public function getGeneralName():string{
        if($this->realName === null){
            throw new \RuntimeException('build()되지 않음');
        }
        return $this->realName;
    }

    public function getBirthYear():int{
        if($this->birth === null){
            throw new \RuntimeException('lifespan 미지정');
        }
        return $this->birth;
    }

    public function getDeathYear():int{
        if($this->death === null){
            throw new \RuntimeException('lifespan 미지정');
        }
        return $this->death;
    }

    public function build($env=[]){
        //scenario에 life==1인 경우 수명 제한이 없어지는 모양.

        

        if(!key_exists('stored_icons', $env)){
            try{
                $text = \file_get_contents(\sammo\ServConfig::getSharedIconPath('../hook/list.json?1'));
                $storedIcons = \sammo\Json::decode($text);
            }
            catch(\Exception $e){
                $storedIcons = [];
            }
    
            $env['stored_icons'] = $storedIcons;
        }

        $isFictionMode = (Util::array_get($env['fiction'], 0)!=0);

        $year = $env['year'];
        $month = $env['month'];
        $age = $year - $this->birth;
        $name = $this->name;

        if($this->nameCustomPrefix === null){
            $name = (static::$prefixList[$this->npc]??'ⓧ').$this->name;
        }
        else{
            $name = $this->nameCustomPrefix.$this->name;
        }
        
        $this->realName = $name;

        if($this->death <= $year){
            return true; //죽었으니 넘어간다.
        }
        if($age < GameConst::$adultAge){
            return false; //예약.
        }

        $isNewGeneral = ($age == GameConst::$adultAge);

        $nationID = $this->nationID;
        if($isFictionMode && $isNewGeneral){
            $nationID = 0;
        }

        if(!\sammo\getNationStaticInfo($nationID)){
            $nationID = 0;
        };


        $db = DB::db();

        if($isNewGeneral){
            $josaYi = JosaUtil::pick($name, '이');
            $logger = new \sammo\ActionLogger(0, 0, $year, $month);
            $logger->pushGlobalActionLog("<Y>{$name}</>{$josaYi} 성인이 되어 <S>등장</>했습니다.");
            $logger->flush();
        }

        $picturePath = $this->picturePath;
        if($env['show_img_level'] < 3){
            $picturePath = 'default.jpg';
        }
        else if(is_numeric($picturePath)){
            if($picturePath < 0){
                $picturePath = null;
            }
            else if(\key_exists($picturePath, $env['stored_icons']['.']??[])){
                $picturePath = $env['stored_icons']['.'][$picturePath];
            }
            else{
                $picturePath = null;
            }
        }
        else if($this->imgsvr){

        }
        else if($picturePath !== null && in_array($picturePath, $env['stored_icons'][$env['icon_path']??'.']??[])){
            $picturePath = ($env['icon_path']??'.').'/'.$picturePath;
        }
        else if($picturePath === null && \key_exists('stored_icons', $env)){
            $target = $env['stored_icons']??[];
            $target = $target[$env['icon_path']??'.']??[];
            $picturePath = $target[$this->name]??null;
            if($picturePath){
                $picturePath = ($env['icon_path']??'.').'/'.$picturePath;
            }
        }


        if($picturePath === null){
            $picturePath = 'default.jpg';
        }

        if($this->cityID === null){
            if($nationID == 0 || !CityHelper::getAllNationCities($nationID)){
                $cityObj = Util::choiceRandom(CityHelper::getAllCities());
            }
            else{
                $cityObj = Util::choiceRandom(CityHelper::getAllNationCities($nationID));
            }
            '@phan-var array<string,string|int> $cityObj';
            $this->cityID = $cityObj['id'];
        }
        

        $experience = $this->experience?:$age * 100;
        $dedication = $this->dedication?:$age * 100;
        $officerLevel = $this->officerLevel;
        if(!$officerLevel || $isNewGeneral){
            $officerLevel = $nationID?1:0;
        }

        $turntime = \sammo\getRandTurn($env['turnterm'], new \DateTimeImmutable($env['turntime']));

        if($this->killturn){
            $killturn = $this->killturn;
        }
        else if($this->birth !== null){
            $killturn = ($this->death - $year) * 12 + mt_rand(0, 11) + $month - 1;
        }
        else{
            throw new \InvalidArgumentException();
        }

        $db->insert('general',[
            'owner'=>$this->owner,
            'owner_name'=>$this->ownerName,
            'npc'=>$this->npc,
            'npc_org'=>$this->npc,
            'affinity'=>$this->affinity,
            'name'=>$name,
            'imgsvr'=>$this->imgsvr,
            'picture'=>$picturePath,
            'nation'=>$nationID,
            'city'=>$this->cityID,
            'leadership'=>$this->leadership,
            'strength'=>$this->strength,
            'intel'=>$this->intel,
            'experience'=>$experience,
            'dedication'=>$dedication,
            'officer_level'=>$officerLevel,
            'gold'=>$this->gold,
            'rice'=>$this->rice,
            'crew'=>0,
            'crewtype'=>GameUnitConst::DEFAULT_CREWTYPE,
            'train'=>0,
            'atmos'=>0,
            'turntime'=>$turntime,
            'killturn'=>$killturn,
            'age'=>$age,
            'belong'=>1,
            'personal'=>$this->ego,
            'special'=>$this->specialDomestic,
            'specage'=>$this->specAge,
            'special2'=>$this->specialWar,
            'specage2'=>$this->specAge2,
            'npcmsg'=>$this->text,
            'makelimit'=>0,
            'bornyear'=>$this->birth,
            'deadyear'=>$this->death,
            'dex1'=>$this->dex1,
            'dex2'=>$this->dex2,
            'dex3'=>$this->dex3,
            'dex4'=>$this->dex4,
            'dex5'=>$this->dex5,
            'aux'=>\sammo\Json::encode($this->aux),
            'lastrefresh'=>TimeUtil::now(),
        ]);
        $this->generalID = $db->insertId();
        $turnRows = [];
        foreach(Util::range(GameConst::$maxTurn) as $turnIdx){
            $turnRows[] = [
                'general_id'=>$this->generalID,
                'turn_idx'=>$turnIdx,
                'action'=>'휴식',
                'arg'=>null,
                'brief'=>'휴식',
            ];
        }
        $db->insert('general_turn', $turnRows);

        $rank_data = [];
        foreach(array_keys(\sammo\General::RANK_COLUMN) as $rankColumn){
            $rank_data[] = [
                'general_id'=>$this->generalID,
                'nation_id'=>0,
                'type'=>$rankColumn,
                'value'=>0
            ];
        }
        $db->insert('rank_data', $rank_data);
        $db->insert('betting', [
            'general_id'=>$this->generalID,
        ]);

        return true; //생성되었다.
    }
}