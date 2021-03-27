<?php
namespace sammo\Event\Action;

//기존 event_4.php
class CreateAdminNPC extends \sammo\Event\Action{
    public function __construct(){
        
    }

    public function run(array $env){
        return [__CLASS__, 'NYI'];   
    }

}