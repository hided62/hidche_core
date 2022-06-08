<?php

namespace sammo;

use DateTimeImmutable;
use Ds\Set;
use sammo\DTO\AuctionBidItem;
use sammo\DTO\AuctionInfo;
use sammo\DTO\AuctionInfoDetail;
use sammo\Enums\AuctionType;
use sammo\Enums\InheritanceKey;
use sammo\Enums\ResourceType;
use sammo\RandUtil;

class AuctionUniqueItem extends Auction
{
  const COEFF_EXTENSION_MINUTES_LIMIT_UNIQUE_CNT = 24;

  static AuctionType $auctionType = AuctionType::UniqueItem;

  static public function openItemAuction(BaseItem $item, General $general, int $startAmount): self|string
  {
    if ($startAmount < GameConst::$inheritItemUniqueMinPoint) {
      return '최소 경매 금액은 ' . GameConst::$inheritItemUniqueMinPoint . '입니다.';
    }

    if ($general->getInheritancePoint(InheritanceKey::previous) < $startAmount) {
      return '경매를 시작할 포인트가 부족합니다.';
    }

    if ($item->isBuyable()) {
      return '구매할 수 있는 아이템입니다.';
    }

    $itemKey = $item->getRawClassName();
    $db = DB::db();
    $auctionIDonProgress = $db->queryFirstField(
      'SELECT `id` FROM ng_auction WHERE `finished` = 0 AND `type` = %s AND `target` = %s',
      AuctionType::UniqueItem->value,
      $itemKey
    );
    if ($auctionIDonProgress !== null) {
      return '이미 경매가 진행중입니다.';
    }

    $prevAuctionID = $db->queryFirstField(
      'SELECT id FROM ng_auction WHERE host_general_id = %i AND finished = 0 AND `type` = %s',
      $general->getID(),
      AuctionType::UniqueItem->value,
    );
    if ($prevAuctionID !== null) {
      return '아직 경매가 끝나지 않았습니다.';
    }

    $gameStor = KVStorage::getStorage($db, 'game_env');

    $now = new DateTimeImmutable();

    [$turnTerm, $year, $month] = $gameStor->getValuesAsArray(['turnterm', 'year', 'month']);

    $closeDate = $now->add(TimeUtil::secondsToDateInterval(
      max(static::MIN_AUCTION_CLOSE_MINUTES, $turnTerm * static::COEFF_AUCTION_CLOSE_MINUTES) * 60
    ));
    $availableLatestBidCloseDate = $closeDate->add(TimeUtil::secondsToDateInterval(
      max(static::MIN_EXTENSION_MINUTES_LIMIT_BY_BID, $turnTerm * static::COEFF_EXTENSION_MINUTES_LIMIT_BY_BID) * 60
    ));

    $info = new AuctionInfo(
      null,
      AuctionType::UniqueItem,
      false,
      $itemKey,
      $general->getID(),
      ResourceType::inheritancePoint,
      $now,
      $closeDate,
      new AuctionInfoDetail(
        "{$item->getName()} 경매",
        static::genObfuscatedName($general->getID()),
        1,
        false,
        $startAmount,
        null,
        1,
        $availableLatestBidCloseDate,
      )
    );

    $auctionID = static::openAuction($info, $general);
    if (!is_int($auctionID)) {
      return $auctionID;
    }
    $auction = new static($auctionID, $general);
    try {
      $auction->bid($startAmount, false);
    } catch (\Exception $e) {
      //실패해선 안된다.
      $msg = $e->getMessage();
      $auction->closeAuction();
      return "경매를 시작했지만, 첫 입찰에 실패했습니다: {$msg}";
    }

    $itemName = $item->getName();
    $josaRa = JosaUtil::pick($item->getRawName(), '라');

    $logger = new ActionLogger(0, 0, $year, $month);
    $logger->pushGlobalHistoryLog("<C><b>【보물수배】</b></>누군가가 <C>{$itemName}</>{$josaRa}는 보물을 구한다는 소문이 들려옵니다.");
    $logger->flush();

    return $auction;
  }

  protected function rollbackAuction(): void
  {
    // 유니크 옥션의 개최자는 운영자이므로 할 일이 없다.
  }

  public function bid(int $amount, bool $tryExtendCloseDate): ?string
  {

    $db = DB::db();
    /** @var AuctionInfo[] */
    $openUniqueAuctions = array_map(fn ($raw) => AuctionInfo::fromArray($raw), $db->query(
      'SELECT * FROM `ng_auction` WHERE `finished` = 0 AND `type`= %s',
      AuctionType::UniqueItem->value
    ) ?? []);

    $auctionIDList = [];
    foreach($openUniqueAuctions as $auction){
      $auctionIDList[] = $auction->id;
    }
    $db = DB::db();
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

    $itemCode = $this->info->target;

    if($itemCode === null){
      throw new \Exception('아이템 코드가 없습니다.');
    }

    $bidItemTypes = new Set();
    foreach (GameConst::$allItems as $itemType => $itemList) {
      if (key_exists($itemCode, $itemList) && $itemList[$itemCode] <= 0) {
        continue;
      }
      $bidItemTypes->add($itemType);
    }

    foreach ($openUniqueAuctions as $auction) {
      $auctionID = $auction->id;
      if (!isset($highestBids[$auctionID])) {
        continue;
      }
      if ($auctionID === $this->auctionID) {
        continue;
      }

      $bid = $highestBids[$auctionID];
      if ($bid->generalID !== $this->general->getID()) {
        continue;
      }

      $itemCodeComp = $auction->target;

      foreach (GameConst::$allItems as $itemType => $itemList) {
        if (($itemList[$itemCodeComp] ?? 0) <= 0) {
          continue;
        }
        if ($bidItemTypes->contains($itemType)) {
          return '1순위 입찰자인 경매중에 같은 부위가 있습니다.';
        }
      }
    }

    return $this->_bid($amount, $tryExtendCloseDate);
  }

  protected function finishAuction(AuctionBidItem $highestBid, General $bidder): ?string
  {
    $itemKey = $this->info->target;
    if ($itemKey === null) {
      throw new \Exception('아이템 키가 없습니다.');
    }
    $itemObj = buildItemClass($itemKey);
    $general = $bidder;
    $availableItemTypes = [];
    $reasons = [];
    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');
    [$startYear, $year] = $gameStor->getValuesAsArray(['startyear', 'year']);
    $relYear = $year - $startYear;
    $availableEquipUniqueCnt = 1;
    foreach (GameConst::$maxUniqueItemLimit as $tmpVals) {
      [$targetYear, $targetTrialCnt] = $tmpVals;
      if ($relYear < $targetYear) {
        break;
      }
      $availableEquipUniqueCnt = $targetTrialCnt;
    }

    $availableEquipUniqueCnt = Util::valueFit($availableEquipUniqueCnt, null, count(GameConst::$allItems));

    foreach ($general->getItems() as $item) {
      if (!$item->isBuyable()) {
        $availableEquipUniqueCnt -= 1;
      }
    }

    if ($availableEquipUniqueCnt <= 0) {
      $turnTerm = $gameStor->getValue('turnterm');
      //제한에 걸렸다면 자동 연장
      $extendedCloseDate = $this->info->closeDate->add(TimeUtil::secondsToDateInterval(
        max(static::MIN_EXTENSION_MINUTES_BY_EXTENSION_QUERY, $turnTerm * static::COEFF_EXTENSION_MINUTES_LIMIT_UNIQUE_CNT) * 60
      ));

      $this->extendCloseDate($extendedCloseDate, true);
      $this->extendLatestBidCloseDate(null);
      $this->applyDB();
      return '유니크 아이템 소유 제한 상태입니다. 종료 시간이 연장됩니다.';
    }

    foreach (GameConst::$allItems as $itemType => $itemList) {
      //아직은 그런 경우는 없지만 동일 유니크를 여러 부위에 장착할 수 있을지도 모름
      if (!key_exists($itemKey, $itemList)) {
        continue;
      }

      $ownItem = $general->getItem($itemType);
      if ($ownItem->getRawClassName() == $itemKey) {
        //FIXME: 이 경우에는 환불이 되던가 해야함.
        $reasons[] = '이미 그 유니크를 가지고 있습니다.';
        continue;
      }

      if (!$ownItem->isBuyable()) {
        $reasons[] = '이미 다른 유니크를 가지고 있습니다.';
        continue;
      }

      $availableCnt = $itemList[$itemKey];
      $occupiedCnt = $db->queryFirstField('SELECT count(*) FROM general WHERE %b = %s', $itemType, $itemKey);
      if ($occupiedCnt >= $availableCnt) {
        //FIXME: 이 경우에는 환불이 되던가 해야함.
        $reasons[] = '그 유니크는 모두 점유되었습니다.';
        continue;
      }
      $availableItemTypes[] = $itemType;
    }

    if (!$availableItemTypes) {
      return join(' ', $reasons);
    }

    $itemType = $availableItemTypes[0];

    $general->setVar($itemType, $itemKey);

    $logger = $general->getLogger();
    $nationName = $general->getStaticNation()['name'];
    $generalName = $general->getName();
    $josaYi = JosaUtil::pick($generalName, '이');
    $itemName = $itemObj->getName();
    $itemRawName = $itemObj->getRawName();
    $josaUl = JosaUtil::pick($itemRawName, '을');

    $logger->pushGeneralActionLog("<C>{$itemName}</>{$josaUl} 습득했습니다!");
    $logger->pushGeneralHistoryLog("<C>{$itemName}</>{$josaUl} 습득");
    $logger->pushGlobalActionLog("<Y>{$generalName}</>{$josaYi} <C>{$itemName}</>{$josaUl} 습득했습니다!");
    $logger->pushGlobalHistoryLog("<C><b>【보물수배】</b></><D><b>{$nationName}</b></>의 <Y>{$generalName}</>{$josaYi} <C>{$itemName}</>{$josaUl} 습득했습니다!");

    $userLogger = new UserLogger($general->getVar('owner'));
    $userLogger->push(sprintf("유니크 %s 경매로 %d 포인트 사용", $itemName, $highestBid->amount), "inheritPoint");

    $general->applyDB($db);

    return null;
  }
}
