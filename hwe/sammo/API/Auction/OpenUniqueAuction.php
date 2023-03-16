<?php

namespace sammo\API\Auction;

use sammo\Session;
use DateTimeInterface;
use sammo\AuctionUniqueItem;
use sammo\DB;
use sammo\Enums\APIRecoveryType;
use sammo\Validator;
use sammo\GameConst;
use sammo\General;
use sammo\KVStorage;
use sammo\Util;

use function sammo\buildItemClass;

class OpenUniqueAuction extends \sammo\BaseAPI
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
      'itemID',
      'amount'
    ])
      ->rule('int', 'amount')
      ->rule('min', 'amount', GameConst::$inheritItemUniqueMinPoint)
      ->rule('keyExists', 'itemID', $availableItems);


    if (!$v->validate()) {
      return $v->errorStr();
    }
    return null;
  }

  public function getRequiredSessionMode(): int
  {
    return static::REQ_GAME_LOGIN;
  }

  public function launch(Session $session, ?DateTimeInterface $modifiedSince, ?string $reqEtag): null | string | array | APIRecoveryType
  {
    $itemID = $this->args['itemID'];
    $amount = $this->args['amount'];
    $generalID = $session->generalID;

    $itemObj = buildItemClass($itemID);
    $general = General::createGeneralObjFromDB($generalID);

    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');
    [$initYear, $initMonth, $year, $month] = $gameStor->getValuesAsArray(['init_year', 'init_month', 'year', 'month']);
    $initYearMonth = Util::joinYearMonth($initYear, $initMonth);
    $yearMonth = Util::joinYearMonth($year, $month);

    if($yearMonth < $initYearMonth + 3){
      return '시작 후 3개월이 지나야 경매를 열 수 있습니다.';
    }

    $auctionResult = AuctionUniqueItem::openItemAuction($itemObj, $general, $amount);

    if(is_string($auctionResult)) {
      return $auctionResult;
    }

    return [
      'result' => true,
      'auctionID' => $auctionResult->getInfo()->id,
    ];
  }
}
