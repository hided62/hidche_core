<?php
namespace sammo\Constraint;

abstract class Constraint{
    private function __construct(){

    }

    const REQ_GENERAL = 0x10;
    const REQ_CITY = 0x20;
    const REQ_NATION = 0x40;
    const REQ_ARG = 0x80;
    const REQ_DEST_GENERAL = 0x100;
    const REQ_DEST_CITY = 0x200;
    const REQ_DEST_NATION = 0x400;
    const REQ_STRING_ARG = self::REQ_ARG | 0x1000;
    const REQ_INT_ARG = self::REQ_ARG | 0x2000;
    const REQ_NUMERIC_ARG = self::REQ_ARG | 0x4000;
    const REQ_BOOLEAN_ARG = self::REQ_ARG | 0x8000;
    const REQ_ARRAY_ARG = self::REQ_ARG | 0x10000;
    
    const REQ_VALUES = 0;

    protected $general = null;
    protected $city = null;
    protected $nation = null;
    protected $arg = null;

    protected $env = null;

    protected $cmd_arg = null;

    protected $destGeneral = null;
    protected $destCity = null;
    protected $destNation = null;

    protected $tested = false;
    protected $reason = null;

    abstract public function test():bool;


    static public function requiredValueType():int{
        return static::REQ_VALUES;
    }

    public function setGeneral(array $general){
        $this->general = $general;
        $this->tested = false;
        $this->reason = null;
    }
    public function setCity(array $city){
        $this->city = $city;
        $this->tested = false;
        $this->reason = null;
    }
    public function setNation(array $nation){
        $this->nation = $nation;
        $this->tested = false;
        $this->reason = null;
    }
    public function setArg($arg){
        $this->arg = $arg;
        $this->tested = false;
        $this->reason = null;
    }
    public function setEnv($env){
        $this->env = $env;
        $this->tested = false;
        $this->reason = null;
    }
    public function setCmdArg($cmd_arg){
        $this->cmd_arg = $cmd_arg;
        $this->tested = false;
        $this->reason = null;
    }

    public function setDestGeneral(array $general){
        $this->destGeneral = $general;
        $this->tested = false;
        $this->reason = null;
    }
    public function setDestCity(array $city){
        $this->destCity = $city;
        $this->tested = false;
        $this->reason = null;
    }
    public function setDestNation(array $nation){
        $this->destNation = $nation;
        $this->tested = false;
        $this->reason = null;
    }

    static public function build(array $input):self{
        $self = new static();
        foreach($input as $key=>$value){
            if($value === null){
                continue;
            }
            switch($key){
                case 'general': $self->setGeneral($value); break;
                case 'city': $self->setCity($value); break;
                case 'nation': $self->setNation($value); break;
                case 'cmd_arg': $self->setCmdArg($value); break;

                case 'destGeneral': $self->setDestGeneral($value); break;
                case 'destCity': $self->setDestCity($value); break;
                case 'destNation': $self->setDestNation($value); break;
            }
        }
        
        return $self;
    }

    public function checkInputValues(bool $throwExeception=true):bool{
        $valueType = static::requiredValueType();

        if(($valueType&static::REQ_GENERAL) && $this->general === null){
            if(!$throwExeception){return false; }
            throw new \InvalidArgumentException('require general');
        }

        if(($valueType&static::REQ_CITY) && $this->city === null){
            if(!$throwExeception){return false; }
            throw new \InvalidArgumentException('require city');
        }

        if(($valueType&static::REQ_NATION) && $this->nation === null){
            if(!$throwExeception){return false; }
            throw new \InvalidArgumentException('require nation');
        }

        if(($valueType&static::REQ_DEST_GENERAL) && $this->destGeneral === null){
            if(!$throwExeception){return false; }
            throw new \InvalidArgumentException('require dest general');
        }

        if(($valueType&static::REQ_DEST_CITY) && $this->destCity === null){
            if(!$throwExeception){return false; }
            throw new \InvalidArgumentException('require dest city');
        }

        if(($valueType&static::REQ_DEST_NATION) && $this->destNation === null){
            if(!$throwExeception){return false; }
            throw new \InvalidArgumentException('require dest nation');
        }

        if (!($valueType&static::REQ_ARG)) {
           return true;
        }

        if($valueType === null){
            if(!$throwExeception){return false; }
            throw new \InvalidArgumentException('require arg');
        }

        if((($valueType&static::REQ_STRING_ARG)===static::REQ_STRING_ARG) && !is_string($this->arg)){
            if(!$throwExeception){return false; }
            throw new \InvalidArgumentException('require string arg');
        }

        if((($valueType&static::REQ_BOOLEAN_ARG)===static::REQ_BOOLEAN_ARG) && !is_bool($this->arg)){
            if(!$throwExeception){return false; }
            throw new \InvalidArgumentException('require bool arg');
        }

        if((($valueType&static::REQ_INT_ARG)===static::REQ_INT_ARG) && !is_int($this->arg)){
            if(!$throwExeception){return false; }
            throw new \InvalidArgumentException('require int arg');
        }

        if((($valueType&static::REQ_NUMERIC_ARG)===static::REQ_NUMERIC_ARG) && !is_numeric($this->arg)){
            if(!$throwExeception){return false; }
            throw new \InvalidArgumentException('require numeric arg');
        }

        if((($valueType&static::REQ_ARRAY_ARG)===static::REQ_ARRAY_ARG) && !is_array($this->arg)){
            if(!$throwExeception){return false; }
            throw new \InvalidArgumentException('require array arg');
        }

        return true;
    }

    public function reason():?string{
        if($this->tested === false){
            throw new \RuntimeException(get_class($this).'::test가 실행되지 않음');
        }
        return $this->reason;
    }

    public static function testAll(array $constraintPacks, array $input, array $env):?array{
        foreach($constraintPacks as $constraintArgs){
            if (!$constraintArgs){
                continue;
            }

            $constraintName = $constraintArgs[0];
            $method = __NAMESPACE__.'\\'.$constraintName.'::build';

            /** @var \sammo\Constraint\Constraint $contraint */
            $constraint = call_user_func($method,$input);

            assert($constraint instanceof Constraint);
            $constraint->setEnv($env);
            if(count($constraintArgs) == 2){
                $arg = $constraintArgs[1];
                $constraint->setArg($arg);
            }
        

            if(!$constraint->test()){

                if($constraint->reason() === null){
                    throw new \RuntimeException('Reason is not set:'.$constraintName);
                }
                return [$constraintName, $constraint->reason()];
            }
        }

        return null;
    }

}