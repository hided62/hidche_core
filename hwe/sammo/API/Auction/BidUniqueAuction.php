<?php

namespace sammo\API\Auction;

use sammo\Session;
use DateTimeInterface;
use sammo\AuctionUniqueItem;
use sammo\Validator;
use sammo\GameConst;
use sammo\General;

class BidUniqueAuction extends \sammo\BaseAPI
{
  public function validateArgs(): ?string
  {
    $v = new Validator($this->args);
    $v->rule('required', [
      'auctionID',
      'amount',
    ])
      ->rule('int', 'amount')
      ->rule('int', 'auctionID')
      ->rule('boolean', 'extendCloseDate');

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
    $auctionID = $this->args['auctionID'];
    $amount = $this->args['amount'];
    $tryExtendCloseDate = $this->arg['extendCloseDate'] ?? false;

    $generalID = $session->generalID;
    $general = General::createGeneralObjFromDB($generalID);
    $auction = new AuctionUniqueItem($auctionID, $general);
    $result = $auction->bid($amount, $tryExtendCloseDate);

    if (is_string($result)) {
      return $result;
    }

    return null;
  }
}
