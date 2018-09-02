<?php
namespace sammo\TriggerNationType;
use \sammo\iActionTrigger;

class che_도가 implements iActionTrigger{
    use \sammo\DefaultActionTrigger;

    static $id = 6;
    static $name = '도가';
    static $info = '';
    static $pros = '인구↑';
    static $cons = '기술↓ 치안↓';


}