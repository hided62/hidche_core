<?php

namespace sammo\API\NationBetting;

use sammo\Session;
use DateTimeInterface;
use sammo\DB;
use Sammo\DTO\BettingItem;
use sammo\Validator;
use sammo\Json;
use sammo\DTO\NationBettingInfo;
use sammo\GameConst;
use sammo\General;
use sammo\KVStorage;
use sammo\Util;

use function sammo\getAllNationStaticInfo;

class BetNation extends \sammo\BaseAPI
{
    public function validateArgs(): ?string
    {
        $v = new Validator($this->args);
        $v->rule('required', [
            'betting_id',
            'betting_type',
            'amount'
        ])
            ->rule('integer', 'betting_id')
            ->rule('integerArray', 'betting_type')
            ->rule('integer', 'amount')
            ->rule('min', 'amount', 1);

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

        /** @var int */
        $bettingID = $this->arg['betting_id'];
        /** @var int[] */
        $bettingType = $this->arg['betting_type'];
        /** @var int[] */
        $amount = $this->args['amount'];

        $gameStor = KVStorage::getStorage($db, 'game_env');
        $nationBettingStor = KVStorage::getStorage($db, 'nation_betting');
        $rawBettingInfo = $nationBettingStor->getValue("id_{$bettingID}");
        if($rawBettingInfo === null){
            return '해당 베팅이 없습니다';
        }

        try{
            $bettingInfo = new NationBettingInfo($rawBettingInfo);
        }
        catch(\Error $e){
            return $e->getMessage();
        }

        if($bettingInfo->finished){
            return '이미 종료된 베팅입니다';
        }

        [$year, $month] = $gameStor->getValuesAsArray(['year', 'month']);
        $yearMonth = Util::joinYearMonth($year, $month);


        if($bettingInfo->closeYearMonth > $yearMonth){
            return '이미 마감된 베팅입니다';
        }

        if($bettingInfo->openYearMonth > $yearMonth){
            return '아직 시작되지 않은 베팅입니다';
        }

        if(count($bettingType) != $bettingInfo->selectCnt){
            return '필요한 선택 수를 채우지 못했습니다.';
        }


        sort($bettingType);//NOTE: key로 바로 사용하므로 중요함
        $bettingTypeKey = Json::encode($bettingType);
        $nations = getAllNationStaticInfo();

        foreach($bettingType as $bettingNationID){
            if(!key_exists($bettingNationID, $nations)){
                return '존재하지 않는 국가를 선택했습니다.';
            }
        }

        $general = General::createGeneralObjFromDB($session->generalID, ['gold', 'aux'], 1);

        if($bettingInfo->reqInheritancePoint){
            if($general->getInheritancePoint('previous') < $amount){
                return '유산포인트가 충분하지 않습니다.';
            }
        }
        else {
            if($general->getVar('gold') < GameConst::$generalMinimumGold + $amount){
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

        if($bettingInfo->reqInheritancePoint){
            $general->increaseInheritancePoint('previous', -$amount);
        }
        else{
            $general->increaseVar('gold', -$amount);
        }
        $db->insertUpdate('ng_betting', $bettingItem->toArray());
        if(!$db->affected_rows){
            $general->flushUpdateValues();
            return '베팅을 실패했습니다.';
        }
        $general->applyDB($db);

        return [
            'result'=>true
        ];
    }
}
