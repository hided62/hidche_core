<?php
namespace sammo;

class LastTurn{
    protected $command = '휴식';
    protected $arg = null;
    protected $term = null;
    protected $seq = null;

    function __construct(?string $command=null, ?array $arg=null, ?int $term=null, ?int $seq=null)
    {
        $this->setCommand($command);
        $this->setArg($arg);
        $this->setTerm($term);
        $this->setSeq($seq);
    }

    static function fromRaw(?array $raw):self{
        if(!$raw){
            return new static();
        }
        $obj = new static(
            $raw['command']??null,
            $raw['arg']??null,
            $raw['term']??null,
            $raw['seq']??null
        );
        return $obj;
    }

    static function fromJson(?string $json):self{
        if($json === null || $json === ''){
            return new static();
        }
        return static::fromRaw(Json::decode($json));
        
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

    function setSeq(?int $seq){
        $this->seq = $seq;
    }
    
    function getSeq():?int{
        return $this->seq;
    }

    function toRaw():array{
        $result = [
            'command'=>$this->command
        ];
        if($this->arg !== null){
            $result['arg'] = $this->arg;
        }
        if($this->term !== null){
            $result['term'] = $this->term;
        }
        if($this->seq !== null){
            $result['seq'] = $this->seq;
        }
        return $result;
    }

    function toJson():string{
        
        return Json::encode($this->toRaw());
    }

    function duplicate():LastTurn{
        return new static($this->command, $this->arg, $this->term, $this->seq);
    }
}