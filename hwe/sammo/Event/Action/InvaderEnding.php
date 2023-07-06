<?php
namespace sammo\Event\Action;

use sammo\ActionLogger;
use sammo\CityConst;
use sammo\DB;
use sammo\KVStorage;
use sammo\Util;

class InvaderEnding extends \sammo\Event\Action{
    public function __construct(){
    }

    public function run(array $env){
        //FIXME: 조건 체크를 여기서 하지 말라.
        $db = DB::db();
        $gameStor = KVStorage::getStorage($db, 'game_env');
        $logger = new ActionLogger(0, 0, $env['year'], $env['month']);

        $isunited = $gameStor->isunited;
        if(in_array($isunited, [0, 2])){
            return [__CLASS__, "No Invader"];
        }

        $nationCnt = $db->queryFirstField('SELECT count(*) FROM nation');
        if($nationCnt >= 2){
            return [__CLASS__, "On Event"];
        }

        $gameStor = KVStorage::getStorage($db, 'game_env');

        $logger = new ActionLogger(0, 0, $env['year'], $env['month']);

        $needStop = false;
        $userWin = false;
        $cityCnt = $db->queryFirstField('SELECT count(*) FROM city WHERE nation = 0');
        if($cityCnt == 0){
            $needStop = true;
            $nationName = $db->queryFirstField('SELECT name FROM nation LIMIT 1');
            if(!\str_starts_with($nationName, 'ⓞ')){
                $userWin = true;
            }
        }
        else if($cityCnt == count(CityConst::all())){
            $needStop = true;
            $userWin = false;
        }

        if(!$needStop){
            return [__CLASS__, "On Event"];
        }

        if($userWin){
            //천통 엔딩
            $logger->pushGlobalHistoryLog("<L><b>【이벤트】</b></>이민족을 모두 소탕했습니다!");
            $logger->pushGlobalHistoryLog("<L><b>【이벤트】</b></>중원은 당분간 태평성대를 누릴 것입니다.");
        }
        else {
            //이민족 엔딩
            $logger->pushGlobalHistoryLog("<L><b>【이벤트】</b></>중원은 이민족에 의해 혼란에 빠졌습니다.");
            $logger->pushGlobalHistoryLog("<L><b>【이벤트】</b></>백성은 언젠가 영웅이 나타나길 기다립니다.");
        }
        $gameStor->setValue('isunited', 3);
        $logger->flush();

        $gameStor->refreshLimit = $gameStor->refreshLimit * 100;

        $eventID = Util::array_get($env['currentEventID']);
        $db->delete('event', 'id = %i', $eventID);

        return [__CLASS__, 'Deleted'];
    }

}