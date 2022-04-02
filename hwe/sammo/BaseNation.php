<?php
namespace sammo;
use \sammo\iAction;
use \sammo\General;

abstract class BaseNation implements iAction{
    use \sammo\DefaultAction;

    protected $name = '-';
    protected $info = '';
    static $pros = '';
    static $cons = '';

    public function getInfo(): string{
        $pros = static::$pros;
        $cons = static::$cons;
        return "{$pros} {$cons}";
    }
}