<?php
namespace sammo\Engine;

use \sammo\DB;
use \sammo\DiplomaticMessage;
use \sammo\KVStorage;
use \sammo\Json;
use \sammo\GameConst;

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
        $gameStor = KVStorage::getStorage($db, 'game_env');
        $srcNation = $db->queryFirstRow(
            'SELECT nation, `name`, `power`, capital, gold, rice, surlimit, color, `level` FROM nation WHERE nation=%i',
            $srcNationID
        );

        $destNation = $db->queryFirstRow(
            'SELECT nation, `name`, `power`, capital, gold, rice, surlimit, color, `level` FROM nation WHERE nation=%i',
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
        ) = $gameStor->getValuesAsArray(['startyear', 'year', 'month']);

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

    protected function checkValidNation(array $prev = [DiplomaticMessage::ACCEPTED, '']){
        if($prev[0] !== DiplomaticMessage::ACCEPTED){
            return $prev;
        }

        if(!$this->srcNation || !$this->destNation || !$this->valid){
            return [DiplomaticMessage::DECLINED, '올바르지 않은 국가입니다.'];
        }

        if($this->srcNation['level'] == 0 || $this->destNation['level'] == 0){
            return [DiplomaticMessage::DECLINED, '방랑군과 외교룰 수행할 수 없습니다.'];
        }

        return $prev;
    }

    protected function checkNotWar(array $prev = [DiplomaticMessage::ACCEPTED, '']){
        if($prev[0] !== DiplomaticMessage::ACCEPTED){
            return $prev;
        }

        if($this->srcToDestDiplomacy['state'] == 0 || $this->srcToDestDiplomacy['state'] == 0
            ||$this->destToSrcDiplomacy['state'] == 1 || $this->destToSrcDiplomacy['state'] == 1){
            return [DiplomaticMessage::DECLINED, '상대국과 선포, 전쟁 중입니다.'];
        }

        return $prev;
    }

    protected function checkNonAggressionTreaty(array $prev = [DiplomaticMessage::ACCEPTED, '']){
        if($prev[0] !== DiplomaticMessage::ACCEPTED){
            return $prev;
        }

        if($this->srcToDestDiplomacy['state'] !== 7 || $this->destToSrcDiplomacy['state'] !== 7){
            return [DiplomaticMessage::DECLINED, '상대국과 불가침 중이 아닙니다.'];
        }

        return $prev;
    }

    protected function checkInWar(array $prev = [DiplomaticMessage::ACCEPTED, '']){
        if($prev[0] !== DiplomaticMessage::ACCEPTED){
            return $prev;
        }

        if($this->srcToDestDiplomacy['state'] == 0 || $this->srcToDestDiplomacy['state'] == 0
            ||$this->destToSrcDiplomacy['state'] == 1 || $this->destToSrcDiplomacy['state'] == 1){
            return $prev;
        }

        return [DiplomaticMessage::DECLINED, '상대국과 선포, 전쟁 중이 아닙니다.'];
    }

    protected function checkDiplomacyLimit(array $prev = [DiplomaticMessage::ACCEPTED, '']){
        if($prev[0] !== DiplomaticMessage::ACCEPTED){
            return $prev;
        }

        if($this->destNation['surlimit'] > 0){
            return [DiplomaticMessage::DECLINED, '본국의 외교 기한이 남아있습니다.'];
        }

        if($this->srcNation['surlimit'] > 0){
            return [DiplomaticMessage::DECLINED, '상대국의 외교 기한이 남아있습니다.'];
        }

        return $prev;
    }

    protected function checkStrictlyAdjacent(array $prev = [DiplomaticMessage::ACCEPTED, '']){
        if($prev[0] !== DiplomaticMessage::ACCEPTED){
            return $prev;
        }

        if(!\sammo\isNeighbor($this->srcNation['nation'], $this->destNation['nation'], false)){
            return [DiplomaticMessage::DECLINED, '상대국의 도시들과 보급선이 이어지지 않았습니다.'];
        }

        return $prev;
    }

    protected function checkAdjacent(array $prev = [DiplomaticMessage::ACCEPTED, '']){
        if($prev[0] !== DiplomaticMessage::ACCEPTED){
            return $prev;
        }

        if(!\sammo\isNeighbor($this->srcNation['nation'], $this->destNation['nation'], true)){
            return [DiplomaticMessage::DECLINED, '상대국의 도시와 인접하지 않았습니다.'];
        }

        return $prev;
    }

    protected function checkAlreadyMerging(array $prev = [DiplomaticMessage::ACCEPTED, '']){
        if($prev[0] !== DiplomaticMessage::ACCEPTED){
            return $prev;
        }

        if((3 <= $this->srcToDestDiplomacy['state'] && $this->srcToDestDiplomacy['state'] <= 6)
            ||(3 <= $this->destToSrcDiplomacy['state'] && $this->destToSrcDiplomacy['state'] <= 6)){
            return [DiplomaticMessage::DECLINED, '상대국과 합병 중입니다.'];
        }

        return $prev;
    }

    protected function checkSrcNationHasNeutralDiplomacy(array $prev = [DiplomaticMessage::ACCEPTED, '']){
        if($prev[0] !== DiplomaticMessage::ACCEPTED){
            return $prev;
        }

        $db = \sammo\DB::db();

        $states = $db->queryFirstColumn(
            'SELECT `state` FROM diplomacy WHERE `state` NOT IN (2, 7) AND me=%i AND you <>%i',
            $this->srcNation['nation'],
            $this->destNation['nation']
        );

        foreach($states as $state){
            if($state == 0 || $state == 1){
                return [DiplomaticMessage::DECLINED, '상대국이 전쟁 중입니다.'];
            }
            if(3 <= $state && $state <= 6){
                return [DiplomaticMessage::DECLINED, '상대국이 합병 중입니다.'];
            }
        }

        return $prev;
    }

    protected function checkContradictoryDiplomacy(array $prev = [DiplomaticMessage::ACCEPTED, '']){
        if($prev[0] !== DiplomaticMessage::ACCEPTED){
            return $prev;
        }

        $db = \sammo\DB::db();

        $cnt = $db->queryFirstField(
            'SELECT count(dest.you) FROM diplomacy src 
            JOIN diplomacy dest ON src.you = dest.you AND src.me != dest.me
            WHERE src.state = 7 AND dest.state IN (0, 1) AND src.me = %i AND dest.me = %i',
            $this->srcNation['nation'],
            $this->destNation['nation']
        );

        if($cnt > 0){
            return [DiplomaticMessage::DECLINED, '상대국이 본국의 교전국과 불가침중입니다.'];
        }

        return $prev;
    }

    protected function checkMorePower(array $prev = [DiplomaticMessage::ACCEPTED, '']){
        if($prev[0] !== DiplomaticMessage::ACCEPTED){
            return $prev;
        }

        if($this->srcNation['power'] < $this->destNation['power'] * 3){
            return [DiplomaticMessage::DECLINED, '상대국과 국력차가 크지 않습니다.'];
        }

        return $prev;
    }

    protected function checkMergePower(array $prev = [DiplomaticMessage::ACCEPTED, '']){
        if($prev[0] !== DiplomaticMessage::ACCEPTED){
            return $prev;
        }

        $db = DB::db();
        list(
            $powerAvg,
            $powerStddev,
            $genAvg,
            $genStddev
        ) = $db->queryFirstList(
            'SELECT avg(`power`), std(`power`), avg(`gennum`), std(`gennum`) FROM nation WHERE `level` >= 1'
        );
        
        $mergedPower = ($this->srcNation['power'] + $this->destNation['power'])/2;
        $ZPower = ($mergedPower - $powerAvg) / $powerStddev;
        if($ZPower >= -0.25){
            return [DiplomaticMessage::DECLINED, '두 국가의 국력 평균이 상위 60% 보다 높습니다.'];
        }

        $mergedGeneral = ($this->srcNation['gennum'] + $this->destNation['gennum'])/2;
        $ZGeneral = ($mergedGeneral - $genAvg) / $genStddev;
        if($ZGeneral >= -0.67){
            return [DiplomaticMessage::DECLINED, '두 국가의 장수수 평균이 상위 75% 보다 높습니다.'];
        }

        return $prev;
    }

    public function noAggression(int $when, string $option){
        $chk = $this->checkValidNation();
        $chk = $this->checkNotWar($chk);
        $chk = $this->checkAlreadyMerging($chk);

        list($result, $reason) = $chk;
        if($result !== DiplomaticMessage::ACCEPTED){
            return $chk;
        }

        $db = DB::db();
        $db->update('diplomacy',[
            'state'=>7,
            'term'=>$when*12,
            'fixed'=>$option
        ],
        '(me=%i AND you=%i) OR (you=%i AND me=%i)', 
        $this->srcNation['nation'], $this->destNation['nation'],
        $this->srcNation['nation'], $this->destNation['nation']);

        return $chk;
    }
    
    public function cancelNA(){
        $chk = $this->checkValidNation();
        $chk = $this->checkNonAggressionTreaty($chk);

        [$result, $reason] = $chk;
        if($result !== DiplomaticMessage::ACCEPTED){
            return $chk;
        }

        $db = DB::db();
        $db->update('diplomacy',[
            'state'=>2,
            'term'=>0,
            'fixed'=>''
        ],
        '(me=%i AND you=%i) OR (you=%i AND me=%i)', 
        $this->srcNation['nation'], $this->destNation['nation'],
        $this->srcNation['nation'], $this->destNation['nation']);

        return $chk;
    }

    public function stopWar(){
        $chk = $this->checkValidNation();
        $chk = $this->checkInWar($chk);

        [$result, $reason] = $chk;
        if($result !== DiplomaticMessage::ACCEPTED){
            return $chk;
        }

        $db = DB::db();
        $db->update('diplomacy',[
            'state'=>2,
            'term'=>0,
            'fixed'=>''
        ],
        '(me=%i AND you=%i) OR (you=%i AND me=%i)', 
        $this->srcNation['nation'], $this->destNation['nation'],
        $this->srcNation['nation'], $this->destNation['nation']);

        return $chk;
    }

    public function acceptMerge(int $srcGeneral,  int $destGeneral){
        $chk = $this->checkValidNation();
        $chk = $this->checkDiplomacyLimit($chk);
        $chk = $this->checkContradictoryDiplomacy($chk);
        $chk = $this->checkSrcNationHasNeutralDiplomacy($chk);
        $chk = $this->checkStrictlyAdjacent($chk);
        $chk = $this->checkMergePower($chk);

        list($result, $reason) = $chk;
        if($result !== DiplomaticMessage::ACCEPTED){
            return $chk;
        }

        $db = DB::db();
        $db->update('diplomacy',[
            'state'=>4,
            'term'=>24,
            'fixed'=>''
        ],
        'me=%i AND you=%i', 
        $this->srcNation['nation'], $this->destNation['nation']);

        $db->update('diplomacy',[
            'state'=>3,
            'term'=>24,
            'fixed'=>''
        ],
        'you=%i AND me=%i', 
        $this->srcNation['nation'], $this->destNation['nation']);

        return $chk;
    }

    public function acceptSurrender(int $srcGeneral,  int $destGeneral){
        $chk = $this->checkValidNation();
        $chk = $this->checkDiplomacyLimit($chk);
        $chk = $this->checkContradictoryDiplomacy($chk);
        $chk = $this->checkSrcNationHasNeutralDiplomacy($chk);
        $chk = $this->checkStrictlyAdjacent($chk);
        $chk = $this->checkMorePower($chk);

        list($result, $reason) = $chk;
        if($result !== DiplomaticMessage::ACCEPTED){
            return $chk;
        }

        $db = DB::db();
        $db->update('diplomacy',[
            'state'=>6,
            'term'=>24,
            'fixed'=>''
        ],
        'me=%i AND you=%i', 
        $this->srcNation['nation'], $this->destNation['nation']);

        $db->update('diplomacy',[
            'state'=>5,
            'term'=>24,
            'fixed'=>''
        ],
        'you=%i AND me=%i', 
        $this->srcNation['nation'], $this->destNation['nation']);

        return $chk;
    }
}