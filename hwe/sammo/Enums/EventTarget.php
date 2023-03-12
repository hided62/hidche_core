<?php
namespace sammo\Enums;

enum EventTarget: string{
  //YearMonth 변경 전에 처리해야할 Month 이벤트
  case PreMonth = 'PRE_MONTH';
  case Month = 'MONTH';
  //PostMonth는 없음
  case OccupyCity = 'OCCUPY_CITY';
  case DestroyNation = 'DESTROY_NATION';
  case United = 'UNITED';
}