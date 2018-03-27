<?php
namespace sammo\Event\Action;

//기존 event_2.php
class RaiseInavderWeak extends sammo\Event\Action{
    public function __construct($npcCount = 200){
        
    }

    public function run($env=null){
        return [__CLASS__, 'NYI'];   
    }
}