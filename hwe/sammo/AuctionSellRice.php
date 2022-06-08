<?php

namespace sammo;

use sammo\Enums\AuctionType;
use sammo\Enums\ResourceType;

/** 경매에 금을 매물로 등록, 입찰자가 쌀로 판매 */
class AuctionSellRice extends AuctionBasicResource
{
  static AuctionType $auctionType = AuctionType::SellRice;
  static ResourceType $hostRes = ResourceType::gold;
  static ResourceType $bidderRes = ResourceType::rice;
}
