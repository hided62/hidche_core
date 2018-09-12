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

    protected $logger;

    public function __construct(General $generalObj, array $env, $arg){
        $this->generalObj = $generalObj;
        $this->logger = $generalObj->getLogger();
        $this->env = $env;
        $this->arg = $arg;
        $this->init();
    }
    
    protected function setCity(array $args=null){
        $db = DB::db();
        if($args == null){
            $this->city = $this->generalObj->getRawCity();
            if($this->city){
                return;
            }
            $this->city = $db->queryFirstRow('SELECT * FROM city WHERE city=%i', $this->generalObj->getVar('city'));
            return;
        }
        $this->city = $db->queryFirstRow('SELECT %lb FROM city WHERE city=%i', $args, $this->generalObj->getVar('city'));
    }

    protected function setNation(?array $args = null){
        if($args == null){
            $this->nation = $this->generalObj->getStaticNation();
            return;
        }

        $db = DB::db();
        $this->nation = $db->queryFirstRow('SELECT %lb FROM nation WHERE nation=%i', $args, $this->generalObj->getVar('nation'));
    }

    protected function setDestGeneralFromObj(General $destGeneral){
        $this->destGeneral = $destGeneral->getRaw();
    }

    protected function setDestGeneral(int $generalNo, array $args){
        $db = DB::db();
        $this->destGeneral = $db->queryFirstRow('SELECT %lb FROM general WHERE no=%i', $args, $generalNo);
    }

    protected function setDestCity(int $cityNo, ?array $args){
        $db = DB::db();
        if($args == null){
            $this->destCity = $db->queryFirstRow('SELECT * FROM city WHERE city=%i', $cityNo);
            return;
        }
        $this->destCity = $db->queryFirstRow('SELECT %lb FROM city WHERE city=%i', $args, $cityNo);
    }

    protected function setDestNation(int $nationNo, ?array $args = null){
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

    public function test():?string{
        if($this->constraints === null){
            throw new \InvalidArgumentException('인자가 제대로 설정되지 않았습니다');
        }

        $this->reason = Constraint::testAll($this->constraints, [
            'general'=>$this->generalObj->getRaw(),
            'city'=>$this->city,
            'nation'=>$this->city,
            'arg'=>$this->arg,

            'destGeneral'=>$this->destGeneral,
            'destCity'=>$this->destCity,
            'destNation'=>$this->destNation,
        ]);
        $this->available = $this->reason === null;
        return $this->reason;
        
    }
    public function isAvailable():bool {
        if($this->available === null){
            $this->test();
        }
        return $this->available;
        
    }

    abstract public function getCost():array;

    abstract public function run():bool;

    
}