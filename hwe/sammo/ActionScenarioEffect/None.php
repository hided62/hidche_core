<?php
namespace sammo\ActionScenarioEffect;
use \sammo\iAction;
use \sammo\General;

class None implements iAction{
    use \sammo\DefaultAction;

    protected $id = -1;
    protected $name = '-';
    protected $info = '';
}