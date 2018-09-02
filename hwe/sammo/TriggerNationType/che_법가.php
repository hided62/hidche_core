<?php
namespace sammo\TriggerNationType;
use \sammo\iActionTrigger;

class che_법가 implements iActionTrigger{
    use \sammo\DefaultActionTrigger;

    static $id = 1;
    static $name = '법가';
    static $info = '';
    static $pros = '금수입↑ 치안↑';
    static $cons = '인구↓ 민심↓';


}