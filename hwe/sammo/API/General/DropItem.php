<?php

namespace sammo\API\General;

use Ds\Set;
use sammo\DB;
use sammo\Validator;

use sammo\Session;
use sammo\GameConst;
use sammo\General;
use sammo\JosaUtil;
use sammo\KVStorage;

class DropItem extends \sammo\BaseAPI
{
  public function validateArgs(): ?string
  {
    $v = new Validator($this->args);
    $v->rule('required', [
      'itemType',
    ])
      ->rule('in', 'itemType', array_keys(GameConst::$allItems));

    if (!$v->validate()) {
      return "{$v->errorStr()}";
    }
    return null;
  }

  public function getRequiredSessionMode(): int
  {
    return static::REQ_GAME_LOGIN;
  }

  public function launch(Session $session, ?\DateTimeInterface $modifiedSince, ?string $reqEtag)
  {
    $generalID = $session->generalID;
    $me = General::createGeneralObjFromDB($generalID);

    $itemType = $this->args['itemType'];
    $item = $me->getItem($itemType);

    if ($item->getRawClassName() === 'None') {
      return '아이템을 가지고 있지 않습니다.';
    }

    $me->setItem($itemType, 'None');
    $logger = $me->getLogger();

    $generalName = $me->getName();
    $josaYi = JosaUtil::pick($generalName, '이');


    $itemName = $item->getName();
    $itemRawName = $item->getRawName();
    $josaUl = JosaUtil::pick($itemRawName, '을');
    $logger->pushGeneralActionLog("<C>{$itemName}</>{$josaUl} 버렸습니다.");

    $nationName = $me->getStaticNation()['name'];
    $db = DB::db();
    if (!$item->isBuyable()) {
      $logger->pushGlobalActionLog("<Y>{$generalName}</>{$josaYi} <C>{$itemName}</>{$josaUl} 잃었습니다!");
      $logger->pushGlobalHistoryLog("<R><b>【망실】</b></><D><b>{$nationName}</b></>의 <Y>{$generalName}</>{$josaYi} <C>{$itemName}</>{$josaUl} 잃었습니다!");
    }

    $me->applyDB($db);

    return null;
  }
}
