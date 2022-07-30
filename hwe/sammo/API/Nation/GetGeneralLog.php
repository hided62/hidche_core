<?php

namespace sammo\API\Nation;

use sammo\DB;
use sammo\Session;
use sammo\Validator;

use function sammo\checkLimit;
use function sammo\checkSecretPermission;
use function sammo\getBattleDetailLogMore;
use function sammo\getBattleDetailLogRecent;
use function sammo\getBattleResultMore;
use function sammo\getBattleResultRecent;
use function sammo\getGeneralActionLogMore;
use function sammo\getGeneralActionLogRecent;
use function sammo\getGeneralHistoryLogWithLogID;

class GetGeneralLog extends \sammo\BaseAPI
{
    const GENERAL_HISTORY = 'generalHistory';
    const GENERAL_ACTION = 'generalAction';
    const BATTLE_RESULT = 'battleResult';
    const BATTLE_DETAIL = 'battleDetail';

    public function validateArgs(): ?string
    {
        $v = new Validator($this->args);
        $v
            ->rule('required', [
                'generalID',
                'reqType',
            ])
            ->rule('integer', [
                'generalID',
                'reqTo',
            ])
            ->rule('in', 'reqType', [
                self::GENERAL_HISTORY,
                self::GENERAL_ACTION,
                self::BATTLE_RESULT,
                self::BATTLE_DETAIL,
            ]);

        if (!$v->validate()) {
            return $v->errorStr();
        }

        $this->args['generalID'] = (int)$this->args['generalID'];
        if (key_exists('reqTo', $this->args)) {
            $this->args['reqTo'] = (int)$this->args['reqTo'];
        }
        return null;
    }

    public function checkPermission(array $me): ?string
    {

        $permission = checkSecretPermission($me);
        if ($permission < 0) {
            return '국가에 소속되어있지 않습니다.';
        }

        $db = DB::db();
        $generalID = $this->args['generalID'];
        [$testGeneralNationID, $testGeneralNPCType] = $db->queryFirstList(
            'SELECT nation,npc FROM general WHERE no = %i',
            $generalID
        );

        if ($permission < 1) {
            return '권한이 부족합니다. 수뇌부가 아니거나 사관년도가 부족합니다.';
        }

        $nationID = $me['nation'];
        if ($testGeneralNationID !== $nationID) {
            return '같은 나라의 장수가 아닙니다.';
        }

        $reqType = $this->args['reqType'];
        if (
            $reqType === self::GENERAL_ACTION &&
            $testGeneralNPCType < 2 &&
            $generalID !== $me['no'] &&
            $permission < 2
        ) {
            return '권한이 부족합니다. 유저 장수의 개인 기록은 수뇌만 열람 가능합니다.';
        }

        return null;
    }

    public function getRequiredSessionMode(): int
    {
        return static::REQ_GAME_LOGIN | static::REQ_READ_ONLY;
    }

    public function getTargetGeneralID(Session $session): int{
        return $this->args['generalID'];
    }

    public function launch(Session $session, ?\DateTimeInterface $modifiedSince, ?string $reqEtag)
    {
        $userID = $session->userID;

        $targetGeneralID = $this->getTargetGeneralID($session);
        $reqType = $this->args['reqType'];
        $reqTo = $this->args['reqTo'] ?? null;

        $db = DB::db();
        $me = $db->queryFirstRow('SELECT no,nation,officer_level,con,turntime,belong,permission,penalty from general where owner=%i', $userID);

        $con = checkLimit($me['con']);
        if ($con >= 2) {
            return '접속 제한입니다.';
        }

        $permissionResult = $this->checkPermission($me);
        if ($permissionResult !== null) {
            return $permissionResult;
        }

        if ($reqType == static::GENERAL_HISTORY) {
            return [
                'result' => true,
                'reqType' => $reqType,
                'generalID' => $targetGeneralID,
                'log' => getGeneralHistoryLogWithLogID($targetGeneralID)
            ];
        }

        if ($reqType == static::GENERAL_ACTION) {
            return [
                'result' => true,
                'reqType' => $reqType,
                'generalID' => $targetGeneralID,
                'log' => $reqTo === null
                    ? getGeneralActionLogRecent($targetGeneralID, 30)
                    : getGeneralActionLogMore($targetGeneralID, $reqTo, 30)
            ];
        }

        if ($reqType == static::BATTLE_RESULT) {
            return [
                'result' => true,
                'reqType' => $reqType,
                'generalID' => $targetGeneralID,
                'log' => $reqTo === null
                    ? getBattleResultRecent($targetGeneralID, 30)
                    : getBattleResultMore($targetGeneralID, $reqTo, 30)
            ];
        }

        if ($reqType == static::BATTLE_DETAIL) {
            return [
                'result' => true,
                'reqType' => $reqType,
                'generalID' => $targetGeneralID,
                'log' => $reqTo === null
                    ? getBattleDetailLogRecent($targetGeneralID, 30)
                    : getBattleDetailLogMore($targetGeneralID, $reqTo, 30)
            ];
        }

        return '잘못된 요청입니다: ' . $reqType;
    }
}
