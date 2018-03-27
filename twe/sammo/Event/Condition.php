<?php
namespace sammo\Event;

abstract class Condition{
    public abstract function __construct($args=null);
    public abstract function eval($env=null);

    public static function build($conditionChain){
        if(!is_array($conditionChain)){
            return $conditionChain;
        }

        $key = $conditionChain[0];
        if(\array_key_exists(strtolower($key), Condition\Logic::AVAILABLE_LOGIC_NAME)){
            //logic 단축 명령.
            return new Condition\Logic($key, array_slice($conditionChain, 1));
        }

        $className = 'sammo\\Event\\Condition\\'.$key;
        if(class_exists($className)){
            $args = [];

            reset($conditionChain);
            while (next($conditionChain) !== FALSE)
            {
                $args[] = static::build(current($conditionChain));
            }

            $ref = new \ReflectionClass($className);
            return $ref->newInstanceArgs($args);
        }

        //array의 첫번째 값이 Condition이 아닌 경우에는 그냥 배열로 처리함.
        return array_map(static::build, $conditionChain);
    }

    protected static function _eval($arg, $env=null){
        if(is_bool($arg)){
            return [
                'value'=>$arg,
                'chain'=>['boolean']
            ];
        }
        if($arg instanceof Condition){
            return $arg->checkCondition($env);
        }
        throw new \InvalidArgumentException('평가 인자는 boolean이거나 Condition 클래스여야 합니다.');
    }
}