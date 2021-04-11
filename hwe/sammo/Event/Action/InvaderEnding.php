<?php
namespace sammo\Event\Action;

use function sammo\getNationStaticInfo;

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

        $cityCnt = $db->queryFirstField('SELECT count(*) FROM city WHERE nation = 0');
        if($cityCnt == 0){
            //천통 엔딩
            $logger->pushGlobalHistoryLog("<L><b>【이벤트】</b></>이민족을 모두 소탕했습니다!");
            $logger->pushGlobalHistoryLog("<L><b>【이벤트】</b></>중원은 당분간 태평성대를 누릴 것입니다.");
        }
        else if($cityCnt == count(CityConst::all())){
            //이민족 엔딩
            $logger->pushGlobalHistoryLog("<L><b>【이벤트】</b></>중원은 이민족에 의해 혼란에 빠졌습니다.");
            $logger->pushGlobalHistoryLog("<L><b>【이벤트】</b></>백성은 언젠가 영웅이 나타나길 기다립니다.");
        }
        else{
            return [__CLASS__, "On Event"];
        }
        $gameStor->setValue('isunited', 3);
        $logger->flush();

        $gameStor->conlimit = $gameStor->conlimit * 100;

        $eventID = Util::array_get($env['currentEventID']);
        $db->delete('event', 'id = %i', $eventID);

        return [__CLASS__, 'Deleted'];   
    }

}