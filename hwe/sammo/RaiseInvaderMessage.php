<?php

namespace sammo;

use DateTime;
use sammo\Enums\MessageType;
use sammo\Event\Action\RaiseInvader;

class RaiseInvaderMessage extends Message
{

    const ACCEPTED = 1;
    const DECLINED = -1;
    const INVALID = 0;
    protected $valid = true;

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
            throw new \InvalidArgumentException('private msgType');
        }

        if (Util::array_get($msgOption['action']) !== 'raiseInvader') {
            throw new \InvalidArgumentException('Action !== raiseInvader');
        }

        parent::__construct(...func_get_args());

        if (Util::array_get($msgOption['used'])) {
            $this->valid = false;
        }
    }

    protected function checkMessageValidation(int $receiverID)
    {
        if (!$this->valid) {
            return [self::INVALID, '이미 사용하였습니다.'];
        }
        if ($this->mailbox !== $this->dest->generalID) {
            return [self::INVALID, '송신자가 메시지를 처리할 수 없습니다.'];
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
        $general = \sammo\General::createGeneralObjFromDB($receiverID);

        $logger = $general->getLogger();

        [$result, $reason] = $this->checkMessageValidation($receiverID);


        if($result === self::ACCEPTED && $gameStor->isunited != 2){
            $result = self::INVALID;
            $reason = '천하통일이 되지 않았습니다.';
        }

        if ($result !== self::ACCEPTED) {
            if ($result === self::DECLINED) {
                $this->_declineMessage();
            }
            else{
                Message::sendPrivateMsgAsNotice($this->dest, "{$reason} 이민족 등장 불가.");
            }
            return $result;
        }

        [$npcEachCount, $specAvg, $tech, $dex] = $this->msgOption['args'];

        $invaderAction = new RaiseInvader(
            $npcEachCount,
            $specAvg,
            $tech,
            $dex
        );

        $invaderAction->run($gameStor->getAll());

        return self::ACCEPTED;
    }

    protected function _declineMessage()
    {
        $this->msgOption['used'] = true;
        $this->invalidate();
        $this->valid = false;

        return self::DECLINED;
    }

    public function declineMessage(int $receiverID, string &$reason): int
    {
        if (!$this->id) {
            throw new \RuntimeException('전송되지 않은 메시지에 거절 진행 중');
        }

        $db = DB::db();
        $gameStor = KVStorage::getStorage($db, 'game_env');

        [$result, $reason] = $this->checkMessageValidation($receiverID);

        if ($result === self::INVALID) {
            Message::sendPrivateMsgAsNotice($this->dest, "{$reason} 취소 불가.");
            return $result;
        }
        $this->_declineMessage();

        return self::DECLINED;
    }

    /**
     *
     * @param int $destGeneralID
     * @param DateTime|null $date
     * @return RaiseInvaderMessage[]
     */
    public static function buildRaiseInvaderMessage(int $destGeneralID, \DateTime $date = null): array
    {
        $srcTarget = MessageTarget::buildSystemTarget();
        $destTarget = MessageTarget::buildQuick($destGeneralID);
        if ($date === null) {
            $date = new \DateTime();
        }

        /**
         * @var [[float,float,float,float],string][] $argsList
         */
        $argsList = [
            [[-2, -1.2, 15000, -1], '어려움'],
            [[-2, -1.2, -1, -0.5], '보통'],
            [[-1, -1, -0.8, 0], '쉬움'],
        ];
        //XXX: 난이도 설정을 어디서 해야하는가?

        $msgList = [];
        foreach($argsList as [$args, $difficulty]){
            $msg = new self(
                MessageType::private,
                $srcTarget,
                $destTarget,
                "이벤트 게임으로 이민족[{$difficulty}]을 소환",
                $date,
                new \DateTime('9999-12-31'),
                [
                    'action' => 'raiseInvader',
                    'args' => $args,
                    'used' => false
                ]
            );

            $msgList[] = $msg;
        }
        return $msgList;
    }
}
