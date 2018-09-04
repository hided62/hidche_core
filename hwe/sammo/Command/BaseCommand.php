<?php
namespace sammo\Command;

use \sammo\{
    Util, JosaUtil,
    General,
};

use \sammo\Constraint\{
    Constraint, NoNeutral, NoOpeningPart, NoWanderingNation, OccupiedCity,
     RemainCityCapacity, ReqGeneralGold, SuppliedCity
};
abstract class BaseCommand{
    protected $id = 0;
    protected $name = 'CommandName';

    abstract public function __construct(General $general, ...$args);

    public function getName():string {
        return static::$name;
    }
}