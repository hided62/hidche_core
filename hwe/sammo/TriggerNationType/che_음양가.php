<?php
namespace sammo\TriggerNationType;
use \sammo\iActionTrigger;

class che_음양가 implements iActionTrigger{
    use \sammo\DefaultActionTrigger;

    static $id = 12;
    static $name = '음양가';
    static $info = '';
    static $pros = '내정↑ 인구↑';
    static $cons = '기술↓ 전략↓';


}