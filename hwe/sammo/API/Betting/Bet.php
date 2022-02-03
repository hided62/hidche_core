<?php

namespace sammo\API\Betting;

use sammo\Session;
use DateTimeInterface;
use sammo\Betting;
use sammo\DB;
use sammo\DTO\BettingItem;
use sammo\Validator;
use sammo\GameConst;
use sammo\KVStorage;
use sammo\Util;

use function sammo\getAllNationStaticInfo;

class Bet extends \sammo\BaseAPI
{
    public function validateArgs(): ?string
    {
        $v = new Validator($this->args);
        $v->rule('required', [
            'bettingID',
            'bettingType',
            'amount'
        ])
            ->rule('integer', 'bettingID')
            ->rule('integerArray', 'bettingType')
            ->rule('integer', 'amount')
            ->rule('min', 'amount', 1);

        if (!$v->validate()) {
            return $v->errorStr();
        }
        return null;
    }

    public function getRequiredSessionMode(): int
    {
        return static::REQ_GAME_LOGIN;
    }

    public function launch(Session $session, ?DateTimeInterface $modifiedSince, ?string $reqEtag)
    {
        $db = DB::db();

        /** @var int */
        $bettingID = $this->args['bettingID'];
        /** @var int[] */
        $bettingType = $this->args['bettingType'];
        /** @var int */
        $amount = $this->args['amount'];

        $gameStor = KVStorage::getStorage($db, 'game_env');

        $bettingHelper = new Betting($bettingID);
        $bettingInfo = $bettingHelper->getInfo();

        if($bettingInfo->finished){
            return '이미 종료된 베팅입니다';
        }

        [$year, $month] = $gameStor->getValuesAsArray(['year', 'month']);
        $yearMonth = Util::joinYearMonth($year, $month);


        if($bettingInfo->closeYearMonth <= $yearMonth){
            return '이미 마감된 베팅입니다';
        }

        if($bettingInfo->openYearMonth > $yearMonth){
            return '아직 시작되지 않은 베팅입니다';
        }

        if(count($bettingType) != $bettingInfo->selectCnt){
            return '필요한 선택 수를 채우지 못했습니다.';
        }

        $bettingTypeKey = $bettingHelper->convertBettingKey($bettingType);

        $inheritStor = KVStorage::getStorage($db, "inheritance_{$session->userID}");

        $prevBetAmount = $db->queryFirstField('SELECT sum(amount) FROM ng_betting WHERE betting_id = %i AND user_id = %i', $bettingID, $session->userID) ?? 0;

        if($prevBetAmount + $amount > 1000){
            return (1000 - $prevBetAmount).' 포인트까지만 베팅 가능합니다.';
        }

        if($bettingInfo->reqInheritancePoint){
            $remainPoint = ($inheritStor->getValue('previous') ?? [0,0])[0];
            if($remainPoint < $amount){
                return '유산포인트가 충분하지 않습니다.';
            }
        }
        else {
            $remainPoint = $db->queryFirstField('SELECT gold FROM general WHERE no = %i', $session->generalID)??0;
            if($remainPoint < GameConst::$generalMinimumGold + $amount){
                return '금이 부족합니다.';
            }
        }

        $userID = $session->userID;

        $bettingItem = new BettingItem([
            'betting_id'=>$bettingID,
            'general_id'=>$session->generalID,
            'user_id'=>$userID,
            'betting_type'=>$bettingTypeKey,
            'amount'=>$amount
        ]);

        $db->insertUpdate(
            'ng_betting',
            $bettingItem->toArray(),
            ['amount' => $db->sqleval('amount + %i', $amount)]
        );        if($bettingInfo->reqInheritancePoint){
            $inheritStor->setValue('previous', [$remainPoint - $amount, null]);
        }
        else{
            $db->update('general', [
                'gold' => $db->sqleval('gold - %i', $amount)
            ], 'no = %i', $session->generalID);
        }
        if(!$db->affected_rows){
            return '베팅을 실패했습니다.';
        }

        return [
            'result'=>true
        ];
    }
}
