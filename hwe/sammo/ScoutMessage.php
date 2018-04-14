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

    protected function checkScoutMessageValidation(int $receiverID){
        if(!$this->validScout){
            return [self::INVALID, '유효하지 않은 등용장입니다.'];
        }
        if($this->mailbox !== $this->dest->generalID){
            return [self::INVALID, '송신자가 등용장을 처리할 수 없습니다.'];
        }

        if($this->mailbox !== $receiverID){
            return [self::INVALID, '올바른 수신자가 아닙니다.'];
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

        list($result, $reason) = $this->checkScoutMessageValidation($receiverID);

        if($result !== self::ACCEPTED){
            pushGenLog(['no'=>$receiverID], ["<C>●</>{$reason} 등용 수락 불가."]);
            if($result === self::DECLINED){
                $this->_declineMessage();
            }
            return $result;
        }

        $helper = new Engine\Personnel($this->src->nationID);

        list($result, $reason) = $helper->scoutGeneral($receiverID);

        if($result !== self::ACCEPTED){
            pushGenLog(['no'=>$receiverID], ["<C>●</>{$reason} 등용 수락 불가."]);
            if($result === self::DECLINED){
                $this->_declineMessage();
            }
            return $result;
        }

        //메시지 비 활성화
        $this->msgOption['used'] = true;
        $this->invalidate();
        $this->validScout = false;

        $newMsg = new Message(
            self::MSGTYPE_PRIVATE, 
            $this->dest, 
            $this->dest, 
            "{$scoutNation['name']}(으)로 등용 제의 수락",
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
        $this->validScout = false;

        $newMsg = new Message(
            self::MSGTYPE_PRIVATE, 
            $this->dest, 
            $this->dest, 
            "{$scoutNation['name']}(으)로 등용 제의 거부",
            new \DateTime(),
            new \DateTime('9999-12-31'),
            Json::encode([
                'related'=>$this->id
            ])
        );
        $newMsg->send();

        return self::DECLINED;
    }

    public function declineMessage(int $receiverID):int{
        if(!$this->id){
            throw \RuntimeException('전송되지 않은 메시지에 거절 진행 중');
        }

        list($result, $reason) = $this->checkScoutMessageValidation($receiverID);

        if($result === self::INVALID){
            pushGenLog(['no'=>$receiverID], ["<C>●</>{$reason} 등용 취소 불가."]);
            return $result;
        }

        pushGenLog(['no'=>$receiverID], "<C>●</><D>{$this->src->nationName}</>(으)로 망명을 거부했습니다.");
        pushGenLog(['no'=>$this->src->generalID], "<C>●</><Y>{$this->dest->generalName}</>(이)가 등용을 거부했습니다.");
        $this->_declineMessage();        

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
            $date = new \DateTime();
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