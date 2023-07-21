<?php

namespace sammo\API\Vote;

use DateTimeInterface;
use sammo\DB;
use sammo\DTO\VoteInfo;
use sammo\Enums\APIRecoveryType;
use sammo\Enums\GeneralQueryMode;
use sammo\General;
use sammo\Json;
use sammo\KVStorage;
use sammo\LiteHashDRBG;
use sammo\RandUtil;
use sammo\Session;
use sammo\UniqueConst;
use sammo\Util;
use sammo\Validator;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Lock\Store\SemaphoreStore;

use function sammo\tryUniqueItemLottery;

class Vote extends \sammo\BaseAPI
{
    function getRequiredSessionMode(): int
    {
        return static::REQ_GAME_LOGIN | static::REQ_READ_ONLY;
    }

    function validateArgs(): ?string
    {
        $v = new Validator($this->args);
        $v->rule('required', [
            'voteID',
            'selection'
        ])->rule('lengthMin', 'title', 1)
            ->rule('int', 'voteID')
            ->rule('integerArray', 'seletion');

        if (!$v->validate()) {
            return $v->errorStr();
        }
        return null;
    }

    function launch(Session $session, ?DateTimeInterface $modifiedSince, ?string $reqEtag): null | string | array | APIRecoveryType
    {

        $voteID = $this->args['voteID'];
        /** @var int[] */
        $selection = $this->args['selection'];
        if (!$selection) {
            return '선택한 항목이 없습니다.';
        }
        $db = DB::db();
        $voteStor = KVStorage::getStorage($db, 'vote');

        $rawVoteInfo = $voteStor->getValue("vote_{$voteID}");
        if (!$rawVoteInfo) {
            return '설문조사가 없습니다.';
        }
        $voteInfo = VoteInfo::fromArray($rawVoteInfo);

        if ($voteInfo->endDate && $voteInfo->endDate < new \DateTimeImmutable()) {
            return '설문조사가 종료되었습니다.';
        }

        if ($voteInfo->multipleOptions >= 1 && count($selection) > $voteInfo->multipleOptions) {
            return '선택한 항목이 너무 많습니다.';
        }

        $optionsCnt = count($voteInfo->options);
        foreach ($selection as $sel) {
            if ($sel >= $optionsCnt) {
                return '선택한 항목이 없습니다.';
            }
        }

        sort($selection, SORT_NUMERIC);

        $userID = $session->userID;
        $generalID = $session->generalID;

        $serverPrefix = DB::prefix();
        $lockFactory = new LockFactory(new SemaphoreStore());

        $nationID = $db->query('SELECT nation FROM general WHERE no = %i', $generalID);


        $lock = $lockFactory->createLock("api_{$serverPrefix}_{$userID}");
        $lock->acquire(true);


        $db->insertIgnore('vote', [
            'vote_id' => $voteID,
            'general_id' => $generalID,
            'nation_id' => $nationID,
            'selection' => Json::encode($selection),
        ]);

        if ($db->affectedRows() == 0) {
            return '이미 설문조사를 완료하였습니다.';
        }

        $gameStor = KVStorage::getStorage($db, 'game_env');
        $voteReward = $gameStor->getValue('develcost') * 5;

        $general = General::createGeneralObjFromDB($generalID, null, GeneralQueryMode::Full);
        $general->increaseVar('gold', $voteReward);
        $uniqueRng = new RandUtil(new LiteHashDRBG(Util::simpleSerialize(
            UniqueConst::$hiddenSeed,
            'voteUnique',
            $voteID,
            $generalID
        )));
        $wonLottery = tryUniqueItemLottery($uniqueRng, $general, '설문조사');

        $general->applyDB($db);

        return [
            'result' => true,
            'wonLottery' => $wonLottery,
        ];
    }
}
