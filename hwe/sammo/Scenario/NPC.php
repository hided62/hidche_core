<?php
namespace sammo\Scenario;
use \sammo\Util;
use \sammo\JosaUtil;
use \sammo\DB;
use \sammo\CityHelper;
use \sammo\GameUnitConst;
use \sammo\CityConst;
use \sammo\GameConst;
use \sammo\SpecialityConst;

class NPC{

    public $generalID = null;
    public $realName = null;

    public $affinity; 
    public $name; 
    public $picturePath; 
    public $nationID; 
    public $locatedCity; 
    public $leadership; 
    public $power; 
    public $intel; 
    public $level;
    public $birth; 
    public $death; 
    public $ego;
    public $charDomestic; 
    public $charWar; 
    public $npc = 2;
    public $text;
    static $prefixList = [
        1 => 'ⓝ', //빙의 NPC
        2 => 'ⓝ', //NPC
        3 => 'ⓜ', //인탐 장수
        4 => 'ⓖ', //의병장(전략)
        5 => '㉥', //부대장
        6 => 'ⓤ', //unselectable npc, 빙의 불가 npc
        
        9 => 'ⓞ', //오랑캐?
    ];

    protected $gold;
    protected $rice;

    protected $specAge = null;
    protected $specAge2 = null;
    protected $experience = null;
    protected $dedication = null;

    //XXX: 코드 못 바꾸나?
    protected $dex0 = 0;
    protected $dex10 = 0;
    protected $dex20 = 0;
    protected $dex30 = 0;
    protected $dex40 = 0;

    public function __construct(
        int $affinity, 
        string $name, 
        $picturePath, 
        int $nationID, 
        $locatedCity,
        int $leadership, 
        int $power, 
        int $intel, 
        int $level = 0,
        int $birth = 160, 
        int $death = 300, 
        $ego = null,
        $char = null, 
        $text = null
    ){
        $this->affinity = $affinity;
        $this->name = $name;
        $this->picturePath = $picturePath;
        $this->nationID = $nationID;
        $this->locatedCity = $locatedCity;
        $this->leadership = $leadership;
        $this->power = $power;
        $this->intel = $intel;
        $this->level = $level;
        $this->birth = $birth;
        $this->death = $death;
        $this->ego = $ego;
        $this->text = $text;

        $this->gold = GameConst::$defaultGold;
        $this->rice = GameConst::$defaultRice;

        $general = [
            'leader'=>$leadership,
            'power'=>$power,
            'intel'=>$intel
        ];

        $this->charDomestic = GameConst::$defaultSpecialDomestic;
        $this->charWar = GameConst::$defaultSpecialWar;

        if($char === '랜덤전특'){
            $this->charWar = SpecialityConst::pickSpecialWar($general);
        }
        else if($char === '랜덤내특'){
            $this->charDomestic = SpecialityConst::pickSpecialDomestic($general);
        }
        else if($char === '랜덤'){
            if(Util::randBool(2/3)){
                $this->charWar = SpecialityConst::pickSpecialWar($general);
            }
            else{
                $this->charDomestic = SpecialityConst::pickSpecialDomestic($general);
            }
        }
        else if($char !== null){
        }
        else{
            //TODO: 내특, 전특 구분 필요

            try{
                $domesticClass = \sammo\getGeneralSpecialDomesticClass($char);
                $this->charDomestic = Util::getClassName($domesticClass);
            }
            catch (Exception $e) {
                $warClass = \sammo\getGeneralSpecialWarClass($char);
                $this->charWar = Util::getClassName($domesticClass);
            }
        }  
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

    public function setDex(int $footman, int $archer, int $cavalry, int $wizard, int $siege):self{
        $this->dex0 = $footman;
        $this->dex10 = $archer;
        $this->dex20 = $cavalry;
        $this->dex30 = $wizard;
        $this->dex40 = $siege;
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
            \sammo\pushWorldHistory(["<C>●</>{$month}월:<Y>{$name}</>{$josaYi} 성인이 되어 <S>등장</>했습니다."], $year, $month);
        }

        if($this->ego == null || $isFictionMode){
            $ego = mt_rand(0, 9);//TODO: 나중에 성격을 따로 분리할 경우 클래스를 참조.
        }
        else{
            $ego = \sammo\CharCall($this->ego);
        }
        
        $affinity = $this->affinity;

        $charWar = $this->charWar;
        $charDomestic = $this->charDomestic;

        if($affinity === 0 || $isFictionMode){
            $affinity = mt_rand(1, 150);
        }

        if($isFictionMode){
            $charWar = GameConst::$defaultSpecialWar;
            $charDomestic = GameConst::$defaultSpecialDomestic;
        }

        $name = (static::$prefixList[$this->npc]?:'ⓧ').$this->name;

        $duplicateCnt = $db->queryFirstField('SELECT count(no) FROM general WHERE name LIKE %s', $name.'%') + 1;

        if($duplicateCnt > 1){
            $name = "{$name}($duplicateCnt}";   
        }
        $this->realName = $name;

        $query = "select no from general where name like '{$name}%'";
            $result = MYDB_query($query, $connect) or Error(__LINE__.MYDB_error($connect),"");
            $count = MYDB_num_rows($result);
            $count++;

        $this->realName = $name;

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

        $city = $this->locatedCity;
        if(is_int($city)){
            $city = CityConst::byID($city)['id']??null;
        }
        else if(is_string($city)){
            $city = CityConst::byName($city)['id']??null;
        }
        if($city === null){
            if($nationID == 0 || !CityHelper::getAllNationCities($nationID)){
                $cityObj = Util::choiceRandom(CityHelper::getAllCities());
            }
            else{
                $cityObj = Util::choiceRandom(CityHelper::getAllNationCities($nationID));
            }
            '@phan-var array<string,string|int> $cityObj';
            $city = $cityObj['id'];
        }
        

        $experience = $this->experience?:$age * 100;
        $dedication = $this->dedication?:$age * 100;
        $level = $this->level;
        if(!$level || $isNewGeneral){
            $level = $nationID?1:0;
        }

        $turntime = \sammo\getRandTurn($env['turnterm']);

        $killturn = ($this->death - $year) * 12 + mt_rand(0, 11);

        $specage = $this->specAge?:$age + 1;
        $specage2 = $this->specAge2?:$age + 1;

        $db->insert('general',[
            'npc'=>$this->npc,
            'npc_org'=>$this->npc,
            'affinity'=>$affinity,
            'name'=>$name,
            'picture'=>$picturePath,
            'nation'=>$nationID,
            'city'=>$city,
            'leader'=>$this->leadership,
            'power'=>$this->power,
            'intel'=>$this->intel,
            'experience'=>$experience,
            'dedication'=>$dedication,
            'level'=>$level,
            'gold'=>$this->gold,
            'rice'=>$this->rice,
            'crew'=>0,
            'crewtype'=>GameUnitConst::DEFAULT_CREWTYPE,
            'train'=>0,
            'atmos'=>0,
            'weap'=>0,
            'book'=>0,
            'horse'=>0,
            'turntime'=>$turntime,
            'killturn'=>$killturn,
            'age'=>$age,
            'belong'=>1,
            'personal'=>$ego,
            'special'=>$charDomestic,
            'specage'=>$specage,
            'special2'=>$charWar,
            'specage2'=>$specage2,
            'npcmsg'=>$this->text,
            'makelimit'=>0,
            'bornyear'=>$this->birth,
            'deadyear'=>$this->death,
            'dex0'=>$this->dex0,
            'dex10'=>$this->dex10,
            'dex20'=>$this->dex20,
            'dex30'=>$this->dex30,
            'dex40'=>$this->dex40,
        ]);
        $this->generalID = $db->insertId();

        return true; //생성되었다.
    }
}