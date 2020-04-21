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
    public $reqNationRice = 10000;
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
    public $reqHumanWarUrgentGold = 10000;
    public $reqHumanWarUrgentRice = 10000;
    public $reqHumanWarRecommandGold = 30000;
    public $reqHumanWarRecommandRice = 30000;
    public $reqHumanDevelGold = 10000;
    public $reqHumanDevelRice = 10000;
    public $reqNPCWarGold = 5000;
    public $reqNPCWarRice = 5000;
    public $reqNPCDevelGold = 1000;
    public $reqNPCDevelRice = 500;

    public $minimumResourceActionAmount = 1000;

    public $minNPCWarLeadership = 40;
    public $minWarCrew = 1500;

    public $allowNpcAttackCity = true;
    public $minNPCRecruitCityPopulation = 50000;
    public $safeRecruitCityPopulationRatio = 0.5;
    public $properWarTrainAtmos = 90;


    function __construct(General $general, array $nationPolicy, array $serverPolicy)
    {
        foreach($serverPolicy as $policy=>$value){
            if(!property_exists($this, $policy)){
                throw new \InvalidArgumentException($policy);
            }
            $this->$policy = $value;
        }

        foreach($nationPolicy as $policy){
            if(!property_exists($this, $policy)){
                throw new \InvalidArgumentException($policy);
            }
            $this->$policy = $value;
        }

        if(!$this->priority){
            $this->priority = $this::$defaultPriority;
        }
    }
}
