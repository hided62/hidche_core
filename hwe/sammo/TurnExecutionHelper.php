<?php
namespace sammo;

use \Symfony\Component\Lock;
class TurnExecutionHelper
{
    /**
     * @var General $generalObj;
     */
    protected $generalObj;
    protected $turn;

    public function __construct(array $rawGeneral, array $turn, int $year, int $month)
    {
        $this->generalObj = new General($rawGeneral, null, $year, $month);
        $this->turn = $turn;
    }

    public function getGeneral():General{
        return $this->generalObj;
    }

    public function preprocessCommand(){
        $general = $this->getGeneral();
        $general->onPreTurnExecute($general);

        if($general->getVar('injury') && !$general->hasActivatedSkill('pre.부상경감')){
            $general->increaseVarWithLimit('injury', -10, 0);
            $general->activateSkill('pre.부상경감');
        }

        if($general->getVar('crew') >= 100){
            $currentRice = $general->getVar('rice');
            $consumeRice = Util::toInt($general->getVar('crew') / 100);
            if($consumeRice <= $currentRice){
                $general->increaseVar('rice', -$consumeRice);
            }
            else{
                $general->setVar('rice', 0);
                $general->getLogger()->pushGeneralActionLog(
                    '군량이 모자라 병사들이 <R>소집해제</>되었습니다!', ActionLogger::PLAIN
                );
            }
            $general->activateSkill('pre.소집해제');
        }

        $general->clearActivatedSkill();
    }

    public function processCommand(){

    }

    public function updateCommand(){

    }

    function updateTurnTime(){

    }


    static public function executeGeneralCommandUntil(string $date, \DateTimeInterface $limitActionTime, int $year, int $month){
        $generalsTodo = $db->queryFirstRow(
            'SELECT npc,no,name,picture,imgsvr,nation,nations,city,troop,injury,affinity,
leader,leader2,power,power2,intel,intel2,weap,book,horse,item,
experience,dedication,level,gold,rice,crew,crewtype,train,atmos,
turntime,makenation,makelimit,killturn,block,dedlevel,explevel,
age,belong,personal,special,special2,term,
dex0,dex10,dex20,dex30,dex40,
warnum,killnum,deathnum,killcrew,deathcrew,recwar,
general_turn.`action` AS `action`, general_turn.arg AS arg 
FROM general LEFT JOIN general_turn ON general.`no` = general_turn.general_id
WHERE turntime < %s AND general_turn.turn_idx = 0 ORDER BY turntime ASC, `no` ASC',
            $date
        );

        $currentTurn = null;

        foreach($generalsTodo as $generalWork){
            $currActionTime = new \DateTimeImmutable();
            if($currActionTime > $limitActionTime){
                return [true, $currentTurn];
            }

            $turn = [
                'action'=>$generalWork['action'],
                'arg'=>Json::decode($generalWork['arg'])
            ];
            unset($generalWork['action']);
            unset($generalWork['arg']);

            if($generalWork['npc'] >= 2){
                processAI($generalID); // npc AI 처리
            }

            $turnObj = new static($generalWork, $turn, $year, $month);
            $turnObj->preprocessCommand();
            $turnObj->processCommand();
            $turnObj->updateCommand();
            $turnObj->updateTurntime();

            $currentTurn = $generalWork['turntime'];
        }

        return [false, $currentTurn];
    }

    static public function executeAllCommand(){
        if(!timeover()) { return; }

        $db = DB::db();

        $gameStor = KVStorage::getStorage($db, 'game_env');

        DB::db();

        if(!tryLock()){
            return;
        }

        if ($gameStor->isunited == 2) {
            //천통시에는 동결
            return;
        }

        $gameStor->cacheAll();
        // 1턴이상 갱신 없었으면 서버 지연
        checkDelay();
        // 접속자수, 접속국가, 국가별 접속장수 갱신
        updateOnline();
        //접속자 수 따라서 갱신제한 변경
        CheckOverhead();

        $date = TimeUtil::now(true);
        // 최종 처리 월턴의 다음 월턴시간 구함
        $prevTurn = cutTurn($gameStor->turntime, $gameStor->turnterm);
        $nextTurn = addTurn($prevTurn, $gameStor->turnterm);

        $maxActionTime = ini_get('max_execution_time');
        $maxActionTime = max($maxActionTime * 2 / 3, $maxActionTime - 10);
        $limitActionTime = (new \DateTimeImmutable())->add(TimeUtil::secondsToDateInterval($maxActionTime));

        // 현재 턴 이전 월턴까지 모두처리.
        //최종 처리 이후 다음 월턴이 현재 시간보다 전이라면
        while ($nextTurn <= $date) {

            [$executionOver, $currentTurn] = static::executeGeneralCommandUntil(
                $nextTurn, $limitActionTime, $gameStor->year, $gameStor->month
            );

            // 트래픽 업데이트
            updateTraffic();

            if($executionOver){
                if($currentTurn !== null){
                    $gameStor->turntime = $currentTurn;
                }
                return;
            }

            // 1달마다 처리하는 것들, 벌점 감소 및 건국,전턴,합병 -1, 군량 소모
            if(!preUpdateMonthly()){
                $gameStor->resetCache(true);
                unlock();
                throw new \RuntimeException('preUpdateMonthly() 처리 에러');
            }

            [$gameStor->year, $gameStor->month] = turnDate($nextTurn);

            // 이벤트 핸들러 동작
            foreach (DB::db()->query('SELECT * from event') as $rawEvent) {
                $eventID = $rawEvent['id'];
                $cond = Json::decode($rawEvent['condition']);
                $action = Json::decode($rawEvent['action']);
                $event = new Event\EventHandler($cond, $action);

                $event->tryRunEvent(['currentEventID'=>$eventID] + $gameStor->getAll(true));
            }

            $logger = new ActionLogger(0, 0, $gameStor->year, $gameStor->month, false);

            // 분기계산. 장수들 턴보다 먼저 있다면 먼저처리
            if($gameStor->month == 1) {
                processGoldIncome();
                processSpring();
                updateYearly();
                updateQuaterly();
                disaster();
                tradeRate();
                addAge();
                // 새해 알림
                $logger->pushGlobalActionLog("<C>{$gameStor->year}</>년이 되었습니다.");
                $logger->flush(); //TODO: globalAction류는 전역에서 관리하는것이 좋을 듯.
            } elseif($gameStor->month == 4) {
                updateQuaterly();
                disaster();
            } elseif($gameStor->month == 7) {
                processRiceIncome();
                processFall();
                updateQuaterly();
                disaster();
                tradeRate();
            } elseif($gameStor->month == 10) {
                updateQuaterly();
                disaster();
            }

            postUpdateMonthly();

            // 다음달로 넘김
            $prevTurn = $nextTurn;
            $nextTurn = addTurn($prevTurn, $gameStor->turnterm);
        }

        // 이시각까지는 업데이트 완료했음
        $gameStor->turntime = $prevTurn;
        // 그 시각 년도,월 저장
        [$gameStor->year, $gameStor->month] = turnDate($prevTurn);
        // 현재시간의 월턴시간 이후 분단위 장수 처리

        [$executionOver, $currentTurn] = static::executeGeneralCommandUntil(
            $nextTurn, $limitActionTime, $gameStor->year, $gameStor->month
        );

        // 부상 과도 제한
        //TODO: 없애고, 부상 자체가 80 이상 넘지 않도록 처리
        $db->update('general', [
            'injury'=>80
        ], 'injury>%i', 80);

        //토너먼트 처리
        processTournament();
        //거래 처리
        processAuction();
        // 잡금 해제
        $gameStor->resetCache(true);
        unlock();
    }
}