<?php
namespace sammo\Enums;

/**
 * 입찰자 기준
*/
enum AuctionType: string{
  /** 쌀을 매물로 등록, 금으로 구매 */
  case BuyRice = 'buyRice';
  /** 금을 매물로 등록, 쌀로 판매 */
  case SellRice = 'sellRice';
  /** 유미크를 매물로 등록, 유산 포인트로 구매 */
  case UniqueItem = 'uniqueItem';
}