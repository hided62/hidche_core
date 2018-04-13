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
    }

    /**
     * @return int 수행 결과 반환, ACCEPTED(등용장 소모), DECLINED(등용장 소모), INVALID 중 반환
     */
    public function agreeMessage(string &$reason=null):int{
        //NOTE: 올바른 유저가 agreeMessage() 호출을 한건지는 외부에서 체크 필요(Session->userID 등)

        if(!$this->validScout){
            if($reason !== null){
                $reason = '이미 사용한 등용장입니다';
            }
            return self::INVALID;
        }
        if($this->mailbox !== $this->dest->generalID){
            if($reason !== null){
                $reason = '송신자가 등용장을 수락할 수 없습니다';
            }
            return self::INVALID;
        }


        $db = DB::db();
        $general = $db->queryFirstRow('SELECT nation, `no`, city, nations FROM general WHERE `no`=%i', $this->dest->generalID);

    }

    public static function buildScoutMessage(int $srcGeneralID, int $destGeneralID, &$reason = null, \DateTime $date = null): Message{
        if($srcGeneralID == $destGeneralID){
            if($reason !== null){
                $reason = '같은 장수에게 등용장을 보낼 수 없습니다';
            }
            return null;
        }

        $db = DB::db();
        $srcGeneral = $db->queryFirstRow('SELECT nation FROM nation WHERE `no`=%i', $srcGeneralID);
        $destGeneral = $db->queryFirstRow('SELECT nation, `level` FROM nation WHERE `no`=%i', $destGeneralID);
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
            $srcGeneral['nation'], 
            $srcNationInfo['name'], 
            $srcNationInfo['color']
        );

        $dest = new MessageTarget(
            $destGeneralID, 
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