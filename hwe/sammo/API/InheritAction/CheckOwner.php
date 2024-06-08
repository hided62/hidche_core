<?php

namespace sammo\API\InheritAction;

use sammo\Session;
use DateTimeInterface;
use sammo\DB;
use sammo\Enums\APIRecoveryType;
use sammo\Enums\MessageType;
use sammo\Enums\RankColumn;
use sammo\GameConst;
use sammo\General;
use sammo\KVStorage;
use sammo\Message;
use sammo\MessageTarget;
use sammo\RootDB;
use sammo\TimeUtil;
use sammo\UserLogger;
use sammo\Validator;

use function sammo\GetImageURL;
use function sammo\getNationStaticInfo;

/**
 *
 * 유산 포인트 1000 포인트를 사용하면 지정한 상대의 본래 유저명을 확인 가능.
 * 개인 메시지로 전달되며, 이 기능이 사용되었음을 상대에게도 알림.
 */
class CheckOwner extends \sammo\BaseAPI
{
    public function validateArgs(): ?string
    {
        $v = new Validator($this->args);
        $v->rule('required', [
            'destGeneralID',
        ])
            ->rule('int', 'destGeneralID')
            ->rule('min', 'destGeneralID', 1);

        if (!$v->validate()) {
            return $v->errorStr();
        }

        return null;
    }

    public function getRequiredSessionMode(): int
    {
        return static::REQ_GAME_LOGIN;
    }

    public function launch(Session $session, ?DateTimeInterface $modifiedSince, ?string $reqEtag): null|string|array|APIRecoveryType
    {
        $userID = $session->userID;
        $generalID = $session->generalID;

        $destGeneralID = $this->args['destGeneralID'];
        if ($generalID == $destGeneralID) {
            return '자신의 정보는 확인할 수 없습니다.';
        }

        $general = General::createObjFromDB($generalID);
        if ($userID != $general->getVar('owner')) {
            return '로그인 상태가 이상합니다. 다시 로그인해 주세요.';
        }

        $db = DB::db();
        $destRawGeneral = $db->queryFirstRow('SELECT no,name,nation,owner,owner_name,imgsvr,picture FROM general WHERE no = %i', $destGeneralID);

        if (!$destRawGeneral) {
            return '대상 장수가 존재하지 않습니다.';
        }

        if (!$destRawGeneral['owner']) {
            return '대상 장수는 NPC입니다.';
        }

        $gameStor = KVStorage::getStorage($db, 'game_env');
        if ($gameStor->isunited) {
            return '이미 천하가 통일되었습니다.';
        }

        $reqPoint = GameConst::$inheritCheckOwnerPoint;

        $inheritStor = KVStorage::getStorage($db, "inheritance_{$userID}");
        $previousPoint = ($inheritStor->getValue('previous') ?? [0, 0])[0];
        if ($previousPoint < $reqPoint) {
            return '충분한 유산 포인트를 가지고 있지 않습니다.';
        }

        $userLogger = new UserLogger($userID);
        $userLogger->push("{$reqPoint} 포인트로 장수 소유자 확인", "inheritPoint");
        $userLogger->flush();

        $destGeneralName = $destRawGeneral['name'];
        $destGeneralOwnerName = $destRawGeneral['owner_name'];
        if (!$destGeneralOwnerName) {
            $rootDB = RootDB::db();
            $destGeneralOwnerName = $rootDB->queryFirstField('SELECT name FROM member WHERE no = %i', $destRawGeneral['owner']) ?? '알수없음';
        }

        $src = new MessageTarget(0, '', 0, 'System', '#000000');

        if (true) {
            $staticNation = $general->getStaticNation();
            $dest = new MessageTarget(
                $generalID,
                $general->getName(),
                $general->getNationID(),
                $staticNation['name'],
                $staticNation['color'],
                GetImageURL($general->getVar('imgsvr'), $general->getVar('picture'))
            );
            $msg = new Message(
                MessageType::private,
                $src,
                $dest,
                "{$destGeneralName}의 소유자는 {$destGeneralOwnerName} 입니다.",
                new \DateTime(),
                new \DateTime('9999-12-31'),
                []
            );
            $msg->send(true);
        }

        $inheritStor->setValue('previous', [$previousPoint - $reqPoint, null]);
        $general->increaseRankVar(RankColumn::inherit_point_spent_dynamic, $reqPoint);
        $general->applyDB($db);

        if(true){
            $destStaticNation = getNationStaticInfo($destRawGeneral['nation']);
            $dest = new MessageTarget(
                $destGeneralID,
                $destGeneralName,
                $destRawGeneral['nation'],
                $destStaticNation['name'],
                $destStaticNation['color'],
                GetImageURL($destRawGeneral['imgsvr'], $destRawGeneral['picture'])
            );
            $msg = new Message(
                MessageType::private,
                $src,
                $dest,
                "소유자명이 누군가에 의해 확인되었습니다.",
                new \DateTime(),
                new \DateTime('9999-12-31'),
                []
            );
            $msg->send(true);
        }

        return null;
    }
}
