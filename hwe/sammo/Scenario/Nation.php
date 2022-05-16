<?php
namespace sammo\Scenario;
use \sammo\DB;
use \sammo\GameConst;
use \sammo\Util;
use \sammo\KVStorage;
use \sammo\Json;
use sammo\RandUtil;

use function \sammo\getNationChiefLevel;

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

    private $capital = null;

    private $cities = [];

    private $generals = [];

    public function __construct(
        private readonly RandUtil $rng,
        int $id = null,
        string $name = '국가',
        string $color = '#000000',
        int $gold = 0,
        int $rice = 2000,
        string $infoText = '국가 설명',
        int $tech = 0,
        ?string $type = null,
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

        if(count($this->cities)){
            $this->capital = $this->cities[0];
        }
    }

    public function setID(int $id){
        $this->id = $id;
    }

    public function getID(){
        return $this->id;
    }

    public function getName(){
        return $this->name;
    }

    public function build($env){
        //NOTE: NPC의 숫자는 아직 확정된 것이 아니다.
        $cities = array_map(function($cityName){
            return \sammo\CityHelper::getCityByName($cityName)['id'];
        }, $this->cities);
        if($this->capital){
            $capital = \sammo\CityHelper::getCityByName($this->capital)['id'];
        }
        else{
            $capital = 0;
        }

        if($this->type === null){
            $type = $this->rng->choice(GameConst::$availableNationType);
        }
        else if(strpos($this->type, '_') === FALSE){
            $type = 'che_'.$this->type;
        }
        else{
            $type = $this->type;
        }

        $db = DB::db();
        $nationStor = KVStorage::getStorage($db, $this->id, 'nation_env');
        $otherNations = $db->queryFirstColumn('SELECT nation FROM nation');

        $aux = [
            'can_국기변경'=>1
        ];

        if($this->nationLevel==7){
            $aux['can_국호변경']=1;
        }

        $db->insert('nation', [
            'nation'=>$this->id,
            'name'=>$this->name,
            'color'=>$this->color,
            'capital'=>$capital,
            'gennum'=>0,
            'gold'=>$this->gold,
            'rice'=>$this->rice,
            'bill'=>100,
            'rate'=>15,
            'scout'=>0,
            'war'=>0,
            'strategic_cmd_limit'=>24,
            'surlimit'=>72,
            'tech'=>$this->tech,
            'level'=>$this->nationLevel,
            'type'=>$type,
            'aux'=>Json::encode($aux),
        ]);

        if($cities){
            $db->update('city', [
                'nation'=>$this->id
            ], 'city IN %li', $cities);
        }

        $nationStor->scout_msg = $this->infoText;


        $diplomacy = [];
        foreach($otherNations as $nation){
            $diplomacy[] = [
                'me'=>$this->id,
                'you'=>$nation,
                'state'=>2
            ];
            $diplomacy[] = [
                'me'=>$nation,
                'you'=>$this->id,
                'state'=>2
            ];
        }
        if(count($diplomacy) > 0){
            $db->insert('diplomacy', $diplomacy);
        }

    }

    public function addGeneral(GeneralBuilder $general){
        $this->generals[] = $general;
    }

    public function postBuild($env){
        $npc_cnt = count($this->generals);

        $db = DB::db();
        $db->update('nation', [
            'gennum'=>$npc_cnt,
        ], 'nation=%i', $this->id);

        //군주가 없는지 확인
        $hasRuler = $db->queryFirstField('SELECT count(*) FROM general WHERE nation=%i AND officer_level=12', $this->id);
        if(!$hasRuler){
            $newRuler = $db->queryFirstField('SELECT `no` FROM general WHERE nation=%i ORDER BY leadership+strength+intel DESC LIMIT 1', $this->id);
            $db->update('general',['officer_level'=>12], 'no=%i', $newRuler);
        }

        $turnRows = [];
        foreach(Util::range(12, getNationChiefLevel($this->nationLevel)-1, -1) as $chiefLevel){
            foreach(Util::range(GameConst::$maxChiefTurn) as $turnIdx){
                $turnRows[] = [
                    'nation_id'=>$this->id,
                    'officer_level'=>$chiefLevel,
                    'turn_idx'=>$turnIdx,
                    'action'=>'휴식',
                    'arg'=>'{}',
                    'brief'=>'휴식',
                ];
            }
        }
        $db->insertIgnore('nation_turn', $turnRows);
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
            'cities'=>$this->cities
        ];
    }
}