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

    private $tmpGeneralQueue = [];

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
                $affinity, $name, $pictureID, $nationID, $locatedCity, 
                $leadership, $power, $intel, $birth, $death, $ego,
                $char, $text
            ) = $rawGeneral;

            if(!key_exists($nationID, $this->nations)){
                $nationID = 0;
            }

            $this->tmpGeneralQueue[$name] = $rawGeneral;

            $general = new Scenario\NPC(
                $affinity, 
                $name, 
                $pictureID, 
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

        $this->generalsEx = array_map(function($rawGeneral){
            while(count($rawGeneral) < 14){
                $rawGeneral[] = null;
            }

            list(
                $affinity, $name, $pictureID, $nationID, $locatedCity, 
                $leadership, $power, $intel, $birth, $death, $ego,
                $char, $text
            ) = $rawGeneral;

            if(!key_exists($nationID, $this->nations)){
                $nationID = 0;
            }

            $this->tmpGeneralQueue[$name] = $rawGeneral;

            $general = new Scenario\NPC(
                $affinity, 
                $name, 
                $pictureID, 
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

            $this->nations[$nationID]->addNPC($general, true);
        }, Util::array_get($data['generalEx'], []));

        $this->initialEvents = array_map(function($rawEvent){
            return new \sammo\Event\EventHandler($rawEvent[0], array_slice($rawEvent, 1));
        }, Util::array_get($data['initialEvents'], []));

        $this->events = array_map(function($rawEvent){
            //event는 여기서 풀지 않는다.
            return [
                'cond' => $rawEvent[0],
                'action' => array_slice($rawEvent, 1)
            ];
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
        return $this->nations;

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
            'nation'=>array_map(function($nation){
                return $nation->getBrief();
            },$this->getNation())
        ];
    }

    private function buildGenerals($env){
        $remainGenerals = [];
        foreach($this->generals as $general){
            if($general->build($env)){
                continue;
            }

            $rawGeneral = $this->tmpGeneralQueue[$general->$name];
            $birth = $general->birth; 
            if(!key_exists($birth, $remainGenerals)){
                $remainGenerals[$birth] = [];
            }
            $remainGenerals[$birth][] = array_merge(['RegNPC'], $rawGeneral);
        }

        if($env['useExtentedGeneral']){
            foreach($this->generalsEx as $general){
                if($general->build($env)){
                    continue;
                }
            }

            $rawGeneral = $this->tmpGeneralQueue[$general->$name];
            $birth = $general->birth;
            if(!key_exists($birth, $remainGenerals)){
                $remainGenerals[$birth] = [];
            }
            $remainGenerals[$birth][] = array_merge(['RegNPC'], $rawGeneral);
        }
        return $remainGenerals;
    }

    private function buildDiplomacy($env){
        $db = DB::db();
        foreach($this->diplomacy as $diplomacy){
            list($me, $you, $state, $remain) = $diplomacy;
            $db->update('diplomacy', [
                'state'=>$state,
                'remain'=>$remain
            ], '(me = %i_me AND you = %i_you) OR (me = %i_you AND you = %i_me)', [
                'me'=>$me,
                'you'=>$you
            ]);
        }
    }

    public function buildGame($env=[]){
        //NOTE: 초기화가 되어있다고 가정함.

        /*
        env로 사용된 것들,
        게임 변수 : year, month
        game 테이블 변수 : startyear, year, month, genius, turnterm, show_img_level, extend, fiction, npcmode
        install 변수 : npcmode, show_img_level, extend, scenario, fiction

        event변수 : currentEventID
        */

        $db = DB::db();

        foreach($this->nations as $id=>$nation){
            if($id == 0){
                continue;
            }

            $nation->build($env);
        }
        CityHelper::flushCache();

        $remainGenerals = $this->buildGenerals($env);

        foreach($remainGenerals as $birth=>$actions){
            $targetYear = $birth + 14;//FIXME: 14가 어디서 튀어나왔나?

            $actions[] = ['DeleteEvent'];
            $this->events[] = [
                'cond'=>['date', '==', $targetYear, '1'],
                'action'=>$actions
            ];
        }

        $this->buildDiplomacy($env);

        foreach($this->initialEvents as $event){
            $event->tryRunEvent($env);
        }

        $events = array_map(function($rawEvent){
            return [
                'cond'=>Json::encode($rawEvent['cond']),
                'action'=>Json::encode($rawEvent['action'])
            ];
        }, $this->events);

        $db->insert('event', $this->events);
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