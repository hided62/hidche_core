<?php

namespace sammo;

use DateTimeImmutable;
use sammo\DTO\AuctionBidItem;
use sammo\DTO\AuctionInfo;
use sammo\DTO\AuctionInfoDetail;
use sammo\Enums\AuctionType;
use sammo\Enums\ResourceType;

abstract class AuctionBasicResource extends Auction
{
  const MIN_AUCTION_AMOUNT = 100;
  const MAX_AUCTION_AMOUNT = 10000;
  static ResourceType $hostRes;
  static ResourceType $bidderRes;

  static public function openResourceAuction(General $general, int $amount, int $closeTurnCnt, int $startBidAmount, int $finishBidAmount): self|string
  {
    if ($closeTurnCnt < 1 || $closeTurnCnt > 24) {
      return '종료기한은 1 ~ 24 턴 이어야 합니다.';
    }
    if ($amount < self::MIN_AUCTION_AMOUNT || $amount > self::MAX_AUCTION_AMOUNT) {
      return '거래량은 ' . self::MIN_AUCTION_AMOUNT . ' ~ ' . self::MAX_AUCTION_AMOUNT . ' 이어야 합니다.';
    }
    if ($startBidAmount < $amount * 0.5 || $amount * 2 < $startBidAmount) {
      return '시작거래가는 50% ~ 200% 이어야 합니다.';
    }
    if ($finishBidAmount < $amount * 1.1 || $amount * 2 < $finishBidAmount) {
      return '즉시거래가는 110% ~ 200% 이어야 합니다.';
    }
    if ($finishBidAmount < $startBidAmount * 1.1) {
      return '즉시거래가는 시작판매가의 110% 이상이어야 합니다.';
    }

    $hostRes = static::$hostRes;
    $hostResName = $hostRes->getName();
    $bidderRes = static::$bidderRes;
    $minimumRes = static::$hostRes === ResourceType::rice ? GameConst::$generalMinimumRice : GameConst::$generalMinimumGold;
    if ($general->getVar($hostRes->value) < $amount + $minimumRes) {
      return "기본 {$hostRes->getName()} {$minimumRes}은 거래할 수 없습니다.";
    }

    $db = DB::db();
    if (!($general instanceof DummyGeneral)) {
      $prevAuctionID = $db->queryFirstField(
        'SELECT id FROM ng_auction WHERE host_general_id = %i AND finished = 0 AND `type` IN %ls',
        $general->getID(),
        [AuctionType::BuyRice->value, AuctionType::SellRice->value],
      );
      if ($prevAuctionID !== null) {
        return '아직 경매가 끝나지 않았습니다.';
      }
    }


    $now = new \DateTimeImmutable();
    $gameStor = KVStorage::getStorage($db, 'game_env');
    $turnTerm = $gameStor->getValue('turnterm');
    $closeDate = $now->add(TimeUtil::secondsToDateInterval($closeTurnCnt * $turnTerm * 60));

    $openResult = static::openAuction(new AuctionInfo(
      null,
      static::$auctionType,
      false,
      "$amount",
      $general->getId(),
      $bidderRes,
      $now,
      $closeDate,
      new AuctionInfoDetail(
        "{$hostResName} {$amount} 경매",
        $general->getName(),
        $amount,
        false,
        $startBidAmount,
        $finishBidAmount,
        null,
        null
      )
    ), $general);

    if (is_string($openResult)) {
      return $openResult;
    }

    $general->increaseVarWithLimit($hostRes->value, -$amount, 0);
    $general->applyDB($db);

    return new static($openResult, $general);
  }

  static public function genDummy(bool $initFullLogger = true): DummyGeneral
  {
    $dummyGeneral = new DummyGeneral(false);
    $dummyGeneral->setVar('name', '상인');
    $dummyGeneral->setVar('gold', static::MAX_AUCTION_AMOUNT * 10);
    $dummyGeneral->setVar('rice', static::MAX_AUCTION_AMOUNT * 10);

    if($initFullLogger){
      $db = DB::db();
      $gameStor = KVStorage::getStorage($db, 'game_env');
      [$year, $month] = $gameStor->getValuesAsArray(['year', 'month']);
      $dummyGeneral->initLogger($year, $month);
    }

    return $dummyGeneral;
  }

  protected function rollbackAuction(): void
  {
    if ($this->general->getID() === $this->info->hostGeneralID) {
      $auctionHost = $this->general;
    } else if ($this->info->hostGeneralID == 0) {
      $auctionHost = $this->genDummy();
    } else {
      $auctionHost = General::createGeneralObjFromDB($this->info->hostGeneralID);
    }

    $hostRes = static::$hostRes;
    $hostResName = $hostRes->getName();

    $auctionHost->increaseVar($hostRes->value, $this->info->detail->amount);
    $auctionHost->applyDB(DB::db());

    $staticNation = $auctionHost->getStaticNation();
    $src = new MessageTarget(0, '', 0, 'System', '#000000');
    $dest = new MessageTarget(
      $auctionHost->getID(),
      $auctionHost->getName(),
      $auctionHost->getNationID(),
      $staticNation['name'],
      $staticNation['color'],
      GetImageURL($auctionHost->getVar('imgsvr'), $auctionHost->getVar('picture'))
    );

    //TODO: 전역 알림이 나타나야한다. 일반 메시지보다는 중요하고, 메시지보단 약하게..
    //TODO: 바로가기를 제공하는 편이 좋을 것 같다.
    $msg = new Message(
      Message::MSGTYPE_PRIVATE,
      $src,
      $dest,
      "{$this->auctionID}번 {$hostResName} 경매에 입찰이 없어 취소되었습니다.",
      new \DateTime(),
      new \DateTime('9999-12-31'),
      []
    );
    $msg->send(true);
  }

  protected function finishAuction(AuctionBidItem $highestBid, General $bidder): ?string
  {
    if ($this->general->getID() === $this->info->hostGeneralID) {
      $auctionHost = $this->general;
    } else if ($this->info->hostGeneralID == 0) {
      $auctionHost = $this->genDummy();
    } else {
      $auctionHost = General::createGeneralObjFromDB($this->info->hostGeneralID);
    }

    $highestBid = $this->getHighestBid();
    if ($highestBid === null) {
      throw new \Exception('입찰자가 없습니다.');
    }

    if ($this->general->getID() === $highestBid->generalID) {
      $bidder = $this->general;
    } else {
      $bidder = General::createGeneralObjFromDB($highestBid->generalID);
    }

    $hostRes = static::$hostRes;
    $hostResName = $hostRes->getName();
    $bidderRes = static::$bidderRes;
    $bidderResName = $bidderRes->getName();

    $bidAmount = $highestBid->amount;
    $auctionAmount = $this->info->detail->amount;

    //거래 종료이므로 서로 반대
    $josaUlBidder = JosaUtil::pick($bidAmount, '을');
    $josaUlHost = JosaUtil::pick($auctionAmount, '을');
    $auctionHost->increaseVar($bidderRes->value, $bidAmount);
    $bidder->increaseVar($hostRes->value, $auctionAmount);

    $auctionID = $this->info->id;

    $auctionHost->getLogger()->pushGeneralActionLog(
      "{$auctionID}번 거래 <C>성사</>로 {$bidderResName} <C>{$bidAmount}</>{$josaUlBidder} 지불, {$hostResName} <C>{$auctionAmount}</>{$josaUlHost} 획득!",
      ActionLogger::EVENT_PLAIN
    );
    $bidder->getLogger()->pushGeneralActionLog(
      "{$auctionID}번 거래 <C>성사</>로 {$hostResName} <C>{$auctionAmount}</>{$josaUlHost} 판매, {$bidderResName} <C>{$bidAmount}</>{$josaUlBidder} 획득!",
      ActionLogger::EVENT_PLAIN
    );

    $josaYiHost = JosaUtil::pick($auctionHost->getName(), '이');
    $josaYiBidder = JosaUtil::pick($bidder->getName(), '이');

    $auctionLog = [];
    $auctionLog[] = "{$auctionID}번 {$hostResName} 경매 <C>성사</> : <Y>{$auctionHost->getName()}</>{$josaYiHost} {$hostResName} <C>{$auctionAmount}</> 판매, <Y>{$bidder->getName()}</>{$josaYiBidder} <C>{$bidAmount}</> 구매";


    if ($highestBid->amount === $this->info->detail->finishBidAmount) {
      $auctionLog[0] .= ' <M>★ 즉시구매가 거래 ★</>';
    } else if ($highestBid->amount === $this->info->detail->startBidAmount) {
      $auctionLog[0] .= " <R>★ 최고가 거래 ★</>";
    }

    pushAuctionLog(array_map(
      fn ($log) =>
      $bidder->getLogger()->formatText($log, ActionLogger::EVENT_PLAIN),
      $auctionLog
    ));

    $db = DB::db();
    $bidder->applyDB($db);
    $auctionHost->applyDB($db);

    return null;
  }

  public function bid(int $amount, bool $tryExtendCloseDate = false): ?string
  {
    if ($this->info->hostGeneralID === $this->general->getID()) {
      return '자신이 연 경매에 입찰할 수 없습니다.';
    }
    $result = $this->_bid($amount, $tryExtendCloseDate);

    if (is_string($result)) {
      return $result;
    }

    if ($amount === $this->info->detail->finishBidAmount) {
      //즉구, 1턴 후 지급
      $db = DB::db();
      $gameStor = KVStorage::getStorage($db, 'game_env');
      $turnTerm = $gameStor->getValue('turnterm');
      $date = (new DateTimeImmutable())->add(TimeUtil::secondsToDateInterval($turnTerm * 60));
      $this->shrinkCloseDate($date);
    }

    return null;
  }
}
