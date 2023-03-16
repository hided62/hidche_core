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
use sammo\TimeUtil;
use sammo\Util;

class GetUniqueItemAuctionList extends \sammo\BaseAPI
{
  public function validateArgs(): ?string
  {
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

    /** @var AuctionInfo[] */
    $auctions = array_map(fn($raw)=>AuctionInfo::fromArray($raw), $db->query(
      'SELECT * FROM `ng_auction` WHERE `type` = %s ORDER BY `close_date` ASC',
      AuctionType::UniqueItem->value
    ) ?? []);

    $obfuscatedName = AuctionUniqueItem::genObfuscatedName($generalID);

    if(!$auctions){
      return [
        'result' => true,
        'list' => [],
        'obfuscatedName' => $obfuscatedName,
      ];
    }


    $auctionIDList = [];
    foreach ($auctions as $auction) {
      $auctionIDList[] = $auction->id;
    }

    $rawHighestBids = Util::convertArrayToDict($db->query(
      'SELECT bid.* FROM `ng_auction_bid` bid INNER JOIN (
        SELECT `auction_id`, MAX(`amount`) as `max_amount`
        FROM `ng_auction_bid`
        WHERE `auction_id` IN %li
        GROUP BY `auction_id`
        ORDER BY `amount`
      ) AS max_bid
      ON bid.`auction_id` = max_bid.`auction_id` AND bid.`amount` = max_bid.`max_amount`',
      $auctionIDList,
    ) ?? [], 'auction_id');
    /** @var array<int,AuctionBidItem> */
    $highestBids = Util::mapWithKey(
      fn ($auctionID, $bid) => AuctionBidItem::fromArray($bid),
      $rawHighestBids
    );

    $response = [];
    foreach ($auctions as $auction) {
      $auctionID = $auction->id;
      $highestBid = $highestBids[$auctionID] ?? null;
      if($highestBid === null){
        continue;
      }

      $response[] = [
        'id' => $auctionID,
        'finished' => $auction->finished,
        'title' => $auction->detail->title,
        'target' => $auction->target,
        'isCallerHost' => $auction->hostGeneralID === $generalID,
        'hostName' => $auction->detail->hostName,
        'closeDate' => TimeUtil::format($auction->closeDate, false),
        'remainCloseDateExtensionCnt' => $auction->detail->remainCloseDateExtensionCnt,
        'availableLatestBidCloseDate' => TimeUtil::format($auction->detail->availableLatestBidCloseDate, false),
        'highestBid' => [
          'generalName' => $highestBid->aux->generalName,
          'amount' => $highestBid->amount,
          'isCallerHighestBidder' => $highestBid->generalID === $generalID,
          'date' => $highestBid->date,
        ],
      ];
    }

    return [
      'result' => true,
      'list' => $response,
      'obfuscatedName' => $obfuscatedName,
    ];
  }
}
