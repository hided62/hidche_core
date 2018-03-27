<?php
namespace sammo\Event\Action;

//기존 event_1.php
class RaiseInavderStrong extends sammo\Event\Action{
    public function __construct($npcCount = 200){
        
    }

    public function run($env=null){
        return [__CLASS__, 'NYI'];   
    }
}