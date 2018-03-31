<?php
namespace sammo\Scenario;
use \sammo\Util;
use \sammo\DB;
use \sammo\CityHelper;

class NPC{

    public $affinity; 
    public $name; 
    public $pictureID; 
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
        int $pictureID, 
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
        $this->pictureID = $pictureID;
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

        $char = \sammo\SpecCall($char);
        if($char < 40){
            $this->charDomestic = $char;
        }
        else{
            $this->charWar = $char;
        }
    }


    public function build($env=[]){
        //scenario에 life==1인 경우 수명 제한이 없어지는 모양.
        $nationID = $this->nationID;
        if(!\sammo\getNationStaticInfo($nationID)){
            $nationID = 0;
        };

        $year = $env['year'];
        $month = $env['month'];
        $age = $year - $this->birth;

        if($this->death < $year){
            return true; //죽었으니 넘어간다.
        }
        if($age < \sammo\GameConst::$adultAge){
            return false; //예약.
        }

        $db = DB::db();

        if($age == \sammo\GameConst::$adultAge && $month == 1){//FIXME: 14가 어디서 튀어나왔나?
            \sammo\pushHistory(["<C>●</>1월:<Y>$name</>(이)가 성인이 되어 <S>등장</>했습니다."]);
        }

        if($this->ego == null){
            $ego = mt_rand(0, 9);//TODO: 나중에 성격을 따로 분리할 경우 클래스를 참조.
        }
        else{
            $ego = \sammo\CharCall($this->ego);
        }
        
        $name = 'ⓝ'.$this->name;

        if($env['show_img_level'] == 3 && $pictureID > 0){
            $picture = "{$pictureID}.jpg";
        }
        else{
            $picture = 'default.jpg';
        }

        $city = $this->locatedCity;
        if($city === null){
            if($nationID == 0){
                $city = Util::choiceRandom(CityHelper::getAllCities())['id'];
            }
            else{
                $city = Util::choiceRandom(CityHelper::getAllNationCities($nationID))['id'];
            }
        }

        $experience = $age * 100;
        $dedication = $age * 100;
        $level = $nationID?1:$this->level;

        $turntime = \sammo\getRandTurn($env['turnterm']);

        $killturn = ($this->death - $year) * 12 + mt_rand(0, 11);

        $specage = $age + 1;
        $specage2 = $age + 1;

        $npcID = $db->queryFirstField('SELECT max(npcid)+1 FROM general');

        $db->insert('general',[
            'npcid'=>$npcID,
            'npc'=>2,
            'npc_org'=>2,
            'affinity'=>$this->affinity,
            'name'=>$name,
            'picture'=>$picture,
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
            'special'=>$this->charDomestic,
            'specage'=>$specage,
            'special2'=>$this->charWar,
            'specage2'=>$specage2,
            'npcmsg'=>$this->text,
            'makelimit'=>0,
            'bornyear'=>$this->birth,
            'deadyear'=>$this->death
        ]);

        return true; //생성되었다.
    }
}