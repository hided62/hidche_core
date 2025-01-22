<?php

namespace sammo\Enums;

// Nation['aux'] 에서 지정될 수 있는 키

enum NationAuxKey: string
{
    case can_국기변경 = 'can_국기변경';
    case can_국호변경 = 'can_국호변경';
    case did_특성초토화 = 'did_특성초토화';


    // 이벤트
    case can_무작위수도이전 = 'can_무작위수도이전';

    case can_대검병사용 = 'can_대검병사용';
    case can_극병사용 = 'can_극병사용';
    case can_화시병사용 = 'can_화시병사용';
    case can_원융노병사용 = 'can_원융노병사용';
    case can_산저병사용 = 'can_산저병사용';
    case can_상병사용 = 'can_상병사용';
    case can_음귀병사용 = 'can_음귀병사용';
    case can_무희사용 = 'can_무희사용';
    case can_화륜차사용 = 'can_화륜차사용';
}