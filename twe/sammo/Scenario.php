<?php
namespace sammo;

class Scenario{
    const SCENARIO_PATH = __dir__.'/../scenario';

    private $scenarioIdx;
    private $scenarioPath;

    private $data;

    private $nations;
    private $generals;
    private $generalsEx;

    private $initialEvents;
    private $events;

    public function __construct(int $scenarioIdx){
        $scenarioPath = self::SCENARIO_PATH."/scenario_{$scenarioIdx}.json";

        $this->scenarioIdx = $scenarioIdx;
        $this->scenarioPath = $scenarioPath;

        $this->data = Json::decode(file_get_contents($scenarioPath));

        $this->initialEvents = array_map(function($rawEvent){
            return new \sammo\Event\EventHandler($rawEvent[0], array_slice($rawEvent, 1));
        }, Util::array_get($this->data['initialEvents'], []));

        $this->nations = [];
        $this->nations[0] = new Scenario\Nation(0, '재야', '#ffffff', 0, 0);
        foreach (Util::array_get($this->data['nation'],[]) as $idx=>$nationRaw) {
            list($name, $color, $gold, $rice, $infoText, $tech, $type, $nationLevel, $cities) = $nationRaw;
            $nationID = $idx+1;
            
            $this->nations[$nationID] = new Scenario\Nation(
                $nationID, 
                $name, 
                $color, 
                $gold, 
                $rice, 
                $infoText, 
                $tech, 
                $type, 
                $nationLevel, 
                $cities
            );
        }
    }

    public function getScenarioIdx(){
        return $this->scenarioIdx;
    }

    public function getYear(){
        return Util::array_get($this->data['startYear']);
    }

    public function getTitle(){
        return Util::array_get($this->data['title']);
    }

    public function getNPC(){
        return Util::array_get($this->data['general']);
    }

    public function getNPCex(){
        return Util::array_get($this->data['general_ex']);
    }

    public function getNation(){

        $nationsRaw = Util::array_get($this->data['nation']);
        if(!$nationsRaw){
            return [];
        }

        $nations = [];
        foreach($nationsRaw as $idx=>$nation){
            list($name, $color, $gold, $rice, $infoText, $tech, $type, $nationLevel, $cities) = $nation;
            $nationID = $idx+1;

            $nation['id'] = $nationID;

            $nations[$nationID] = [
                'id'=>$nationID,
                'name'=>$name,
                'color'=>$color,
                'gold'=>$gold,
                'rice'=>$rice,
                'infoText'=>$infoText,
                'tech'=>$tech,
                'type'=>$type,
                'nationLevel'=>$nationLevel,
                'cities'=>$cities,
                'generals'=>0
            ];
        }

        $nations[0] = [
            'id'=>0,
            'name'=>'재야',
            'color'=>'#ffffff',
            'gold'=>0,
            'rice'=>0,
            'infoText'=>'재야',
            'tech'=>0,
            'type'=>'재야',
            'nationLevel'=>0,
            'cities'=>[],
            'generals'=>0
        ];

        foreach(Util::array_get($this->data['general'], []) as $idx=>$general){
            while(count($general) < 14){
                $general[] = null;
            }
            list(
                $a, $name, $npcname, $nationID, $specifiedCity, 
                $leadership, $power, $intel, $birth, $death, 
                $charDom, $charWar, $text
            ) = $general;

            if(array_key_exists($nationID, $nations)){
                $nations[$nationID]['generals']++;
            };
        }

        return $nations;
    }

    public function getScenarioBrief(){
        $nations = [];

        return [
            'year'=>$this->getYear(),
            'title'=>$this->getTitle(),
            'npc_cnt'=>count($this->getNPC()),
            'npcEx_cnt'=>count($this->getNPCex()),
            'nation'=>$this->getNation()
        ];
    }

    public function buildGame($env=[]){
        //NOTE: 초기화가 되어있다고 가정함.

    }

    /**
     * @return \sammo\Scenario[]
     */
    public static function getAllScenarios(){
        $result = [];

        foreach(glob(self::SCENARIO_PATH.'/scenario_*.json') as $scenarioPath){
            $scenarioName = pathinfo(basename($scenarioPath), PATHINFO_FILENAME);
            $scenarioIdx = Util::array_last(explode('_', $scenarioName));
            
            if(!is_numeric($scenarioIdx)){
                continue;
            }
            $scenarioIdx = Util::toInt($scenarioIdx);

            if($scenarioIdx === null){
                continue;
            }

            $result[$scenarioIdx] = new Scenario($scenarioIdx);
        }
        return $result;
    }
}