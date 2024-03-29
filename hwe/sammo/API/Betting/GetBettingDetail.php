<?php

namespace sammo\API\Betting;

use sammo\Session;
use DateTimeInterface;
use sammo\DB;
use sammo\DTO\BettingInfo;
use sammo\Enums\APIRecoveryType;
use sammo\KVStorage;
use sammo\Util;
use sammo\Validator;

class GetBettingDetail extends \sammo\BaseAPI
{
    public function validateArgs(): ?string
    {
        $v = new Validator($this->args);
        $v->rule('required', [
            'betting_id',
        ])
            ->rule('integer', 'betting_id');

        if (!$v->validate()) {
            return $v->errorStr();
        }
        $this->args['betting_id'] = (int)$this->args['betting_id'];
        return null;
    }

    public function getRequiredSessionMode(): int
    {
        return static::REQ_GAME_LOGIN | static::REQ_READ_ONLY;
    }

    public function launch(Session $session, ?DateTimeInterface $modifiedSince, ?string $reqEtag): null | string | array | APIRecoveryType
    {
        $db = DB::db();

        /** @var int */
        $bettingID = $this->args['betting_id'];

        $gameStor = KVStorage::getStorage($db, 'game_env');
        $bettingStor = KVStorage::getStorage($db, 'betting');
        $rawBettingInfo = $bettingStor->getValue("id_{$bettingID}");
        if($rawBettingInfo === null){
            return '해당 베팅이 없습니다';
        }

        try{
            $bettingInfo = BettingInfo::fromArray($rawBettingInfo);
        }
        catch(\Error $e){
            return $e->getMessage();
        }

        [$year, $month] = $gameStor->getValuesAsArray(['year', 'month']);

        $bettingDetail = [];

        foreach ($db->queryAllLists(
            'SELECT betting_type, sum(amount) as sum_amount FROM ng_betting WHERE betting_id = %i GROUP BY betting_type',
            $bettingID
        ) as [$bettingType, $amount]) {
            $bettingDetail[] = [$bettingType, Util::toInt($amount)];
        }

        [$year, $month] = $gameStor->getValuesAsArray(['year', 'month']);

        $myBetting = [];
        foreach($db->queryAllLists(
            'SELECT betting_type, sum(amount) as sum_amount FROM ng_betting WHERE betting_id = %i AND user_id = %i GROUP BY betting_type',
            $bettingID, $session->userID
        ) as [$bettingType, $amount]){
            $myBetting[] = [$bettingType, Util::toInt($amount)];
        }

        if($bettingInfo->reqInheritancePoint){
            $inheritStor = KVStorage::getStorage($db, "inheritance_{$session->userID}");
            $remainPoint = ($inheritStor->getValue('previous') ?? [0,0])[0];
        }
        else{
            $remainPoint = $db->queryFirstField('SELECT gold FROM general WHERE no = %i', $session->generalID)??0;
        }

        return [
            'result' => true,
            'bettingInfo' => $rawBettingInfo,
            'bettingDetail' => $bettingDetail,
            'myBetting' => $myBetting,
            'remainPoint' => $remainPoint,
            'year' => $year,
            'month' => $month,
        ];
    }
}
