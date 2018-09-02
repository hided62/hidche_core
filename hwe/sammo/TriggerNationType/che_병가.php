<?php
namespace sammo\TriggerNationType;
use \sammo\iActionTrigger;
use \sammo\General;

class che_병가 implements iActionTrigger{
    use \sammo\DefaultActionTrigger;

    static $id = 3;
    static $name = '병가';
    static $info = '';
    static $pros = '기술↑ 수성↑';
    static $cons = '인구↓ 민심↓';


}