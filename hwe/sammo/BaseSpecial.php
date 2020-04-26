<?php
namespace sammo;
use \sammo\iAction;
use \sammo\General;

abstract class BaseSpecial implements iAction{
    use \sammo\DefaultAction;

    protected $id = 0;
    protected $name = '-';
    protected $info = '';

    /** @var int */
    static $selectWeightType;
    /** @var int */
    static $selectWeight;
    /** @var int[] */
    static $type;
}