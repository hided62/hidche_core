<?php
namespace sammo\Command;

use \sammo\{
    Util, JosaUtil, DB,
    General, GameConst,
    ActionLogger
};

use \sammo\Constraint\Constraint;
use \sammo\Constraint\ConstraintHelper;

abstract class BaseCommand{
    /**
     * @var General|null $generalObj
     * @var General|null $destGeneralObj
     */
    static protected $actionName = 'CommandName';
    static public $reqArg = false;

    public function getCommandDetailTitle():string{
        return $this->getName();
    }

    protected $generalObj = null;
    protected $city = null;
    protected $nation = null;
    protected $arg = null;
    protected $env = null;

    protected $destGeneralObj = null;
    protected $destCity = null;
    protected $destNation = null;

    protected $runnable = null;
    protected $reservable = null;

    protected $isArgValid=false;

    protected $reasonNotRunnable = null;
    protected $reasonNotReservable = null;

    protected $runnableConstraints = null;
    protected $reservableConstraints = null;

    protected $logger;

    protected $alternative = null;

    static protected $isInitStatic = false;
    protected static function initStatic(){

    }

    public function __construct(General $generalObj, array $env, $arg = null){
        if(!static::$isInitStatic){
            static::initStatic();
            static::$isInitStatic = true;
        }

        $this->generalObj = $generalObj;
        $this->logger = $generalObj->getLogger();
        $this->env = $env;
        $this->arg = $arg;
        if (!$this->argTest()) {
           return;
        }
        $this->isArgValid = true;
        $this->init();
        
    }

    protected function resetTestCache():void{
        $this->runnable = null;
        $this->reservable = null;

        $this->reasonNotRunnable = null;
        $this->reasonNotReservable = null;
    }
    
    protected function setCity(?array $args=null){
        $this->resetTestCache();
        $db = DB::db();
        if($args === null){
            $this->city = $this->generalObj->getRawCity();
            if($this->city){
                return;
            }
            $this->city = $db->queryFirstRow('SELECT * FROM city WHERE city=%i', $this->generalObj->getVar('city'));
            $this->generalObj->setRawCity($this->city);
            return;
        }
        
        $this->city = $this->generalObj->getRawCity();
        $hasArgs = true;
        foreach($args as $arg){
            if(!key_exists($arg, $this->city)){
                $hasArgs = false;
                break;
            }
        }
        if($hasArgs){
            return;
        }
        
        $this->city = $db->queryFirstRow('SELECT %l FROM city WHERE city=%i', Util::formatListOfBackticks($args), $this->generalObj->getVar('city'));
        if($this->generalObj->getRawCity() === null){
            $this->generalObj->setRawCity($this->city);
        }
    }

    protected function setNation(?array $args = null){
        $this->resetTestCache();
        if($args === null){
            $this->nation = $this->generalObj->getStaticNation();
            return;
        }

        $nationID = $this->generalObj->getNationID();
        if($nationID == 0){
            $this->nation = $this->generalObj->getStaticNation();
            return;
        }

        $defaultArgs = ['nation', 'name', 'color', 'type', 'level', 'capital'];
        $args = array_unique(array_merge($defaultArgs, $args));
        if($args == $defaultArgs){
            $this->nation = $this->generalObj->getStaticNation();
            return;
        }
        
        $defaultValues = [
            'nation'=>0,
            'name'=>'재야',
            'color'=>'#000000',
            'type'=>GameConst::$neutralNationType,
            'level'=>0,
            'capital'=>0,
            'gold'=>0,
            'rice'=>2000,
            'tech'=>0,
            'gennum'=>1  
        ];

        

        $db = DB::db();
        $destNation = $db->queryFirstRow('SELECT %l FROM nation WHERE nation=%i', Util::formatListOfBackticks($args), $nationID);
        if($destNation === null){
            $destNation = [];
            foreach($args as $arg){
                $destNation[$arg] = $defaultValues[$arg];
            }
        }
        $this->destNation = $destNation;
    }

    protected function setDestGeneral(General $destGeneralObj){
        $this->resetTestCache();
        $this->destGeneralObj = $destGeneralObj;
    }

    protected function setDestCity(int $cityNo, ?array $args){
        $this->resetTestCache();
        $db = DB::db();
        if($args === []){
            $cityObj = \sammo\CityConst::byID($cityNo);
            $this->destCity = ['city'=>$cityNo, 'name'=>$cityObj->name];
            return;
        }
        if($args === null){
            $this->destCity = $db->queryFirstRow('SELECT * FROM city WHERE city=%i', $cityNo);
            return;
        }
        $this->destCity = $db->queryFirstRow('SELECT %l FROM city WHERE city=%i', Util::formatListOfBackticks($args), $cityNo);
    }

    protected function setDestNation(int $nationID, ?array $args = null){
        $this->resetTestCache();
        if($args === null || $args === []){
            $this->destNation = getNationStaticInfo($nationID);
            return;
        }

        $defaultArgs = ['nation', 'name', 'color', 'type', 'level', 'capital'];
        $args = array_unique(array_merge($defaultArgs, $args));

        $defaultValues = [
            'nation'=>0,
            'name'=>'재야',
            'color'=>'#000000',
            'type'=>GameConst::$neutralNationType,
            'level'=>0,
            'capital'=>0,
            'gold'=>0,
            'rice'=>2000,
            'tech'=>0,
            'gennum'=>1  
        ];

        $db = DB::db();
        $destNation = $db->queryFirstRow('SELECT %l FROM nation WHERE nation=%i', Util::formatListOfBackticks($args), $nationID);
        if($destNation === null){
            $destNation = [];
            foreach($args as $arg){
                $destNation[$arg] = $defaultValues[$arg];
            }
        }
        $this->destNation = $destNation;
    }

    abstract protected function init();
    abstract protected function argTest():bool;

    public function getBrief():string{
        return static::getName();
    }

    public function getCompensationStyle():?int{
        //1 : Positive
        //0 : Neutral
        //-1 : Negative
        //null : can't calculate
        //TODO: 구현
        return 0;
    }
    
    static public function getName():string {
        return static::$actionName;
    }

    public function getLogger():ActionLogger{
        return $this->logger;
    }

    public function testReservable():?string{
        if($this->reservableConstraints === null){
            return true;
        }

        if($this->reasonNotReservable){
            return $this->reasonNotReservable;
        }

        $constraintInput = [
            'general'=>$this->generalObj->getRaw(),
            'city'=>$this->city,
            'nation'=>$this->nation,
            'arg'=>$this->arg,

            'destGeneral'=>$this->destGeneralObj->getRaw(),
            'destCity'=>$this->destCity,
            'destNation'=>$this->destNation,
        ];

        $this->reasonNotReservable = Constraint::testAll($this->reservableConstraints, $constraintInput);
        $this->reservable = $this->reasonNotReservable === null;
        return $this->reasonNotReservable;
    }

    public function canDisplay():bool{
        return true;
    }

    public function testRunnable():?string{
        if(!$this->isArgValid()){
            throw new \InvalidArgumentException('인자가 제대로 설정되지 않았습니다');
        }        
        if($this->runnableConstraints === null){
            throw new \InvalidArgumentException('runnableConstraits가 제대로 설정되지 않았습니다');
        }

        if(!$this->isReservable()){
            return $this->testReservable();
        }

        if($this->reasonNotRunnable){
            return $this->reasonNotRunnable;
        }

        $constraintInput = [
            'general'=>$this->generalObj->getRaw(),
            'city'=>$this->city,
            'nation'=>$this->nation,
            'arg'=>$this->arg,

            'destGeneral'=>$this->destGeneralObj->getRaw(),
            'destCity'=>$this->destCity,
            'destNation'=>$this->destNation,
        ];

        $this->reasonNotRunnable = Constraint::testAll($this->runnableConstraints, $constraintInput);
        $this->runnable = $this->reasonNotRunnable === null;
        return $this->reasonNotRunnable;
        
    }

    public function isReservable():bool{
        if($this->reservable !== null){
            return $this->reservable;
        }

        return $this->testReservable() === null;
    }

    public function isArgValid():bool{
        return $this->isArgValid;
    }

    public function isRunnable():bool {
        if($this->runnable !== null){
            return $this->runnable;
        }
        
        return $this->testRunnable() === null;
    }

    public function getFailString():string{
        $commandName = $this->getName();
        $failReason = $this->testRunnable();
        if($failReason === null){
            throw new \RuntimeException('실행 가능한 커맨드에 대해 실패 이유를 수집');
        }
        return "{$failReason} {$commandName} 실패.";
    }

    public function getAlternativeCommand():?self{
        return $this->alternative;
    }

    abstract public function getCost():array;

    abstract public function getPreReqTurn():int;
    abstract public function getPostReqTurn():int;

    abstract public function run():bool;

    
}