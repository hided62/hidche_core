<?php

namespace sammo\API\NationBetting;

use sammo\Session;
use DateTimeInterface;
use sammo\DB;
use sammo\DTO\BettingInfo;
use sammo\General;
use sammo\KVStorage;
use sammo\Validator;

use function sammo\increaseRefresh;

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
        return null;
    }

    public function getRequiredSessionMode(): int
    {
        return static::REQ_GAME_LOGIN | static::REQ_READ_ONLY;
    }

    public function launch(Session $session, ?DateTimeInterface $modifiedSince, ?string $reqEtag)
    {
        $db = DB::db();

        increaseRefresh("국가베팅장", 1);
        /** @var int */
        $bettingID = $this->arg['betting_id'];

        $gameStor = KVStorage::getStorage($db, 'game_env');
        $bettingStor = KVStorage::getStorage($db, 'betting');
        $rawBettingInfo = $bettingStor->getValue("id_{$bettingID}");
        if($rawBettingInfo === null){
            return '해당 베팅이 없습니다';
        }

        try{
            $bettingInfo = new BettingInfo($rawBettingInfo);
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
            $bettingDetail[] = [$bettingType, $amount];
        }

        [$year, $month] = $gameStor->getValuesAsArray(['year', 'month']);

        $myBetting = [];
        foreach($db->queryAllLists(
            'SELECT betting_type, sum(amount) as sum_amount FROM ng_betting WHERE betting_id = %i AND user_id = %i GROUP BY betting_type',
            $bettingID, $session->userID
        ) as [$bettingType, $amount]){
            $myBetting[] = [$bettingType, $amount];
        }

        $general = General::createGeneralObjFromDB($session->generalID, ['gold', 'aux'], 1);

        if($bettingInfo->reqInheritancePoint){
            $remainPoint = $general->getInheritancePoint('previous');
        }
        else{
            $remainPoint = $general->getVar('gold');
        }

        return [
            'result' => false,
            'bettingInfo' => $rawBettingInfo,
            'bettingDetail' => $bettingDetail,
            'myBetting' => $myBetting,
            'remainPoint' => $remainPoint,
            'year' => $year,
            'month' => $month,
        ];
    }
}
