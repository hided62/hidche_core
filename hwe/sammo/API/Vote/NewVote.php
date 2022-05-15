<?php

namespace sammo\API\Vote;

use DateTimeInterface;
use sammo\DB;
use sammo\DTO\VoteInfo;
use sammo\KVStorage;
use sammo\Session;
use sammo\Util;
use sammo\Validator;

class NewVote extends \sammo\BaseAPI
{
    function getRequiredSessionMode(): int
    {
        return static::REQ_GAME_LOGIN;
    }

    function validateArgs(): ?string
    {
        $v = new Validator($this->args);
        $v->rule('required', [
            'title',
        ])->rule('lengthMin', 'title', 1)
            ->rule('int', 'multipleOptions')
            ->rule('date', 'endDate')
            ->rule('stringArray', 'options')
            ->rule('boolean', 'keepOldVote');

        if (!$v->validate()) {
            return $v->errorStr();
        }
        return null;
    }

    function closeOldVote(int $voteID, KVStorage $voteStor)
    {
        $db = DB::db();
        $voteStor = KVStorage::getStorage($db, 'vote');
        $rawLastVoteInfo = $voteStor->getValue("vote_{$voteID}");
        if (!$rawLastVoteInfo) {
            return;
        }
        $lastVoteInfo = new VoteInfo(...$rawLastVoteInfo);
        if ($lastVoteInfo->endDate) {
            return;
        }

        $lastVoteInfo->endDate = new \DateTimeImmutable();;
        $voteStor->setValue("vote_{$voteID}", $lastVoteInfo->toArray());
    }

    function launch(Session $session, ?DateTimeInterface $modifiedSince, ?string $reqEtag)
    {
        $isVoteAdmin = in_array('vote', $session->acl[DB::prefix()] ?? []);
        $isVoteAdmin = $isVoteAdmin || $session->userGrade >= 5;

        if (!$isVoteAdmin) {
            return "권한이 부족합니다.";
        }

        /** @var string */
        $title = $this->args['title'];
        /** @var int */
        $multipleOptions = $this->args['multipleOptions'] ?? 1;
        if ($multipleOptions < 0) {
            $multipleOptions = 0;
        }


        $now = new \DateTimeImmutable();
        $endDate = $this->args['endDate'] ?? null;
        /** @var string[] */
        $options = $this->args['options'] ?? [];

        $db = DB::db();
        $gameStor = KVStorage::getStorage($db, 'game_env');


        $lastVote = $gameStor->getValue('lastVote') ?? 0;
        $voteID = $lastVote + 1;

        $voteStor = KVStorage::getStorage($db, 'vote');

        if (!($this->args['keepOldVote'] ?? false)) {
            $this->closeOldVote($lastVote, $voteStor);
        }

        $multipleOptions = Util::valueFit($multipleOptions, 0, count($options));

        $voteInfo = new VoteInfo(
            id: $voteID,
            title: $title,
            multipleOptions: $multipleOptions,
            startDate: $now,
            endDate: $endDate,
            options: $options,
        );

        $voteStor->setValue("vote_{$voteID}", $voteInfo->toArray());
        $db->update('general', [
            'newvote' => 1
        ], true);

        return [
            'result' => true
        ];
    }
}
