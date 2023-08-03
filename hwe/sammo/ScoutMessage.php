<?php

namespace sammo;

use sammo\Enums\MessageType;

class ScoutMessage extends Message
{

    const ACCEPTED = 1;
    const DECLINED = -1;
    const INVALID = 0;
    protected $validScout = true;

    public function __construct(
        MessageType $msgType,
        MessageTarget $src,
        MessageTarget $dest,
        string $msg,
        \DateTime $date,
        \DateTime $validUntil,
        array $msgOption
    ) {
        if ($msgType !== MessageType::private) {
            throw new \InvalidArgumentException('DiplomaticMessage msgType');
        }

        if (Util::array_get($msgOption['action']) !== 'scout') {
            throw new \InvalidArgumentException('Action !== scout');
        }

        parent::__construct(...func_get_args());

        if (Util::array_get($msgOption['used'])) {
            $this->validScout = false;
        }

        if ($this->validUntil <= new \DateTime()) {
            $this->validScout = false;
        }
    }

    protected function checkScoutMessageValidation(int $receiverID)
    {
        if (!$this->validScout) {
            return [self::INVALID, '유효하지 않은 등용장입니다.'];
        }
        if ($this->mailbox !== $this->dest->generalID) {
            return [self::INVALID, '송신자가 등용장을 처리할 수 없습니다.'];
        }

        if ($this->mailbox !== $receiverID) {
            return [self::INVALID, '올바른 수신자가 아닙니다.'];
        }

        return [self::ACCEPTED, '성공'];
    }

    /**
     * @return int 수행 결과 반환, ACCEPTED(등용장 소모), DECLINED(등용장 소모), INVALID 중 반환
     */
    public function agreeMessage(int $receiverID, string &$reason): int
    {
        //NOTE: 올바른 유저가 agreeMessage() 호출을 한건지는 외부에서 체크 필요(Session->userID 등)

        if (!$this->id) {
            throw new \RuntimeException('전송되지 않은 메시지에 수락 진행 중');
        }

        $gameStor = KVStorage::getStorage(DB::db(), 'game_env');
        $general = \sammo\General::createObjFromDB($receiverID);

        $logger = $general->getLogger();

        list($result, $reason) = $this->checkScoutMessageValidation($receiverID);

        if ($result !== self::ACCEPTED) {
            $logger->pushGeneralActionLog("{$reason} 등용 수락 불가.");
            if ($result === self::DECLINED) {
                $this->_declineMessage();
            }
            return $result;
        }

        $commandObj = buildGeneralCommandClass('che_등용수락', $general, $gameStor->getAll(true), [
            'destNationID' => $this->src->nationID,
            'destGeneralID' => $this->src->generalID,
        ]);

        if (!$commandObj->hasFullConditionMet()) {
            $logger->pushGeneralActionLog($commandObj->getFailString());
            $reason = $commandObj->getFailString();
            return self::DECLINED;
        }

        $commandObj->run(NoRNG::rngInstance());
        $commandObj->setNextAvailable();

        //메시지 비 활성화
        $this->msgOption['used'] = true;
        $this->invalidate();
        static::invalidateAll($this->src->generalID, $this->id);
        $this->validScout = false;

        $josaRo = JosaUtil::pick($this->src->nationName, '로');
        $newMsg = new Message(
            MessageType::private,
            $this->src,
            $this->dest,
            "{$this->src->nationName}{$josaRo} 등용 제의 수락",
            new \DateTime(),
            new \DateTime('9999-12-31'),
            [
                'delete' => $this->id
            ]
        );
        $newMsg->send(true);

        return self::ACCEPTED;
    }

    public static function invalidateAll(int $generalID, ?int $exceptMsgID = null)
    {
        $db = DB::db();
        $now = TimeUtil::now();
        //XXX: 뭔가 기존 쿼리가 애매하다. invalid 관련해서 다른 옵션이 가능한가?
        $rawMsgList = Util::convertArrayToDict($db->query(
            'SELECT * FROM `message` WHERE
            `mailbox` = %i AND `type` = "private" AND `dest` = `mailbox` AND `valid_until` > %s AND
            JSON_VALUE(message, "$.option.action") = %s',
            $generalID,
            $now,
            'scout',
        ), 'id');
        if ($exceptMsgID && key_exists($exceptMsgID, $rawMsgList)) {
            unset($rawMsgList[$exceptMsgID]);
        }
        if (!$rawMsgList) {
            return;
        }
        foreach ($rawMsgList as $rawMsg) {
            $msg = static::buildFromArray($rawMsg);
            $msg->invalidate();
        }
    }

    protected function _declineMessage()
    {
        $this->msgOption['used'] = true;
        $this->invalidate();
        $this->validScout = false;

        $josaRo = JosaUtil::pick($this->src->nationName, '로');
        $newMsg = new Message(
            MessageType::private,
            $this->src,
            $this->dest,
            "{$this->src->nationName}{$josaRo} 등용 제의 거부",
            new \DateTime(),
            new \DateTime('9999-12-31'),
            [
                'delete' => $this->id
            ]
        );
        $newMsg->send(true);

        return self::DECLINED;
    }

    public function declineMessage(int $receiverID, string &$reason): int
    {
        if (!$this->id) {
            throw new \RuntimeException('전송되지 않은 메시지에 거절 진행 중');
        }

        $db = DB::db();
        $gameStor = KVStorage::getStorage($db, 'game_env');
        [$year, $month] = $gameStor->getValuesAsArray(['year', 'month']);

        list($result, $reason) = $this->checkScoutMessageValidation($receiverID);

        if ($result === self::INVALID) {
            (new ActionLogger($receiverID, 0, $year, $month))->pushGeneralActionLog("{$reason} 등용 취소 불가.", ActionLogger::PLAIN);
            return $result;
        }

        $josaRo = JosaUtil::pick($this->src->nationName, '로');
        $josaYi = JosaUtil::pick($this->dest->generalName, '이');
        (new ActionLogger($receiverID, 0, $year, $month))->pushGeneralActionLog("{$this->src->nationName}</>{$josaRo} 망명을 거부했습니다.", ActionLogger::PLAIN);
        (new ActionLogger($this->src->generalID, 0, $year, $month))->pushGeneralActionLog("<Y>{$this->dest->generalName}</>{$josaYi} 등용을 거부했습니다.", ActionLogger::PLAIN);
        $this->_declineMessage();

        return self::DECLINED;
    }

    public static function buildScoutMessage(int $srcGeneralID, int $destGeneralID, &$reason = null, \DateTime $date = null): ?self
    {
        if ($srcGeneralID == $destGeneralID) {
            if ($reason !== null) {
                $reason = '같은 장수에게 등용장을 보낼 수 없습니다';
            }
            return null;
        }

        $db = DB::db();
        $srcGeneral = $db->queryFirstRow('SELECT `name`, nation FROM general WHERE `no`=%i', $srcGeneralID);
        $destGeneral = $db->queryFirstRow('SELECT `name`, nation, `officer_level` FROM general WHERE `no`=%i', $destGeneralID);
        if ($date === null) {
            $date = new \DateTime();
        }

        if ($destGeneral['officer_level'] == 12) {
            if ($reason !== null) {
                $reason = '군주에게 등용장을 보낼 수 없습니다';
            }
            return null;
        }

        if (!$srcGeneral['nation']) {
            if ($reason !== null) {
                $reason = '재야 상태일 때에는 등용장을 보낼 수 없습니다';
            }
            return null;
        }

        if ($srcGeneral['nation'] === $destGeneral['nation']) {
            if ($reason !== null) {
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
            $destNationInfo['name'],
            $destNationInfo['color']
        );

        $josaRo = JosaUtil::pick($src->nationName, '로');
        $msg = "{$src->nationName}{$josaRo} 망명 권유 서신";
        $validUntil = new \DateTime("9999-12-31 12:59:59");

        $msgOption = [
            'action' => 'scout'
        ];

        return new ScoutMessage(MessageType::private, $src, $dest, $msg, $date, $validUntil, $msgOption);
    }
}
