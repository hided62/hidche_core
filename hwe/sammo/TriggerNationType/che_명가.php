<?php
namespace sammo\TriggerNationType;
use \sammo\iActionTrigger;
use \sammo\General;

class che_명가 implements iActionTrigger{
    use \sammo\DefaultActionTrigger;

    static $name = '명가';
    static $info = '';
    static $pros = '기술↑ 인구↑';
    static $cons = '쌀수입↓ 수성↓';


}