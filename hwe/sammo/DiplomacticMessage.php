<?php
namespace sammo;

class DiplomacticMessage extends Message{

    const ACCEPTED = 1;
    const DECLINED = -1;
    const INVALID = 0;

    
    const TYPE_NO_AGGRESSION = 'noAggression'; //불가침
    const TYPE_CANCEL_NA = 'cancelNA'; //불가침 파기
    const TYPE_STOP_WAR = 'stopWar'; //종전
    const TYPE_MERGE = 'merge'; //통합
    const TYPE_SURRENDER = 'surrender'; //항복
    

    protected $diplomaticType = '';
    protected $diplomacyName = '';
    protected $validDiplomacy = true;

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
        if ($msgType !== self::MSGTYPE_DIPLOMACY){
            throw new \InvalidArgumentException('DiplomaticMessage msgType');
        }

        $this->diplomaticType = Util::array_get($msgOption['action']);
        switch($this->diplomaticType){
            case self::TYPE_NO_AGGRESSION: $this->diplomacyName = '불가침'; break;
            case self::TYPE_CANCEL_NA: $this->diplomacyName = '불가침 파기'; break;
            case self::TYPE_STOP_WAR: $this->diplomacyName = '종전'; break;
            case self::TYPE_MERGE: $this->diplomacyName = '통합'; break;
            case self::TYPE_SURRENDER: $this->diplomacyName = '투항'; break;
            default: throw \RuntimeException('diplomaticType이 올바르지 않음');
        }

        parent::__construct(...func_get_args());


        if(Util::array_get($msgOption['used'])){
            $this->validDiplomacy = false;
        }

        if($this->$validUntil < (new \DateTime())){
            $this->validDiplomacy = false;
        }
    }

    protected function checkDiplomaticMessageValidation(array $general){
        if(!$this->validDiplomacy){
            return [self::INVALID, '유효하지 않은 외교서신입니다.'];
        }

        if($this->mailbox !== $this->dest->nationID + static::MAILBOX_NATIONAL){
            return [self::INVALID, '송신자가 외교서신을 처리할 수 없습니다.'];
        }

        if(!$general || $general['level'] < 5){
            return [self::INVALID, '해당 국가의 수뇌가 아닙니다.'];
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
        $general = $db->queryFirstRow(
            'SELECT `name`, `level` FROM general WHERE `no`=%i AND nation=%i', 
            $receiverID, 
            $this->dest->nationID
        );

        list($result, $reason) = $this->checkDiplomaticMessageValidation($general);

        $helper = new Engine\Diplomacy($this->src->nationID, $this->dest->nationID);

        switch($this->diplomaticType){
            case self::TYPE_NO_AGGRESSION:
                list($result, $reason) = $helper->noAggression();
                break;
            case self::TYPE_CANCEL_NA:
                list($result, $reason) = $helper->cancelNA();
                break;
            case self::TYPE_STOP_WAR:
                list($result, $reason) = $helper->stopWar();
                break;
            case self::TYPE_MERGE:
                list($result, $reason) = $helper->acceptMerge();
                break;
            case self::TYPE_SURRENDER:
                list($result, $reason) = $helper->acceptSurrender();
                break;
            default: 
                throw \RuntimeException('diplomaticType이 올바르지 않음');
        }


        if($result !== self::ACCEPTED){
            pushGenLog(['no'=>$receiverID], ["<C>●</>{$reason} {$this->diplomacyName} 실패."]);
            if($result === self::DECLINED){
                $this->_declineMessage();
            }
            return $result;
        }

        
        list(
            $year, 
            $month
        ) = $db->queryFirstList('SELECT year, month FROM game LIMIT 1');


        $this->dest->generalID = $receiverID;
        $this->dest->generalName = $general['name'];
        $this->msgOption['used'] = true;
        $this->invalidate();
        $this->validDiplomacy = false;

        $newMsg = new Message(
            self::MSGTYPE_NATIONAL, 
            $this->dest, 
            $this->src, 
            "【외교】{$year}년 {$month}월: {$this->src->nationName}이 {$this->dest->nationName}에게 제안한 {$this->diplomacyName} 동의.",
            new \DateTime(),
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
        $this->validDiplomacy = false;

        return self::DECLINED;
    }

    public function declineMessage(int $receiverID):int{
        if(!$this->id){
            throw \RuntimeException('전송되지 않은 메시지에 거절 진행 중');
        }

        list($result, $reason) = $this->checkScoutMessageValidation($receiverID);

        if($result === self::INVALID){
            pushGenLog(['no'=>$receiverID], ["<C>●</>{$reason} {$this->diplomacyName} 거절 불가."]);
            return $result;
        }

        pushGenLog(['no'=>$receiverID], "<C>●</><D>{$this->src->nationName}</>의 {$this->diplomacyName} 제안을 거절했습니다.");
        pushGenLog(['no'=>$this->src->generalID], "<C>●</><Y>{$this->dest->nationName}</>(이)가 {$this->diplomacyName} 제안을 거절했습니다.");
        $this->_declineMessage();  
        return self::DECLINED;
    }

}