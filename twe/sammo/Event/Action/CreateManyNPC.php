<?php
namespace sammo\Event\Action;

//기존 event_3.php
class CreateManyNPC extends sammo\Event\Action{
    public function __construct($npcCount = 200){
        
    }

    public function run($env=null){
        return [__CLASS__, 'NYI'];   
    }
}