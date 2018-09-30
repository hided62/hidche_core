<?php
namespace sammo;

abstract class GeneralTrigger extends ObjectTrigger{
    abstract public function __construct(General $general);
}
