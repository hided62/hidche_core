<?php

namespace sammo\API\Auction;

use sammo\Session;
use DateTimeInterface;
use sammo\AuctionSellRice;
use sammo\DB;
use sammo\DTO\AuctionBidItem;
use sammo\DTO\AuctionInfo;
use sammo\Enums\APIRecoveryType;
use sammo\Enums\AuctionType;
use sammo\Validator;
use sammo\General;
use sammo\Json;
use sammo\TimeUtil;
use sammo\Util;

use function sammo\getAuctionLogRecent;

class GetActiveResourceAuctionList extends \sammo\BaseAPI
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

    $buyRiceList = [];
    $sellRiceList = [];
    /** @var AuctionInfo[] */
    $auctions = array_map(fn ($raw) => AuctionInfo::fromArray($raw), $db->query(
      'SELECT * FROM `ng_auction` WHERE `type` IN %ls AND `finished` = 0 ORDER BY `close_date` ASC',
      [
        AuctionType::BuyRice->value,
        AuctionType::SellRice->value,
      ]
    ));

    $recentLogs = getAuctionLogRecent(20);


    if (!$auctions) {
      return [
        'result' => true,
        'buyRice' => $buyRiceList,
        'sellRice' => $sellRiceList,
        'recentLogs' => $recentLogs,
        'generalID' => $session->generalID,
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

    foreach ($auctions as $auction) {
      $rawAuction = [
        'id' => $auction->id,
        'type' => $auction->type->value,
        'hostGeneralID' => $auction->hostGeneralID,
        'hostName' => $auction->detail->hostName,
        'openDate' => TimeUtil::format($auction->openDate, false),
        'closeDate' => TimeUtil::format($auction->closeDate, false),
        'amount' => $auction->detail->amount,
        'startBidAmount' => $auction->detail->startBidAmount,
        'finishBidAmount' => $auction->detail->finishBidAmount,
      ];

      $highestBid = $highestBids[$rawAuction['id']] ?? null;
      if ($highestBid === null) {
        $rawAuction['highestBid'] = null;
      } else {
        $rawAuction['highestBid'] = [
          'amount' => $highestBid->amount,
          'date' => TimeUtil::format($highestBid->date, false),
          'generalID' => $highestBid->generalID,
          'generalName' => $highestBid->aux->generalName,
        ];
      }

      if ($rawAuction['type'] == AuctionType::BuyRice->value) {
        $buyRiceList[] = $rawAuction;
      } else {
        $sellRiceList[] = $rawAuction;
      }
    }

    return [
      'result' => true,
      'buyRice' => $buyRiceList,
      'sellRice' => $sellRiceList,
      'recentLogs' => $recentLogs,
      'generalID' => $session->generalID,
    ];
  }
}
