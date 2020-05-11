<?php
namespace sammo;

class AutorunNationPolicy {

    // 수뇌 행동
    static $부대전방발령 = '부대전방발령';
    static $부대후방발령 = '부대후방발령';

    static $부대유저장후방발령 = '부대유저장후방발령';
    static $유저장후방발령 = '유저장후방발령';
    static $유저장전방발령 = '유저장전방발령';
    static $유저장구출발령 = '유저장구출발령';
    static $유저장내정발령 = '유저장내정발령';

    static $NPC후방발령 = 'NPC후방발령';
    static $NPC전방발령 = 'NPC전방발령';
    static $NPC구출발령 = 'NPC구출발령';
    static $NPC내정발령 = 'NPC내정발령';

    static $유저장긴급포상 = '유저장긴급포상';
    static $유저장포상 = '유저장포상';
    //static $유저장몰수 = '유저장몰수';

    static $NPC긴급포상 = 'NPC긴급포상';
    static $NPC포상 = 'NPC포상';
    static $NPC몰수 = 'NPC몰수';

    // 군주 행동
    static $선전포고 = '선전포고';
    static $천도 = '천도';

    

    //실제 행동
    static public $defaultPriority = [
        '선전포고',
        '천도',

        '유저장긴급포상',
        '부대전방발령',
        '유저장구출발령',

        '유저장후방발령',
        '부대유저장후방발령',

        '유저장전방발령',
        '유저장포상',

        //'유저장몰수',
        '부대후방발령',

        'NPC긴급포상',
        'NPC구출발령',
        'NPC후방발령',

        'NPC포상',

        'NPC전방발령',

        '유저장내정발령',
        'NPC내정발령',
        'NPC몰수',
    ];

    //순서는 중요하지 않음
    static public $availableInstantTurn = [
        '유저장긴급포상'=>true,
        '유저장구출발령'=>true,
        '유저장후방발령'=>true,
        '유저장전방발령'=>true,
        '유저장내정발령'=>true,
        '유저장포상'=>true,
        'NPC긴급포상'=>true,
        'NPC구출발령'=>true,
        'NPC후방발령'=>true,
        'NPC내정발령'=>true,
        'NPC포상'=>true,
        'NPC전방발령'=>true,
    ];

    public $priority = [];

    public $can부대전방발령 = true;
    public $can부대후방발령 = true;

    public $can부대유저장후방발령 = true;
    public $can유저장후방발령 = true;
    public $can유저장전방발령 = true;
    public $can유저장구출발령 = true;
    public $can유저장내정발령 = true;

    public $canNPC후방발령 = true;
    public $canNPC전방발령 = true;
    public $canNPC구출발령 = true;
    public $canNPC내정발령 = true;

    public $can유저장긴급포상 = true;
    public $can유저장포상 = true;
    //public $can유저장몰수 = true;

    public $canNPC긴급포상 = true;
    public $canNPC포상 = true;
    public $canNPC몰수 = true;

    public $can선전포고 = true;
    public $can천도 = true;

    //Policy Variables
    public $reqNationGold = 10000;
    public $reqNationRice = 12000;
    public $CombatForce = [
        //200 => [10, 24],//troopLeader, fromCity, toCity
        //242 => [10, 24]
    ];
    public $SupportForce = [
        //211=>true
    ];
    public $DevelopForce = [
        //123=>true
    ];
    public $reqHumanWarUrgentGold = 0;
    public $reqHumanWarUrgentRice = 0;
    public $reqHumanWarRecommandGold = 0;
    public $reqHumanWarRecommandRice = 0;
    public $reqHumanDevelGold = 10000;
    public $reqHumanDevelRice = 10000;
    public $reqNPCWarGold = 0;
    public $reqNPCWarRice = 0;
    public $reqNPCDevelGold = 0;
    public $reqNPCDevelRice = 500;

    public $minimumResourceActionAmount = 1000;

    public $minNPCWarLeadership = 40;
    public $minWarCrew = 1500;

    public $allowNpcAttackCity = true;
    public $minNPCRecruitCityPopulation = 50000;
    public $safeRecruitCityPopulationRatio = 0.5;
    public $properWarTrainAtmos = 90;

    ///이쪽의 값이 실제 초기화 값임
    public static $defaultPolicy = [
        'reqNationGold'=>10000,
        'reqNationRice'=>12000,
        'CombatForce'=>[],
        'SupportForce'=>[],
        'DevelopForce'=>[],
        'reqHumanWarUrgentGold'=>0,
        'reqHumanWarUrgentRice'=>0,
        'reqHumanWarRecommandGold'=>0,
        'reqHumanWarRecommandRice'=>0,
        'reqHumanDevelGold'=>10000,
        'reqHumanDevelRice'=>10000,
        'reqNPCWarGold'=>0,
        'reqNPCWarRice'=>0,
        'reqNPCDevelGold'=>0,
        'reqNPCDevelRice'=>500,
    
        'minimumResourceActionAmount'=>1000,
    
        'minNPCWarLeadership'=>40,
        'minWarCrew'=>1500,
    
        'allowNpcAttackCity'=>true,
        'minNPCRecruitCityPopulation'=>50000,
        'safeRecruitCityPopulationRatio'=>0.5,
        'properWarTrainAtmos'=>90,
    ];

    function __construct(General $general, $aiOptions, ?array $nationPolicy, ?array $serverPolicy, array $nation, array $env)
    {
        foreach(static::$defaultPolicy as $policy=>$value){
            $this->{$policy} = $value;
        }
        
        if($serverPolicy){
            foreach($serverPolicy['values']??[] as $policy=>$value){
                if(!property_exists($this, $policy)){
                    continue;
                }
                $this->$policy = $value;
            }

            if(key_exists('priority', $serverPolicy)){
                $this->priority = $serverPolicy['priority'];
            }
        }
        

        if($nationPolicy){
            foreach($nationPolicy['values']??[] as $policy){
                if(!property_exists($this, $policy)){
                    continue;
                }
                $this->$policy = $value;
            }

            if(key_exists('priority', $nationPolicy)){
                $this->priority = $nationPolicy['priority'];
            }
        }
        

        if(!$this->priority){
            $this->priority = $this::$defaultPriority;
        }


        if($this->reqNPCDevelGold === 0){
            $this->reqNPCDevelGold = $env['develcost'] * 30;
        }


        if($this->reqNPCWarGold === 0 || $this->reqNPCWarRice === 0){
            $defaultCrewType = GameUnitConst::byID(GameUnitConst::DEFAULT_CREWTYPE);
            $reqGold = $defaultCrewType->costWithTech($nation['tech'], GameConst::$defaultStatNPCMax * 100);
            $reqRice = $defaultCrewType->riceWithTech($nation['tech'], GameConst::$defaultStatNPCMax * 100);
            if($this->reqNPCWarGold === 0){
                $this->reqNPCWarGold = Util::round($reqGold * 4, -2);
            }
            if($this->reqNPCWarRice === 0){
                $this->reqNPCWarRice = Util::round($reqRice * 4, -2);
            }
        }

        if($this->reqHumanWarUrgentGold === 0 || $this->reqHumanWarUrgentRice === 0){
            $defaultCrewType = GameUnitConst::byID(GameUnitConst::DEFAULT_CREWTYPE);
            $reqGold = $defaultCrewType->costWithTech($nation['tech'], GameConst::$defaultStatMax * 100);
            $reqRice = $defaultCrewType->riceWithTech($nation['tech'], GameConst::$defaultStatMax * 100);
            if($this->reqHumanWarUrgentGold === 0){
                $this->reqHumanWarUrgentGold = Util::round(max(7000, $reqGold * 3 * 2), -2);
            }
            if($this->reqHumanWarUrgentRice === 0){
                $this->reqHumanWarUrgentRice = Util::round(max(7000, $reqRice * 3 * 2), -2);
            }
        }

        if($this->reqHumanWarRecommandGold === 0){
            $this->reqHumanWarRecommandGold = Util::round(max(20000, $this->reqHumanWarUrgentGold * 3), -2);
        }
        if($this->reqHumanWarRecommandRice === 0){
            $this->reqHumanWarRecommandRice = Util::round(max(20000, $this->reqHumanWarRecommandRice * 3), -2);
        }

        if($general->getNPCType() >= 2){
            return;
        }

        if(!key_exists('chief', $aiOptions)){
            $this->can부대전방발령 = false;
            $this->can부대후방발령 = false;
    
            $this->can부대유저장후방발령 = false;
            $this->can유저장후방발령 = false;
            $this->can유저장전방발령 = false;
            $this->can유저장구출발령 = false;
            $this->can유저장내정발령 = false;
    
            $this->canNPC후방발령 = false;
            $this->canNPC전방발령 = false;
            $this->canNPC구출발령 = false;
            $this->canNPC내정발령 = false;
    
            $this->can유저장긴급포상 = false;
            $this->can유저장포상 = false;
            //$this->can유저장몰수 = false;
    
            $this->canNPC긴급포상 = false;
            $this->canNPC포상 = false;
            $this->canNPC몰수 = false;
    
            $this->can선전포고 = false;
            $this->can천도 = false;
        }
    }
}
