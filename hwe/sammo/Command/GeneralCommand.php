<?php
namespace sammo\Command;
use \sammo\Util;

abstract class GeneralCommand extends BaseCommand{

    protected function getNextExecuteKey():string{
        $turnKey = static::$actionName;
        $generalID = $this->getGeneral()->getID();
        $executeKey = "next_execute_{$generalID}_{$turnKey}";
        return $executeKey;
    }

    public function getNextAvailable():?int{
        if($this->isArgValid && !$this->getPostReqTurn()){
            return null;
        }
        $db = \sammo\DB::db();
        $lastExecuteStor = \sammo\KVStorage::getStorage($db, 'next_execute');
        return $lastExecuteStor->getValue($this->getNextExecuteKey());
    }

    public function setNextAvailable(?int $yearMonth=null){
        if(!$this->getPostReqTurn()){
            return;            
        }
        if($yearMonth === null){
            $yearMonth = Util::joinYearMonth($this->env['year'], $this->env['month']) + $this->getPostReqTurn();
        }
        
        $db = \sammo\DB::db();
        $lastExecuteStor = \sammo\KVStorage::getStorage($db, 'next_execute');
        $lastExecuteStor->setValue($this->getNextExecuteKey(), $yearMonth);
    }

};