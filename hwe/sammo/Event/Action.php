<?php
namespace sammo\Event;

abstract class Action{
    //public abstract function __construct(...$args);
    public abstract function run($env=null);

    public static function build($actionArgs):Action{
        if(!is_array($actionArgs)){
            throw new \InvalidArgumentException('action을 입력해야 합니다.');
        }

        $className = __NAMESPACE__.'\\Action\\'.$actionArgs[0];
        if(!class_exists($className)){
            throw new \InvalidArgumentException('존재하지 않는 Action입니다 :'.$actionArgs[0]);
        }

        $args = array_slice($actionArgs, 1);
        $ref = new \ReflectionClass($className);
        return $ref->newInstanceArgs($args);
    }
}