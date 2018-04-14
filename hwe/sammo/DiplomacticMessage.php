<?php
namespace sammo;

class DiplomacticMessage extends Message{

    const ACCEPTED = 1;
    const DECLINED = -1;
    const INVALID = 0;

    const TYPE_SURRENDER = 'surrender'; //항복
    const TYPE_ALLY = 'ally'; //불가침
    const TYPE_MERGE = 'merge'; //통합
    const TYPE_CEASE = 'cease'; //종전
    const TYPE_CANCEL = 'cancel'; //불가침 파기
    

    protected $diplomaticType = '';
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
        if(array_search($this->diplomaticType, [
                self::TYPE_SURRENDER,
                self::TYPE_ALLY,
                self::TYPE_MERGE,
                self::TYPE_CEASE,
                self::TYPE_CANCEL
            ]) === false)
        {
            throw new \InvalidArgumentException('Invalid Diplomatic ActionType');
        }

        parent::__construct(...func_get_args());


        if(Util::array_get($msgOption['used'])){
            $this->validDiplomacy = false;
        }

        if($this->$validUntil < (new \DateTime())){
            $this->validDiplomacy = false;
        }
    }

    /**
     * @return int 수행 결과 반환, ACCEPTED(등용장 소모), DECLINED(등용장 소모), INVALID 중 반환
     */
    public function agreeMessage(int $receiverID, string &$reason=null):int{
        //NOTE: 올바른 유저가 agreeMessage() 호출을 한건지는 외부에서 체크 필요(Session->userID 등)

        if(!$this->validScout){
            if($reason !== null){
                $reason = '이미 사용한 외교서신입니다';
            }
            return self::INVALID;
        }
        if($this->mailbox !== $this->dest->nationID + static::MAILBOX_NATIONAL){
            if($reason !== null){
                $reason = '송신자가 외교서신을 수락할 수 없습니다';
            }
            return self::INVALID;
        }

        //거절하지 않았어야함.
        //수뇌여야함
        //방랑군이 아니어야함
        //상대도 방랑군이 아니어야함
        
        //불가침시 : 교전중이 아니어야함, 선포중이 아니어야함. 합병중이 아니어얗.
        //불가침 파기시 : 불가침 중이어야함.
        //종전시 : 교전중이거나 선포중이어야함.
        //합병시 : 양국 다 외교제한이 지나지 않았어야함. 국력, 장수수가 적절해야함. 인접한 국가여야함. 서로 교전중이어선 안됨.
        //        송신자가 선포, 전쟁중이어선 안됨. 송신자가 C국과 불가침인데 수신자가 C국과 전쟁중이면 안됨
        //항복시 :  양국 다 외교제한이 지나지 않았어야함. 국력, 장수수가 적절해야함. 인접한 국가여야함. 서로 교전중이어선 안됨.
        //        송신자가 선포, 전쟁중이어선 안됨. 송신자가 C국과 불가침인데 수신자가 C국과 전쟁중이면 안됨


        $db = DB::db();
        $general = $db->queryFirstRow('SELECT nation, `no`, city, `level` FROM general WHERE `no`=%i', $receiverID);


    }

    public function declineMessage(string &$reason=null):int{

    }

}