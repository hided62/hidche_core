<?php

namespace sammo\API\Message;

use sammo\Session;
use DateTimeInterface;
use sammo\DB;
use sammo\Enums\APIRecoveryType;
use sammo\Enums\GeneralStorKey;
use sammo\Enums\MessageType;
use sammo\Json;
use sammo\KVStorage;
use sammo\Message;
use sammo\TimeUtil;
use sammo\Util;
use sammo\Validator;

use function sammo\checkSecretPermission;

class GetRecentMessage extends \sammo\BaseAPI
{
  public function validateArgs(): ?string
  {
    $v = new Validator($this->args);
    $v->rule('integer', 'sequence');
    if (!$v->validate()) {
      return $v->errorStr();
    }
    $this->args['sequence'] = (int)($this->args['sequence'] ?? -1);
    return null;
  }

  public function getRequiredSessionMode(): int
  {
    return static::REQ_GAME_LOGIN;
  }

  private function delayFrequentCall(Session $session): void
  {
    $lastMsgGet = Json::decode($session->lastMsgGet) ?? [];
    $now = new \DateTimeImmutable();
    $delayTime = false;

    if (count($lastMsgGet) >= 10) {
      try {
        if (!is_string($lastMsgGet[0])) {
          throw new \Exception('Why not string?');
        }
        $first = new \DateTimeImmutable($lastMsgGet[0]);
        $diffSec = TimeUtil::DateIntervalToSeconds($first->diff($now));
        if ($diffSec < 1) {
          $delayTime = true;
        }
        array_shift($lastMsgGet);
      } catch (\Exception $e) {
        $lastMsgGet = [];
      }
    }
    $lastMsgGet[] = TimeUtil::format($now, true);
    $session->lastMsgGet = Json::encode($lastMsgGet);

    if ($delayTime) {
      usleep(200);
    }
  }

  public function launch(Session $session, ?DateTimeInterface $modifiedSince, ?string $reqEtag): null | string | array | APIRecoveryType
  {
    $this->delayFrequentCall($session);
    $session->setReadOnly();
    $userID = $session->getUserID();



    $db = DB::db();
    $me = $db->queryFirstRow('SELECT `no`,`name`,`nation`,`officer_level`,`picture`,`imgsvr`,penalty,permission FROM general WHERE `owner`=%i', $userID);

    if ($me === null) {
      return '장수가 사망했습니다.';
    }

    [$generalID, $nationID, $generalName] = [$me['no'], $me['nation'], $me['name']];
    $permission = checkSecretPermission($me, false);
    $reqSequence = $this->args['sequence'];

    $result = [];
    $result['result'] = true;
    $nextSequence = $reqSequence;
    $minSequence = $reqSequence;
    $lastType = null;

    $result['private'] = array_map(function (Message $msg) use (&$nextSequence, &$minSequence, &$lastType) {
      if ($msg->id > $nextSequence) {
        $nextSequence = $msg->id;
      }
      if ($msg->id <= $minSequence) {
        $minSequence = $msg->id;
        $lastType = 'private';
      }
      return $msg->toArray();
    }, Message::getMessagesFromMailBox($generalID, MessageType::private, 15, $reqSequence));

    $result['public'] = array_map(function (Message $msg) use (&$nextSequence, &$minSequence, &$lastType) {
      if ($msg->id > $nextSequence) {
        $nextSequence = $msg->id;
      }
      if ($msg->id <= $minSequence) {
        $minSequence = $msg->id;
        $lastType = 'public';
      }
      return $msg->toArray();
    }, Message::getMessagesFromMailBox(Message::MAILBOX_PUBLIC, MessageType::public, 15, $reqSequence));

    $result['national'] = array_map(function (Message $msg) use (&$nextSequence, &$minSequence, &$lastType) {
      if ($msg->id > $nextSequence) {
        $nextSequence = $msg->id;
      }
      if ($msg->id <= $minSequence) {
        $minSequence = $msg->id;
        $lastType = 'national';
      }
      return $msg->toArray();
    }, Message::getMessagesFromMailBox(Message::MAILBOX_NATIONAL + $nationID, MessageType::national, 15, $reqSequence));

    $result['diplomacy'] = array_map(function (Message $msg) use (&$nextSequence, &$minSequence, &$lastType, $permission) {
      if ($msg->id > $nextSequence) {
        $nextSequence = $msg->id;
      }
      if ($msg->id <= $minSequence) {
        $minSequence = $msg->id;
        $lastType = 'diplomacy';
      }
      $values = $msg->toArray();
      if ($msg->dest->nationID != 0 && $permission < 3) {
        $values['text'] = '(외교 메시지입니다)'; //TODO: 외교서신이라 읽을 수 없음을 보여줘야함
        $values['option']['invalid'] = true;
      }
      return $values;
    }, Message::getMessagesFromMailBox(Message::MAILBOX_NATIONAL + $nationID, MessageType::diplomacy, 15, $reqSequence));

    if ($lastType !== null) {
      array_pop($result[$lastType]);
    }

    $generalStor = KVStorage::getStorage($db, "general_{$generalID}");
    [$latestReadDiplomacyMsg, $latestReadPrivateMsg] = $generalStor->getValuesAsArray([
      GeneralStorKey::latestReadDiplomacyMsg,
      GeneralStorKey::latestReadPrivateMsg
    ]);

    $result['sequence'] = $nextSequence;
    $result['nationID'] = $nationID;
    $result['generalName'] = $generalName;
    $result['latestRead'] = [
      'diplomacy' => $latestReadDiplomacyMsg ?? 0,
      'private' => $latestReadPrivateMsg ?? 0,
    ];

    return $result;
  }
}
