<?php
namespace sammo\Scenario;
use \sammo\DB;
use \sammo\Util;

class Nation{
    private $id;
    private $name;
    private $color;
    private $gold;
    private $rice;
    private $infoText;
    private $tech;
    private $type;
    private $nationLevel;

    private $capital;

    private $cities = [];
    private $generals = [];
    private $generalsEx = [];

    public function __construct(
        int $id = null, 
        string $name = '국가', 
        string $color = '#ffffff', 
        int $gold = 0, 
        int $rice = 2000, 
        string $infoText = '국가 설명', 
        int $tech = 0, 
        string $type = '유가', 
        int $nationLevel = 0, 
        array $cities = []
    ){
        $this->id = $id;
        $this->name = $name;
        $this->color = $color;
        $this->gold = $gold;
        $this->rice = $rice;
        $this->infoText = $infoText;
        $this->tech = $tech;
        $this->type = $type;
        $this->nationLevel = $nationLevel;
        $this->cities = $cities;
        
        $this->capital = count($cities)>0?$cities[0]:null;
    }

    public function setID(int $id){
        $this->id = $id;
    }

    public function addNPC(NPC $npc, bool $isExtend=false){
        if($isExtend){
            $this->generalsEx[] = $npc;
        }
        else{
            $this->generals[] = $npc;
        }
        
    }

    public function build($env=[]){
        $npc_cnt = count($this->generals);
        if($env['useExtentedGeneral']){
            $npc_cnt += count($this->generalsEx);
        }

        $cities = array_map(function($cityName){
            return \sammo\CityHelper::getCityByName($cityName)['id'];
        }, $this->cities);
        $capital = \sammo\CityHelper::getCityByName($this->capital)['id'];

        $type = \sammo\NationCharCall($this->$type);

        $db = DB::db();
        $otherNations = $db->queryFirstColumn('SELECT nation FROM nation');

        $db->insert('nation', [
            'nation'=>$this->id,
            'name'=>$this->name,
            'color'=>$this->color,
            'capital'=>$capital,
            'gennum'=>$npc_cnt,
            'gold'=>$this->gold,
            'rice'=>$this->rice,
            'bill'=>100,
            'rate'=>15,
            'scout'=>0,
            'war'=>0,
            'tricklimit'=>24,
            'surlimit'=>72,
            'scoutmsg'=>$this->infoText,
            'tech'=>$this->tech,
            'totaltech'=>$this->tech*$npc_cnt,
            'level'=>$this->level,
            'type'=>$type,
        ]);

        $db->update('city', [
            'nation'=>$this->id
        ], 'city IN (%li)', $cities);

        
        $diplomacy = [];
        foreach($otherNations as $nation){
            $diplomacy[] = [
                'me'=>$this->$id,
                'you'=>$nation,
                'state'=>2
            ];
            $diplomacy[] = [
                'me'=>$nation,
                'you'=>$this->$id,
                'state'=>2
            ];
        }
        $db->insert('diplomacy', $diplomacy);
    }

    public function getBrief(){
        return [
            'id'=>$this->id,
            'name'=>$this->name,
            'color'=>$this->color,
            'gold'=>$this->gold,
            'rice'=>$this->rice,
            'infoText'=>$this->infoText,
            'tech'=>$this->tech,
            'type'=>$this->type,
            'nationLevel'=>$this->nationLevel,
            'cities'=>$this->cities,
            'generals'=>count($this->generals),
            'generalsEx'=>count($this->generalsEx)
        ];
    }
}