<?php
namespace sammo;

use sammo\Scenario\GeneralBuilder;

class Scenario{
    const SCENARIO_PATH = __DIR__.'/../scenario';

    private $scenarioIdx;
    private $scenarioPath;

    private $iconPath = '.';
    private $data;

    /** @var int */
    private $year;

    /** @var string */
    private $title;

    private $history;

    /** @var \sammo\Scenario\Nation[] */
    private $nations;
    /** @var \sammo\Scenario\Nation[] */
    private $nationsInv;

    private $diplomacy;

    /** @var \sammo\Scenario\GeneralBuilder[] */
    private $generals;
    /** @var \sammo\Scenario\GeneralBuilder[] */
    private $generalsEx;
    /** @var \sammo\Scenario\GeneralBuilder[] */
    private $generalsNeutral;

    private $tmpGeneralQueue = [];

    private $initialEvents;
    private $events;

    private $initBasic = false;
    private $initOK = false;

    private $gameConf = null;

    private $tmpEnv;

    public function generateGeneral($rawGeneral, $initFull, $npcType=2): GeneralBuilder{
        while(count($rawGeneral) < 14){
            $rawGeneral[] = null;
        }

        list(
            $affinity, $name, $picturePath, $nationName, $locatedCity,
            $leadership, $strength, $intel, $officerLevel, $birth, $death, $ego,
            $char, $text
        ) = $rawGeneral;

        if(key_exists($nationName, $this->nationsInv)){
            $nationID = $this->nationsInv[$nationName]->getID();
        }
        else if(key_exists($nationName, $this->nations)){
            $nationID = (int)$nationName;
        }
        else{
            $nationID = 0;
        }

        $this->tmpGeneralQueue[$name] = $rawGeneral;

        $obj = (new Scenario\GeneralBuilder(
            $name,
            false,
            $picturePath,
            $nationID
        ));
        if(!$initFull){
            return $obj;
        }
        return $obj
        ->setCity($locatedCity, true)
        ->setStat($leadership, $strength, $intel)
        ->setOfficerLevel($officerLevel)
        ->setEgo($ego)
        ->setSpecialSingle($char)
        ->setNPCText($text)
        ->setAffinity($affinity)
        ->setLifeSpan($birth, $death)
        ->setNPCType($npcType)
        ->fillRemainSpecAsZero($this->tmpEnv);
    }

    public function initLite(){
        if($this->initOK){
            return;
        }
        if($this->initBasic){
            return;
        }

        $data = $this->data;
        $this->tmpEnv = [
            'startyear'=>$this->year,
            'year'=>$this->year,
            'month'=>0 //포인트
        ];

        $neutralNation = new Scenario\Nation(0, '재야', '#000000', 0, 0);
        $this->nations = [];
        $this->nations[0] = $neutralNation;
        $this->nationsInv = [$neutralNation->getName() => $neutralNation];

        foreach (Util::array_get($data['nation'],[]) as $idx=>$rawNation) {
            list($name, $color, $gold, $rice, $infoText, $tech, $type, $nationLevel, $cities) = $rawNation;
            $nationID = $idx+1;


            $nation = new Scenario\Nation(
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
            $this->nations[$nationID] = $nation;
            $this->nationsInv[$nation->getName()] = $nation;
        }

        $this->diplomacy = Util::array_get($data['diplomacy'], []);


        $this->generals = array_map(function($rawGeneral){
            return $this->generateGeneral($rawGeneral, false, 2);
        }, Util::array_get($data['general'], []));

        $this->generalsEx = array_map(function($rawGeneral){
            return $this->generateGeneral($rawGeneral, false, 2);
        }, Util::array_get($data['general_ex'], []));

        $this->generalsNeutral = array_map(function($rawGeneral){
            return $this->generateGeneral($rawGeneral, false, 6);
        }, Util::array_get($data['general_neutral'], []));

        $this->initBasic = true;
    }

    public function initFull(){
        if($this->initOK){
            return;
        }
        $this->initOK = true;
        $data = $this->data;
        $this->tmpEnv = [
            'startyear'=>$this->year,
            'year'=>$this->year,
            'month'=>0 //포인트
        ];

        $neutralNation = new Scenario\Nation(0, '재야', '#000000', 0, 0);
        $this->nations = [];
        $this->nations[0] = $neutralNation;
        $this->nationsInv = [$neutralNation->getName() => $neutralNation];

        foreach (Util::array_get($data['nation'],[]) as $idx=>$rawNation) {
            list($name, $color, $gold, $rice, $infoText, $tech, $type, $nationLevel, $cities) = $rawNation;
            $nationID = $idx+1;


            $nation = new Scenario\Nation(
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
            $this->nations[$nationID] = $nation;
            $this->nationsInv[$nation->getName()] = $nation;
        }

        $this->diplomacy = Util::array_get($data['diplomacy'], []);


        $this->generals = array_map(function($rawGeneral){
            return $this->generateGeneral($rawGeneral, true, 2);
        }, Util::array_get($data['general'], []));

        $this->generalsEx = array_map(function($rawGeneral){
            return $this->generateGeneral($rawGeneral, true, 2);
        }, Util::array_get($data['general_ex'], []));

        $this->generalsNeutral = array_map(function($rawGeneral){
            return $this->generateGeneral($rawGeneral, true, 6);
        }, Util::array_get($data['general_neutral'], []));

        $this->initialEvents = array_map(function($rawEvent){
            $cond = $rawEvent[0];
            $action = array_slice($rawEvent, 1);
            return new \sammo\Event\EventHandler($cond, $action);
        }, Util::array_get($data['initialEvents'], []));

        $this->events = array_map(function($rawEvent){
            //event는 여기서 풀지 않는다. 평가만 한다.
            $target = $rawEvent[0];
            if(!is_string($target)){
                throw new \RuntimeException("{$target}이 문자열이 아님");
            }
            $priority = $rawEvent[1];
            if(!is_int($priority)){
                throw new \RuntimeException("{$priority}가 정수가 아님");
            }
            $cond = $rawEvent[2];
            $action = array_slice($rawEvent, 3);

            new \sammo\Event\EventHandler($cond, $action);

            return [
                'target' => $target,
                'priority' => $priority,
                'cond' => $cond,
                'action' => $action
            ];
        }, Util::array_get($data['events'], []));
    }

    public function getGameConf(){
        if($this->gameConf){
            return $this->gameConf;
        }
        $defaultPath = self::SCENARIO_PATH."/default.json";
        if(!file_exists($defaultPath)){
            throw new \RuntimeException('기본 시나리오 설정 파일 없음!');
        }
        $default = Json::decode(file_get_contents($defaultPath));

        $stat = [
            'defaultStatTotal'=>$this->data['stat']['total']??$default['stat']['total'],
            'defaultStatMin'=>$this->data['stat']['min']??$default['stat']['min'],
            'defaultStatMax'=>$this->data['stat']['max']??$default['stat']['max'],
            'defaultStatNPCTotal'=>$this->data['stat']['npcTotal']??$default['stat']['npcTotal'],
            'defaultStatNPCMax'=>$this->data['stat']['npcMax']??$default['stat']['npcMax'],
            'defaultStatNPCMin'=>$this->data['stat']['npcMin']??$default['stat']['npcMin'],
            'chiefStatMin'=>$this->data['stat']['chiefMin']??$default['stat']['chiefMin'],
        ];

        $this->gameConf = array_merge($stat, $this->data['map']??[], $this->data['const']??[]);

        $this->iconPath = $this->data['iconPath']??$default['iconPath'];

        $this->gameConf['mapName'] = $this->gameConf['mapName']??'che';
        $this->gameConf['unitSet'] = $this->gameConf['unitSet']??'che';

        return $this->gameConf;
    }

    public function __construct(int $scenarioIdx, bool $lazyInit = true){
        $scenarioPath = self::SCENARIO_PATH."/scenario_{$scenarioIdx}.json";

        $this->scenarioIdx = $scenarioIdx;
        $this->scenarioPath = $scenarioPath;

        $data = Json::decode(file_get_contents($scenarioPath));
        $this->data = $data;

        $this->getGameConf();

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

    public function getIconPath(){
        return $this->iconPath;
    }

    public function getNPC(){
        $this->initLite();
        return $this->generals;
    }

    public function getNPCex(){
        $this->initLite();
        return $this->generalsEx;
    }

    public function getNPCneutral(){
        $this->initLite();
        return $this->generalsNeutral;
    }

    public function getNation(){
        $this->initLite();
        return $this->nations;
    }

    public function getMapTheme(){
        return $this->gameConf['mapName'];
    }

    public function getUnitSet(){
        return $this->gameConf['unitSet'];
    }

    public function getScenarioBrief(){
        $this->initLite();

        $nations = [];
        $nationGeneralCnt = [];
        $nationGeneralExCnt = [];
        $nationGeneralNeutralCnt = [];

        foreach($this->generals as $general){
            $nationID = $general->getNationID();
            if(!key_exists($nationID, $nationGeneralCnt)){
                $nationGeneralCnt[$nationID] = 1;
            }
            else{
                $nationGeneralCnt[$nationID] += 1;
            }
        }

        foreach($this->generalsEx as $general){
            $nationID = $general->getNationID();
            if(!key_exists($nationID, $nationGeneralExCnt)){
                $nationGeneralExCnt[$nationID] = 1;
            }
            else{
                $nationGeneralExCnt[$nationID] += 1;
            }
        }

        foreach($this->generalsNeutral as $general){
            $nationID = $general->getNationID();
            if(!key_exists($nationID, $nationGeneralNeutralCnt)){
                $nationGeneralNeutralCnt[$nationID] = 1;
            }
            else{
                $nationGeneralNeutralCnt[$nationID] += 1;
            }
        }

        return [
            'year'=>$this->getYear(),
            'title'=>$this->getTitle(),
            'npc_cnt'=>count($this->getNPC()),
            'npcEx_cnt'=>count($this->getNPCex()),
            'npcNeutral_cnt'=>count($this->getNPCneutral()),
            'nation'=>array_map(function($nation) use ($nationGeneralCnt, $nationGeneralExCnt, $nationGeneralNeutralCnt){
                $brief = $nation->getBrief();
                $brief['generals'] = $nationGeneralCnt[$nation->getID()] ?? 0;
                $brief['generalsEx'] = $nationGeneralExCnt[$nation->getID()] ?? 0;
                $brief['generalsNeutral'] = $nationGeneralNeutralCnt[$nation->getID()] ?? 0;

                return $brief;
            },$this->getNation())
        ];
    }

    private function buildGenerals($env){
        $this->initFull();


        try{
            $text = \file_get_contents(ServConfig::getSharedIconPath('../hook/list.json?1'));
            $storedIcons = Json::decode($text);
        }
        catch(\Throwable $e){
            $storedIcons = [];
        }

        $env['stored_icons'] = $storedIcons;

        $remainGenerals = [];
        foreach($this->generals as $general){
            if($general->build($env)){
                if($general->getNationID()){
                    $this->nations[$general->getNationID()]->addGeneral($general);
                }
                continue;
            }

            $rawGeneral = $this->tmpGeneralQueue[$general->getGeneralRawName()];
            $birth = $general->getBirthYear();
            if(!key_exists($birth, $remainGenerals)){
                $remainGenerals[$birth] = [];
            }
            $remainGenerals[$birth][] = array_merge(['RegNPC'], $rawGeneral);
        }

        if($env['extended_general']){
            foreach($this->generalsEx as $general){
                if($general->build($env)){
                    if($general->getNationID()){
                        $this->nations[$general->getNationID()]->addGeneral($general);
                    }
                    continue;
                }

                $rawGeneral = $this->tmpGeneralQueue[$general->getGeneralRawName()];
                $birth = $general->getBirthYear();
                if(!key_exists($birth, $remainGenerals)){
                    $remainGenerals[$birth] = [];
                }
                $remainGenerals[$birth][] = array_merge(['RegNPC'], $rawGeneral);
            }
        }

        foreach($this->generalsNeutral as $general){
            if($general->build($env)){
                if($general->getGeneralID()){
                    $this->nations[$general->getNationID()]->addGeneral($general);
                }
                continue;
            }

            $rawGeneral = $this->tmpGeneralQueue[$general->getGeneralRawName()];
            $birth = $general->getBirthYear();
            if(!key_exists($birth, $remainGenerals)){
                $remainGenerals[$birth] = [];
            }
            $remainGenerals[$birth][] = array_merge(['RegNeutralNPC'], $rawGeneral);
        }

        return $remainGenerals;
    }

    private function buildDiplomacy($env){
        $this->initFull();

        $monthDiff = ($env['year'] * 12) + ($env['month'] - 1) - ($env['startyear'] * 12);

        $db = DB::db();
        foreach($this->diplomacy as $diplomacy){
            list($me, $you, $state, $remain) = $diplomacy;
            $db->update('diplomacy', [
                'state'=>$state,
                'term'=>$remain - $monthDiff
            ], '(me = %i AND you = %i) OR (me = %i AND you = %i)', $me, $you, $you, $me);
        }
    }

    public function addGameConf(string $key, $value){
        $this->gameConf[$key] = $value;
    }

    public function buildConf(){
        $path = __DIR__.'/../d_setting';

        $mapPath = __DIR__.'/../scenario/map';
        $unitPath = __DIR__.'/../scenario/unit';

        $mapName = $this->gameConf['mapName'];
        $unitSet = $this->gameConf['unitSet'];


        if(!file_exists("$mapPath/$mapName.php")){
            throw new \RuntimeException('맵 파일이 올바르게 지정되지 않음! : '.$mapName);
        }
        if(!file_exists("$unitPath/$unitSet.php")){
            throw new \RuntimeException('유닛 파일이 올바르게 지정되지 않음! : '.$unitSet);
        }

        Util::generatePHPClassFile($path.'/GameConst.php', $this->gameConf, 'GameConstBase', 'sammo');

        copy("$mapPath/$mapName.php", $path.'/CityConst.php');
        copy("$unitPath/$unitSet.php", $path.'/GameUnitConst.php');
    }

    public function build($env){

        $db = DB::db();
        getGeneralPoolClass(GameConst::$targetGeneralPool)::initPool($db);
        $this->initFull();

        //NOTE: 초기화가 되어있다고 가정함.

        /*
        env로 사용된 것들,
        게임 변수 : year, month
        gameStor 변수 : startyear, year, month, genius, turnterm, show_img_level, extended_general, fiction, npcmode
        install 변수 : npcmode, show_img_level, extended_general, scenario, fiction

        event변수 : currentEventID
        */

        foreach($this->nations as $id=>$nation){
            if($id == 0){
                continue;
            }

            $nation->build($env);
        }

        refreshNationStaticInfo();
        CityHelper::flushCache();

        $remainGenerals = $this->buildGenerals($env);

        foreach($remainGenerals as $birth=>$actions){
            $targetYear = $birth + \sammo\GameConst::$adultAge;

            $actions[] = ['DeleteEvent'];
            $this->events[] = [
                'target'=>'Month',
                'priority'=>1000,
                'cond'=>['Date', '>=', $targetYear, '1'],
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
                'target'=>$rawEvent['target'],
                'priority'=>$rawEvent['priority'],
                'condition'=>Json::encode($rawEvent['cond']),
                'action'=>Json::encode($rawEvent['action'])
            ];
        }, $this->events);

        if(count($events) > 0){
            $db->insert('event', $events);
        }



        pushGlobalHistoryLog($this->history, $env['year'], $env['month']);

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