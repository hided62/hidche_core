<?php

namespace sammo;

use sammo\Enums\AuctionType;
use sammo\Enums\ResourceType;

/** 경매에 쌀을 매물로 등록, 입찰자가 금으로 구매 */
class AuctionBuyRice extends AuctionBasicResource
{
  static AuctionType $auctionType = AuctionType::BuyRice;
  static ResourceType $hostRes = ResourceType::rice;
  static ResourceType $bidderRes = ResourceType::gold;
}
