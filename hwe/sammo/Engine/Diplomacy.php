<?php
namespace sammo\Engine;

use \sammo\DiplomacticMessage;

//NOTE: A가 B에게 항복, 통합 서신을 보냈을 때 통합 후 대상이 A이므로 A가 주체임.
class Diplomacy{

    protected $srcNation = null;//TODO: 클래스로 변경
    protected $destNation = null;

    protected $srcToDestDiplomacy = null;
    protected $destToSrcDiplomacy = null;
    public $valid = false;

    public $startYear = 0;
    public $year = 0;
    public $month = 0;

    public function __construct(int $srcNationID, int $destNationID){
        if($srcNationID === $destNationID){
            return;
        }

        $db = DB::db();
        $srcNation = $db->queryFirstRow(
            'SELECT nation, `name`, capital, gold, rice, surlimit, color, `level` FROM nation WHERE nation=%i',
            $srcNationID
        );

        $destNation = $db->queryFirstRow(
            'SELECT nation, `name`, capital, gold, rice, surlimit, color, `level` FROM nation WHERE nation=%i',
            $destNationID
        );

        if(!$srcNation || !$destNation){
            return;
        }

        $this->srcToDestDiplomacy = $db->queryFirstRow(
            'SELECT * FROM diplomacy WHERE me = %i AND you = %i',
            $srcNationID,
            $destNationID
        );

        $this->destToSrcDiplomacy = $db->queryFirstRow(
            'SELECT * FROM diplomacy WHERE me = %i AND you = %i',
            $destNationID,
            $srcNationID
        );

        $this->srcNation = $srcNation;
        $this->destNation = $destNation;
        $this->valid = true;

        list(
            $this->startYear,
            $this->year, 
            $this->month
        ) = $db->queryFirstList('SELECT startyear, year, month FROM game LIMIT 1');

    }

    //방랑군이 아니어야함
    //상대도 방랑군이 아니어야함
    
    //불가침시 : 교전중이 아니어야함, 선포중이 아니어야함. (서로) 합병중이 아니어얗.
    //불가침 파기시 : 불가침 중이어야함.
    //종전시 : 교전중이거나 선포중이어야함.
    //합병시 : 양국 다 외교제한이 지나지 않았어야함. 국력, 장수수가 적절해야함. 인접한 국가여야함. 서로 교전중이어선 안됨.
    //        송신자가 선포, 전쟁중이어선 안됨. 송신자가 C국과 불가침인데 수신자가 C국과 전쟁중이면 안됨
    //항복시 :  양국 다 외교제한이 지나지 않았어야함. 국력, 장수수가 적절해야함. 인접한 국가여야함. 서로 교전중이어선 안됨.
    //        송신자가 선포, 전쟁중이어선 안됨. 송신자가 C국과 불가침인데 수신자가 C국과 전쟁중이면 안됨

    protected function checkValidNation(array $prev = [DiplomacticMessage::ACCEPTED, '']){
        if($prev[0] !== DiplomacticMessage::ACCEPTED){
            return $prev;
        }

        if(!$this->srcNation || !$this->destNation || !$this->valid){
            return [ScoutMessage::DECLINED, '이미 멸망한 국가입니다.'];
        }

        if($this->srcNation['level'] == 0 || $this->destNation['level'] == 0){
            return [DiplomacticMessage::DECLINED, '방랑군과 외교룰 수행할 수 없습니다.'];
        }

        return $prev;
    }

    protected function checkNotWar(array $prev = [DiplomacticMessage::ACCEPTED, '']){
        if($prev[0] !== DiplomacticMessage::ACCEPTED){
            return $prev;
        }

        if($this->srcToDestDiplomacy['state'] == 0 || $this->srcToDestDiplomacy['state'] == 0
            ||$this->destToSrcDiplomacy['state'] == 1 || $this->destToSrcDiplomacy['state'] == 1){
            return [DiplomacticMessage::DECLINED, '상대국과 선포, 전쟁 중입니다.'];
        }

        return $prev;
    }

    protected function checkInWar(array $prev = [DiplomacticMessage::ACCEPTED, '']){
        if($prev[0] !== DiplomacticMessage::ACCEPTED){
            return $prev;
        }

        if($this->srcToDestDiplomacy['state'] == 0 || $this->srcToDestDiplomacy['state'] == 0
            ||$this->destToSrcDiplomacy['state'] == 1 || $this->destToSrcDiplomacy['state'] == 1){
            return $prev;
        }

        return [DiplomacticMessage::DECLINED, '상대국과 선포, 전쟁 중이 아닙니다.'];
    }

    protected function checkDiplomacyLimit(array $prev = [DiplomacticMessage::ACCEPTED, '']){
        if($prev[0] !== DiplomacticMessage::ACCEPTED){
            return $prev;
        }

        if($this->destNation['surlimit'] > 0){
            return [DiplomacticMessage::DECLINED, '본국의 외교 기한이 남아있습니다.'];
        }

        if($this->srcNation['surlimit'] > 0){
            return [DiplomacticMessage::DECLINED, '상대국의 외교 기한이 남아있습니다.'];
        }

        return $prev;
    }

    protected function checkStrictlyAdjacent(array $prev = [DiplomacticMessage::ACCEPTED, '']){
        if($prev[0] !== DiplomacticMessage::ACCEPTED){
            return $prev;
        }

        if(!\sammo\isClose($this->srcNation['nation'], $this->destNation['nation'], false)){
            return [DiplomacticMessage::DECLINED, '상대국의 도시들과 보급선이 이어지지 않았습니다.'];
        }

        return $prev;
    }

    protected function checkAdjacent(array $prev = [DiplomacticMessage::ACCEPTED, '']){
        if($prev[0] !== DiplomacticMessage::ACCEPTED){
            return $prev;
        }

        if(!\sammo\isClose($this->srcNation['nation'], $this->destNation['nation'], true)){
            return [DiplomacticMessage::DECLINED, '상대국의 도시와 인접하지 않았습니다.'];
        }

        return $prev;
    }

    protected function checkAlreadyMerging(array $prev = [DiplomacticMessage::ACCEPTED, '']){
        if($prev[0] !== DiplomacticMessage::ACCEPTED){
            return $prev;
        }

        if((3 <= $this->srcToDestDiplomacy['state'] && $this->srcToDestDiplomacy['state'] <= 6)
            ||(3 <= $this->destToSrcDiplomacy['state'] && $this->destToSrcDiplomacy['state'] <= 6)){
            return [DiplomacticMessage::DECLINED, '상대국과 합병 중입니다.'];
        }

        return $prev;
    }

    protected function checkSrcNationHasNeutralDiplomacy(array $prev = [DiplomacticMessage::ACCEPTED, '']){
        if($prev[0] !== DiplomacticMessage::ACCEPTED){
            return $prev;
        }

        if(false){
            return [DiplomacticMessage::DECLINED, '상대국이 전쟁 중입니다.'];
        }

        if(false){
            return [DiplomacticMessage::DECLINED, '상대국이 합병 중입니다.'];
        }

        return $prev;
    }

    protected function checkContradictoryDiplomacy(array $prev = [DiplomacticMessage::ACCEPTED, '']){
        if($prev[0] !== DiplomacticMessage::ACCEPTED){
            return $prev;
        }

        if(false){
            return [DiplomacticMessage::DECLINED, '상대국이 본국의 교전국과 불가침중입니다.'];
        }

        return $prev;
    }

    public function noAggression(int $destNation){

    }
    
    public function cancelNA(int $destNation){
        
    }

    public function stopWar(int $destNation){
        
    }

    public function acceptMerge(int $destNation){
        
    }

    public function acceptSurrender(int $destNation){
        
    }
}