<?php
namespace sammo;

abstract class BaseStaticEvent {
    
    function __construct(){

    }

    abstract function run(GeneralLite|General $general, null|GeneralLite|General $destGeneral, array $env, array $params): bool | string;
}