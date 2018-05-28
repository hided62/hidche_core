<?php
namespace sammo\Scenario;
use \sammo\Util;
use \sammo\DB;
use \sammo\CityHelper;

class NPC{

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
    public $charDomestic = 0; 
    public $charWar = 0; 
    public $text;

    //[  1,     "헌제",1002,  1,    null, 17, 13, 61, 0, 170, 250, "안전",    null, "산 넘어 산이로구나..."],
    public function __construct(
        int $affinity, 
        string $name, 
        $picturePath, 
        int $nationID, 
        $locatedCity, //FIXME: 7.1로 올릴 때 ?string 으로 변경
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

        $general = [
            'leader'=>$leadership,
            'power'=>$power,
            'intel'=>$intel
        ];

        if($char === '랜덤전특'){
            $this->charWar = \sammo\SpecialityConst::pickSpecialWar($general);
        }
        else if($char === '랜덤내특'){
            $this->charDomestic = \sammo\SpecialityConst::pickSpecialDomestic($general);
        }
        else if($char === '랜덤'){
            if(Util::randBool(2/3)){
                $this->charWar = \sammo\SpecialityConst::pickSpecialWar($general);
            }
            else{
                $this->charDomestic = \sammo\SpecialityConst::pickSpecialDomestic($general);
            }
        }
        else{
            $char = \sammo\SpecCall($char);
            if($char < 40){
                $this->charDomestic = $char;
            }
            else{
                $this->charWar = $char;
            }
        }  
    }

    public function build($env=[]){
        //scenario에 life==1인 경우 수명 제한이 없어지는 모양.

        $isFictionMode = (Util::array_get($env['fiction'], 0)!=0);

        $year = $env['year'];
        $month = $env['month'];
        $age = $year - $this->birth;
        $name = $this->name;

        if($this->death <= $year){
            return true; //죽었으니 넘어간다.
        }
        if($age < \sammo\GameConst::$adultAge){
            return false; //예약.
        }

        $isNewGeneral = ($age == \sammo\GameConst::$adultAge);

        $nationID = $this->nationID;
        if($isFictionMode && $isNewGeneral){
            $nationID = 0;
        }

        if(!\sammo\getNationStaticInfo($nationID)){
            $nationID = 0;
        };


        $db = DB::db();

        if($isNewGeneral){
            \sammo\pushWorldHistory(["<C>●</>{$month}월:<Y>{$name}</>(이)가 성인이 되어 <S>등장</>했습니다."], $year, $month);
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
            $charWar = 0;
            $charDomestic = 0;
        }

        $name = 'ⓝ'.$this->name;

        $picturePath = $this->picturePath;
        if($env['show_img_level'] < 3){
            $picturePath = 'default.jpg';
        }
        else if(is_numeric($picturePath)){
            $picturePath = "{$picturePath}.jpg";
        }

        $city = $this->locatedCity;
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
        else{
            $city = CityHelper::getCityByName($city)['id'];
        }

        $experience = $age * 100;
        $dedication = $age * 100;
        $level = $this->level;
        if(!$level){
            $level = $nationID?1:0;
        }

        $turntime = \sammo\getRandTurn($env['turnterm']);

        $killturn = ($this->death - $year) * 12 + mt_rand(0, 11);

        $specage = $age + 1;
        $specage2 = $age + 1;

        $npcID = $db->queryFirstField('SELECT max(npcid)+1 FROM general');

        $db->insert('general',[
            'npcid'=>$npcID,
            'npc'=>2,
            'npc_org'=>2,
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
            'gold'=>1000,
            'rice'=>1000,
            'crew'=>0,
            'crewtype'=>0,
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
            'deadyear'=>$this->death
        ]);

        return true; //생성되었다.
    }
}