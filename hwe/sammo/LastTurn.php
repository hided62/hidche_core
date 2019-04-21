<?php
namespace sammo;

class LastTurn{
    protected $command = '휴식';
    protected $arg = null;
    protected $term = null;

    function __construct(?string $command=null, ?array $arg=null, ?int $term=null)
    {
        $this->setCommand($command);
        $this->setArg($arg);
        $this->setTerm($term);
    }

    static function fromJson(?string $json):self{
        if($json === null || $json === ''){
            return new static();
        }
        $values = Json::decode($json);
        $obj = new static(
            $values['command']??null,
            $values['arg']??null,
            $values['term']??null
        );
        return $obj;
    }

    function setCommand(?string $command){
        if($command === null){
            $command = '휴식';
        }
        $this->command = $command;
    }

    function getCommand():string{
        return $this->command;
    }

    function setArg(?array $arg){
        $this->arg = $arg;
    }

    function getArg():?array{
        return $this->arg;
    }    

    function setTerm(?int $term){
        $this->term = $term;
    }

    function getTerm():?int{
        return $this->term;
    }

    function toJson():string{
        $result = [
            'command'=>$this->command
        ];
        if($this->arg !== null){
            $result['arg'] = $this->arg;
        }
        if($this->term !== null){
            $result['term'] = $this->term;
        }
        return Json::encode($result);
    }
}