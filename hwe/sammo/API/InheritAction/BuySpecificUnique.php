<?php

namespace sammo\API\InheritAction;

use sammo\Session;
use DateTimeInterface;
use sammo\DB;
use sammo\Enums\RankColumn;
use sammo\GameConst;
use sammo\General;
use sammo\KVStorage;
use sammo\UserLogger;
use sammo\Validator;

use function sammo\buildItemClass;

class BuySpecificUnique extends \sammo\BaseAPI
{
    public function validateArgs(): ?string
    {
        $availableItems = [];
        foreach (GameConst::$allItems as $items) {
            foreach ($items as $itemKey => $amount) {
                if ($amount == 0) {
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
            ->rule('min', 'amount', GameConst::$inheritItemUniqueMinPoint)
            ->rule('keyExists', 'item', $availableItems);

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
        if ($userID != $general->getVar('owner')) {
            return '로그인 상태가 이상합니다. 다시 로그인해 주세요.';
        }

        $itemTrials = $general->getAuxVar('inheritUniqueTrial') ?? [];
        if (key_exists($itemKey, $itemTrials)) {
            return '이미 입찰한 아이템입니다. 다음 턴에 시도해 주세요.';
        }

        foreach(GameConst::$allItems as $itemType => $items){
            if(!key_exists($itemKey, $items)){
                continue;
            }

            $prevItem = $general->getItem($itemType);
            if(!$prevItem->isBuyable()){
                return '이미 같은 자리에 유니크를 보유하고 있습니다.';
            }

            break;
        }

        $db = DB::db();
        $inheritStor = KVStorage::getStorage($db, "inheritance_{$userID}");
        $trialStor = KVStorage::getStorage($db, "ut_{$itemKey}");
        $previousPoint = ($inheritStor->getValue('previous') ?? [0, 0])[0];
        if ($previousPoint < $amount) {
            return '충분한 유산 포인트를 가지고 있지 않습니다.';
        }

        $itemObj = buildItemClass($itemKey);
        $userLogger = new UserLogger($userID);
        $userLogger->push("{$amount} 포인트로 유니크 {$itemObj->getName()} 구입 시도", "inheritPoint");
        $userLogger->flush();

        $itemTrials[$itemKey] = $amount;
        $general->setAuxVar('inheritUniqueTrial', $itemTrials);
        $inheritStor->setValue('previous', [$previousPoint - $amount, null]);
        $general->increaseRankVar(RankColumn::inherit_point_spent_dynamic, $amount);
        $trialStor->setValue("u{$userID}", [$userID, $generalID, $amount]);
        $general->applyDB($db);
        return null;
    }
}
