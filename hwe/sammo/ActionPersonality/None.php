<?php
namespace sammo\ActionPersonality;
use \sammo\iAction;

class None implements iAction{
    use \sammo\DefaultAction;

    static $id = -1;
    static $name = '-';
    static $info = '';
}