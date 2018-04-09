<?php
namespace sammo;

class ScoutMessage extends Message{

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
        parent::__construct(...func_get_args());

        //TODO: 누가, 누구에게 보낸 건지 파싱
    }

    public static function generateScoutMessage(int $srcGeneralID, int $destGeneralID, &$reason = null, \DateTime $date = null): Message{
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

    public function send(){
        $this->sendToReceiver();
    }

}