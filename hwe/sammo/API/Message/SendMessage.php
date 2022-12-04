<?php

namespace sammo\API\Message;

use sammo\Session;
use DateTimeInterface;
use sammo\DB;
use sammo\Enums\MessageType;
use sammo\Message;
use sammo\MessageTarget;
use sammo\Validator;

use function sammo\checkLimit;
use function sammo\checkSecretPermission;
use function sammo\getBlockLevel;
use function sammo\GetImageURL;
use function sammo\getNationStaticInfo;
use function sammo\increaseRefresh;

class SendMessage extends \sammo\BaseAPI
{
  public function validateArgs(): ?string
  {
    $v = new Validator($this->args);
    $v->rule('required', ['mailbox', 'text']);
    $v->rule('integer', 'mailbox');
    $v->rule('lengthMin', 'text', 1);
    if (!$v->validate()) {
      return $v->errorStr();
    }
    $this->args['mailbox'] = (int)($this->args['mailbox'] ?? Message::MAILBOX_PUBLIC);

    return null;
  }

  public function getRequiredSessionMode(): int
  {
    return static::REQ_GAME_LOGIN;
  }

  private function genPublicMessage(MessageTarget $src, string $text): Message
  {
    $now = new \DateTime();
    $unlimited = new \DateTime('9999-12-31');

    $msg = new Message(
      MessageType::public,
      $src,
      $src,
      $text,
      $now,
      $unlimited,
      []
    );

    return $msg;
  }

  private function genNationalMessage(MessageTarget $src, string $text): Message
  {
    $now = new \DateTime();
    $unlimited = new \DateTime('9999-12-31');

    $dest = new MessageTarget(0, '', $src->nationID, $src->nationName, $src->color);

    $msg = new Message(
      MessageType::national,
      $src,
      $dest,
      $text,
      $now,
      $unlimited,
      []
    );

    return $msg;
  }

  private function genDiplomacyMessage(MessageTarget $src, int $destNationID, string $text): Message|string
  {
    $now = new \DateTime();
    $unlimited = new \DateTime('9999-12-31');

    $destNation = getNationStaticInfo($destNationID);

    if ($destNation === null) {
      return '존재하지 않는 국가입니다.';
    }

    $dest = new MessageTarget(0, '', $destNation['nation'], $destNation['name'], $destNation['color']);

    $msg = new Message(
      MessageType::diplomacy,
      $src,
      $dest,
      $text,
      $now,
      $unlimited,
      []
    );
    return $msg;
  }

  private function genPrivateMessage(MessageTarget $src, int $destGeneralID, int $permission, string $text): Message|string
  {
    $now = new \DateTime();
    $unlimited = new \DateTime('9999-12-31');

    $db = DB::db();
    $destUser = $db->queryFirstRow('SELECT `no`,`name`,`nation`,`officer_level`,`con`,`picture`,`imgsvr`,permission,penalty FROM general WHERE `no`=%i', $destGeneralID);

    if (!$destUser) {
      return '존재하지 않는 유저입니다.';
    }

    $destPermission = checkSecretPermission($destUser, false);
    if ($permission == 4 && $destPermission == 4 && $destUser['nation'] != $src->nationID) {
      return '외교권자끼리는 메시지를 보낼 수 없습니다.';
    }

    $destNation = getNationStaticInfo($destUser['nation']);
    if ($destNation === null) {
      $destNation = getNationStaticInfo(0);
    }

    $dest = new MessageTarget(
      $destUser['no'],
      $destUser['name'],
      $destNation['nation'],
      $destNation['name'],
      $destNation['color'],
      GetImageURL($destUser['imgsvr'], $destUser['picture'])
    );

    $msg = new Message(
      MessageType::private,
      $src,
      $dest,
      $text,
      $now,
      $unlimited,
      []
    );

    return $msg;
  }

  public function launch(Session $session, ?DateTimeInterface $modifiedSince, ?string $reqEtag)
  {
    $userID = $session->userID;
    $mailbox = $this->args['mailbox'];
    $text = $this->args['text'];

    increaseRefresh('서신전달', 1);

    $blockLevel = getBlockLevel();
    if ($blockLevel == 1 || $blockLevel == 3) {
      return '차단되었습니다.';
    }

    $db = DB::db();
    $me = $db->queryFirstRow('SELECT `no`,`name`,`nation`,`officer_level`,`con`,`picture`,`imgsvr`,penalty,permission,belong FROM general WHERE `owner`=%i', $userID);

    if (!$me) {
      $session->logoutGame();
      return '장수가 없습니다.';
    }

    $con = checkLimit($me['con']);
    if ($con >= 2) {
      return '접속 제한입니다.';
    }

    $nationID = $me['nation'];
    $iconPath = GetImageURL($me['imgsvr'], $me['picture']);
    $srcNation = getNationStaticInfo($me['nation']);

    if ($srcNation === null) {
      return '존재하지 않는 국가입니다.';
    }

    $src = new MessageTarget($me['no'], $me['name'], $srcNation['nation'], $srcNation['name'], $srcNation['color'], $iconPath);

    // 전체 메세지
    if ($mailbox === Message::MAILBOX_PUBLIC) {
      $msgID = $this->genPublicMessage($src, $text)->send();
      return [
        'msgID' => $msgID
      ];
    }

    $permission = checkSecretPermission($me);

    if ($mailbox >= Message::MAILBOX_NATIONAL) {
      if ($permission < 4) {
        $destNationID = $nationID;
      } else {
        $destNationID = $mailbox - Message::MAILBOX_NATIONAL;
      }

      if ($destNationID === $nationID) {
        $msgID = $this->genNationalMessage($src, $text)->send();
        return [
          'msgID' => $msgID
        ];
      }

      $msgObjOrError = $this->genDiplomacyMessage($src, $destNationID, $text);
      if(is_string($msgObjOrError)) {
        return $msgObjOrError;
      }
      $msgID = $msgObjOrError->send();
      return [
        'msgID' => $msgID
      ];
    }

    if ($mailbox > 0) {
      $now = new \DateTime();
      $lastMsg = new \DateTime($session->lastMsg ?? '0000-00-00');
      $msg_interval = $now->getTimestamp() - $lastMsg->getTimestamp();
      if ($msg_interval < 2) {
        return '개인메세지는 2초당 1건만 보낼 수 있습니다!';
      }
      $session->lastMsg = $now->format('Y-m-d H:i:s');

      $msgObjOrError = $this->genPrivateMessage($src, $mailbox, $permission, $text);
      if(is_string($msgObjOrError)) {
        return $msgObjOrError;
      }
      $msgID = $msgObjOrError->send();

      return [
        'msgID' => $msgID
      ];
    }

    return '알 수 없는 에러입니다.';
  }
}
