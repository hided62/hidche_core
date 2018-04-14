<?php
namespace sammo\Engine;

//NOTE: A가 B에게 항복, 통합 서신을 보냈을 때 통합 후 대상이 A이므로 A가 주체임.
class Diplomacy{

    protected $nation = null;//TODO: 상속체로 변경.
    public $valid = false;

    public $startYear = 0;
    public $year = 0;
    public $month = 0;

    public function __construct(int $nationID){
        $db = DB::db();
        $nation = $db->queryFirstRow(
            'SELECT nation, `name`, capital, gold, rice, surlimit, color FROM nation WHERE nation=%i',
            $nationID
        );

        if(!$nation){
            return;
        }

        $this->nation = $nation;
        $this->valid = true;

        list(
            $this->startYear,
            $this->year, 
            $this->month
        ) = $db->queryFirstList('SELECT startyear, year, month FROM game LIMIT 1');

    }

    public function ally(int $destNation){

    }
    
    public function cancelAlly(int $destNation){
        
    }

    public function stopWar(int $destNation){
        
    }

    public function acceptMerge(int $destNation){
        
    }

    public function acceptSurrender(int $destNation){
        
    }
}