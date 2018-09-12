<?php
namespace sammo\TriggerCharacter;
use \sammo\iActionTrigger;

class None implements iActionTrigger{
    use \sammo\DefaultActionTrigger;

    static $id = -1;
    static $name = '-';
    static $info = '';
}