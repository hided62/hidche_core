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

    protected $available = null;
    protected $reason = null;

    protected $constraints = null;
    protected $constraintsLight = null;

    protected $logger;

    public function __construct(General $generalObj, array $env, $arg = null){
        $this->generalObj = $generalObj;
        $this->logger = $generalObj->getLogger();
        $this->env = $env;
        $this->arg = $arg;
        $this->init();
    }

    protected function resetTestCache():void{
        $this->reason = null;
        $this->available = null;
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
    
    public function getName():string {
        return static::$name;
    }

    public function getLogger():ActionLogger{
        return $this->logger;
    }

    public function test(bool $fullCheck):?string{
        if($this->constraints === null){
            throw new \InvalidArgumentException('인자가 제대로 설정되지 않았습니다');
        }

        if($this->reason){
            return $this->reason;
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

        if(!$fullCheck && $this->constraintsLight){
            return Constraint::testAll($this->constraintsLight, $constraintInput);
        }

        $this->reason = Constraint::testAll($this->constraints, $constraintInput);
        $this->available = $this->reason === null;
        return $this->reason;
        
    }
    public function isAvailable(bool $fullCheck=true):bool {
        if($this->available !== null){
            return $this->available;
        }

        return $this->test($fullCheck) === null;
    }

    abstract public function getCost():array;

    abstract public function run():bool;

    
}