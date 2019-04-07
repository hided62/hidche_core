<?php
namespace sammo;

class Message
{
    const MAILBOX_PUBLIC = 9999;
    const MAILBOX_NATIONAL = 9000;

    const MSGTYPE_PUBLIC = 'public';
    const MSGTYPE_PRIVATE = 'private';
    const MSGTYPE_NATIONAL = 'national';
    const MSGTYPE_DIPLOMACY = 'diplomacy';

    //기본 정보
    public $mailbox = null;
    public $id = null;
    public $isInboxMail = false;

    protected $sendCnt = 0;
    
    public $msgType;
    /** @var \sammo\MessageTarget */
    public $src;
    /** @var \sammo\MessageTarget */
    public $dest;
    public $msg;
    /** @var \DateTime */
    public $date;
    /** @var \DateTime */
    public $validUntil;
    
    public $msgOption;

    public function __construct(
        string $msgType,
        MessageTarget $src,
        MessageTarget $dest,
        string $msg,
        \DateTime $date,
        \DateTime $validUntil,
        array $msgOption
    ) {
        if (!static::isValidMsgType($msgType)) {
            throw new \InvalidArgumentException('올바르지 않은 msgType');
        }

        $this->msgType = $msgType;
        $this->src = $src;
        $this->dest = $dest;
        $this->msg = $msg;
        $this->date = $date;
        $this->validUntil = $validUntil;
        $this->msgOption = $msgOption;
    }

    public function setSentInfo(int $mailbox, int $messageID) : self
    {
        if(!Message::isValidMailBox($mailbox)){
            throw new \InvalidArgumentException('올바르지 않은 mailbox');
        }

        do{
            if($mailbox === Message::MAILBOX_PUBLIC){
                if($this->msgType !== Message::MSGTYPE_PUBLIC){
                    throw new \InvalidArgumentException('올바르지 않은 mailbox, msgType !== MSGTYPE_PUBLIC');
                }
                $this->isInboxMail = true;
                break;
            }
            if($mailbox >= Message::MAILBOX_NATIONAL){
                if($this->msgType === Message::MSGTYPE_DIPLOMACY){
                    $this->isInboxMail = true;
                    break;
                }
                if ($this->msgType !== Message::MSGTYPE_NATIONAL) {
                    throw new \InvalidArgumentException('올바르지 않은 mailbox, msgType not in (MSGTYPE_DIPLOMACY, MSGTYPE_NATIONAL)');
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
            if($this->msgType !== Message::MSGTYPE_PRIVATE){
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
        if($this->msgType === Message::MSGTYPE_PUBLIC){
            $src = $this->src->toArray();
            $dest = [];
        }
        else if($this->msgType === Message::MSGTYPE_NATIONAL || $this->msgType === Message::MSGTYPE_DIPLOMACY){
            $src = $this->src->toArray();
            $dest = $this->dest->toArray();
        }
        else{
            $src = $this->src->toArray();
            $dest = $this->dest->toArray();
        }

        return [
            'id'=>$this->id,
            'msgType'=>$this->msgType,
            'src'=>$src,
            'dest'=>$dest,
            'text'=>$this->msg,
            'option'=>$this->msgOption,
            'time'=>$this->date->format('Y-m-d H:i:s')
        ];
    }

    public static function buildFromArray(array $row) : Message
    {
        $dbMessage = Json::decode($row['message']);

        $msgType = $row['type'];
        $src = MessageTarget::buildFromArray($dbMessage['src']);
        $dest = MessageTarget::buildFromArray($dbMessage['dest']);
        $option = Util::array_get($dbMessage['option'], []);

        $args = [
            $msgType,
            $src,
            $dest,
            $dbMessage['text'],
            new \DateTime($row['time']),
            new \DateTime($row['valid_until']),
            $option
        ];

        $action = Util::array_get($option['action'], null);
        if ($msgType === self::MSGTYPE_DIPLOMACY && $action !== null) {
            $objMessage = new DiplomaticMessage(...$args);
        } elseif ($action === 'scout') {
            $objMessage = new ScoutMessage(...$args);
        } else {
            $objMessage = new Message(...$args);
        }

        $objMessage->setSentInfo($row['mailbox'], $row['id']);

        return $objMessage;
    }

    protected static function isValidMailBox(int $mailbox): bool
    {
        if ($mailbox > self::MAILBOX_PUBLIC) {
            return false;
        }
        if ($mailbox <= 0) {
            return false;
        }
        return true;
    }

    protected static function isValidMsgType(string $msgType): bool
    {
        switch ($msgType) {
            case static::MSGTYPE_PUBLIC: return true;
            case static::MSGTYPE_PRIVATE: return true;
            case static::MSGTYPE_NATIONAL: return true;
            case static::MSGTYPE_DIPLOMACY: return true;
        }
        return false;
    }

    public static function getMessageByID(int $messageID) : ?Message
    {
        $db = DB::db();
        $now = new \DateTime();
        $row = $db->queryFirstRow('SELECT * FROM `message` WHERE `id` = %i AND valid_until', $messageID);
        if (!$row) {
            return null;
        }
        return static::buildFromArray($row);
    }

    /**
     * @param int $mailbox 메일 사서함.
     * @param string $msgType 메일 타입. MSGTYPE 중 하나.
     * @param int $limit 가져오고자 하는 수. 0 이하의 값이면 모두.
     * @param int $fromSeq 가져오고자 하는 위치. $fromSeq보다 '큰' seq만 가져온다. 따라서 0 이하이면 모두 가져옴.
     * @return Message[]
     */
    public static function getMessagesFromMailBox(int $mailbox, string $msgType, int $limit=30, int $fromSeq = 0)
    {
        $db = DB::db();

        if (!static::isValidMsgType($msgType)) {
            throw new \InvalidArgumentException('올바르지 않은 $msgType');
        }

        $date = (new \DateTime())->format('Y-m-d H:i:s');

        $where = new \WhereClause('and');
        $where->add('mailbox = %i', $mailbox);
        $where->add('type = %s', $msgType);
        $where->add('valid_until > %s', $date);
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
     * @param string $msgType 메일 타입. MSGTYPE 중 하나.
     * @param int $toSeq 가져오고자 하는 위치. $toSeq보다 '작은' seq만 가져온다.
     * @param int $limit 가져오고자 하는 수. 
     * @return Message[]
     */
    public static function getMessagesFromMailBoxOld(int $mailbox, string $msgType, int $toSeq, int $limit = 20)
    {
        $db = DB::db();

        if (!static::isValidMsgType($msgType)) {
            throw new \InvalidArgumentException('올바르지 않은 $msgType');
        }

        $date = (new \DateTime())->format('Y-m-d H:i:s');

        $where = new \WhereClause('and');
        $where->add('mailbox = %i', $mailbox);
        $where->add('type = %s', $msgType);
        $where->add('valid_until > %s', $date);
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

        $prev5min = new \DateTime();
        $prev5min->sub(new \DateInterval('PT5M'));

        if($msgObj->date < $prev5min){
            return '5분 이내의 메시지만 삭제할 수 있습니다.';
        }

        $msgOption = [
            'hide'=>true,   
            'silence'=>true,
            'overwrite'=>[$msgObj->id]
        ];

        if($msgObj->msgType == Message::MSGTYPE_PRIVATE || $msgObj->msgType == Message::MSGTYPE_NATIONAL){
            $receiveID = $msgObj->msgOption['receiverMessageID']??null;
            if($receiveID !== null){
                $msgObj2 = static::getMessageByID($receiveID);
                if($msgObj2 !== null){
                    $msgObj2->invalidate(null, false);
                    $msgOption['overwrite'][] = [$msgObj2->id];
                }
            }
            
        }

        $in1min = new \DateTime();
        $in1min->add(new \DateInterval('PT1M'));
        $newMsg = new Message(
            $msgObj->msgType, 
            $msgObj->src, 
            $msgObj->dest, 
            "req_del_msg",
            new \DateTime(),
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
        $db->insert('message', [
            'mailbox' => $mailbox,
            'type' => $this->msgType,
            'src' => $src_id,
            'dest' => $dest_id,
            'time' => $this->date->format('Y-m-d H:i:s'),
            'valid_until' => $this->validUntil->format('Y-m-d H:i:s'),
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
        if($this->msgType === self::MSGTYPE_PRIVATE && $this->src->generalID !== $this->dest->generalID){
            return $this->sendRaw($this->src->generalID);
        }
        if($this->msgType === self::MSGTYPE_NATIONAL && $this->src->nationID !== $this->dest->nationID){
            return $this->sendRaw($this->src->nationID + self::MAILBOX_NATIONAL);
        }
        if($this->msgType === self::MSGTYPE_DIPLOMACY && !key_exists('action', $this->msgOption)){
            return $this->sendRaw($this->src->nationID + self::MAILBOX_NATIONAL);
        }
        return [0, 0];
    }

    private function sendToReceiver() : array{
        if($this->sendCnt > 0 || $this->isInboxMail){
            throw new \RuntimeException('이미 전송한 메일입니다.');
        }

        if($this->msgType === self::MSGTYPE_PRIVATE){
            if(!($this->msgOption['silence']??false)){
                //XXX: 알림을 이런식으로 보내는게 맞는가에 대한 의문 있음
                DB::db()->update('general', [
                    'newmsg'=>1
                ], 'no=%i',$this->dest->generalID);
            }
            return $this->sendRaw($this->dest->generalID);
        }

        if($this->msgType === self::MSGTYPE_NATIONAL || $this->msgType === self::MSGTYPE_DIPLOMACY){
            return $this->sendRaw($this->dest->nationID + self::MAILBOX_NATIONAL);
        }

        if($this->msgType === self::MSGTYPE_PUBLIC){
            return $this->sendRaw(self::MAILBOX_PUBLIC);
        }

        throw new \RuntimeException('이곳에 올 수 없습니다.');
    }

    public function send(bool $sendDestOnly=false):int{
        [$receiverMailbox, $receiveID] = $this->sendToReceiver();
        if(!$receiveID && !$sendDestOnly){
            return $sendID;
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
        if($newMsgOption !== null){
            $this->msgOption = $newMsgOption;
        }

        $this->msgOption['invalid'] = true;

        if($hideMsg){
            $this->validUntil = new \DateTime('2000-12-31');
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
            'valid_until'=>$this->validUntil->format('Y-m-d H:i:s'),
        ], 'id=%i', $this->id);

    }
}
