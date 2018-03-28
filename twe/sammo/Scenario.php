<?php
namespace sammo;

class Scenario{
    const SCENARIO_PATH = __dir__.'/../scenario';

    private $scenarioIdx;
    private $scenarioPath;

    private $data;

    private $year;
    private $title;

    private $history;
    
    private $nations;
    private $diplomacy;

    private $generals;
    private $generalsEx;

    private $initialEvents;
    private $events;

    public function __construct(int $scenarioIdx){
        $scenarioPath = self::SCENARIO_PATH."/scenario_{$scenarioIdx}.json";

        $this->scenarioIdx = $scenarioIdx;
        $this->scenarioPath = $scenarioPath;

        $data = Json::decode(file_get_contents($scenarioPath));
        $this->data = $data;

        $this->year = Util::array_get($data['startYear']);
        $this->title = Util::array_get($data['title'] , '');

        $this->history = Util::array_get($data['history'], []);

        $this->nations = [];
        $this->nations[0] = new Scenario\Nation(0, '재야', '#ffffff', 0, 0);
        foreach (Util::array_get($data['nation'],[]) as $idx=>$rawNation) {
            list($name, $color, $gold, $rice, $infoText, $tech, $type, $nationLevel, $cities) = $rawNation;
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

        $this->displomacy = Util::array_get($data['diplomacy'], []);

        
        $this->generals = array_map(function($rawGeneral){
            while(count($rawGeneral) < 14){
                $rawGeneral[] = null;
            }

            list(
                $affinity, $name, $npcID, $nationID, $locatedCity, 
                $leadership, $power, $intel, $birth, $death, $ego,
                $char, $text
            ) = $rawGeneral;

            if(!key_exists($nationID, $this->nations)){
                $nationID = 0;
            }

            $general = new Scenario\NPC(
                $affinity, 
                $name, 
                $npcID, 
                $nationID, 
                $locatedCity, 
                $leadership, 
                $power, 
                $intel, 
                $birth, 
                $death, 
                $ego,
                $char, 
                $text
            );

            $this->nations[$nationID]->addNPC($general);
        }, Util::array_get($data['general'], []));

        $this->initialEvents = array_map(function($rawEvent){
            return new \sammo\Event\EventHandler($rawEvent[0], array_slice($rawEvent, 1));
        }, Util::array_get($data['initialEvents'], []));

        $this->events = array_map(function($rawEvent){
            return new \sammo\Event\EventHandler($rawEvent[0], array_slice($rawEvent, 1));
        }, Util::array_get($data['events'], []));

    }

    public function getScenarioIdx(){
        return $this->scenarioIdx;
    }

    public function getYear(){
        return $this->year;
    }

    public function getTitle(){
        return $this->title;
    }

    public function getNPC(){
        return $this->generals;
    }

    public function getNPCex(){
        return $this->generalsEx;
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


        foreach($this->nations as $id=>$nation){
            if($id == 0){
                continue;
            }
            
            $nation->build($env);
        }
        CityHelper::flushCache();
        foreach($this->generals as $general){
            $general->build($env);
        }

        if($env['useExtentedGeneral']){
            foreach($this->generalsEx as $general){
                $general->build($env);
            }
        }

        //TODO: 외교를 추가해야함
        foreach($this->initialEvents as $event){
            $event->tryRunEvent($env);
        }
        //TODO: event를 전역 handler에 등록해야함.
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