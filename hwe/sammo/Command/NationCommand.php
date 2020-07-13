<?php
namespace sammo\Command;
use \sammo\General;
use sammo\KVStorage;
use \sammo\LastTurn;
use \sammo\Util;

abstract class NationCommand extends BaseCommand{
    protected $lastTurn;
    protected $resultTurn;

    public function __construct(General $generalObj, array $env, LastTurn $lastTurn, $arg = null){
        $this->lastTurn = $lastTurn;
        $this->resultTurn = $lastTurn->duplicate();
        parent::__construct($generalObj, $env, $arg);
    }

    public function getLastTurn():LastTurn{
        return $this->lastTurn;
    }

    public function setResultTurn(LastTurn $lastTurn){
        $this->resultTurn = $lastTurn;
    }

    public function getResultTurn():LastTurn{
        return $this->resultTurn;
    }
    
    protected function getNextExecuteKey():string{
        $turnKey = static::$actionName;
        $executeKey = "next_execute_{$turnKey}";
        return $executeKey;
    }

    public function getNextAvailable():?int{
        if($this->isArgValid && !$this->getPostReqTurn()){
            return null;
        }
        $db = \sammo\DB::db();
        $nationStor = \sammo\KVStorage::getStorage($db, $this->getNationID(), 'nation_env');
        return $nationStor->getValue($this->getNextExecuteKey());
    }

    public function setNextAvailable(?int $yearMonth=null){
        if(!$this->getPostReqTurn()){
            return;
        }
        if($yearMonth === null){
            $yearMonth = Util::joinYearMonth($this->env['year'], $this->env['month']) + $this->getPostReqTurn();
        }

        $db = \sammo\DB::db();
        $nationStor = \sammo\KVStorage::getStorage($db, $this->getNationID(), 'nation_env');
        $nationStor->setValue($this->getNextExecuteKey(), $yearMonth);
    }
    
};