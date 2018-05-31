<?php
namespace sammo;

class DiplomaticMessage extends Message{

    const ACCEPTED = 1;
    const DECLINED = -1;
    const INVALID = 0;

    
    const TYPE_NO_AGGRESSION = 'noAggression'; //불가침
    const TYPE_CANCEL_NA = 'cancelNA'; //불가침 파기
    const TYPE_STOP_WAR = 'stopWar'; //종전
    const TYPE_MERGE = 'merge'; //통합
    const TYPE_SURRENDER = 'surrender'; //항복

    protected $diplomaticType = '';
    protected $diplomacyName = '';
    protected $diplomacyDetail = '';
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
        switch($this->diplomaticType){
            case self::TYPE_NO_AGGRESSION: $this->diplomacyName = '불가침'; break;
            case self::TYPE_CANCEL_NA: $this->diplomacyName = '불가침 파기'; break;
            case self::TYPE_STOP_WAR: $this->diplomacyName = '종전'; break;
            case self::TYPE_MERGE: $this->diplomacyName = '통합'; break;
            case self::TYPE_SURRENDER: $this->diplomacyName = '투항'; break;
            default: throw \RuntimeException('diplomaticType이 올바르지 않음');
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

        if(!$general || $general['level'] < 5){
            return [self::INVALID, '해당 국가의 수뇌가 아닙니다.'];
        }

        return [self::ACCEPTED, ''];
    }

    protected function noAggression(){
        $year = Util::array_get($this->msgOption['year']);
        $option = Util::array_get($this->msgOption['option'])??'';
        if($year < 1 || $year > 30){
            return [self::INVALID, '올바르지 않은 불가침 서신입니다.'];
        }

        $this->diplomacyDetail = "{$year}년";

        $helper = new Engine\Diplomacy($this->src->nationID, $this->dest->nationID);
        $chk = $helper->noAggression($year, $option);
        if($chk[0] !== self::ACCEPTED){
            return $chk;
        }

        $josa = JosaUtil::pick($this->dest->nationName, '와');
        $youlog[] = "<C>●</><D><b>{$this->dest->nationName}</b></>{$josa} <C>$year</>년 불가침에 성공했습니다.";

        $josa = JosaUtil::pick($this->src->nationName, '와');
        $mylog[] = "<C>●</><D><b>{$this->src->nationName}</b></>{$josa} <C>$year</>년 불가침에 합의했습니다.";
        pushGenLog(['no'=>$this->dest->generalID], $mylog);
        pushGenLog(['no'=>$this->src->generalID], $youlog);
        $josaWa = JosaUtil::pick($this->src->nationName, '와');
        pushGeneralHistory(['no'=>$this->src->generalID], "<C>●</>{$helper->year}년 {$helper->month}월:<D><b>{$this->dest->nationName}</b></>{$josaWa} {$year}년 불가침 성공");
        $josaWa = JosaUtil::pick($this->dest->nationName, '와');
        pushGeneralHistory(['no'=>$this->dest->generalID], "<C>●</>{$helper->year}년 {$helper->month}월:<D><b>{$this->src->nationName}</b></>{$josaWa} {$year}년 불가침 수락");

        return $chk;
    }

    protected function cancelNA(){
        $helper = new Engine\Diplomacy($this->src->nationID, $this->dest->nationID);
        $chk = $helper->cancelNA();
        if($chk[0] !== self::ACCEPTED){
            return $chk;
        }

        $josaYi = JosaUtil::pick($this->dest->generalName, '이');
        $josaWa = JosaUtil::pick($this->src->nationName, '와');
        $alllog[] = "<C>●</>{$helper->month}월:<Y>{$this->dest->generalName}</>{$josaYi} <D><b>{$this->src->nationName}</b></>{$josaWa} <M>조약 파기</>에 합의.";

        $josaYi = JosaUtil::pick($this->dest->nationName, '이');
        $josaWa = JosaUtil::pick($this->src->nationName, '와');
        $history[] = "<C>●</>{$helper->year}년 {$helper->month}월:<Y><b>【파기】</b></><D><b>{$this->dest->nationName}</b></>{$josaYi} <D><b>{$this->src->nationName}</b></>{$josaWa} 불가침을 파기 하였습니다.";
        
        $josaWa = JosaUtil::pick($this->dest->nationName, '와');
        $youlog[] = "<C>●</><D><b>{$this->dest->nationName}</b></>{$josaWa} 파기에 성공했습니다.";

        $josaWa = JosaUtil::pick($this->src->nationName, '와');
        $mylog[] = "<C>●</><D><b>{$this->src->nationName}</b></>{$josaWa} 파기에 합의했습니다.";

        $josaWa = JosaUtil::pick($this->dest->nationName, '와');
        pushGeneralHistory(['no'=>$this->src->generalID], "<C>●</>{$helper->year}년 {$helper->month}월:<D><b>{$this->dest->nationName}</b></>{$josaWa} 파기 성공");

        $josaWa = JosaUtil::pick($this->src->nationName, '와');
        pushGeneralHistory(['no'=>$this->dest->generalID], "<C>●</>{$helper->year}년 {$helper->month}월:<D><b>{$this->src->nationName}</b></>{$josaWa} 파기 수락");

        pushGenLog(['no'=>$this->dest->generalID], $mylog);
        pushGenLog(['no'=>$this->src->generalID], $youlog);
        pushGeneralPublicRecord($alllog, $helper->year, $helper->month);
        pushWorldHistory($history, $helper->year, $helper->month);

        return $chk;
    }

    protected function stopWar(){
        $helper = new Engine\Diplomacy($this->src->nationID, $this->dest->nationID);
        $chk = $helper->stopWar();
        if($chk[0] !== self::ACCEPTED){
            return $chk;
        }

        $josaYi = JosaUtil::pick($this->dest->generalName, '이');
        $josaWa = JosaUtil::pick($this->src->nationName, '와');
        $alllog[] = "<C>●</>{$helper->month}월:<Y>{$this->dest->generalName}</>{$josaYi} <D><b>{$this->src->nationName}</b></>{$josaWa} <M>종전 합의</> 하였습니다.";

        $josaYi = JosaUtil::pick($this->dest->nationName, '이');
        $josaWa = JosaUtil::pick($this->src->nationName, '와');
        $history[] = "<C>●</>{$helper->year}년 {$helper->month}월:<Y><b>【종전】</b></><D><b>{$this->dest->nationName}</b></>{$josaYi} <D><b>{$this->src->nationName}</b></>{$josaWa} <M>종전 합의</> 하였습니다.";

        $josaWa = JosaUtil::pick($this->dest->nationName, '와');
        $youlog[] = "<C>●</><D><b>{$this->dest->nationName}</b></>{$josaWa} 종전에 성공했습니다.";

        $josaWa = JosaUtil::pick($this->src->nationName, '와');
        $mylog[] = "<C>●</><D><b>{$this->src->nationName}</b></>{$josaWa} 종전에 합의했습니다.";

        $josaWa = JosaUtil::pick($this->dest->nationName, '와');
        pushGeneralHistory(['no'=>$this->src->generalID], "<C>●</>{$helper->year}년 {$helper->month}월:<D><b>{$this->dest->nationName}</b></>{$josaWa} 종전 성공");

        $josaWa = JosaUtil::pick($this->src->nationName, '와');
        pushGeneralHistory(['no'=>$this->dest->generalID], "<C>●</>{$helper->year}년 {$helper->month}월:<D><b>{$this->src->nationName}</b></>{$josaWa} 종전 수락");

        pushGenLog(['no'=>$this->dest->generalID], $mylog);
        pushGenLog(['no'=>$this->src->generalID], $youlog);
        pushGeneralPublicRecord($alllog, $helper->year, $helper->month);
        pushWorldHistory($history, $helper->year, $helper->month);

        return $chk;
    }

    protected function acceptMerge(){
        $helper = new Engine\Diplomacy($this->src->nationID, $this->dest->nationID);
        $chk = $helper->acceptMerge($this->src->generalID, $this->dest->generalID);
        if($chk[0] !== self::ACCEPTED){
            return $chk;
        }

        $josaWa = JosaUtil::pick($this->src->nationName, '와');
        pushGeneralHistory(
            ['no'=>$this->dest->generalID],
            ["<C>●</>{$helper->year}년 {$helper->month}월:<D><b>{$this->src->nationName}</b></>{$josaWa} 통합 시도"]
        );
        pushGenLog(
            ['no'=>$this->dest->generalID],
            ["<C>●</><D><b>{$this->src->nationName}</b></>{$josaWa} 통합에 동의했습니다."]
        );

        $josaWa = JosaUtil::pick($this->src->nationName, '와');
        $josaYi = JosaUtil::pick($this->dest->generalName, '이');
        pushGeneralPublicRecord(
            ["<C>●</>{$helper->month}월:<Y>{$this->dest->generalName}</>{$josaYi} <D><b>{$this->src->nationName}</b></>{$josaWa} <M>통합</>에 동의하였습니다."],
            $helper->year,
            $helper->month);
            $josaYi = JosaUtil::pick($this->src->generalName, '이');


        $josaWa = JosaUtil::pick($this->dest->nationName, '와');
        pushGeneralHistory(
            ['no'=>$this->src->generalID],
            ["<C>●</>{$helper->year}년 {$helper->month}월:<D><b>{$this->dest->nationName}</b></>{$josaWa} 통합 시도"]
        );
        $josaYi = JosaUtil::pick($this->dest->nationName, '이');
        pushGenLog(
            ['no'=>$this->src->generalID],
            ["<C>●</><D><b>{$this->dest->nationName}</b></>{$josaYi} 통합에 동의했습니다."]
        );
        pushWorldHistory(
            ["<C>●</>{$helper->year}년 {$helper->month}월:<Y><b>【통합시도】</b></><D><b>{$this->dest->nationName}</b></>{$josaWa}과 <D><b>{$this->src->nationName}</b></>{$josaYi} 통합을 시도합니다."],
            $helper->year, 
            $helper->month
        );

        return $chk;
    }

    protected function acceptSurrender(){
        $helper = new Engine\Diplomacy($this->src->nationID, $this->dest->nationID);
        $chk = $helper->acceptSurrender($this->src->generalID, $this->dest->generalID);
        if($chk[0] !== self::ACCEPTED){
            return $chk;
        }

        $josaRo = JosaUtil::pick($this->src->nationName, '로');
        pushGeneralHistory(
            ['no'=>$this->src->generalID], 
            "<C>●</>{$helper->year}년 {$helper->month}월:<D><b>{$this->dest->nationName}</b></>에게 투항 제의"
        );
        pushGeneralHistory(
            ['no'=>$this->dest->generalID],
            "<C>●</>{$helper->year}년 {$helper->month}월:<D><b>{$this->src->nationName}</b></>{$josaRo} 투항 동의"
        );
        pushGenLog(
            ['no'=>$this->dest->generalID], 
            ["<C>●</><D><b>{$this->src->nationName}</b></>{$josaRo} 투항에 동의했습니다."]
        );
        $josaYi = JosaUtil::pick($this->dest->nationName, '이');
        pushGenLog(
            ['no'=>$this->src->generalID], 
            ["<C>●</><D><b>{$this->dest->nationName}</b></>{$josaYi} 투항에 동의했습니다."]
        );
        $josaYi = JosaUtil::pick($this->dest->generalName, '이');
        pushGeneralPublicRecord(
            ["<C>●</>{$helper->month}월:<Y>{$this->dest->generalName}</>{$josaYi} <D><b>{$this->src->nationName}</b></>{$josaRo} <M>투항</>에 동의하였습니다."], 
            $helper->year, 
            $helper->month);
        $josaYi = JosaUtil::pick($this->dest->nationName, '이');
        pushWorldHistory(
            ["<C>●</>{$helper->year}년 {$helper->month}월:<Y><b>【투항시도】</b></><D><b>{$this->dest->nationName}</b></>{$josaYi} <D><b>{$this->src->nationName}</b></>{$josaRo} 투항합니다."], 
            $helper->year, 
            $helper->month
        );

        return $chk;
    }

    /**
     * @return int 수행 결과 반환, ACCEPTED(등용장 소모), DECLINED(등용장 소모), INVALID 중 반환
     */
    public function agreeMessage(int $receiverID, string &$reason):int{
        //NOTE: 올바른 유저가 agreeMessage() 호출을 한건지는 외부에서 체크 필요(Session->userID 등)

        if(!$this->id){
            throw \RuntimeException('전송되지 않은 메시지에 수락 진행 중');
        }

        

        $db = DB::db();
        $gameStor = KVStorage::getStorage($db, 'game_env');

        $general = $db->queryFirstRow(
            'SELECT `name`, `level` FROM general WHERE `no`=%i AND nation=%i', 
            $receiverID, 
            $this->dest->nationID
        );

        if($general){
            $this->dest->generalID = $receiverID;
            $this->dest->generalName = $general['name'];
        }
        

        list($result, $reason) = $this->checkDiplomaticMessageValidation($general);
        $db->update('diplomacy', [
            'reserved'=>'',
            'showing'=>null
        ], '(me=%s AND you=%s) OR (you=%s AND me=%s)',
            $this->src->nationID, $this->dest->nationID,
            $this->src->nationID, $this->dest->nationID
        );
        if($result !== self::ACCEPTED){
            pushGenLog(['no'=>$receiverID], ["<C>●</>{$reason} {$this->diplomacyName} 실패."]);
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
            case self::TYPE_MERGE:
                list($result, $reason) = $this->acceptMerge();
                break;
            case self::TYPE_SURRENDER:
                list($result, $reason) = $this->acceptSurrender();
                break;
            default: 
                throw \RuntimeException('diplomaticType이 올바르지 않음');
        }

        if($result !== self::ACCEPTED){
            pushGenLog(['no'=>$receiverID], ["<C>●</>{$reason} {$this->diplomacyName} 실패."]);
            if($result === self::DECLINED){
                $this->_declineMessage();
            }
            return $result;
        }
        
        list($year, $month) = $gameStor->getValuesAsArray(['year', 'month']);


        $this->dest->generalID = $receiverID;
        $this->dest->generalName = $general['name'];
        $this->msgOption['used'] = true;
        $this->invalidate();
        $this->validDiplomacy = false;

        $josaYi = JosaUtil::pick($this->src->nationName, '이');
        $newMsg = new Message(
            self::MSGTYPE_NATIONAL, 
            $this->dest, 
            $this->src, 
            "【외교】{$year}년 {$month}월: {$this->src->nationName}{$josaYi} {$this->dest->nationName}에게 제안한 {$this->diplomacyDetail}{$this->diplomacyName} 동의.",
            new \DateTime(),
            new \DateTime('9999-12-31'),
            [
                'delete'=>$this->id
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
            throw \RuntimeException('전송되지 않은 메시지에 거절 진행 중');
        }

        $db = DB::db();
        $general = $db->queryFirstRow(
            'SELECT `name`, `level` FROM general WHERE `no`=%i AND nation=%i', 
            $receiverID, 
            $this->dest->nationID
        );
        list($result, $reason) = $this->checkDiplomaticMessageValidation($general);

        if($result === self::INVALID){
            pushGenLog(['no'=>$receiverID], ["<C>●</>{$reason} {$this->diplomacyName} 거절 불가."]);
            return $result;
        }

        $josaYi = JosaUtil::pick($this->dest->nationName, '이');
        pushGenLog(['no'=>$receiverID], "<C>●</><D>{$this->src->nationName}</>의 {$this->diplomacyName} 제안을 거절했습니다.");
        pushGenLog(['no'=>$this->src->generalID], "<C>●</><Y>{$this->dest->nationName}</>{$josaYi} {$this->diplomacyName} 제안을 거절했습니다.");
        $this->_declineMessage();  
        return self::DECLINED;
    }

}