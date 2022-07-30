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
use sammo\UserLogger;
use sammo\Util;

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
            ->rule('int', 'bettingID')
            ->rule('integerArray', 'bettingType')
            ->rule('int', 'amount')
            ->rule('min', 'amount', 10);

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
        /** @var int */
        $bettingID = $this->args['bettingID'];
        /** @var int[] */
        $bettingType = $this->args['bettingType'];
        /** @var int */
        $amount = $this->args['amount'];

        $bettingHelper = new Betting($bettingID);
        try{
            $bettingHelper->bet($session->generalID, $session->userID, $bettingType, $amount);
        }
        catch(\Throwable $e){
            return $e->getMessage();
        }

        return [
            'result' => true
        ];
    }
}
