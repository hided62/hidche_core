<?php

namespace sammo\Enums;

// mergeQueryColumn, createGeneralLiteObjListFromDB, createGeneralLiteObjFromDB 호출시 column 특수 모드 지정
enum GeneralLiteQueryMode: int
{
    /** 장수 식별을 위한 최소한의 정보, logger 초기화 없음 */
    case Core = 0;
    /** 게임 내에서 필수 이벤트 처리를 위한 정보 */
    case Lite = 1;
    /** 게임 내 모든 이벤트 처리를 위한 정보 */
    case Full = 2;
}