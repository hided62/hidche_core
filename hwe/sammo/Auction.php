<?php

namespace sammo;

use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use sammo\DTO\AuctionBidItem;
use sammo\DTO\AuctionBidItemData;
use sammo\DTO\AuctionInfo;
use sammo\Enums\AuctionType;
use sammo\Enums\InheritanceKey;
use sammo\Enums\RankColumn;
use sammo\Enums\ResourceType;

abstract class Auction
{

  protected AuctionInfo $info;

  static AuctionType $auctionType;

  public const COEFF_AUCTION_CLOSE_MINUTES = 24;
  public const COEFF_EXTENSION_MINUTES_PER_BID = (1 / 6);
  public const COEFF_EXTENSION_MINUTES_LIMIT_BY_BID = 0.5;
  public const COEFF_EXTENSION_MINUTES_BY_EXTENSION_QUERY = 1;
  public const MIN_AUCTION_CLOSE_MINUTES = 30;
  public const MIN_EXTENSION_MINUTES_PER_BID = 1;
  public const MIN_EXTENSION_MINUTES_LIMIT_BY_BID = 5;
  public const MIN_EXTENSION_MINUTES_BY_EXTENSION_QUERY = 5;

  protected AuctionBidItem|null|false $_highestBid = false;

  static public function genObfuscatedName(int $id): string
  {
    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');

    $namePool = $gameStor->getValue('obfuscatedNamePool');
    if ($namePool === null) {
      $rng = new RandUtil(new LiteHashDRBG(Util::simpleSerialize(
        UniqueConst::$hiddenSeed,
        'obfuscatedNamePool',
      )));
      $namePool = [];
      foreach (GameConst::$randGenFirstName as $ch0) {
        foreach (GameConst::$randGenMiddleName as $ch1) {
          foreach (GameConst::$randGenLastName as $ch2) {
            $namePool[] = "{$ch0}{$ch1}{$ch2}";
          }
        }
      }
      $namePool = $rng->shuffle($namePool);
      $gameStor->setValue('obfuscatedNamePool', $namePool);
    }


    $dupIdx = intdiv($id, count($namePool));
    $subIdx = $id % count($namePool);
    if ($dupIdx == 0) {
      return $namePool[$subIdx];
    }
    return "{$namePool[$subIdx]}{$dupIdx}";
  }

  static protected function openAuction(AuctionInfo $info, General $general): int|string
  {
    $db = DB::db();
    if ($info->id !== null) {
      return 'id가 지정되어 있습니다.';
    }

    $db->insert('ng_auction', $info->toArray());
    return $db->insertId();
  }

  public function getHighestBid(): ?AuctionBidItem
  {
    $db = DB::db();

    if ($this->_highestBid !== false) {
      return $this->_highestBid;
    }

    if (!$this->info->detail->isReverse) {
      $rawHighestBid = $db->queryFirstRow(
        'SELECT * FROM ng_auction_bid WHERE auction_id = %i ORDER BY `amount` DESC LIMIT 1',
        $this->info->id
      );
    } else {
      $rawHighestBid = $db->queryFirstRow(
        'SELECT * FROM ng_auction_bid WHERE auction_id = %i ORDER BY `amount` ASC LIMIT 1',
        $this->info->id
      );
    }

    if (!$rawHighestBid) {
      $this->_highestBid = null;
      return null;
    }

    $highestBid =  AuctionBidItem::fromArray($rawHighestBid);
    $this->_highestBid = $highestBid;
    return $highestBid;
  }

  public function getMyPrevBid(): ?AuctionBidItem
  {
    $db = DB::db();
    if (!$this->info->detail->isReverse) {
      $rawMyPrevBid = $db->queryFirstRow(
        'SELECT * FROM ng_auction_bid WHERE general_id = %i AND auction_id = %i ORDER BY `amount` DESC LIMIT 1',
        $this->general->getID(),
        $this->info->id
      );
    } else {
      $rawMyPrevBid = $db->queryFirstRow(
        'SELECT * FROM ng_auction_bid WHERE general_id = %i AND auction_id = %i ORDER BY `amount` ASC LIMIT 1',
        $this->general->getID(),
        $this->info->id
      );
    }
    if (!$rawMyPrevBid) {
      return null;
    }
    return AuctionBidItem::fromArray($rawMyPrevBid);
  }

  public function __construct(protected readonly int $auctionID, protected General $general)
  {
    $db = DB::db();
    $rawAuctionInfo = $db->queryFirstRow('SELECT * FROM `ng_auction` WHERE id = %i', $auctionID);
    if (!$rawAuctionInfo) {
      throw new \RuntimeException("해당 경매가 없습니다: {$auctionID}");
    }
    $this->info = AuctionInfo::fromArray($rawAuctionInfo);
    $thisAuctionType = static::$auctionType;
    if ($this->info->type !== $thisAuctionType) {
      throw new \RuntimeException("잘못된 경매 타입입니다: {$this->info->type->value} != {$thisAuctionType->value}");
    }
  }

  public function getInfo(): AuctionInfo
  {
    return $this->info;
  }

  public function shrinkCloseDate(?DateTimeInterface $date): ?string
  {
    if ($date === null) {
      $date = new DateTimeImmutable();
    }

    $this->info->closeDate = $date;
    $db = DB::db();
    $db->update('ng_auction', $this->info->toArray('id'), 'id = %i', $this->info->id);

    return null;
  }

  public function extendLatestBidCloseDate(?DateTimeInterface $date): ?string
  {
    if ($date === null) {
      $db = DB::db();
      $gameStor = KVStorage::getStorage($db, 'game_env');
      $turnTerm = $gameStor->getValue('turnterm');
      $date = $this->info->closeDate->add(TimeUtil::secondsToDateInterval(
        max(static::MIN_EXTENSION_MINUTES_PER_BID, $turnTerm * static::COEFF_EXTENSION_MINUTES_PER_BID) * 60
      ));
    }
    else{
      $date = DateTimeImmutable::createFromInterface($date);
    }
    if ($this->info->detail->availableLatestBidCloseDate !== null && $date < $this->info->detail->availableLatestBidCloseDate) {
      return '기간보다 짧습니다.';
    }
    $this->info->detail->availableLatestBidCloseDate = $date;
    return null;
  }

  public function extendCloseDate(DateTimeInterface $date, bool $force = false): ?string
  {
    if (!$force) {
      if ($this->info->detail->remainCloseDateExtensionCnt === null) {
        return '연장할 수 없는 경매입니다.';
      }
      if ($this->info->detail->remainCloseDateExtensionCnt === 0) {
        return '더 이상 연장할 수 없습니다';
      }
      if ($this->info->detail->remainCloseDateExtensionCnt > 0) {
        $this->info->detail->remainCloseDateExtensionCnt--;
      }
    }

    if ($date < $this->info->closeDate) {
      return '종료 기간보다 짧습니다.';
    }

    $closeDate = DateTimeImmutable::createFromInterface($date);
    $this->info->closeDate = $closeDate;
    return null;
  }

  public function applyDB(): void
  {
    $db = DB::db();
    $db->update('ng_auction', $this->info->toArray('id'), 'id = %i', $this->info->id);
  }

  public function refundBid(AuctionBidItem $bidItem, string $reason): void
  {
    if ($bidItem->auctionID !== $this->info->id) {
      throw new \RuntimeException('잘못된 경매입니다.');
    }

    $db = DB::db();
    if ($bidItem->generalID === $this->general->getID()) {
      $oldBidder = $this->general;
    } else {
      $oldBidder = General::createGeneralObjFromDB($bidItem->generalID);
    }

    if ($this->info->reqResource === ResourceType::inheritancePoint) {
      $oldBidder->increaseInheritancePoint(InheritanceKey::previous, $bidItem->amount);
      $oldBidder->increaseRankVar(RankColumn::inherit_point_spent_dynamic, -$bidItem->amount);
    } else {
      $oldBidder->increaseVar($this->info->reqResource->value, $bidItem->amount);
    }

    if ($oldBidder instanceof DummyGeneral) {
      return;
    }

    $staticNation = $oldBidder->getStaticNation();
    $src = new MessageTarget(0, '', 0, 'System', '#000000');
    $dest = new MessageTarget(
      $oldBidder->getID(),
      $oldBidder->getName(),
      $oldBidder->getNationID(),
      $staticNation['name'],
      $staticNation['color'],
      GetImageURL($oldBidder->getVar('imgsvr'), $oldBidder->getVar('picture'))
    );

    //TODO: 전역 알림이 나타나야한다. 일반 메시지보다는 중요하고, 메시지보단 약하게..
    //TODO: 바로가기를 제공하는 편이 좋을 것 같다.
    $msg = new Message(
      Message::MSGTYPE_PRIVATE,
      $src,
      $dest,
      $reason,
      new DateTime(),
      new DateTime('9999-12-31'),
      []
    );
    $oldBidder->applyDB($db);
    $msg->send(true);
  }

  public function closeAuction(bool $isRollback = false): void
  {
    $db = DB::db();

    $this->info->finished = true;

    if ($isRollback) {
      $highestBid = $this->getHighestBid();
      if ($highestBid !== null) {
        $this->refundBid($highestBid, "{$this->info->id}번 {$this->info->detail->title} 경매가 취소되었습니다.");
      }
      $this->rollbackAuction();
    }

    $db->update('ng_auction', $this->info->toArray('id'), 'id = %i', $this->info->id);
  }

  private function bidInheritPoint(int $amount, \DateTimeImmutable $now, bool $tryExtendCloseDate): ?string
  {
    $db = DB::db();

    $auctionInfo = $this->info;
    $general = $this->general;

    $highestBid = $this->getHighestBid();
    if ($highestBid !== null && $amount < $highestBid->amount * 1.01) {
      return '현재입찰가보다 1% 높게 입찰해야 합니다.';
    }
    if ($highestBid !== null && $amount < $highestBid->amount + 10) {
      return '현재입찰가보다 10 포인트 높게 입찰해야 합니다.';
    }

    $myPrevBid = $this->getMyPrevBid();
    if ($myPrevBid !== null && $highestBid->no !== $myPrevBid->no) {
      //이미 환불 받았으니 무효.
      $myPrevBid = null;
    }

    $morePoint = $amount - ($myPrevBid ? $myPrevBid->amount : 0);
    $currPoint = $general->getInheritancePoint(InheritanceKey::previous);
    if ($currPoint === null || $currPoint < $morePoint) {
      return '유산포인트가 부족합니다.';
    }

    $obfuscatedName = static::genObfuscatedName($general->getID());
    //여기서부터 입찰 성공

    $newBid = new AuctionBidItem(
      null,
      $auctionInfo->id,
      $general->getVar('owner'),
      $general->getID(),
      $amount,
      $now,
      new AuctionBidItemData(
        $general->getVar('owner_name'),
        $obfuscatedName,
        $tryExtendCloseDate,
      )
    );
    $db->insert('ng_auction_bid', $newBid->toArray());
    if ($db->affectedRows() == 0) {
      return '입찰에 실패했습니다: DB 오류';
    }

    $gameStor = KVStorage::getStorage($db, 'game_env');
    $turnTerm = $gameStor->getValue('turnterm');

    if ($this->info->detail->availableLatestBidCloseDate !== null) {
      $extendedCloseDate = $now->add(TimeUtil::secondsToDateInterval(
        max(static::MIN_EXTENSION_MINUTES_PER_BID, $turnTerm * static::COEFF_EXTENSION_MINUTES_PER_BID) * 60
      ));

      if ($extendedCloseDate > $this->info->closeDate && $this->info->closeDate < $this->info->detail->availableLatestBidCloseDate) {
        $this->extendCloseDate(min($extendedCloseDate, $this->info->detail->availableLatestBidCloseDate), true);
        $this->applyDB();
      }
    }

    $general->increaseInheritancePoint(InheritanceKey::previous, -$morePoint);
    $general->increaseRankVar(RankColumn::inherit_point_spent_dynamic, $morePoint);

    if ($highestBid !== null && $myPrevBid === null) {
      $this->refundBid($highestBid, "{$auctionInfo->id}번 {$auctionInfo->detail->title}에 상회입찰자가 나타났습니다.");
    }
    $general->applyDB($db);
    return null;
  }

  protected function _bid(int $amount, bool $tryExtendCloseDate = false): ?string
  {
    $auctionInfo = $this->info;
    $general = $this->general;

    if ($auctionInfo->finished) {
      return '경매가 이미 끝났습니다.';
    }

    $now = new \DateTimeImmutable();

    if ($auctionInfo->closeDate < $now) {
      return '경매가 이미 끝났습니다.';
    }
    if ($auctionInfo->openDate > $now) {
      return '경매가 아직 시작되지 않았습니다.';
    }

    if (!$auctionInfo->detail->isReverse) {
      if ($auctionInfo->detail->finishBidAmount !== null && $auctionInfo->detail->finishBidAmount < $amount) {
        return '즉시판매가보다 높을 수 없습니다.';
      }
    } else {
      if ($auctionInfo->detail->finishBidAmount !== null && $auctionInfo->detail->finishBidAmount > $amount) {
        return '즉시판매가보다 낮을 수 없습니다.';
      }
    }


    if ($auctionInfo->reqResource === ResourceType::inheritancePoint) {
      return $this->bidInheritPoint($amount, $now, $tryExtendCloseDate);
    }

    //reqResource는 말 그대로 '구매자가 내야하는 자원'이다.

    $db = DB::db();

    $highestBid = $this->getHighestBid();
    if (!$auctionInfo->detail->isReverse) {
      if ($highestBid !== null && $amount <= $highestBid->amount) {
        return '현재입찰가보다 높게 입찰해야 합니다.';
      }
    } else {
      if ($highestBid !== null && $amount >= $highestBid->amount) {
        return '현재입찰가보다 낮게 입찰해야 합니다.';
      }
    }


    $myPrevBid = $this->getMyPrevBid();
    if ($myPrevBid !== null && $highestBid->no !== $myPrevBid->no) {
      //이미 환불 받았으니 무효.
      $myPrevBid = null;
    }

    $morePoint = $amount - ($myPrevBid ? $myPrevBid->amount : 0);
    $resType = $auctionInfo->reqResource;
    $minReqRes = match ($resType) {
      ResourceType::gold => GameConst::$defaultGold,
      ResourceType::rice => GameConst::$defaultRice,
    };

    if ($general->getVar($resType->value) < $morePoint + $minReqRes) {
      return $resType->getName() . '이 부족합니다.';
    }

    //여기서부터 입찰 성공

    $newBid = new AuctionBidItem(
      null,
      $auctionInfo->id,
      $general->getVar('owner'),
      $general->getID(),
      $amount,
      $now,
      new AuctionBidItemData(
        $general->getVar('owner_name'),
        $general->getName(),
        $tryExtendCloseDate,
      )
    );

    $db->insert('ng_auction_bid', $newBid->toArray());
    if ($db->affectedRows() == 0) {
      return '입찰에 실패했습니다: DB 오류';
    }

    $general->increaseVar($resType->value, -$morePoint);

    $gameStor = KVStorage::getStorage($db, 'game_env');
    $turnTerm = $gameStor->getValue('turnterm');
    $extendedCloseDate = $now->add(TimeUtil::secondsToDateInterval(
      max(static::MIN_EXTENSION_MINUTES_PER_BID, $turnTerm * static::COEFF_EXTENSION_MINUTES_PER_BID) * 60
    ));

    if ($extendedCloseDate > $this->info->closeDate) {
      $this->extendCloseDate($extendedCloseDate, true);
      $this->applyDB();
    }

    if ($highestBid !== null && $myPrevBid === null) {
      $this->refundBid($highestBid, "{$auctionInfo->id}번 {$auctionInfo->detail->title}에 상회입찰자가 나타났습니다.");
    }
    $general->applyDB($db);
    return null;
  }

  public function tryFinish(): ?bool
  {
    $now = new DateTimeImmutable();
    if ($now < $this->info->closeDate) {
      return null;
    }

    //경매를 닫아야한다.
    $highestBid = $this->getHighestBid();
    if ($highestBid === null) {
      $this->closeAuction(true);
      return true;
    }

    if ($highestBid->aux->tryExtendCloseDate) {
      $db = DB::db();
      $gameStor = KVStorage::getStorage($db, 'game_env');
      $turnTerm = $gameStor->getValue('turnterm');

      //연장 요청이 있었다.
      $extendedCloseDate = $this->info->closeDate->add(TimeUtil::secondsToDateInterval(
        max(static::MIN_EXTENSION_MINUTES_BY_EXTENSION_QUERY, $turnTerm * static::COEFF_EXTENSION_MINUTES_BY_EXTENSION_QUERY) * 60
      ));

      if ($this->extendCloseDate($extendedCloseDate) === null) {
        $this->extendLatestBidCloseDate(null);
        $this->applyDB();
        return false;
      }
    }

    $bidder =  General::createGeneralObjFromDB($highestBid->generalID);
    $failReason = $this->finishAuction($highestBid, $bidder);
    if ($failReason === null) {
      $this->closeAuction();
      return true;
    }

    if ($bidder instanceof DummyGeneral) {
      return false;
    }

    $staticNation = $bidder->getStaticNation();
    $src = new MessageTarget(0, '', 0, 'System', '#000000');
    $dest = new MessageTarget(
      $bidder->getID(),
      $bidder->getName(),
      $bidder->getNationID(),
      $staticNation['name'],
      $staticNation['color'],
      GetImageURL($bidder->getVar('imgsvr'), $bidder->getVar('picture'))
    );

    //TODO: 전역 알림이 나타나야한다. 일반 메시지보다는 중요하고, 메시지보단 약하게..
    //TODO: 바로가기를 제공하는 편이 좋을 것 같다.
    $msg = new Message(
      Message::MSGTYPE_PRIVATE,
      $src,
      $dest,
      $failReason,
      new \DateTime(),
      new \DateTime('9999-12-31'),
      []
    );
    $msg->send(true);
    return false;
  }

  abstract public function bid(int $amount, bool $tryExtendCloseDate): ?string;

  abstract protected function rollbackAuction(): void;
  abstract protected function finishAuction(AuctionBidItem $highestBid, General $bidder): ?string;
}
