<?php
namespace sammo\TriggerPersonality;
use \sammo\iActionTrigger;

class None implements iActionTrigger{
    use \sammo\DefaultActionTrigger;

    static $id = -1;
    static $name = '-';
    static $info = '';
}