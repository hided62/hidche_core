<?php

namespace sammo\API\Message;

use sammo\Session;
use DateTimeInterface;
use sammo\DB;
use sammo\Enums\MessageType;
use sammo\Json;
use sammo\Message;
use sammo\TimeUtil;
use sammo\Util;
use sammo\Validator;

use function sammo\checkSecretPermission;

class GetOldMessage extends \sammo\BaseAPI
{
  public function validateArgs(): ?string
  {
    $v = new Validator($this->args);
    $v->rule('required', [
      'to',
      'type',
    ])->rule('integer', 'to')
      ->rule('in', 'type', ['private', 'public', 'national', 'diplomacy']);
    if (!$v->validate()) {
      return $v->errorStr();
    }
    $this->args['to'] = (int)($this->args['to'] ?? 0);
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

  public function launch(Session $session, ?DateTimeInterface $modifiedSince, ?string $reqEtag)
  {
    $this->delayFrequentCall($session);
    $session->setReadOnly();
    $userID = $session->getUserID();



    $db = DB::db();
    $me = $db->queryFirstRow('SELECT `no`,`name`,`nation`,`officer_level`,`con`,`picture`,`imgsvr`,penalty,permission FROM general WHERE `owner`=%i', $userID);

    if ($me === null) {
      return '장수가 사망했습니다.';
    }

    [$generalID, $nationID, $generalName] = [$me['no'], $me['nation'], $me['name']];
    $permission = checkSecretPermission($me, false);
    $reqTo = $this->args['to'];

    $result = [
      MessageType::private->value => [],
      MessageType::public->value => [],
      MessageType::national->value => [],
      MessageType::diplomacy->value => [],
      'result' => true,
      'keepRecent' => true,
      'sequence' => 0,
      'nationID' => $nationID,
      'generalName' => $generalName,
    ];
    $result['result'] = true;

    $nextSequence = $reqTo;
    $reqType = MessageType::tryFrom($this->args['type']);
    if ($reqType === null) {
      return '잘못된 요청입니다.';
    }

    if ($reqType === MessageType::private) {
      $result[MessageType::private->value] = array_map(function (Message $msg) use (&$nextSequence) {
        if ($msg->id > $nextSequence) {
          $nextSequence = $msg->id;
        }
        return $msg->toArray();
      }, Message::getMessagesFromMailBoxOld($generalID, MessageType::private, $reqTo, 15));
    } else if ($reqType === MessageType::public) {
      $result[MessageType::public->value] = array_map(function (Message $msg) use (&$nextSequence) {
        if ($msg->id > $nextSequence) {
          $nextSequence = $msg->id;
        }
        return $msg->toArray();
      }, Message::getMessagesFromMailBoxOld(Message::MAILBOX_PUBLIC, MessageType::public, $reqTo, 15));
    } else if ($reqType === MessageType::national) {
      $result[MessageType::national->value] = array_map(function (Message $msg) use (&$nextSequence) {
        if ($msg->id > $nextSequence) {
          $nextSequence = $msg->id;
        }
        return $msg->toArray();
      }, Message::getMessagesFromMailBoxOld(Message::MAILBOX_NATIONAL + $nationID, MessageType::national, $reqTo, 15));
    } else {
      $result[MessageType::diplomacy->value] = array_map(function (Message $msg) use (&$nextSequence, $permission) {
        if ($msg->id > $nextSequence) {
          $nextSequence = $msg->id;
        }
        $values = $msg->toArray();
        if ($msg->dest->nationID != 0 && $permission < 3) {
          $values['text'] = '(외교 메시지입니다)'; //TODO: 외교서신이라 읽을 수 없음을 보여줘야함
          $values['option']['invalid'] = true;
        }
        return $values;
      }, Message::getMessagesFromMailBoxOld(Message::MAILBOX_NATIONAL + $nationID, MessageType::diplomacy, $reqTo, 15));
    }

    return $result;
  }
}
