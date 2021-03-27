<?php
namespace sammo\Event\Action;

//1회용 event임을 의미함
class DeleteEvent extends \sammo\Event\Action{

    public function __construct(){
    }

    public function run(array $env){

        $eventID = \sammo\Util::array_get($env['currentEventID']);
        if(!$eventID){
            throw new \RuntimeException('currentEventID가 지정되지 않았습니다.');
            //NOTE: 이걸 에러 내야할지 아닐지는 아직 판단 필요
        }
        $db = \sammo\DB::db();
        $db->delete('event', 'id = %i', $eventID);
        return [__CLASS__, $db->affectedRows()];
    }

}