<?php

namespace sammo\API\InheritAction;

use sammo\Session;
use DateTimeInterface;
use sammo\DB;
use sammo\GameConst;
use sammo\General;
use sammo\KVStorage;
use sammo\Validator;

class BuySpecificUnique extends \sammo\BaseAPI
{
    public function validateArgs(): ?string
    {
        foreach(GameConst::$allItems as $items){
            foreach($items as $itemKey=>$amount){
                if($amount == 0){
                    continue;
                }
                $availableItems[$itemKey] = $amount;
            }
        }

        $v = new Validator($this->args);
        $v->rule('required', [
            'item',
            'amount',
        ])
            ->rule('integer', 'amount')
            ->rule('min', GameConst::$inheritItemUniqueMinPoint)
            ->rule('keyExists', $availableItems);

        if (!$v->validate()) {
            return $v->errorStr();
        }
        return null;
    }

    public function getRequiredSessionMode(): int
    {
        //KVStrorage, General.aux 모두 쓰므로 lock;
        return static::REQ_GAME_LOGIN;
    }

    public function launch(Session $session, ?DateTimeInterface $modifiedSince, ?string $reqEtag)
    {
        $itemKey = $this->args['item'];
        $amount = $this->args['amount'];

        $userID = $session->userID;
        $generalID = $session->generalID;

        $general = General::createGeneralObjFromDB($generalID);
        if($userID != $general->getVar('owner')){
            return '로그인 상태가 이상합니다. 다시 로그인해 주세요.';
        }

        $itemTrials = $general->getAuxVar('inheritUniqueTrial') ?? [];
        if(key_exists($itemKey, $itemTrials)){
            return '이미 입찰한 아이템입니다. 다음 턴에 시도해 주세요.';
        }

        $db = DB::db();
        $inheritStor = KVStorage::getStorage($db, "inheritance_{$userID}");
        $trialStor = KVStorage::getStorage($db, "ut_{$itemKey}");
        $previousPoint = $inheritStor->getValue('previous')??0;
        if($previousPoint < $amount){
            return '충분한 유산 포인트를 가지고 있지 않습니다.';
        }

        $itemTrials[$itemKey] = $amount;
        $general->setAuxVar('inheritUniqueTrial', $itemTrials);
        $inheritStor->setValue('previous', $previousPoint - $amount);
        $trialStor->setValue("u{$userID}", [$userID, $generalID, $amount]);
        $general->flushUpdateValues();
        return null;
    }
}
