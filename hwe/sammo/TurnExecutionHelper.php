<?php
namespace sammo;

use \Symfony\Component\Lock;
class TurnExecutionHelper
{
    /**
     * @var General $generalObj;
     */
    protected $generalObj;

    public function __construct(array $rawGeneral, int $year, int $month)
    {
        $this->generalObj = new General($rawGeneral, null, $year, $month);
        $this->nationTurn = $nationTurn;
    }

    public function __destruct()
    {
        $this->applyDB();
    }

    public function applyDB(){
        $db = DB::db();
        $this->generalObj->applyDB($db);
    }

    public function getGeneral():General{
        return $this->generalObj;
    }

    public function preprocessCommand(){
        $general = $this->getGeneral();
        $caller = $general->getPreTurnExecuteTriggerList($general);
        $caller->merge(new GeneralTriggerCaller([
            new GeneralTrigger\che_부상경감($general),
            new GeneralTrigger\che_병력군량소모($general),
        ]));

        $caller->fire();
    }

    public function processBlocked():bool{
        $general = $this->getGeneral();

        $blocked = $general->getVar('block');
        if($blocked < 2){
            return false;
        }

        $date = substr($general->getVar('turntime'),11,5);
        $logger = $general->getLogger();
        if($blocked == 2){
            $general->increaseVarWithLimit('killturn', -1, 0);
            $logger->pushGeneralActionLog("현재 멀티, 또는 비매너로 인한<R>블럭</> 대상자입니다. <1>$date</>");
        }
        else if($blocked == 3){
            $general->increaseVarWithLimit('killturn', -1, 0);
            $logger->pushGeneralActionLog("현재 악성유저로 분류되어 <R>블럭</> 대상자입니다. <1>$date</>");
        }
        else{
            //Hmm?
            return false;
        }
        
        return true;
    }

    public function processNationCommand(string $commandClassName, array $commandArg, LastTurn $commandLast):LastTurn{
        if(!$this->nationTurn){
            throw new \InvalidArgumentException('nationTurn이 없음');
        }
        $general = $this->getGeneral();

        $db = DB::db();
        $gameStor = KVStorage::getStorage($db, 'game_env');

        $commandClass = getNationCommandClass($commandClassName);
        /** @var \sammo\Command\NationCommand $commandObj */
        $commandObj = new $commandClass($general, $gameStor->getAll(true), $commandLast, $commandArg);

        $failReason = $commandObj->testReservable();
        if($failReason){
            $date = substr($general->getVar('turntime'),11,5);
            $commandName = $commandObj->getName();
            $text = "{$failReason} {$commandName} 실패. <1>{$date}</>";
            $general->getLogger()->pushGeneralActionLog($text);
        }
        else{
            $commandObj->run();
        }

        return $commandObj->getResultTurn();
    }

    public function processCommand(string $commandClassName, array $commandArg){

        $general = $this->getGeneral();

        $db = DB::db();
        $gameStor = KVStorage::getStorage($db, 'game_env');

        $commandClass = getGeneralCommandClass($commandClassName);
        /** @var \sammo\Command\GeneralCommand $commandObj */
        $commandObj = new $commandClass($general, $gameStor->getAll(true), $commandArg, $commandLast);

        $failReason = $commandObj->testReservable();
        if($failReason){
            $date = substr($general->getVar('turntime'),11,5);
            $commandName = $commandObj->getName();
            $text = "{$failReason} {$commandName} 실패. <1>{$date}</>";
            $general->getLogger()->pushGeneralActionLog($text);
        }
        else{
            $commandObj->run();
        }

        $general->clearActivatedSkill();

        $killTurn = $gameStor->killturn;

        if($general->getVar('npc') >= 2){
            $general->increaseVarWithLimit('killturn', -1);
        }
        else if($general->getVar('killturn') > $killTurn){
            $general->increaseVarWithLimit('killturn', -1);
        }
        else if($commandClassName == '휴식'){
            $general->increaseVarWithLimit('killturn', -1);
        }
        else{
            $general->setVar('killturn', $killTurn);
        }

        return $general->getResultTurn();
    }

    function updateTurnTime(){
        $db = DB::db();
        $gameStor = KVStorage::getStorage($db, 'game_env');

        $general = $this->getGeneral();
        $generalID = $general->getRaw('no');
        $logger = $general->getLogger();

        $generalName = $general->getName();

        // 삭턴장수 삭제처리
        if($general->getVar('killturn') <= 0){
            // npc유저 삭턴시 npc로 전환
            if($general->getVar('npc') == 1 && $general->getVar('deadyear') > $gameStor->year){

                $ownerName = $general->getVar('name2');
                $josaYi = JosaUtil::pick($ownerName, '이');
                
                $logger->pushGlobalActionLog("{$ownerName}</>{$josaYi} <Y>{$generalName}</>의 육체에서 <S>유체이탈</>합니다!");

                $general->setVar('killturn', ($general->getVar('deadyear') - $gameStor->year) * 12);
                $general->setVar('npc', $general->getVar('npc_org'));
                $general->setVar('owner', 1);
                $general->setVar('mode', 2);
                $general->setVar('name2', null);
            }
            else{
                $general->applyDB($db);
                storeOldGeneral($generalID, $gameStor->year, $gameStor->month);
                $general->kill($db);
                return;
            }
        }

        //은퇴
        if($general->getVar('age') >= 80 && $general->getVar('npc') == 0) {
            if($gameStor->isunited == 0) {
                $general->applyDB($db);
                CheckHall($generalID);
            }

            $general->rebirth();
        }

        $turntime = addTurn($general->getRaw('turntime'), $gameStor->turnterm);
        $general->setVar('turntime', $turntime);

    }


    static public function executeGeneralCommandUntil(string $date, \DateTimeInterface $limitActionTime, int $year, int $month){
        $db = DB::db();
        $generalsTodo = $db->queryFirstRow(
            'SELECT no,name,name2,picture,imgsvr,nation,nations,city,troop,injury,affinity,
leader,leader2,power,power2,intel,intel2,weap,book,horse,item,
experience,dedication,level,gold,rice,crew,crewtype,train,atmos,
turntime,makenation,makelimit,killturn,block,dedlevel,explevel,
age,belong,personal,special,special2,term,
npc,npc_org,npcid,deadyear,
dex0,dex10,dex20,dex30,dex40,
warnum,killnum,deathnum,killcrew,deathcrew,recwar,last_turn
general_turn.`action` AS `action`, general_turn.arg AS arg
FROM general LEFT JOIN general_turn ON general.`no` = general_turn.general_id AND turn_idx = 0
WHERE turntime < %s ORDER BY turntime ASC, `no` ASC',
            $date
        );

        $currentTurn = null;

        foreach($generalsTodo as $generalWork){
            $currActionTime = new \DateTimeImmutable();
            if($currActionTime > $limitActionTime){
                return [true, $currentTurn];
            }

            $generalCommand = $generalWork['action'];
            $generalArg = $generalWork['arg'];
            unset($generalWork['action']);
            unset($generalWork['arg']);

            if($generalWork['npc'] >= 2){
                processAI($generalID); // npc AI 처리
            }

            $hasNationTurn = false;
            if($generalWork['nation'] != 0 && $generalWork['level'] >= 5){
                $nationStor = KVStorage::getStorage($db, 'nation_env');
                $lastNationTurnKey = "turn_last_{$generalWork['nation']}_{$generalWork['level']}";
                $lastNationTurn = $nationStor->getDBValue($lastNationTurnKey)??[];
                //수뇌 몇 없는데 매번 left join 하는건 낭비인것 같다.
                $rawNationTurn = $db->queryFirstRow(
                    'SELECT action, arg FROM nation_turn WHERE nation_id = %i AND level = %i AND turn_idx =0',
                    $generalWork['nation'],
                    $generalWork['level']
                );
                $hasNationTurn = true;
            }

            $turnObj = new static($generalWork, $year, $month);
            if(!$turnObj->processBlocked()){
                $turnObj->preprocessCommand();
                if($hasNationTurn){
                    $resultNationTurn = $turnObj->processNationCommand(
                        $rawNationTurn['action'], 
                        Json::decode($rawNationTurn['arg']), 
                        $lastNationTurn
                    );
                    $nationStor->setValue($lastNationTurnKey, $resultNationTurn->toJson());
                }
                $turnObj->processCommand($generalCommand, $generalArg);
            }
            pullNationCommand($generalWork['nation'], $generalWork['level']);
            pullGeneralCommand($generalWork['no']);
            $turnObj->updateTurnTime();
            $turnObj->applyDB();

            $currentTurn = $generalWork['turntime'];
        }

        return [false, $currentTurn];
    }

    static public function executeAllCommand(){
        if(!timeover()) { return; }

        $db = DB::db();

        $gameStor = KVStorage::getStorage($db, 'game_env');

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

            turnDate($nextTurn);

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
        turnDate($prevTurn);
        // 현재시간의 월턴시간 이후 분단위 장수 처리

        [$executionOver, $currentTurn] = static::executeGeneralCommandUntil(
            $nextTurn, $limitActionTime, $gameStor->year, $gameStor->month
        );

        if($currentTurn !== null){
            $gameStor->turntime = $currentTurn;
        }
        

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