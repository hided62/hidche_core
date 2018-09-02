<?php
namespace sammo\TriggerNationType;
use \sammo\iActionTrigger;
use \sammo\General;

class che_도적 implements iActionTrigger{
    use \sammo\DefaultActionTrigger;

    static $name = '도적';
    static $info = '';
    static $pros = '계략↑';
    static $cons = '금수입↓ 치안↓ 민심↓';


}