<?php
namespace sammo\Command;
use \sammo\Util;

abstract class UserActionCommand extends BaseCommand{

    public function getNextExecuteKey():string{
        $turnKey = static::$actionName;
        $generalID = $this->getGeneral()->getID();
        $executeKey = "next_execute_{$generalID}_user_action_{$turnKey}";
        return $executeKey;
    }

    public function getNextAvailableTurn():?int{
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
            $yearMonth = Util::joinYearMonth($this->env['year'], $this->env['month'])
            + $this->getPostReqTurn() - $this->getPreReqTurn();
        }

        $db = \sammo\DB::db();
        $lastExecuteStor = \sammo\KVStorage::getStorage($db, 'next_execute');
        $lastExecuteStor->setValue($this->getNextExecuteKey(), $yearMonth);
    }

};