<?php

namespace sammo\API\Auction;

use sammo\Session;
use DateTimeInterface;
use sammo\AuctionUniqueItem;
use sammo\DB;
use sammo\DTO\AuctionBidItem;
use sammo\DTO\AuctionInfo;
use sammo\Enums\APIRecoveryType;
use sammo\Enums\AuctionType;
use sammo\Enums\InheritanceKey;
use sammo\InheritancePointManager;
use sammo\TimeUtil;
use sammo\Validator;
use sammo\General;

class GetUniqueItemAuctionDetail extends \sammo\BaseAPI
{
  public function validateArgs(): ?string
  {
    $v = new Validator($this->args);
    $v->rule('required', [
      'auctionID',
    ])
      ->rule('integer', 'auctionID');

    if (!$v->validate()) {
      return $v->errorStr();
    }
    $this->args['auctionID'] = (int)$this->args['auctionID'];
    return null;
  }

  public function getRequiredSessionMode(): int
  {
    return static::REQ_GAME_LOGIN;
  }

  public function launch(Session $session, ?DateTimeInterface $modifiedSince, ?string $reqEtag): null | string | array | APIRecoveryType
  {
    $db = DB::db();

    $generalID = $session->generalID;
    $auctionID = $this->args['auctionID'];

    $rawAuction = $db->queryFirstRow(
      'SELECT * FROM `ng_auction` WHERE `type` = %s AND `id` = %i',
      AuctionType::UniqueItem->value,
      $auctionID
    );

    if (!$rawAuction) {
      return '선택한 경매가 없습니다.';
    }

    $auction = AuctionInfo::fromArray($rawAuction);

    /** @var AuctionBidItem[] */
    $bidList = array_map(fn ($raw) => AuctionBidItem::fromArray($raw), $db->query(
      'SELECT * FROM `ng_auction_bid` WHERE `auction_id` = %s ORDER BY `amount` DESC',
      $auctionID
    ) ?? []);

    $responseBid = [];
    foreach ($bidList as $bid) {
      $responseBid[] = [
        'generalName' => $bid->aux->generalName,
        'amount' => $bid->amount,
        'isCallerHighestBidder' => $bid->generalID === $generalID,
        'date' => TimeUtil::format($bid->date, false),
      ];
    }

    $inheritMgr = InheritancePointManager::getInstance();
    //preveious라서 column을 최대한 비울 수 있다.
    $remainPoint = $inheritMgr->getInheritancePoint(
      General::createGeneralObjFromDB($generalID, ['owner'], 0),
      InheritanceKey::previous
    );

    $obfuscatedName = AuctionUniqueItem::genObfuscatedName($generalID);

    return [
      'result' => true,
      'auction' => [
        'id' => $auction->id,
        'finished' => $auction->finished,
        'title' => $auction->detail->title,
        'target' => $auction->target,
        'isCallerHost' => $auction->hostGeneralID === $generalID,
        'hostName' => $auction->detail->hostName,
        'closeDate' => TimeUtil::format($auction->closeDate, false),
        'remainCloseDateExtensionCnt' => $auction->detail->remainCloseDateExtensionCnt,
        'availableLatestBidCloseDate' => TimeUtil::format($auction->detail->availableLatestBidCloseDate, false),
      ],
      'bidList' => $responseBid,
      'obfuscatedName' => $obfuscatedName,
      'remainPoint' => $remainPoint,
    ];
  }
}
