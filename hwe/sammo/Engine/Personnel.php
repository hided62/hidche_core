<?php
namespace sammo\Engine;

use \sammo\ScoutMessage;

/**
 * 인사(등용, 추방, 임명, 망명) 헬퍼 클래스?
 * Nation 클래스가 있고 그걸 상속한 구조가 더 나을지도?
 */
class Personnel{

    protected $nation = null;//TODO: 상속체로 변경.
    public $valid = false;

    public $startYear = 0;
    public $year = 0;
    public $month = 0;
    public $killturn = 0;

    public function __construct(int $nationID){
        $db = DB::db();
        $nation = $db->queryFirstRow(
            'SELECT nation, `name`, `level`, capital, scout FROM nation WHERE nation=%i',
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
            $this->month, 
            $this->killturn
        ) = $db->queryFirstList('SELECT startyear, year, month, killturn FROM game LIMIT 1');

    }

    public function checkAgreeValidation(array $general){

        if(!$this->valid){
            return [ScoutMessage::DECLINED, '이미 멸망한 국가입니다.'];
        }

        if($this->year < $this->startYear + 3){
            return [ScoutMessage::INVALID, '초반제한 중입니다.'];
        }
        
        if(!$this->nation['scout']){
            return [ScoutMessage::INVALID, '현재 임관금지 중인 국가입니다.'];
        }

        if($this->nation['level'] == 0){
            return [ScoutMessage::DECLINED, '방랑군에는 임관할 수 없습니다.'];
        }

        if($this->nation['nation'] == $general['nation']){
            return [ScoutMessage::DECLINED, '이미 같은 국가입니다.'];
        }

        if($general['level'] == 12){
            return [ScoutMessage::DECLINED, '군주는 등용장을 수락할 수 없습니다.'];
        }

        if(strpos($general['nations'], ",{$this->nation['nation']},") >= 0){
            return [ScoutMessage::DECLINED, '이미 임관했었던 국가입니다.'];
        }

        return [ScoutMessage::ACCEPTED, ''];
    }

    public function scoutGeneral(int $generalID){
        $db = DB::db();

        $general = $db->queryFirstRow(
            'SELECT `no`, `name`, nation, nations, city, `level`, troop, npc, gold, rice FROM general WHERE `no`=%i',
            $generalID
        );

        list($result, $reason) = $this->checkAgreeValidation($general);
        if($result !== ScoutMessage::ACCEPTED){
            return [$result, $reason];
        }

        $scoutNationGeneralCnt = $db->queryFirstField(
            'SELECT COUNT(`no`) FROM general WHERE nation = %i', 
            $this->nation['nation']
        );

        $originalNationGeneralCnt = $db->queryFirstField(
            'SELECT COUNT(`no`) FROM general WHERE nation = %i', 
            $general['nation']
        );

        $isTroopLeader = false;
        if($general['troop']){
            $troopLeader = $db->queryFirstField('SELECT `no` FROM troop WHERE troop = %i', $general['troop']);
            if($troopLeader == $receiverID){
                $isTroopLeader = true;
            }
        }

        $scoutNationGeneralCnt+=1;
        if($scoutNationGeneralCnt < 10){
            $scoutNationGeneralCnt = 10;//XXX: 상수!
        }

        $originalNationGeneralCnt-=1;
        if($originalNationGeneralCnt < 10){
            $originalNationGeneralCnt = 10;//XXX: 상수!
        }

        //TODO: Logging 시스템 들어엎자.
        $senderLog = [];
        $receiverLog = [];
        $generalPublicLog = [];

        $senderLog[] = "<C>●</><Y>{$general['name']}</> 등용에 성공했습니다.";
        $receiverLog[] = "<C>●</><D>{$this->nation['name']}</>(으)로 망명하여 수도로 이동합니다.";
        $generalPublicLog[] = "<C>●</>{$month}월:<Y>{$general['name']}</>(이)가 <D><b>{$this->nation['name']}</b></>(으)로 <S>망명</>하였습니다.";

        // 국가 변경, 도시 변경, 일반으로, 수도로
        $setValues = [
            'belong'=>1,
            'level'=>1,
            'nation'=>$this->nation['nation'],
            'city'=>$this->nation['capital'],
            'nations'=>$db->sqleval('CONCAT(nations, %s)', "{$this->nation['nation']},"),
            'troop'=>0,
        ];

        $setSenderValues = [
            'experience'=>$db->sqleval('experience + %i', 100),//XXX: 상수.
            'dedication'=>$db->sqleval('dedication + %i', 100)
        ];

        $setOriginalNationValues = [
            'totaltech'=>$db->sqleval('tech * %i', $originalNationGeneralCnt),
            'gennum'=>$originalNationGeneralCnt
        ];

        $setScoutNationValues = [
            'totaltech'=>$db->sqleval('tech * %i', $scoutNationGeneralCnt),
            'gennum'=>$scoutNationGeneralCnt
        ];

        $setOriginalCityValues = [];

        // 재야가 아니면 명성N*10% 공헌N*10%감소
        if($general['nation'] != 0){
            // 1000 1000 남기고 환수
            if($general['gold'] > 1000){//XXX: 상수.
                $setValues['gold'] = 1000;
                $setOriginalNationValues['gold'] = $db->sqleval('gold + %i', $general['gold'] - 1000);
            }

            if($general['rice'] > 1000){//XXX: 상수.
                $setValues['rice'] = 1000;
                $setOriginalNationValues['rice'] = $db->sqleval('rice + %i', $general['rice'] - 1000);
            }

            //관직 해제
            if(5 <= $general['level'] && $general['level'] <= 11){
                $setOriginalNationValues["l{$general['level']}set"] = 0;
            }
            else if($general['level'] == 4){
                $setOriginalCityValues['gen1'] = 0;
            }
            else if($general['level'] == 3){
                $setOriginalCityValues['gen2'] = 0;
            }
            else if($general['level'] == 2){
                $setOriginalCityValues['gen3'] = 0;
            }

            $setValues['betray'] = $db->sqleval('betray + 1');
            $setValues['experience'] = $db->sqleval('experience * (1 - 0.1 * betray)');//XXX: 상수
            $setValues['dedication'] = $db->sqleval('dedication * (1 - 0.1 * betray)');//XXX: 상수
        }
        else{
            //재야이면 100 100 증가
            $setValues['experience'] = $db->sqleval('experience + %i', 100);//XXX: 상수
            $setValues['dedication'] = $db->sqleval('experience + %i', 100);//XXX: 상수
        }

        if($me['npc'] < 2){
            $setValues['killturn'] = $killturn;
        }

        $db->update('general', $setValues, 'no=%i', $receiverID);
        $db->update('general', $setSenderValues, 'no=%i', $this->src->generalID);
        $db->update('nation', $setOriginalNationValues, 'nation=%i', $general['nation']);
        $db->update('nation', $setScoutNationValues, 'nation=%i', $this->nation['nation']);
        if($setOriginalCityValues){
            $db->update('city', $setOriginalCityValues, 'city=%i', $general['city']);
        }
        
        if($isTroopLeader){
            // 모두 탈퇴
            $db->update('general', [
                'troop'=>0,
            ], 'troop=%i', $general['troop']);
            // 부대 삭제
            $db->delete('troop', 'troop=%i', $general['troop']);
        }

        pushGenLog($general, $receiverLog);
        pushGenLog(['no'=>$this->src->generalID], $senderLog);
        pushGeneralPublicRecord($generalPublicLog, $year, $month);

        return [ScoutMessage::ACCEPTED, ''];
    }
}