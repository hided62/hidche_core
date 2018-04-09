<?php
namespace sammo;

class DiplomacticMessage extends Message{

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
        parent::__construct(...func_get_args());

        //TODO: 누가, 누구에게 보낸 건지 파싱
    }

    public function send(){
        parent::send();
    }

}