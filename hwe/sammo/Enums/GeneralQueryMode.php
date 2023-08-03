<?php

namespace sammo\Enums;

// mergeQueryColumn, createGeneralObjListFromDB, createGeneralObjFromDB 호출시 column 특수 모드 지정
enum GeneralQueryMode: int
{
    /** 게임 내 모든 이벤트 처리를 위한 정보, iAction 포함 */
    case Full = 3;
    /** 접속 정보를 포함한 모든 정보 */
    case FullWithAccessLog = 4;
}