<?php
namespace sammo\Event\Action;
use sammo\Util;
use sammo\DB;

//기존 시나리오에서 개시 1월에 내정을 깎는 것을 모사.
class ChangeCity extends \sammo\Event\Action{
    const AVAILABLE_KEY = [
        'pop'=>true,
        'agri'=>true,
        'comm'=>true,
        'secu'=>true,
        'rate'=>true,
        'def'=>true,
        'wall'=>true
    ];
    const REGEXP_PERCENT = '/^(\d+(\.\d+)?)%$/';// 123.5%   [1]=float
    const REGEXP_MATH = '/^([\+\-\/\*])(\d+(\.\d+)?)$/'; //+30 [1]=기호, [2]=float

    private $queries;
    private $targetType = 'all';
    private $targetArgs = [];
    public function __construct($target, array $actions){

        //values 포맷은 key, value로 

        if(!$target){
            $this->targetType = 'all';
        }
        else if(is_string($target)){
            $this->targetType = $target;
        }
        else if(is_array($target)){
            $this->targetType = $target[0];
            $this->targetArgs = array_slice($target, 1);
        }
        else{
            throw new \InvalidArgumentException('올바르지 않은 targetType 입니다.');
        }
        

        $queries = [];
        foreach($actions as $key => $value){
            if(!key_exists($key, self::AVAILABLE_KEY)){
                throw new \InvalidArgumentException('지원하지 않는 city 인자입니다 :'.$key);
            }

            if(!is_int($value) && !is_float($value) && !is_string($value)){
                throw new \InvalidArgumentException('int, float, string이어야 합니다.');
            }

            if($key == 'rate'){
                $queries['rate'] = $this->genSQLRate($value);
                continue;
            }

            $queries[$key] = $this->genSQLGeneric($key, $value);
        }
        
        $this->queries = $queries;
    }

    private function genSQLRate($value){
        //민심은 max값이 100으로 고정이므로 처리 방식이 다름.
        if(is_float($value)){
            if($value < 0){
                throw new \InvalidArgumentException('음수를 곱할 수 없습니다.');
            }
            return DB::db()->sqleval('least(100, ROUND(`rate` * %d, 0))', $value);
        }
        if(is_int($value)){
            return DB::db()->sqleval('%i', Util::valueFit($value, 0, 100));
        }

        $matches = null;
        if(preg_match(self::REGEXP_PERCENT, $value, $matches)){
            $value = Util::round((float)$matches[1]);
            return DB::db()->sqleval('%i', Util::valueFit($value, 0, 100));
        }

        if(preg_match(self::REGEXP_MATH, $value, $matches)){
            $op = $matches[1];
            $value = $matches[2];
            if($op == '/' && $value == 0){
                throw new \InvalidArgumentException('0으로 나눌 수 없습니다.');
            }
            return DB::db()->sqleval('least(100, greatest(0, ROUND(`rate` %l %d, 0)))', $op, $value);
        }
        
        throw new \InvalidArgumentException('알 수 없는 패턴입니다.');

    }

    private function genSQLGeneric($key, $value){
        $keyMax = $key.'2'; //comm, comm2

        if(is_float($value)){
            if($value < 0){
                throw new \InvalidArgumentException('음수를 곱할 수 없습니다.');
            }
            return DB::db()->sqleval('least(%b, ROUND(%b * %d, 0))', $keyMax, $key, $value);
        }
        if(is_int($value)){
            return DB::db()->sqleval('least(%b, %i)', $keyMax, max(0, $value));
        }

        $matches = null;
        if(preg_match(self::REGEXP_PERCENT, $value, $matches)){
            $value = Util::round((float)$matches[1]);
            return DB::db()->sqleval('ROUND(%b * %d, 0)', $keyMax, $value/100);
        }

        if(preg_match(self::REGEXP_MATH, $value, $matches)){
            $op = $matches[1];
            $value = $matches[2];
            if($op == '/' && $value == 0){
                throw new \InvalidArgumentException('0으로 나눌 수 없습니다.');
            }
            return DB::db()->sqleval('least(%b, greatest(0, ROUND($b %l %d, 0)))', $keyMax, $key, $op, $value);
        }
        
        throw new \InvalidArgumentException('알 수 없는 패턴입니다.');

    }

    private function getTargetCities($env){

        $targetType = $this->targetType;
        if($targetType == 'all'){
            return DB::db()->queryFirstColumn('SELECT city FROM city');  
        }

        if($targetType == 'free'){
            return DB::db()->queryFirstColumn('SELECT city FROM city WHERE nation = 0');
        }

        if($targetType == 'occupied'){
            return DB::db()->queryFirstColumn('SELECT city FROM city WHERE nation != 0');
        }

        if($targetType == 'cities'){
            if(is_numeric($this->targetArgs)){
                return DB::db()->queryFirstColumn('SELECT city FROM city WHERE city IN (%ls)', $this->targetArgs);
            }
            else{
                return DB::db()->queryFirstColumn('SELECT city FROM city WHERE name IN (%ls)', $this->targetArgs);
            }
            
        }

        throw new \InvalidArgumentException('올바르지 않은 cond 입니다.');
    }

    public function run($env=null){
        $cities = $this->getTargetCities($env);

        DB::db()->update('city', 
            $this->queries
        , 'city IN %li', $cities);
        return [__CLASS__, DB::db()->affectedRows()];
    }

}