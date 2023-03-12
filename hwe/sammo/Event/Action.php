<?php
namespace sammo\Event;

abstract class Action{
    //public abstract function __construct(...$args);
    /*
    TODO: event trigger를 인자로 보낼 수 있으면 좋을 것
    예시로, 도시 점령 시, 점령한 도시의 정보를 인자로 보낼 수 있으면 좋을 것
    /*/
    public abstract function run(array $env);

    public static function build($actionArgs):Action{
        if(!is_array($actionArgs)){
            throw new \InvalidArgumentException('action을 입력해야 합니다.');
        }

        $className = __NAMESPACE__.'\\Action\\'.$actionArgs[0];
        if(!class_exists($className)){
            throw new \InvalidArgumentException('존재하지 않는 Action입니다 :'.$actionArgs[0]);
        }

        $args = array_slice($actionArgs, 1);
        /** @var \ReflectionClass<Action> */
        $ref = new \ReflectionClass($className);
        return $ref->newInstanceArgs($args);
    }
}