<?php
namespace sammo\Command;

use \sammo\{
    Util, JosaUtil, DB,
    General, GameConst,
    ActionLogger,
    LastTurn,
    NotInheritedMethodException
};

use function \sammo\getNationStaticInfo;

use \sammo\Constraint\Constraint;
use \sammo\Constraint\ConstraintHelper;

abstract class BaseCommand{
    static protected $actionName = 'CommandName';
    static public $reqArg = false;
    static protected $isLazyCalcReqTurn = false;

    public function getCommandDetailTitle():string{
        return $this->getName();
    }

    /** @var \sammo\General */
    protected $generalObj = null;
    protected $city = null;
    protected $nation = null;
    protected $arg = null;
    protected $env = null;

    /** @var \sammo\General */
    protected $destGeneralObj = null; 
    protected ?array $destCity = null;
    protected ?array $destNation = null;

    protected $cachedPermissionToReserve = false;
    protected $cachedMinConditionMet = false;
    protected $cachedFullConditionMet = false;

    protected $isArgValid=false;

    protected $reasonNotFullConditionMet = null;
    protected $reasonNotMinConditionMet = null;
    protected $reasonNoPermissionToReserve = null;

    protected $fullConditionConstraints = null;
    protected $minConditionConstraints = null;
    protected $permissionConstraints = null;

    protected $logger;

    protected $alternative = null;

    static protected $isInitStatic = true;
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

        
        $this->init();
        if ($this->argTest()) {
            $this->isArgValid = true;
            if(static::$reqArg){
                $this->initWithArg();
            }
        }
        else{
            $this->isArgValid = false;
        }
    }

    protected function resetTestCache():void{
        $this->cachedFullConditionMet = false;
        $this->cachedMinConditionMet = false;
        $this->cachedPermissionToReserve = false;

        $this->reasonNotFullConditionMet = null;
        $this->reasonNotMinConditionMet = null;
        $this->reasonNoPermissionToReserve = null;
    }
    
    protected function setCity(){
        $this->resetTestCache();
        $db = DB::db();
        $this->city = $this->generalObj->getRawCity();
        if($this->city){
            return;
        }
        $this->city = $db->queryFirstRow('SELECT * FROM city WHERE city=%i', $this->generalObj->getVar('city'));
        $this->generalObj->setRawCity($this->city);
        return;
    }

    protected function setNation(?array $args = null){
        $this->resetTestCache();
        if($args === null){
            if(!$this->nation){
                $this->nation = $this->generalObj->getStaticNation();
            }
            return;
        }

        $nationID = $this->generalObj->getNationID();
        if($nationID == 0){
            $this->nation = $this->generalObj->getStaticNation();
            return;
        }

        $defaultArgs = ['nation', 'name', 'color', 'type', 'level', 'capital', 'gennum'];
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

        if($this->nation && $this->nation['nation'] === $nationID){
            $allArgExists = true;
            foreach($args as $arg){
                if(!key_exists($arg, $this->nation)){
                    $allArgExists = false;
                    break;
                }
            }
            if($allArgExists){
                return;
            }
        }

        $db = DB::db();
        $nation = $db->queryFirstRow('SELECT %l FROM nation WHERE nation=%i', Util::formatListOfBackticks($args), $nationID);
        if($nation === null){
            $nation = [];
            foreach($args as $arg){
                $nation[$arg] = $defaultValues[$arg];
            }
        }

        if($this->nation){
            //NOTE: 이 순서 맞다! https://www.php.net/manual/en/language.operators.array.php
            $this->nation = $nation + $this->nation;
        }
        else{
            $this->nation = $nation;
        }
    }

    protected function setDestGeneral(General $destGeneralObj){
        $this->resetTestCache();
        $this->destGeneralObj = $destGeneralObj;
    }

    protected function setDestCity(int $cityNo, bool $onlyName=false){
        $this->resetTestCache();
        $db = DB::db();
        if($onlyName){
            $cityObj = \sammo\CityConst::byID($cityNo);
            $this->destCity = [
                'city'=>$cityNo,
                'name'=>$cityObj->name,
                'region'=>$cityObj->region,
            ];
            return;
        }
        $this->destCity = $db->queryFirstRow('SELECT * FROM city WHERE city=%i', $cityNo);
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
            $destNation = $defaultValues;
        }
        $this->destNation = $destNation;
    }

    abstract protected function init();
    protected function initWithArg(){
        if(static::$reqArg){
            throw new NotInheritedMethodException();
        }
    }
    abstract protected function argTest():bool;
    
    public function getArg():?array{
        return $this->arg;
    }

    public function getGeneral():General{
        return $this->generalObj;
    }

    public function getNationID(){
        return $this->generalObj->getNationID();        
    }

    public function getOfficerLevel(){
        return $this->generalObj->getVar('officer_level');
    }

    public function getBrief():string{
        return static::getName();
    }

    public function getRawClassName(bool $shortName=true):string{
        if($shortName){
            return Util::getClassNameFromObj($this);
        }
        return static::class;
    }

    public function getCompensationStyle():?int{
        //1 : Positive
        //0 : Neutral
        //-1 : Negative
        //null : can't calculate
        return 0;
    }
    
    static public function getName():string {
        return static::$actionName;
    }

    public function getLogger():?ActionLogger{
        return $this->logger;
    }

    abstract public function getNextExecuteKey():string;
    abstract public function getNextAvailableTurn():?int;
    abstract public function setNextAvailable(?int $yearMonth=null);

    protected function testPostReqTurn():?array{
        if(!$this->getPostReqTurn()){
            return null;
        }

        $nextAvailableTurn = $this->getNextAvailableTurn();
        if($nextAvailableTurn === null){
            return null;
        }

        $yearMonth = Util::joinYearMonth($this->env['year'], $this->env['month']);
        $remainTurn = $nextAvailableTurn - $yearMonth;
        if($remainTurn <= 0){
            return null;
        }

        return ['testPostReqTurn', "{$remainTurn}턴 더 기다려야 합니다"];
    }

    public function testPermissionToReserve():?string{
        if($this->cachedPermissionToReserve){
            return $this->reasonNoPermissionToReserve;
        }

        if(!$this->permissionConstraints){
            return null;
        }

        if($this->reasonNoPermissionToReserve){
            return $this->reasonNoPermissionToReserve;
        }

        $this->generalObj->unpackAux();
        $constraintInput = [
            'general'=>$this->generalObj->getRaw(),
            'city'=>$this->city,
            'nation'=>$this->nation,
            'cmd_arg'=>$this->arg,

            'destGeneral'=>$this->destGeneralObj?$this->destGeneralObj->getRaw():null,
            'destCity'=>$this->destCity,
            'destNation'=>$this->destNation,
        ];

        [$this->reasonConstraint, $this->reasonNoPermissionToReserve] = Constraint::testAll($this->permissionConstraints??[], $constraintInput, $this->env);
        $this->cachedPermissionToReserve = true;
        return $this->reasonNoPermissionToReserve;
    }

    public function canDisplay():bool{
        return true;
    }

    public function testMinConditionMet():?string{
        if(!static::$reqArg && !$this->minConditionConstraints){
            return $this->testFullConditionMet();
        }

        if($this->minConditionConstraints === null){
            throw new \InvalidArgumentException('minConditionConstraints가 제대로 설정되지 않았습니다');
        }

        if($this->cachedMinConditionMet){
            return $this->reasonNotMinConditionMet;
        }

        $this->generalObj->unpackAux();
        $constraintInput = [
            'general'=>$this->generalObj->getRaw(),
            'city'=>$this->city,
            'nation'=>$this->nation,
            'cmd_arg'=>$this->arg,

            'destGeneral'=>$this->destGeneralObj?$this->destGeneralObj->getRaw():null,
            'destCity'=>$this->destCity,
            'destNation'=>$this->destNation,
        ];

        [$this->reasonConstraint, $this->reasonNotMinConditionMet] = Constraint::testAll($this->minConditionConstraints??[], $constraintInput, $this->env);

        if($this->reasonNotMinConditionMet === null && !self::$isLazyCalcReqTurn){
            [$this->reasonConstraint, $this->reasonNotMinConditionMet] = $this->testPostReqTurn();
        }

        $this->cachedMinConditionMet = true;
        return $this->reasonNotMinConditionMet;
        
    }

    public function testFullConditionMet():?string{
        if(!$this->isArgValid()){
            $this->reasonNotFullConditionMet = '인자가 올바르지 않습니다.';
            $this->cachedFullConditionMet = true;
            return $this->reasonNotFullConditionMet;
        }

        if($this->fullConditionConstraints === null){
            throw new \InvalidArgumentException('fullConditionConstraints가 제대로 설정되지 않았습니다');
        }

        if($this->cachedFullConditionMet){
            return $this->reasonNotFullConditionMet;
        }

        $this->generalObj->unpackAux();
        $constraintInput = [
            'general'=>$this->generalObj->getRaw(),
            'city'=>$this->city,
            'nation'=>$this->nation,
            'cmd_arg'=>$this->arg,

            'destGeneral'=>$this->destGeneralObj?$this->destGeneralObj->getRaw():null,
            'destCity'=>$this->destCity,
            'destNation'=>$this->destNation,
        ];

        [$this->reasonConstraint, $this->reasonNotFullConditionMet] = Constraint::testAll($this->fullConditionConstraints??[], $constraintInput, $this->env);

        if($this->reasonNotFullConditionMet === null){
            [$this->reasonConstraint, $this->reasonNotFullConditionMet] = $this->testPostReqTurn();
        }

        $this->cachedFullConditionMet = true;
        return $this->reasonNotFullConditionMet;
        
    }

    public function getTermString():string{
        $commandName = $this->getName();
        $term = $this->getResultTurn()->getTerm();
        $termMax = $this->getPreReqTurn() + 1;
        return "{$commandName} 수행중... ({$term}/{$termMax})";
    }

    public function addTermStack():bool{
        if($this->getPreReqTurn() == 0){
            return true;
        }

        $lastTurn = $this->getLastTurn();
        $commandName = $this->getName();
        if($lastTurn->getCommand() != $commandName || $lastTurn->getArg() !== $this->arg){
            $this->setResultTurn(new LastTurn(
                $commandName,
                $this->arg,
                1
            ));
            return false;
        }

        if($lastTurn->getTerm() < $this->getPreReqTurn()){
            $this->setResultTurn(new LastTurn(
                $commandName,
                $this->arg,
                $lastTurn->getTerm() + 1
            ));
            return false;
        }

        return true;
    }

    public function hasPermissionToReserve():bool{
        return $this->testPermissionToReserve() === null;
    }

    public function isArgValid():bool{
        return $this->isArgValid;
    }

    public function hasMinConditionMet():bool {
        return $this->testMinConditionMet() === null;
    }

    public function hasFullConditionMet():bool {
        return $this->testFullConditionMet() === null;
    }

    public function getFailString():string{
        $commandName = $this->getName();
        $failReason = $this->testFullConditionMet();
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

    public function getJSFiles():array {
        return [];
    }
    public function getCSSFiles():array {
        return [];
    }
    public function getForm():string{
        throw new \sammo\MustNotBeReachedException();
    }

    public function getLastTurn():LastTurn{
        return $this->generalObj->getLastTurn();
    }

    public function setResultTurn(LastTurn $lastTurn){
        $this->generalObj->_setResultTurn($lastTurn);
    }

    public function getResultTurn():LastTurn{
        return $this->generalObj->getResultTurn();
    }
}