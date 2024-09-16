<?php
namespace sammo;

abstract class BaseStaticEvent {
    
    function __construct(){

    }

    abstract function run(General $general, array $env, array $params): bool | string;
}