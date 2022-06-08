<?php

namespace sammo\API\Auction;

use sammo\Session;
use DateTimeInterface;
use sammo\AuctionSellRice;
use sammo\DB;
use sammo\Validator;
use sammo\GameConst;
use sammo\General;
use sammo\KVStorage;
use sammo\Util;

class OpenSellRiceAuction extends \sammo\BaseAPI
{
  public function validateArgs(): ?string
  {
    $availableItems = [];
    foreach (GameConst::$allItems as $items) {
      foreach ($items as $itemKey => $amount) {
        if ($amount == 0) {
          continue;
        }
        $availableItems[$itemKey] = $amount;
      }
    }

    $v = new Validator($this->args);
    $v->rule('required', [
      'amount',
      'closeTurnCnt',
      'startBidAmount',
      'finishBidAmount',
    ])
      ->rule('int', 'amount')
      ->rule('int', 'closeTurnCnt')
      ->rule('min', 'amount', 100)
      ->rule('max', 'amount', 10000)
      ->rule('int', 'startBidAmount')
      ->rule('int', 'finishBidAmount');


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
    /** @var int */
    $amount = $this->args['amount'];
    /** @var int */
    $closeTurnCnt = $this->args['closeTurnCnt'];

    /** @var int */
    $startBidAmount = $this->args['startBidAmount'];
    /** @var int */
    $finishBidAmount = $this->args['finishBidAmount'];
    $generalID = $session->generalID;

    $general = General::createGeneralObjFromDB($generalID);

    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');
    [$initYear, $initMonth, $year, $month] = $gameStor->getValuesAsArray(['init_year', 'init_month', 'year', 'month']);
    $initYearMonth = Util::joinYearMonth($initYear, $initMonth);
    $yearMonth = Util::joinYearMonth($year, $month);

    if($yearMonth < $initYearMonth + 3){
      return '시작 후 3개월이 지나야 경매를 열 수 있습니다.';
    }

    $auctionResult = AuctionSellRice::openResourceAuction(
      $general,
      $amount,
      $closeTurnCnt,
      $startBidAmount,
      $finishBidAmount
    );

    if (is_string($auctionResult)) {
      return $auctionResult;
    }

    return [
      'result' => true,
      'auctionID' => $auctionResult->getInfo()->id,
    ];
  }
}
