<?php
namespace sammo;

class AutorunGeneralPolicy{

    // 일반장 행동
    static $일반내정 = '일반내정';
    static $긴급내정 = '긴급내정'; //민심 ~50
    static $전쟁내정 = '전쟁내정'; //인구 ~50%, 민심 ~90

    static $금쌀구매 = '금쌀구매';
    static $상인무시 = '상인무시';

    static $징병 = '징병';
    static $모병 = '모병';
    static $한계징병 = '한계징병';
    static $고급병종 = '고급병종';
    static $전투준비 = '전투준비';
    static $소집해제 = '소집해제';

    static $출병 = '출병';
    
    //static $NPC증여 = 'NPC증여';
    static $NPC헌납 = 'NPC헌납';
    static $NPC사망대비 = 'NPC사망대비';

    static $후방워프 = '후방워프';
    static $전방워프 = '전방워프';
    static $내정워프 = '내정워프';

    static $귀환 = '귀환';
    //static $전투이동 = '전투이동';
    //static $내정이동 = '내정이동';
    
    static $국가선택 = '국가선택';
    static $집합 = '집합';
    static $건국 = '건국';
    static $선양 = '선양';
    


    static public $default_priority = [
        'NPC사망대비',
        '귀환',
        '금쌀구매',
        '출병',
        '긴급내정',
        '전투준비',
        '전방워프',
        //'NPC증여',
        'NPC헌납',
        '징병',
        '후방워프',
        '전쟁내정',
        '소집해제',
        '일반내정',
        '내정워프'
    ];


    public $canNPC사망대비 = true;
    public $can일반내정 = true;
    public $can긴급내정 = true;
    public $can전쟁내정 = true;

    public $can금쌀구매 = true;
    public $can상인무시 = true;

    public $can징병 = true;
    public $can모병 = false;
    public $can한계징병 = false;
    public $can고급병종 = false;
    public $can전투준비 = true;
    public $can소집해제 = true;

    public $can출병 = true;
    
    //public $canNPC증여 = true;
    public $canNPC헌납 = true;

    public $can후방워프 = true;
    public $can전방워프 = true;
    public $can내정워프 = true;

    public $can귀환 = true;

    public $can국가선택 = true;
    public $can집합 = false;
    public $can건국 = true;
    public $can선양 = false;

    function doNPCState(General $general){
        $npc = $general->getNPCType();
        $nationID = $general->getNationID();

        if($npc==5){
            $this->can집합 = true;
            $this->can선양 = true;
            $this->can집합 = true;
            $this->can국가선택 = false;
            return;
        }

        if($npc==1){
            $this->canNPC사망대비 = false;
        }

        if($nationID != 0){
            $this->can국가선택 = false;
            $this->can건국 = false;
        }


    }

    function __construct(General $general, $aiOptions, ?array $nationPolicy, ?array $serverPolicy, array $nation, array $env){
        $this->priority = static::$default_priority;

        if($serverPolicy && key_exists('priority', $serverPolicy)){
            $priority = [];
            foreach($serverPolicy['priority'] as $priorityItem){
                if(!property_exists($this, $priorityItem)){
                    trigger_error ("{$priorityItem}이 없음", E_USER_NOTICE );
                    continue;
                }
                $priority[] = $priorityItem;
            }
            if($priority){
                $this->priority = $priority;
            }
        }

        if($nationPolicy && key_exists('priority', $nationPolicy)){
            $priority = [];
            foreach($nationPolicy['priority'] as $priorityItem){
                if(!property_exists($this, $priorityItem)){
                    trigger_error ("{$priorityItem}이 없음", E_USER_NOTICE );
                    continue;
                }
                $priority[] = $priorityItem;
            }
            if($priority){
                $this->priority = $priority;
            }
        }

        if($general->getNPCType() >= 2){
            $this->doNPCState($general);
            return;
        }

        $this->can일반내정 = false;
        $this->can긴급내정 = false;
        $this->can전쟁내정 = false;

        $this->can금쌀구매 = false;
        $this->can상인무시 = false;

        $this->can징병 = false;
        $this->can모병 = false;
        $this->can한계징병 = true;
        $this->can고급병종 = true;
        $this->can전투준비 = false;

        $this->can출병 = false;
        
        //$this->canNPC증여 = false;
        $this->canNPC헌납 = false;

        $this->can후방워프 = false;
        $this->can전방워프 = false;
        $this->can내정워프 = false;

        $this->can국가선택 = false;
        $this->can집합 = false;
        $this->can건국 = false;
        $this->can선양 = false;

        foreach($aiOptions as $key=>$value){
            assert($value);
            switch($key){
            case 'develop':
                $this->can일반내정 = true;
                //유저장은 '긴급'을 하지 않음
                $this->can전쟁내정 = true;
                $this->can금쌀구매 = true;
                break;
            case 'warp':
                $this->can후방워프 = true;
                $this->can전방워프 = true;
                $this->can내정워프 = true;
                $this->can금쌀구매 = true;
                $this->can상인무시 = true;
                break;
            case 'recruit_high': 
                $this->can모병 = true;
            case 'recruit': 
                $this->can징병 = true;
                $this->can소집해제 = true;
                $this->can금쌀구매 = true;
                break;
            case 'train':
                $this->can전투준비 = true;
                $this->can금쌀구매 = true;
                break;
            case 'battle':
                $this->can출병 = true;
                $this->can금쌀구매 = true;
                break;
            }
        }
    }
}