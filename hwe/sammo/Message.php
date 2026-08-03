<?php
namespace sammo;

use sammo\Enums\MessageType;

class Message
{
    const MAILBOX_PUBLIC = 9999;
    const MAILBOX_NATIONAL = 9000;

    //기본 정보
    public $mailbox = null;
    public $id = null;
    public $isInboxMail = false;

    protected $sendCnt = 0;

    public function __construct(
        public MessageType $msgType,
        public MessageTarget $src,
        public MessageTarget $dest,
        public string $msg,
        public \DateTime $date,
        public \DateTime $validUntil,
        public ?array $msgOption
    ) {
    }

    public static function gameNow(): \DateTime
    {
        $clock = GameClock::fromStorage(KVStorage::getStorage(DB::db(), 'game_env'));
        return \DateTime::createFromImmutable($clock->nowDateTime());
    }

    public function setSentInfo(int $mailbox, int $messageID) : self
    {
        if(!Message::isValidMailBox($mailbox)){
            throw new \InvalidArgumentException('올바르지 않은 mailbox');
        }

        do{
            if($mailbox === Message::MAILBOX_PUBLIC){
                if($this->msgType !== MessageType::public){
                    throw new \InvalidArgumentException('올바르지 않은 mailbox, msgType !== MessageType::public');
                }
                $this->isInboxMail = true;
                break;
            }
            if($mailbox >= Message::MAILBOX_NATIONAL){
                if($this->msgType === MessageType::diplomacy){
                    $this->isInboxMail = true;
                    break;
                }
                if ($this->msgType !== MessageType::national) {
                    throw new \InvalidArgumentException('올바르지 않은 mailbox, msgType not in (MessageType::diplomacy, MessageType::national)');
                }
                if($this->dest->nationID + Message::MAILBOX_NATIONAL === $mailbox){
                    $this->isInboxMail = true;
                    break;
                }
                if($this->src->nationID + Message::MAILBOX_NATIONAL === $mailbox){
                    $this->isInboxMail = false;
                    break;
                }
                throw new \InvalidArgumentException('송신, 수신국 둘 중의 어느 메일함도 아닙니다');
            }
            if($this->msgType !== MessageType::private){
                throw new \InvalidArgumentException('올바르지 않은 mailbox, msgType !== MSGTYPE_PRIVATE');
            }
            if($this->dest->generalID === $mailbox){
                $this->isInboxMail = true;
                break;
            }
            if($this->src->generalID === $mailbox){
                $this->isInboxMail = false;
                break;
            }
            throw new \InvalidArgumentException('송신자, 수신자 둘 중의 어느 메일함도 아닙니다');
        }while(false);

        $this->id = $messageID;
        $this->mailbox = $mailbox;
        return $this;
    }

    public function toArray():array{
        $clock = GameClock::fromStorage(KVStorage::getStorage(DB::db(), 'game_env'));
        $messageTick = $clock->dateTimeToTick($this->date);
        $deleteUntilTick = GameClock::addTicks($messageTick, $clock->ticksFromMinutes(5));
        $deleteRemainingTicks = max(0, $deleteUntilTick - $clock->nowTick());
        $deleteRemainingTicks = min($deleteRemainingTicks, $clock->ticksFromSeconds(2_147_483));
        $deleteRemainingMilliseconds = intdiv(
            $deleteRemainingTicks * 1000,
            $clock->ticksPerSecond(),
        );
        if($this->msgType === MessageType::public){
            $src = $this->src->toArray();
            $dest = null;
        }
        else if($this->msgType === MessageType::national || $this->msgType === MessageType::diplomacy){
            $src = $this->src->toArray();
            $dest = $this->dest->toArray();
        }
        else{
            $src = $this->src->toArray();
            $dest = $this->dest->toArray();
        }

        return [
            'id'=>$this->id,
            'msgType'=>$this->msgType->value,
            'src'=>$src,
            'dest'=>$dest,
            'text'=>$this->msg,
            'option'=>$this->msgOption,
            'time'=>$this->date->format('Y-m-d H:i:s'),
            'deleteRemainingMilliseconds'=>$deleteRemainingMilliseconds,
            'clockMode'=>$clock->getMode(),
        ];
    }

    public static function buildFromArray(array $row) : Message
    {
        $clock = GameClock::fromStorage(KVStorage::getStorage(DB::db(), 'game_env'));
        $dbMessage = Json::decode($row['message']);

        $msgType = MessageType::from($row['type']);
        $src = MessageTarget::buildFromArray($dbMessage['src']);
        $dest = MessageTarget::buildFromArray($dbMessage['dest']);
        $option = Util::array_get($dbMessage['option'], []);

        $args = [
            $msgType,
            $src,
            $dest,
            $dbMessage['text'],
            \DateTime::createFromImmutable($clock->tickToDateTime(Util::toInt($row['time']))),
            \DateTime::createFromImmutable($clock->tickToDateTime(Util::toInt($row['valid_until']))),
            $option
        ];

        $action = Util::array_get($option['action'], null);
        if ($msgType === MessageType::diplomacy && $action !== null) {
            $objMessage = new DiplomaticMessage(...$args);
        } elseif ($action === 'scout') {
            $objMessage = new ScoutMessage(...$args);
        } elseif ($action === 'raiseInvader') {
            $objMessage = new RaiseInvaderMessage(...$args);
        } else {
            $objMessage = new Message(...$args);
        }

        $objMessage->setSentInfo($row['mailbox'], $row['id']);

        return $objMessage;
    }

    protected static function isValidMailBox(int $mailbox): bool
    {
        if ($mailbox > MessageType::public) {
            return false;
        }
        if ($mailbox <= 0) {
            return false;
        }
        return true;
    }

    public static function getMessageByID(int $messageID) : ?Message
    {
        $db = DB::db();
        $clock = GameClock::fromStorage(KVStorage::getStorage($db, 'game_env'));
        $row = $db->queryFirstRow(
            'SELECT * FROM `message` WHERE `id` = %i AND valid_until > %i',
            $messageID,
            $clock->nowTick(),
        );
        if (!$row) {
            return null;
        }
        return static::buildFromArray($row);
    }

    /**
     * @param int $mailbox 메일 사서함.
     * @param MessageType $msgType 메일 타입.
     * @param int $limit 가져오고자 하는 수. 0 이하의 값이면 모두.
     * @param int $fromSeq 가져오고자 하는 위치. $fromSeq보다 '큰' seq만 가져온다. 따라서 0 이하이면 모두 가져옴.
     * @return Message[]
     */
    public static function getMessagesFromMailBox(int $mailbox, MessageType $msgType, int $limit=30, int $fromSeq = 0)
    {
        $db = DB::db();

        $date = GameClock::fromStorage(KVStorage::getStorage($db, 'game_env'))->nowTick();

        $where = new \WhereClause('and');
        $where->add('mailbox = %i', $mailbox);
        $where->add('type = %s', $msgType->value);
        $where->add('valid_until > %i', $date);
        if ($fromSeq > 0) {
            $where->add('id >= %i', $fromSeq);
        }

        if ($limit > 0) {
            $limitSql = $db->sqleval('LIMIT %i', $limit);
        } else {
            $limitSql = new \MeekroDBEval('');
        }

        return array_map(function ($row) {
            return static::buildFromArray($row);
        }, $db->query('SELECT * FROM `message` WHERE %l ORDER BY id DESC %? ', $where, $limitSql));
    }

    /**
     * @param int $mailbox 메일 사서함.
     * @param MessageType $msgType 메일 타입.
     * @param int $toSeq 가져오고자 하는 위치. $toSeq보다 '작은' seq만 가져온다.
     * @param int $limit 가져오고자 하는 수.
     * @return Message[]
     */
    public static function getMessagesFromMailBoxOld(int $mailbox, MessageType $msgType, int $toSeq, int $limit = 20)
    {
        $db = DB::db();

        $date = GameClock::fromStorage(KVStorage::getStorage($db, 'game_env'))->nowTick();

        $where = new \WhereClause('and');
        $where->add('mailbox = %i', $mailbox);
        $where->add('type = %s', $msgType->value);
        $where->add('valid_until > %i', $date);
        $where->add('id < %i', $toSeq);

        if ($limit > 0) {
            $limitSql = $db->sqleval('LIMIT %i', $limit);
        } else {
            $limitSql = new \MeekroDBEval('');
        }

        return array_map(function ($row) {
            return static::buildFromArray($row);
        }, $db->query('SELECT * FROM `message` WHERE %l ORDER BY id DESC %? ', $where, $limitSql));
    }

    public static function deleteMsg(int $msgID, int $generalID):?string{
        $msgObj = static::getMessageByID($msgID);
        if($msgObj=== null){
            return '메시지가 없습니다';
        }

        if($msgObj->src->generalID != $generalID){
            return '본인의 메시지만 삭제할 수 있습니다.';
        }

        if($msgObj instanceof DiplomaticMessage){
            return '시스템 외교 메시지는 삭제할 수 없습니다.';
        }

        $clock = GameClock::fromStorage(KVStorage::getStorage(DB::db(), 'game_env'));
        $prev5min = \DateTime::createFromImmutable($clock->tickToDateTime($clock->nowTick()));
        $prev5min->sub(new \DateInterval('PT5M'));

        if($msgObj->date < $prev5min){
            return '5분 이내의 메시지만 삭제할 수 있습니다.';
        }

        if(!($msgObj->msgOption['deletable']??true)){
            return '삭제할 수 없는 메시지입니다.';
        }

        $msgOption = [
            'hide'=>true,
            'silence'=>true,
            'overwrite'=>[$msgObj->id]
        ];

        if($msgObj->msgType === MessageType::private || $msgObj->msgType === MessageType::national){
            $receiveID = $msgObj->msgOption['receiverMessageID']??null;
            if($receiveID !== null){
                $msgObj2 = static::getMessageByID($receiveID);
                if($msgObj2 !== null){
                    $msgObj2->invalidate(null, false);
                    $msgOption['overwrite'][] = [$msgObj2->id];
                }
            }

        }

        $now = \DateTime::createFromImmutable($clock->tickToDateTime($clock->nowTick()));
        $in1min = clone $now;
        $in1min->add(new \DateInterval('PT1M'));
        $newMsg = new Message(
            $msgObj->msgType,
            $msgObj->src,
            $msgObj->dest,
            "req_del_msg",
            $now,
            $in1min,
            $msgOption
        );
        $msgObj->invalidate(null, false);
        $newMsg->send(false);
        return null;
    }

    protected function sendRaw(int $mailbox):array{
        //NOTE:: 여기선 검증하지 않는다.


        if($mailbox === self::MAILBOX_PUBLIC){
            $src_id = $this->src->generalID;
            $dest_id = self::MAILBOX_PUBLIC;
        }
        else if($mailbox >= self::MAILBOX_NATIONAL){
            $src_id = $this->src->nationID + self::MAILBOX_NATIONAL;
            $dest_id = $this->dest->nationID + self::MAILBOX_NATIONAL;
        }
        else{
            $src_id = $this->src->generalID;
            $dest_id = $this->dest->generalID;
        }


        $db = DB::db();
        $clock = GameClock::fromStorage(KVStorage::getStorage($db, 'game_env'));
        $timeTick = $clock->nowTick();
        if (Util::toInt($this->validUntil->format('Y')) >= 9000) {
            $validUntilTick = GameClock::MAX_SAFE_TICK;
        } else {
            $validitySeconds = $this->validUntil->getTimestamp() - $this->date->getTimestamp();
            $validUntilTick = $timeTick + $clock->ticksFromSeconds($validitySeconds);
        }
        $this->date = \DateTime::createFromImmutable($clock->tickToDateTime($timeTick));
        $this->validUntil = \DateTime::createFromImmutable($clock->tickToDateTime($validUntilTick));
        $db->insert('message', [
            'mailbox' => $mailbox,
            'type' => $this->msgType->value,
            'src' => $src_id,
            'dest' => $dest_id,
            'time' => $timeTick,
            'valid_until' => $validUntilTick,
            'message' => Json::encode([
                'src'=>($this->src)?($this->src->toArray()):[],
                'dest'=>($this->dest)?($this->dest->toArray()):[],
                'text' => $this->msg,
                'option' => $this->msgOption
            ])
        ]);
        return [$mailbox, $db->insertId()];
    }

    private function sendToSender():array{
        if($this->sendCnt > 1){
            throw new \RuntimeException('이미 전송한 메일입니다.');
        }
        if($this->msgType === MessageType::private && $this->src->generalID !== $this->dest->generalID){
            return $this->sendRaw($this->src->generalID);
        }
        if($this->msgType === MessageType::national && $this->src->nationID !== $this->dest->nationID){
            return $this->sendRaw($this->src->nationID + self::MAILBOX_NATIONAL);
        }
        if($this->msgType === MessageType::diplomacy){
            if(key_exists('action', $this->msgOption)){
                $tmp = $this->msgOption;
                $this->msgOption = null;
                $retVal = $this->sendRaw($this->src->nationID + self::MAILBOX_NATIONAL);
                $this->msgOption = $tmp;
                return $retVal;
            }
            else{
                return $this->sendRaw($this->src->nationID + self::MAILBOX_NATIONAL);
            }

        }
        return [0, 0];
    }

    private function sendToReceiver() : array{
        if($this->sendCnt > 0 || $this->isInboxMail){
            throw new \RuntimeException('이미 전송한 메일입니다.');
        }

        if($this->msgType === MessageType::private){
            if(!($this->msgOption['silence']??false)){
                //XXX: 알림을 이런식으로 보내는게 맞는가에 대한 의문 있음
                DB::db()->update('general', [
                    'newmsg'=>1
                ], 'no=%i',$this->dest->generalID);
            }
            return $this->sendRaw($this->dest->generalID);
        }

        if($this->msgType === MessageType::national){
            return $this->sendRaw($this->dest->nationID + self::MAILBOX_NATIONAL);
        }

        if($this->msgType === MessageType::diplomacy){
            if(!($this->msgOption['silence']??false)){
                //XXX: 알림을 이런식으로 보내는게 맞는가에 대한 의문 있음
                DB::db()->update('general', [
                    'newmsg'=>1
                ], 'nation = %i AND (officer_level = 12 OR permission IN (\'ambassador\', \'auditor\')) ',$this->dest->nationID);
            }
            return $this->sendRaw($this->dest->nationID + self::MAILBOX_NATIONAL);
        }

        if($this->msgType === MessageType::public){
            return $this->sendRaw(self::MAILBOX_PUBLIC);
        }

        throw new \RuntimeException('이곳에 올 수 없습니다.');
    }

    /**
     * @param int[]|MessageTarget[]|int|MessageTarget $targets
     * @param string $msg
     */
    public static function sendPrivateMsgAsNotice(array|int|MessageTarget $targets, string $msg): void{
        $src = MessageTarget::buildSystemTarget();
        if(is_int($targets)){
            $targets = [$targets];
        }
        else if($targets instanceof MessageTarget){
            $targets = [$targets];
        }

        $reqTargetGeneralIDList = [];
        $objTargets = [];

        foreach($targets as $target){
            if($target instanceof MessageTarget){
                $objTargets[] = $target;
                continue;
            }
            if(!is_int($target)){
                throw new \InvalidArgumentException('올바르지 않은 타입');
            }

            $reqTargetGeneralIDList[] = $target;
        }

        if($reqTargetGeneralIDList){
            $db = DB::db();
            $rawGenerals = $db->query('SELECT no, name, nation, imgsvr, picture FROM general WHERE no in %li', $reqTargetGeneralIDList);
            foreach($rawGenerals as $rawGeneral){
                $staticNation = getNationStaticInfo($rawGeneral['nation']);
                $objTarget = new MessageTarget(
                    $rawGeneral['no'],
                    $rawGeneral['name'],
                    $rawGeneral['nation'],
                    $staticNation['name'],
                    $staticNation['color'],
                    GetImageURL($rawGeneral['imgsvr'], $rawGeneral['picture'])
                );
                $objTargets[] = $objTarget;
            }
        }

        foreach($objTargets as $dest){
            $msg = new Message(
                MessageType::private,
                $src,
                $dest,
                $msg,
                self::gameNow(),
                new \DateTime('9999-12-31'),
                []
              );
            $msg->send(true);
        }
    }

    public function send(bool $sendDestOnly=false):int{
        [$receiverMailbox, $receiveID] = $this->sendToReceiver();
        if(!$receiveID && !$sendDestOnly){
            throw new \RuntimeException('메시지 전송 불가');
        }
        $this->mailbox = $receiverMailbox;
        $this->isInboxMail = true;
        $this->id = $receiveID;
        $this->msgOption['receiverMessageID'] = $receiveID;
        $this->sendCnt = 1;

        if(!$sendDestOnly){
            [$senderMailbox, $sendID] = $this->sendToSender();
            if($sendID){
                $this->mailbox = $senderMailbox;
                $this->isInboxMail = false;
                $this->id = $sendID;
                $this->msgOption['senderMessageID'] = $sendID;
                $this->sendCnt = 2;
            }
        }

        return $receiveID;
    }

    public function invalidate(?array $newMsgOption=null, bool $hideMsg=true){
        $clock = GameClock::fromStorage(KVStorage::getStorage(DB::db(), 'game_env'));
        $validUntilTick = $clock->dateTimeToTick($this->validUntil);
        if($newMsgOption !== null){
            $this->msgOption = $newMsgOption;
        }

        $this->msgOption['invalid'] = true;

        if($hideMsg){
            $validUntilTick = GameClock::addTicks($clock->nowTick(), -1);
            $this->validUntil = \DateTime::createFromImmutable($clock->tickToDateTime($validUntilTick));
        }
        else{
            if(key_exists('receiverMessageID', $this->msgOption)){
                $this->msgOption['originalText'] = $this->msg;
            }
            $this->msg = '삭제된 메시지입니다.';
        }


        $db = DB::db();
        $db->update('message', [
            'message' => Json::encode([
                'src' => $this->src->toArray(),
                'dest' =>$this->dest->toArray(),
                'text' => $this->msg,
                'option' => $this->msgOption
            ]),
            'valid_until'=>$validUntilTick,
        ], 'id=%i', $this->id);

    }

    public function agreeMessage(int $receiverID, string &$reason):int{
        throw new NotInheritedMethodException();
    }

    public function declineMessage(int $receiverID, string &$reason):int{
        throw new NotInheritedMethodException();
    }
}
