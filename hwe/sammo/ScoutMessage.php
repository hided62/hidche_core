<?php
namespace sammo;

class ScoutMessage extends Message{

    const ACCEPTED = 1;
    const DECLINED = -1;
    const INVALID = 0;
    protected $validScout = true;

    public function __construct(
        string $msgType,
        MessageTarget $src,
        MessageTarget $dest,
        string $msg,
        \DateTime $date,
        \DateTime $validUntil,
        array $msgOption
    )
    {
        if ($msgType !== self::MSGTYPE_PRIVATE){
            throw new \InvalidArgumentException('DiplomaticMessage msgType');
        }

        if(Util::array_get($msgOption['action']) !== 'scout'){
            throw new \InvalidArgumentException('Action !== scout');
        }

        parent::__construct(...func_get_args());
        
        if(Util::array_get($msgOption['used'])){
            $this->validScout = false;
        }

        if($this->validUntil <= new DateTime()){
            $this->validScout = false;
        }
    }

    protected function checkAgreeValidation(int $startYear, int $year, array $general, array $scoutNation){
        if($year < $startYear + 3){
            return [self::INVALID, '초반제한 중입니다.'];
        }

        if(!$this->validScout){
            return [self::INVALID, '유효하지 않은 등용장입니다.'];
        }
        if($this->mailbox !== $this->dest->generalID){
            return [self::INVALID, '송신자가 등용장을 수락할 수 없습니다.'];
        }

        if($this->mailbox !== $receiverID){
            return [self::INVALID, '올바른 수신자가 아닙니다.'];
        }

        if(!$scoutNation){
            return [self::DECLINED, '이미 멸망한 국가입니다.'];
        }

        if(!$scoutNation['scout']){
            return [self::INVALID, '현재 임관금지 중인 국가입니다.'];
        }

        if($scoutNation['level'] == 0){
            return [self::DECLINED, '방랑군에는 임관할 수 없습니다.'];
        }

        if($scoutNation['nation'] == $general['nation']){
            return [self::DECLINED, '이미 같은 국가입니다.'];
        }

        if($general['level'] == 12){
            return [self::DECLINED, '군주는 등용장을 수락할 수 없습니다.'];
        }

        if(strpos($general['nations'], ",{$scoutNation['nation']},") >= 0){
            return [self::DECLINED, '이미 임관했었던 국가입니다.'];
        }

        return [self::ACCEPTED, ''];
    }

    /**
     * @return int 수행 결과 반환, ACCEPTED(등용장 소모), DECLINED(등용장 소모), INVALID 중 반환
     */
    public function agreeMessage(int $receiverID):int{
        //NOTE: 올바른 유저가 agreeMessage() 호출을 한건지는 외부에서 체크 필요(Session->userID 등)

        if(!$this->id){
            throw \RuntimeException('전송되지 않은 메시지에 수락 진행 중');
        }

        $db = DB::db();
        list(
            $startYear,
            $year, 
            $month, 
            $killturn
        ) = $db->queryFirstList('SELECT startyear, year, month, killturn FROM game LIMIt 1');
        $scoutNation = $db->queryFirstRow(
            'SELECT nation, `name`, `level`, capital, scout FROM nation WHERE nation=%i',
            $this->src->nationID
        );
        $general = $db->queryFirstRow(
            'SELECT `no`, `name`, nation, nations, city, `level`, troop, npc, gold, rice FROM general WHERE `no`=%i',
            $receiverID
        );

        list($result, $reason) = $this->checkAgreeValidation($startYear, $year, $general, $scoutNation);

        if($result !== self::ACCEPTED){
            pushGenLog($general, ["<C>●</>{$reason} 등용 수락 불가."]);
            if($result === self::DECLINED){
                $this->_declineMessage();
            }
            return $result;
        }

        $scoutNationGeneralCnt = $db->queryFirstField(
            'SELECT COUNT(`no`) FROM general WHERE nation = %i', 
            $scoutNation['nation']
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
        $receiverLog[] = "<C>●</><D>{$scoutNation['name']}</>(으)로 망명하여 수도로 이동합니다.";
        $generalPublicLog[] = "<C>●</>{$month}월:<Y>{$general['name']}</>(이)가 <D><b>{$scoutNation['name']}</b></>(으)로 <S>망명</>하였습니다.";

        // 국가 변경, 도시 변경, 일반으로, 수도로
        $setValues = [
            'belong'=>1,
            'level'=>1,
            'nation'=>$scoutNation['nation'],
            'city'=>$scoutNation['capital'],
            'nations'=>$db->sqleval('CONCAT(nations, %s)', "{$scoutNation['nation']},"),
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

        $setOriginalCityValues = [

        ];

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
        $db->update('nation', $setScoutNationValues, 'nation=%i', $scoutNation['nation']);
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
        
        $now = new \DateTime();

        //메시지 비 활성화
        $this->msgOption['used'] = true;
        $this->invalidate();
        $this->validScout = false;

        $newMsg = new Message(
            self::MSGTYPE_PRIVATE, 
            $this->dest, 
            $this->dest, 
            "{$scoutNation['name']}(으)로 등용 제의 수락",
            $now,
            new \DateTime('9999-12-31'),
            Json::encode([
                'related'=>$this->id
            ])
        );
        $newMsg->send();

        return self::ACCEPTED;
    }

    protected function _declineMessage(){
        $this->msgOption['used'] = true;
        $this->invalidate();
        $this->validScout = false;

        $newMsg = new Message(
            self::MSGTYPE_PRIVATE, 
            $this->dest, 
            $this->dest, 
            "{$scoutNation['name']}(으)로 등용 제의 거부",
            $now,
            new \DateTime('9999-12-31'),
            Json::encode([
                'related'=>$this->id
            ])
        );
        $newMsg->send();

        return self::ACCEPTED;
    }

    protected function checkDeclineValidation(int $receiverID){
        if(!$this->validScout){
            return [self::INVALID, '유효하지 않은 등용장입니다.'];
        }
        if($this->mailbox !== $this->dest->generalID){
            return [self::INVALID, '송신자가 등용장을 거부할 수 없습니다.'];
        }

        if($this->mailbox !== $receiverID){
            return [self::INVALID, '올바른 수신자가 아닙니다.'];
        }
    }

    public function declineMessage(int $receiverID):int{
        if(!$this->id){
            throw \RuntimeException('전송되지 않은 메시지에 거절 진행 중');
        }

        list($result, $reason) = $this->checkDeclineValidation($receiverID);

        if($result === self::INVALID){
            pushGenLog($general, ["<C>●</>{$reason} 등용 수락 불가."]);
            return $result;
        }

        $this->_declineMessage();
        pushGenLog(['no'=>$receiverID], "<C>●</><D>{$this->src->nationName}</>(으)로 망명을 거부했습니다.");
        pushGenLog(['no'=>$this->src->generalID], "<C>●</><Y>{$this->dest->generalName}</>(이)가 등용을 거부했습니다.");

        return self::DECLINED;
    }

    public static function buildScoutMessage(int $srcGeneralID, int $destGeneralID, &$reason = null, \DateTime $date = null): Message{
        if($srcGeneralID == $destGeneralID){
            if($reason !== null){
                $reason = '같은 장수에게 등용장을 보낼 수 없습니다';
            }
            return null;
        }

        $db = DB::db();
        $srcGeneral = $db->queryFirstRow('SELECT `name`, nation FROM nation WHERE `no`=%i', $srcGeneralID);
        $destGeneral = $db->queryFirstRow('SELECT `name`, nation, `level` FROM nation WHERE `no`=%i', $destGeneralID);
        if($date === null){
            $date = new DateTime();
        }

        if($destGeneral['level'] == 12){
            if($reason !== null){
                $reason = '군주에게 등용장을 보낼 수 없습니다';
            }
            return null;
        }

        if(!$srcGeneral['nation']){
            if($reason !== null){
                $reason = '재야 상태일 때에는 등용장을 보낼 수 없습니다';
            }
            return null;
        }

        if($srcGeneral['nation'] === $destGeneral['nation']){
            if($reason !== null){
                $reason = '같은 소속의 장수에게 등용장을 보낼 수 없습니다';
            }
            return null;
        }

        $srcNationInfo = getNationStaticInfo($srcGeneral['nation']);
        $destNationInfo = getNationStaticInfo($destGeneral['nation']);

        $src = new MessageTarget(
            $srcGeneralID, 
            $srcGeneral['name'],
            $srcGeneral['nation'], 
            $srcNationInfo['name'], 
            $srcNationInfo['color']
        );

        $dest = new MessageTarget(
            $destGeneralID, 
            $destGeneral['name'],
            $destGeneral['nation'],
            Util::array_get($srcNationInfo['name'], ''), 
            Util::array_get($srcNationInfo['color'], '')
        );

        $msg = "{$src->nationName}(으)로 망명 권유 서신";
        $validUntil = new \DateTime("9999-12-31 12:59:59");

        $msgOption = [
            'action'=>'scout'
        ];

        return new ScoutMessage(Message::MSGTYPE_PRIVATE, $src, $dest, $msg, $date, $validUntil, $msgOption);
    }


}