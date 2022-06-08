<?php

namespace sammo\API\Auction;

use sammo\Session;
use DateTimeInterface;
use sammo\AuctionSellRice;
use sammo\Validator;
use sammo\General;

class BidSellRiceAuction extends \sammo\BaseAPI
{
  public function validateArgs(): ?string
  {
    $v = new Validator($this->args);
    $v->rule('required', [
      'auctionID',
      'amount',
    ])
      ->rule('int', 'amount')
      ->rule('int', 'auctionID');

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

    $generalID = $session->generalID;
    $general = General::createGeneralObjFromDB($generalID);
    $auction = new AuctionSellRice($auctionID, $general);
    $result = $auction->bid($amount, true);

    if (is_string($result)) {
      return $result;
    }

    return null;
  }
}
