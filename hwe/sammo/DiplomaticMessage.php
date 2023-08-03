<?php
namespace sammo;

use sammo\Enums\GeneralQueryMode;
use sammo\Enums\MessageType;

class DiplomaticMessage extends Message{

    const ACCEPTED = 1;
    const DECLINED = -1;
    const INVALID = 0;


    const TYPE_NO_AGGRESSION = 'noAggression'; //불가침
    const TYPE_CANCEL_NA = 'cancelNA'; //불가침 파기
    const TYPE_STOP_WAR = 'stopWar'; //종전

    protected $diplomaticType = '';
    protected $diplomacyName = '';
    protected $diplomacyDetail = '';
    protected $validDiplomacy = true;

    public function __construct(
        MessageType $msgType,
        MessageTarget $src,
        MessageTarget $dest,
        string $msg,
        \DateTime $date,
        \DateTime $validUntil,
        array $msgOption
    )
    {
        if ($msgType !== MessageType::diplomacy){
            throw new \InvalidArgumentException('DiplomaticMessage msgType');
        }

        $this->diplomaticType = Util::array_get($msgOption['action']);
        switch($this->diplomaticType){
            case self::TYPE_NO_AGGRESSION: $this->diplomacyName = '불가침'; break;
            case self::TYPE_CANCEL_NA: $this->diplomacyName = '불가침 파기'; break;
            case self::TYPE_STOP_WAR: $this->diplomacyName = '종전'; break;
            default: throw new \RuntimeException('diplomaticType이 올바르지 않음');
        }

        parent::__construct(...func_get_args());


        if(Util::array_get($msgOption['used'])){
            $this->validDiplomacy = false;
        }

        if($this->validUntil < (new \DateTime())){
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

        $permission = checkSecretPermission($general, false);

        if(!$general || $permission < 4){
            return [self::INVALID, '해당 국가의 외교권자가 아닙니다.'];
        }

        return [self::ACCEPTED, ''];
    }

    protected function noAggression(){

        $gameStor = KVStorage::getStorage(DB::db(), 'game_env');

        $destGeneralObj = General::createObjFromDB($this->dest->generalID);

        $commandObj = buildNationCommandClass('che_불가침수락', $destGeneralObj, $gameStor->getAll(true), new LastTurn(), [
            'destNationID'=>$this->src->nationID,
            'destGeneralID'=>$this->src->generalID,
            'year'=>$this->msgOption['year'],
            'month'=>$this->msgOption['month']
        ]);

        $this->diplomacyDetail = $commandObj->getBrief();

        if(!$commandObj->hasFullConditionMet()){
            return [self::INVALID, $commandObj->getFailString()];
        }

        $commandObj->run(NoRNG::rngInstance());
        $commandObj->setNextAvailable();

        return [self::ACCEPTED, ''];
    }

    protected function cancelNA(){
        $gameStor = KVStorage::getStorage(DB::db(), 'game_env');

        $destGeneralObj = General::createObjFromDB($this->dest->generalID);

        $commandObj = buildNationCommandClass('che_불가침파기수락', $destGeneralObj, $gameStor->getAll(true), new LastTurn(), [
            'destNationID'=>$this->src->nationID,
            'destGeneralID'=>$this->src->generalID,
        ]);

        $this->diplomacyDetail = $commandObj->getBrief();

        if(!$commandObj->hasFullConditionMet()){
            return [self::INVALID, $commandObj->getFailString()];
        }

        $commandObj->run(NoRNG::rngInstance());
        $commandObj->setNextAvailable();

        return [self::ACCEPTED, ''];
    }

    protected function stopWar(){
        $gameStor = KVStorage::getStorage(DB::db(), 'game_env');

        $destGeneralObj = General::createObjFromDB($this->dest->generalID);

        $commandObj = buildNationCommandClass('che_종전수락', $destGeneralObj, $gameStor->getAll(true), new LastTurn(), [
            'destNationID'=>$this->src->nationID,
            'destGeneralID'=>$this->src->generalID,
        ]);

        $this->diplomacyDetail = $commandObj->getBrief();

        if(!$commandObj->hasFullConditionMet()){
            return [self::INVALID, $commandObj->getFailString()];
        }

        $commandObj->run(NoRNG::rngInstance());
        $commandObj->setNextAvailable();

        return [self::ACCEPTED, ''];
    }

    /**
     * @return int 수행 결과 반환, ACCEPTED(등용장 소모), DECLINED(등용장 소모), INVALID 중 반환
     */
    public function agreeMessage(int $receiverID, string &$reason):int{
        //NOTE: 올바른 유저가 agreeMessage() 호출을 한건지는 외부에서 체크 필요(Session->userID 등)

        if(!$this->id){
            throw new \RuntimeException('전송되지 않은 메시지에 수락 진행 중');
        }



        $db = DB::db();
        $gameStor = KVStorage::getStorage($db, 'game_env');
        [$year, $month] = $gameStor->getValuesAsArray(['year', 'month']);


        $general = $db->queryFirstRow(
            'SELECT `name`, nation, `officer_level`, `permission`, `penalty`,belong FROM general WHERE `no`=%i AND nation=%i',
            $receiverID,
            $this->dest->nationID
        );

        if($general){
            $this->dest->generalID = $receiverID;
            $this->dest->generalName = $general['name'];
        }


        list($result, $reason) = $this->checkDiplomaticMessageValidation($general);
        if($result !== self::ACCEPTED){
            (new ActionLogger($receiverID, 0, $year, $month))->pushGeneralActionLog("{$reason} {$this->diplomacyName} 실패", ActionLogger::PLAIN);
            if($result === self::DECLINED){
                $this->_declineMessage();
            }
            return $result;
        }

        switch($this->diplomaticType){
            case self::TYPE_NO_AGGRESSION:
                list($result, $reason) = $this->noAggression();
                break;
            case self::TYPE_CANCEL_NA:
                list($result, $reason) = $this->cancelNA();
                break;
            case self::TYPE_STOP_WAR:
                list($result, $reason) = $this->stopWar();
                break;
            default:
                throw new \RuntimeException('diplomaticType이 올바르지 않음');
        }

        if($result !== self::ACCEPTED){
            (new ActionLogger($receiverID, 0, $year, $month))->pushGeneralActionLog($reason, ActionLogger::PLAIN);
            if($result === self::DECLINED){
                $this->_declineMessage();
            }
            return $result;
        }




        $this->dest->generalID = $receiverID;
        $this->dest->generalName = $general['name'];
        $this->msgOption['used'] = true;
        $this->validDiplomacy = false;

        $josaYi = JosaUtil::pick($this->src->nationName, '이');
        $newMsg = new Message(
            MessageType::national,
            $this->dest,
            $this->src,
            "【외교】{$year}년 {$month}월: {$this->src->nationName}{$josaYi} {$this->dest->nationName}에게 제안한 {$this->diplomacyDetail}",
            new \DateTime(),
            new \DateTime('9999-12-31'),
            [
                'delete'=>$this->id,
                'silence'=>true,
                'deletable' => false
            ]
        );
        $this->invalidate();
        $newMsg->send();

        $newMsg = new Message(
            MessageType::diplomacy,
            $this->dest,
            $this->src,
            "【외교】{$year}년 {$month}월: {$this->src->nationName}{$josaYi} {$this->dest->nationName}에게 제안한 {$this->diplomacyDetail}",
            new \DateTime(),
            new \DateTime('9999-12-31'),
            [
                'delete'=>$this->id,
                'silence'=>true,
                'deletable' => false
            ]
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

    public function declineMessage(int $receiverID, string &$reason):int{
        if(!$this->id){
            throw new \RuntimeException('전송되지 않은 메시지에 거절 진행 중');
        }

        $db = DB::db();
        $gameStor = KVStorage::getStorage($db, 'game_env');
        [$year, $month] = $gameStor->getValuesAsArray(['year', 'month']);

        $general = $db->queryFirstRow(
            'SELECT `name`, nation, `officer_level`, `permission`, `penalty`,belong  FROM general WHERE `no`=%i AND nation=%i',
            $receiverID,
            $this->dest->nationID
        );
        list($result, $reason) = $this->checkDiplomaticMessageValidation($general);

        if($result === self::INVALID){
            (new ActionLogger($receiverID, 0, $year, $month))->pushGeneralActionLog("{$reason} {$this->diplomacyName} 거절 불가.", ActionLogger::PLAIN);
            return $result;
        }

        $josaYi = JosaUtil::pick($this->dest->nationName, '이');
        (new ActionLogger($receiverID, 0, $year, $month))->pushGeneralActionLog("<D>{$this->src->nationName}</>의 {$this->diplomacyName} 제안을 거절했습니다.", ActionLogger::PLAIN);
        (new ActionLogger($this->src->generalID, 0, $year, $month))->pushGeneralActionLog("<Y>{$this->dest->nationName}</>{$josaYi} {$this->diplomacyName} 제안을 거절했습니다.", ActionLogger::PLAIN);
        $this->_declineMessage();
        return self::DECLINED;
    }

}