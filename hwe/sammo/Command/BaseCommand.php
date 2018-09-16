<?php
namespace sammo\Command;

use \sammo\{
    Util, JosaUtil, DB,
    General, 
    ActionLogger
};

use \sammo\Constraint\Constraint;

abstract class BaseCommand{
    /**
     * @var General|null $generalObj
     * @var array|null $destGeneral
     */
    protected $id = 0;
    protected $name = 'CommandName';

    protected $generalObj = null;
    protected $city = null;
    protected $nation = null;
    protected $arg = null;
    protected $env = null;

    protected $destGeneral = null;
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

    public function __construct(General $generalObj, array $env, $arg = null){
        $this->generalObj = $generalObj;
        $this->logger = $generalObj->getLogger();
        $this->env = $env;
        $this->arg = $arg;
        if ($this->argTest()) {
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
    
    protected function setCity(array $args=null){
        $this->resetTestCache();
        $db = DB::db();
        if($args == null){
            $this->city = $this->generalObj->getRawCity();
            if($this->city){
                return;
            }
            $this->city = $db->queryFirstRow('SELECT * FROM city WHERE city=%i', $this->generalObj->getVar('city'));
            $this->generalObj->setRawCity($this->city);
            return;
        }
        $this->city = $db->queryFirstRow('SELECT %lb FROM city WHERE city=%i', $args, $this->generalObj->getVar('city'));
        if($this->generalObj->getRawCity() === null){
            $this->generalObj->setRawCity($this->city);
        }
    }

    protected function setNation(?array $args = null){
        $this->resetTestCache();
        if($args == null){
            $this->nation = $this->generalObj->getStaticNation();
            return;
        }

        $db = DB::db();
        $this->nation = $db->queryFirstRow('SELECT %lb FROM nation WHERE nation=%i', $args, $this->generalObj->getVar('nation'));
    }

    protected function setDestGeneralFromObj(General $destGeneral){
        $this->resetTestCache();
        $this->destGeneral = $destGeneral->getRaw();
    }

    protected function setDestGeneral(int $generalNo, array $args){
        $this->resetTestCache();
        $db = DB::db();
        $this->destGeneral = $db->queryFirstRow('SELECT %lb FROM general WHERE no=%i', $args, $generalNo);
    }

    protected function setDestCity(int $cityNo, ?array $args){
        $this->resetTestCache();
        $db = DB::db();
        if($args == null){
            $this->destCity = $db->queryFirstRow('SELECT * FROM city WHERE city=%i', $cityNo);
            return;
        }
        $this->destCity = $db->queryFirstRow('SELECT %lb FROM city WHERE city=%i', $args, $cityNo);
    }

    protected function setDestNation(int $nationNo, ?array $args = null){
        $this->resetTestCache();
        if($args == null){
            $this->destNation = getNationStaticInfo($nationNo);
            return;
        }

        $db = DB::db();
        $this->destNation = $db->queryFirstRow('SELECT %lb FROM nation WHERE nation=%i', $args, $nationNo);
    }

    abstract protected function init();
    abstract protected function argTest():bool;
    
    public function getName():string {
        return static::$name;
    }

    public function getLogger():ActionLogger{
        return $this->logger;
    }

    public function testReservable():?string{

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
            'nation'=>$this->city,
            'arg'=>$this->arg,

            'destGeneral'=>$this->destGeneral,
            'destCity'=>$this->destCity,
            'destNation'=>$this->destNation,
        ];

        if(!$fullCheck && $this->reservableConstraints){
            return Constraint::testAll($this->reservableConstraints, $constraintInput);
        }

        $this->reasonNotRunnable = Constraint::testAll($this->runnableConstraints, $constraintInput);
        $this->runnable = $this->reason === null;
        return $this->reason;
        
    }

    public function isReservable():bool{

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

    abstract public function getCost():array;

    abstract public function run():bool;

    
}