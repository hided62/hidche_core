<?php

namespace sammo\Enums;

// mergeQueryColumn, createGeneralObjListFromDB, createGeneralObjFromDB 호출시 column 특수 모드 지정
enum GeneralQueryMode: int
{
    /** 장수 식별을 위한 최소한의 정보 */
    case Core = 0;
    /** 게임 내에서 필수 이벤트 처리를 위한 정보, iAction 제외 */
    case Lite = 1;
    /** 게임 내 모든 이벤트 처리를 위한 정보, iAction 제외 */
    case FullWithoutIAction = 2;
    /** 게임 내 모든 이벤트 처리를 위한 정보, iAction 포함 */
    case Full = 3;
    /** 접속 정보를 포함한 모든 정보 */
    case FullWithAccessLog = 4;
}