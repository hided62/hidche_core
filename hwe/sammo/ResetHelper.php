<?php
namespace sammo;

class ResetHelper{
    private function __construct(){

    }

    static public function clearDB(){
        $servRoot = realpath(__dir__.'/../');

        if(!file_exists($servRoot.'/logs') || !file_exists($servRoot.'/data')){
            if(!is_writable($servRoot)){
                return [
                    'result'=>false,
                    'reason'=>'logs, data 디렉토리를 생성할 권한이 없습니다.'
                ];
            }
            mkdir($servRoot.'/logs', 0755);
            mkdir($servRoot.'/data', 0755);
        }

        if(!is_writable($servRoot.'/logs')){
            return [
                'result'=>false,
                'reason'=>'logs 디렉토리의 쓰기 권한이 없습니다'
            ];
        }

        if(!is_writable($servRoot.'/data')){
            return [
                'result'=>false,
                'reason'=>'data 디렉토리의 쓰기 권한이 없습니다'
            ];
        }

        if(!is_writable($servRoot.'/d_setting')){
            return [
                'result'=>false,
                'reason'=>'d_setting 디렉토리의 쓰기 권한이 없습니다'
            ];
        }

        if(!file_exists($servRoot.'/logs/preserved')){
            mkdir($servRoot.'/logs/preserved', 0755);
        }

        if(!file_exists($servRoot.'/logs/.htaccess')){
            @file_put_contents($servRoot.'/logs/.htaccess', 'Deny from  all');
        }

        if(!file_exists($servRoot.'/data/.htaccess')){
            @file_put_contents($servRoot.'/data/.htaccess', 'Deny from  all');
        }

        $dir = new \DirectoryIterator($servRoot.'/logs');
        foreach ($dir as $fileinfo) {
            /** @var \DirectoryIterator $fileinfo */
            if (!$fileinfo->isDir() || $fileinfo->isDot()) {
                continue;
            }
            $basename = $fileinfo->getFilename();
            if($basename == 'preserved'){
                continue;
            }
            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                exec("move ".escapeshellarg($servRoot.'/logs/'.$basename)." ".escapeshellarg($servRoot.'/logs/preserved/'.$basename));
            } else {
                exec("mv ".escapeshellarg($servRoot.'/logs/'.$basename)." ".escapeshellarg($servRoot.'/logs/preserved/'.$basename));
            }
        }

        $prefix = DB::prefix();
        AppConf::getList()[$prefix]->closeServer();

        $db = DB::db();
        $mysqli_obj = $db->get();

        $serverID = DB::prefix().'_'.date("ymd").'_'.Util::randomStr(4);

        mkdir($servRoot.'/logs/'.$serverID, 0755);
        mkdir($servRoot.'/data/'.$serverID, 0755);

        $result = Util::generateFileUsingSimpleTemplate(
            $servRoot.'/d_setting/UniqueConst.orig.php',
            $servRoot.'/d_setting/UniqueConst.php',[
                'serverID'=>$serverID,
                'serverName'=>AppConf::getList()[$prefix]->getKorName(),
            ], true
        );

        if($mysqli_obj->multi_query(file_get_contents($servRoot.'/sql/reset.sql'))){
            while(true){
                if (!$mysqli_obj->more_results()) {
                    break;
                }
                if(!$mysqli_obj->next_result()){
                    break;
                }
            }
            
        }

        if($mysqli_obj->multi_query(file_get_contents($servRoot.'/sql/schema.sql'))){
            while(true){
                if (!$mysqli_obj->more_results()) {
                    break;
                }
                if(!$mysqli_obj->next_result()){
                    break;
                }
            }
        }

        (KVStorage::getStorage($db, 'game_env'))->resetValues();

        return [
            'result'=>true,
            'serverID'=>$serverID
        ];
    }

    static public function buildScenario(
        int $turnterm,
        int $sync,
        int $scenario,
        int $fiction,
        int $extend,
        int $npcmode,
        int $show_img_level,
        int $tournament_trig,
        string $join_mode,
        string $turntime
    ):array{
        //FIXME: 분리할 것
        if(120 % $turnterm != 0){
            return [
                'result'=>false,
                'reason'=>'turnterm은 120의 약수여야 합니다.'
            ];
        }

        if($tournament_trig < 0 || $tournament_trig > 7){
            return [
                'result'=>false,
                'reason'=>'올바르지 않은 토너먼트 주기입니다.'
            ];
        }

        $clearResult = self::clearDB();
        if(!$clearResult['result']){
            return $clearResult;
        }

        $serverID = $clearResult['serverID'];
        
        $scenarioObj = new Scenario($scenario, false);
        $scenarioObj->buildConf();

        $startyear = $scenarioObj->getYear()??GameConst::$defaultStartYear;

        $db = DB::db();
        $gameStor = KVStorage::getStorage($db, 'game_env');
        $db->insert('plock', [
            'plock'=>1
        ]);

        $prevWinner = $db->queryFirstField('SELECT l12name FROM emperior ORDER BY `no` DESC LIMIT 1');

        CityConst::build();
        $cityPositions = [];
        foreach(CityConst::all() as $city){
            $cityPositions[$city->id] = [
                $city->name,
                $city->posX,
                $city->posY
            ];
        }
        Util::generateFileUsingSimpleTemplate(
            __dir__.'/../templates/base_map.orig.js',
            __dir__.'/../d_shared/base_map.js',
            [
                'cityPosition'=>Json::encode($cityPositions),
                'regionMap'=>Json::encode(CityConst::$regionMap),
                'levelMap'=>Json::encode(CityConst::$levelMap),
            ],
            true
        );

        $time = substr($turntime, 11, 2);
        if($sync == 0) {
            // 현재 시간을 1월로 맞춤
            $starttime = cutTurn($turntime, $turnterm);
            $month = 1;
            $year = $startyear;
        } else {
            // 현재 시간과 동기화
            [$starttime, $yearPulled, $month] = cutDay($turntime, $turnterm);
            if($yearPulled){
                $year = $startyear-1;
            }
            else{
                $year = $startyear;
            }
        }

        $killturn = 4800 / $turnterm;
        if($npcmode == 1) { $killturn = intdiv($killturn, 3); }

        $develcost = ($year - $startyear + 10) * 2;

        $env = [
            'scenario'=>$scenario,
            'scenario_text'=>$scenarioObj->getTitle(),
            'icon_path'=>$scenarioObj->getIconPath(),
            'startyear'=>$startyear,
            'year'=> $year,
            'month'=> $month,
            'init_year'=> $year,
            'init_month'=>$month,
            'map_theme' => $scenarioObj->getMapTheme(),
            'msg'=>'공지사항',//TODO:공지사항
            'maxgeneral'=>GameConst::$defaultMaxGeneral,
            'maxnation'=>GameConst::$defaultMaxNation,
            'conlimit'=>300,
            'gold_rate'=>100,
            'rice_rate'=>100,
            'develcost'=>$develcost,
            'turntime'=>$turntime,
            'starttime'=>$starttime,
            'opentime'=>$turntime,
            'turnterm'=>$turnterm,
            'killturn'=>$killturn,
            'genius'=>GameConst::$defaultMaxGenius,
            'show_img_level'=>$show_img_level,
            'join_mode'=>$join_mode,
            'npcmode'=>$npcmode,
            'extended_general'=>$extend,
            'fiction'=>$fiction,
            'tnmt_trig'=>$tournament_trig,
            'prev_winner'=>$prevWinner
        ];

        foreach(RootDB::db()->query('SELECT `no`, `name`, `picture`, `imgsvr` FROM member WHERE grade >= 5') as $admin){
            $db->insert('general', [
                'owner'=>$admin['no'],
                'name'=>$admin['name'],
                'picture'=>$admin['picture'],
                'imgsvr'=>$admin['imgsvr'],
                'turntime'=>$turntime,
                'killturn'=>9999,
                'crewtype'=>GameUnitConst::DEFAULT_CREWTYPE
            ]);
        }

        foreach($env as $key=>$value){
            $gameStor->$key = $value;
        }

        $db->insert('ng_games', [
            'server_id'=>$serverID,
            'date'=>$turntime,
            'winner_nation'=>null,
            'map'=>$scenarioObj->getMapTheme(),
            'scenario'=>$scenario,
            'scenario_name'=>$scenarioObj->getTitle(),
            'env'=>Json::encode($env)
        ]);  

        $scenarioObj->build($env);

        $db->update('plock', [
            'plock'=>0
        ], true);

        LogHistory(1);

        $prefix = DB::prefix();
        AppConf::getList()[$prefix]->closeServer();

        return [
            'result'=>true
        ];
    }
}