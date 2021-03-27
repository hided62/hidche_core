<?php
namespace sammo\Event\Action;

use function sammo\getNationStaticInfo;
use sammo\DB;
use sammo\Util;

class AutoDeleteInvader extends \sammo\Event\Action{
    private $nationID;
    public function __construct(int $nationID){
        $this->nationID = $nationID;
    }

    public function run(array $env){
        if(getNationStaticInfo($this->nationID)===null){
            return [__CLASS__, "Not Exists"];
        }
        $db = DB::db();
        $onWar = $db->queryFirstField('SELECT count(*) FROM diplomacy WHERE me = %i AND state IN %li', $this->nationID, [0, 1]);
        if($onWar){
            return [__CLASS__, "On War"];
        }

        $rulerID = $db->queryFirstField('SELECT no FROM general WHERE nation = %i AND officer_level = 12', $this->nationID);
        $db->update('general_turn', [
            'action'=>'che_방랑',
            'arg'=>'[]',
            'brief'=>"이민족 방랑"
        ], 'general_id = %i', $rulerID);
        $db->update('general', [
            'killturn'=>5
        ], 'nation = %i', $this->nationID);

        $eventID = Util::array_get($env['currentEventID']);
        $db->delete('event', 'id = %i', $eventID);

        return [__CLASS__, 'Deleted'];   
    }

}