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

    private $initOK = false;

    private function initFull(){
        if($this->initOK){
            return;
        }
        $this->initOK = true;
        $data = $this->data;

        $this->nations = [];
        $this->nations[0] = new Scenario\Nation(0, '재야', '#000000', 0, 0);
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

        $this->diplomacy = Util::array_get($data['diplomacy'], []);

        
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

            return new Scenario\NPC(
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
        }, Util::array_get($data['general'], []));

        $this->generalsEx = array_map(function($rawGeneral){
            while(count($rawGeneral) < 14){
                $rawGeneral[] = null;
            }

            list(
                $affinity, $name, $pictureID, $nationID, $locatedCity, 
                $leadership, $power, $intel, $level, $birth, $death, $ego,
                $char, $text
            ) = $rawGeneral;

            if(!key_exists($nationID, $this->nations)){
                $nationID = 0;
            }

            $this->tmpGeneralQueue[$name] = $rawGeneral;

            return new Scenario\NPC(
                $affinity, 
                $name, 
                $pictureID, 
                $nationID, 
                $locatedCity, 
                $leadership, 
                $power, 
                $intel, 
                $level,
                $birth, 
                $death, 
                $ego,
                $char, 
                $text
            );

        }, Util::array_get($data['generalEx'], []));

        $this->initialEvents = array_map(function($rawEvent){
            $cond = $rawEvent[0];
            $action = array_slice($rawEvent, 1);
            return new \sammo\Event\EventHandler($cond, $action);
        }, Util::array_get($data['initialEvents'], []));

        $this->events = array_map(function($rawEvent){
            //event는 여기서 풀지 않는다. 평가만 한다.
            $cond = $rawEvent[0];
            $action = array_slice($rawEvent, 1);
            
            new \sammo\Event\EventHandler($cond, $action);
            
            return [
                'cond' => $cond,
                'action' => $action
            ];
        }, Util::array_get($data['events'], []));
    }

    public function __construct(int $scenarioIdx, bool $lazyInit = true){
        $scenarioPath = self::SCENARIO_PATH."/scenario_{$scenarioIdx}.json";

        $this->scenarioIdx = $scenarioIdx;
        $this->scenarioPath = $scenarioPath;

        $data = Json::decode(file_get_contents($scenarioPath));
        $this->data = $data;

        $this->year = Util::array_get($data['startYear']);
        $this->title = Util::array_get($data['title'] , '');

        $this->history = Util::array_get($data['history'], []);

        if(!$lazyInit){
            $this->initFull();
        }

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
        $this->initFull();
        return $this->generals;
    }

    public function getNPCex(){
        $this->initFull();
        return $this->generalsEx;
    }

    public function getNation(){
        $this->initFull();
        return $this->nations;
    }

    public function getScenarioBrief(){
        $this->initFull();

        $nations = [];
        $nationGeneralCnt = [];
        $nationGeneralExCnt = [];

        foreach($this->generals as $general){
            $nationID = $general->nationID;
            if(!key_exists($nationID, $nationGeneralCnt)){
                $nationGeneralCnt[$nationID] = 1;
            }
            else{
                $nationGeneralCnt[$nationID] += 1;
            }
        }

        foreach($this->generalsEx as $general){
            $nationID = $general->nationID;
            if(!key_exists($nationID, $nationGeneralExCnt)){
                $nationGeneralExCnt[$nationID] = 1;
            }
            else{
                $nationGeneralExCnt[$nationID] += 1;
            }
        }

        return [
            'year'=>$this->getYear(),
            'title'=>$this->getTitle(),
            'npc_cnt'=>count($this->getNPC()),
            'npcEx_cnt'=>count($this->getNPCex()),
            'nation'=>array_map(function($nation) use ($nationGeneralCnt, $nationGeneralExCnt){
                $brief = $nation->getBrief();
                $brief['generals'] = Util::array_get($nationGeneralCnt[$nation->getID()], 0);
                $brief['generalsEx'] = Util::array_get($nationGeneralExCnt[$nation->getID()], 0);

                return $brief;
            },$this->getNation())
        ];
    }

    private function buildGenerals($env){
        $this->initFull();

        $remainGenerals = [];
        foreach($this->generals as $general){
            if($general->build($env)){
                if($general->nationID){
                    $this->nations[$general->nationID]->addGeneral($general);
                }
                continue;
            }

            $rawGeneral = $this->tmpGeneralQueue[$general->name];
            $birth = $general->birth; 
            if(!key_exists($birth, $remainGenerals)){
                $remainGenerals[$birth] = [];
            }
            $remainGenerals[$birth][] = array_merge(['RegNPC'], $rawGeneral);
        }

        if($env['extended_general']){
            foreach($this->generalsEx as $general){
                if($general->build($env)){
                    if($general->nationID){
                        $this->nations[$general->nationID]->addGeneral($general);
                    }
                    continue;
                }

                $rawGeneral = $this->tmpGeneralQueue[$general->name];
                $birth = $general->birth;
                if(!key_exists($birth, $remainGenerals)){
                    $remainGenerals[$birth] = [];
                }
                $remainGenerals[$birth][] = array_merge(['RegNPC'], $rawGeneral);
            }
        }
        return $remainGenerals;
    }

    private function buildDiplomacy($env){
        $this->initFull();

        $db = DB::db();
        foreach($this->diplomacy as $diplomacy){
            list($me, $you, $state, $remain) = $diplomacy;
            $db->update('diplomacy', [
                'state'=>$state,
                'term'=>$remain
            ], '(me = %i AND you = %i) OR (me = %i AND you = %i)', $me, $you, $you, $me);
        }
    }

    public function build($env=[]){
        $this->initFull();

        //NOTE: 초기화가 되어있다고 가정함.

        /*
        env로 사용된 것들,
        게임 변수 : year, month
        game 테이블 변수 : startyear, year, month, genius, turnterm, show_img_level, extended_general, fiction, npcmode
        install 변수 : npcmode, show_img_level, extended_general, scenario, fiction

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
            $targetYear = $birth + \sammo\GameConst::$adultAge;

            $actions[] = ['DeleteEvent'];
            $this->events[] = [
                'cond'=>['Date', '==', $targetYear, '1'],
                'action'=>$actions
            ];
        }

        foreach($this->nations as $id=>$nation){
            if($id == 0){
                continue;
            }

            $nation->postBuild($env);
        }

        $this->buildDiplomacy($env);

        foreach($this->initialEvents as $event){
            $event->tryRunEvent($env);
        }

        $events = array_map(function($rawEvent){
            return [
                'condition'=>Json::encode($rawEvent['cond']),
                'action'=>Json::encode($rawEvent['action'])
            ];
        }, $this->events);

        if(count($events) > 0){
            $db->insert('event', $events);
        }

        

        pushWorldHistory($this->history, $env['year'], $env['month']);

        refreshNationStaticInfo();
        foreach(getAllNationStaticInfo() as $nation){
            SetNationFront($nation['nation']);
        }
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