<?php
namespace sammo;
use \sammo\iAction;
use \sammo\General;

//XXX: 아직 아이템 구현이 끝나지 않았으므로 바뀔 수 있음
class BaseItem implements iAction{
    use \sammo\DefaultAction;

    static $id = 0;
    static $name = '-';
    static $info = '';
    static $consumable = false;

    function isValidTurnItem(string $actionType, string $command):bool{
        return false;
    }
}