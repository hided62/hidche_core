<?php
namespace sammo\ActionNationType;
use \sammo\iAction;

class None implements iAction{
    use \sammo\DefaultAction;

    protected $name = '-';
    protected $info = '';
    static $pros = '';
    static $cons = '';
}