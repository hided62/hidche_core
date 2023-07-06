<?php

namespace sammo;

use sammo\Enums\EventTarget;
use sammo\Enums\InheritanceKey;
use \Symfony\Component\Lock;

class TurnExecutionHelper
{
    /** @var General*/
    protected $generalObj;

    public function __construct(General $general)
    {
        $this->generalObj = $general;
    }

    public function __destruct()
    {
        $this->applyDB();
    }

    public function applyDB()
    {
        $db = DB::db();
        $this->generalObj->applyDB($db);
    }

    public function getGeneral(): General
    {
        return $this->generalObj;
    }

    public function preprocessCommand(RandUtil $rng, array $env)
    {
        $general = $this->getGeneral();
        $caller = $general->getPreTurnExecuteTriggerList($general);
        $caller->merge(new GeneralTriggerCaller(
            new GeneralTrigger\che_부상경감($general),
            new GeneralTrigger\che_병력군량소모($general)
        ));

        $caller->fire($rng, $env);
    }

    public function processBlocked(): bool
    {
        $general = $this->getGeneral();

        $blocked = $general->getVar('block');
        if ($blocked < 2) {
            return false;
        }

        $date = $general->getTurnTime($general::TURNTIME_HM);
        $logger = $general->getLogger();
        if ($blocked == 2) {
            $general->increaseVarWithLimit('killturn', -1, 0);
            $logger->pushGeneralActionLog("현재 멀티, 또는 비매너로 인한<R>블럭</> 대상자입니다. <1>$date</>");
        } else if ($blocked == 3) {
            $general->increaseVarWithLimit('killturn', -1, 0);
            $logger->pushGeneralActionLog("현재 악성유저로 분류되어 <R>블럭</> 대상자입니다. <1>$date</>");
        } else {
            //Hmm?
            return false;
        }

        return true;
    }

    public function processNationCommand(RandUtil $rng, Command\NationCommand $commandObj): LastTurn
    {
        $general = $this->getGeneral();

        while (true) {
            if (!$commandObj->hasFullConditionMet()) {
                $date = $general->getTurnTime($general::TURNTIME_HM);
                $failString = $commandObj->getFailString();
                $text = "{$failString} <1>{$date}</>";
                $general->getLogger()->pushGeneralActionLog($text);
                break;
            }

            if (!$commandObj->addTermStack()) {
                $date = $general->getTurnTime($general::TURNTIME_HM);
                $termString = $commandObj->getTermString();
                $text = "{$termString} <1>{$date}</>";
                $general->getLogger()->pushGeneralActionLog($text);
                break;
            }

            $result = $commandObj->run($rng);
            if ($result) {
                $commandObj->setNextAvailable();
                break;
            }


            $alt = $commandObj->getAlternativeCommand();
            if ($alt === null) {
                break;
            }
            $commandObj = $alt;
            $commandClassName = $alt->getName();
        }

        return $commandObj->getResultTurn();
    }

    public function processCommand(RandUtil $rng, Command\GeneralCommand $commandObj, bool $autorunMode)
    {

        $general = $this->getGeneral();

        $db = DB::db();
        $gameStor = KVStorage::getStorage($db, 'game_env');
        $commandClassName = $commandObj->getName();

        while (true) {
            if (!$commandObj->hasFullConditionMet()) {
                $date = $general->getTurnTime($general::TURNTIME_HM);
                $failString = $commandObj->getFailString();
                $text = "{$failString} <1>{$date}</>";
                $general->getLogger()->pushGeneralActionLog($text);
                break;
            }

            if (!$commandObj->addTermStack()) {
                $date = $general->getTurnTime($general::TURNTIME_HM);
                $termString = $commandObj->getTermString();
                $text = "{$termString} <1>{$date}</>";
                $general->getLogger()->pushGeneralActionLog($text);
                break;
            }

            $result = $commandObj->run($rng);
            if ($result) {
                $commandObj->setNextAvailable();
                break;
            }
            $alt = $commandObj->getAlternativeCommand();
            if ($alt === null) {
                break;
            }
            $commandObj = $alt;
            $commandClassName = $alt->getName();
        }


        $general->clearActivatedSkill();

        $killTurn = $gameStor->killturn;

        if ($general->getNPCType() >= 2) {
            $general->increaseVarWithLimit('killturn', -1);
        } else if ($general->getVar('killturn') > $killTurn) {
            $general->increaseVarWithLimit('killturn', -1);
        } else if ($autorunMode) {
            $general->increaseVarWithLimit('killturn', -1);
        } else if ($commandClassName == '휴식') {
            $general->increaseVarWithLimit('killturn', -1);
        } else {
            $general->setVar('killturn', $killTurn);
        }

        return $commandObj->getResultTurn();
    }

    function updateTurnTime()
    {
        $db = DB::db();
        $gameStor = KVStorage::getStorage($db, 'game_env');

        $general = $this->getGeneral();
        $generalID = $general->getID();
        $logger = $general->getLogger();
        $db->update('general_access_log', [
            'refresh_score' => 0,
        ], 'general_id=%i', $generalID);

        $generalName = $general->getName();

        // 삭턴장수 삭제처리
        if ($general->getVar('killturn') <= 0) {
            // npc유저 삭턴시 npc로 전환
            if ($general->getNPCType() == 1 && $general->getVar('deadyear') > $gameStor->year) {

                $ownerName = $general->getVar('owner_name');
                $josaYi = JosaUtil::pick($ownerName, '이');

                $logger->pushGlobalActionLog("{$ownerName}</>{$josaYi} <Y>{$generalName}</>의 육체에서 <S>유체이탈</>합니다!");

                $general->setVar('killturn', ($general->getVar('deadyear') - $gameStor->year) * 12);
                $general->setVar('npc', $general->getVar('npc_org'));
                $general->setVar('owner', 0);
                $general->setVar('defence_train', 80);
                $general->setVar('owner_name', null);

                $db->delete('general_access_log', 'general_id=%i', $generalID);
            } else {
                $general->applyDB($db);
                $general->kill($db);
                return;
            }
        }

        //은퇴
        if ($general->getVar('age') >= GameConst::$retirementYear && $general->getNPCType() == 0) {
            if ($gameStor->isunited == 0) {
                $general->applyDB($db);
                CheckHall($generalID);
            }

            $general->rebirth();
        }

        $turntime = addTurn($general->getTurnTime(), $gameStor->turnterm);
        $general->setVar('turntime', $turntime);
    }


    static public function executeGeneralCommandUntil(string $date, \DateTimeInterface $limitActionTime, int $year, int $month)
    {
        $db = DB::db();
        $generalsTodo = $db->query(
            'SELECT no,name,turntime,killturn,block,npc,deadyear FROM general WHERE turntime < %s ORDER BY turntime ASC, `no` ASC',
            $date
        );

        $currentTurn = null;

        $gameStor = KVStorage::getStorage($db, 'game_env');
        $autorun_user = $gameStor->autorun_user;

        foreach ($generalsTodo as $rawGeneral) {
            $currActionTime = new \DateTimeImmutable();
            if ($currActionTime > $limitActionTime) {
                return [true, $currentTurn];
            }

            $general = General::createGeneralObjFromDB($rawGeneral['no']);
            $nationStor = KVStorage::getStorage($db, $general->getNationID(), 'nation_env');
            $turnObj = new static($general);

            $env = $gameStor->getAll(); //NOTE: 매번 재 갱신하도록 유지할 것.
            [$startYear, $year, $month, $turnterm] = $gameStor->getValuesAsArray(['startyear', 'year', 'month', 'turnterm']);

            $hasNationTurn = false;
            if ($general->getVar('nation') != 0 && $general->getVar('officer_level') >= 5) {
                $lastNationTurnKey = "turn_last_{$general->getVar('officer_level')}";
                //수뇌 몇 없는데 매번 left join 하는건 낭비인것 같다.
                $rawNationTurn = $db->queryFirstRow(
                    'SELECT action, arg FROM nation_turn WHERE nation_id = %i AND officer_level = %i AND turn_idx =0',
                    $general->getVar('nation'),
                    $general->getVar('officer_level')
                ) ?? [];
                $hasNationTurn = true;
                $nationCommand = $rawNationTurn['action'] ?? null;
                $nationArg = Json::decode($rawNationTurn['arg'] ?? null);
                $lastNationTurn = LastTurn::fromRaw($nationStor->getValue($lastNationTurnKey));
                $nationCommandObj = buildNationCommandClass($nationCommand, $general, $env, $lastNationTurn, $nationArg);
            }

            $autorunMode = false;
            $ai = null;

            $general->increaseInheritancePoint(InheritanceKey::lived_month, 1);

            $rng = new RandUtil(new LiteHashDRBG(Util::simpleSerialize(
                UniqueConst::$hiddenSeed,
                'preprocess',
                $year,
                $month,
                $general->getID(),
            )));
            $turnObj->preprocessCommand($rng, $env);

            if ($general->getNPCType() >= 2) {
                $ai = new GeneralAI($turnObj->getGeneral());
            } else {
                $limitYearMonth = $general->getAuxVar('autorun_limit') ?? Util::joinYearMonth($year - 2, $month);
                if (Util::joinYearMonth($year, $month) < $limitYearMonth) {
                    $ai = new GeneralAI($turnObj->getGeneral());
                }
            }
            $hasReservedTurn = false;

            if (!$turnObj->processBlocked()) {

                if ($hasNationTurn) {
                    if (!($nationCommandObj instanceof Command\Nation\휴식)) {
                        $hasReservedTurn = true;
                    }
                    if ($ai && ($general->getAuxVar('use_auto_nation_turn') ?? 1)) {
                        $nationCommandObj = $ai->chooseNationTurn($nationCommandObj);
                        $cityName = CityConst::byID($general->getCityID())->name;
                        LogText("NationTurn", "General, {$general->getName()}, {$general->getID()}, {$cityName}, {$general->getStaticNation()['name']}, {$nationCommandObj->getBrief()}, {$nationCommandObj->reason}, ");
                    }
                    $rng = new RandUtil(new LiteHashDRBG(Util::simpleSerialize(
                        UniqueConst::$hiddenSeed,
                        'nationCommand',
                        $year,
                        $month,
                        $general->getID(),
                        $nationCommandObj->getRawClassName()
                    )));
                    $resultNationTurn = $turnObj->processNationCommand(
                        $rng,
                        $nationCommandObj
                    );
                    $nationStor->setValue($lastNationTurnKey, $resultNationTurn->toRaw());
                    $general->setRawCity(null);
                }

                $generalCommandObj = $general->getReservedTurn(0, $env);
                if (!($generalCommandObj instanceof Command\General\휴식)) {
                    $hasReservedTurn = true;
                }

                if ($ai) {
                    $newGeneralCommandObj = $ai->chooseGeneralTurn($generalCommandObj); // npc AI 처리
                    if ($generalCommandObj !== $newGeneralCommandObj) {
                        $autorunMode = true;
                        $generalCommandObj = $newGeneralCommandObj;
                    }
                    $cityName = CityConst::byID($general->getCityID())->name;
                    LogText("turn", "General, {$general->getName()}, {$general->getID()}, {$cityName}, {$general->getStaticNation()['name']}, {$generalCommandObj->getBrief()}, {$generalCommandObj->reason}, ");
                }
                $rng = new RandUtil(new LiteHashDRBG(Util::simpleSerialize(
                    UniqueConst::$hiddenSeed,
                    'generalCommand',
                    $year,
                    $month,
                    $general->getID(),
                    $generalCommandObj->getRawClassName()
                )));
                $turnObj->processCommand($rng, $generalCommandObj, $autorunMode);
            }
            pullNationCommand($general->getVar('nation'), $general->getVar('officer_level'));
            pullGeneralCommand($general->getID());

            $currentTurn = $general->getTurnTime();
            $general->increaseVarWithLimit('myset', 3, null, 9);

            if (($autorun_user['limit_minutes'] ?? false) && $general->getNPCType() < 2 && $hasReservedTurn) {
                $autorun_limit = Util::joinYearMonth($year, $month);
                $autorun_limit += intdiv($autorun_user['limit_minutes'], $turnterm);

                $general->setAuxVar('autorun_limit', $autorun_limit);
            }

            $turnObj->updateTurnTime();
            $turnObj->applyDB();
        }

        return [false, $currentTurn];
    }

    static public function runEventHandler(\MeekroDB $db, ?KVStorage $gameStor, EventTarget $eventTarget): bool{
        if($gameStor === null){
            $gameStor = KVStorage::getStorage($db, 'game_env');
        }
        $e_env = null;
        foreach ($db->query('SELECT * FROM event WHERE target = %s ORDER BY `priority` DESC, `id` ASC', $eventTarget->value) as $rawEvent) {
            if ($e_env === null) {
                $e_env = $gameStor->getAll(false);
            }
            $eventID = $rawEvent['id'];
            $cond = Json::decode($rawEvent['condition']);
            $action = Json::decode($rawEvent['action']);
            $event = new Event\EventHandler($cond, $action);
            $e_env['currentEventID'] = $eventID;

            $event->tryRunEvent($e_env);
        }
        if ($e_env === null) {
            return false;
        }
        return true;
    }

    static public function executeAllCommand(&$executed = false, &$locked = false): string
    {
        $db = DB::db();

        $gameStor = KVStorage::getStorage($db, 'game_env');

        if (TimeUtil::now(true) < $gameStor->turntime) {
            //턴 시각 이전이면 아무것도 하지 않음
            return $gameStor->turntime;
        }

        if (!tryLock()) {
            $locked = true;
            return $gameStor->turntime;
        }

        if ($gameStor->isunited == 2 || $gameStor->isunited == 3) {
            //천통시에는 동결
            $locked = true;
            return $gameStor->turntime;
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
        $lastExecuted = $gameStor->turntime;
        $prevTurn = cutTurn($gameStor->turntime, $gameStor->turnterm);
        $nextTurn = addTurn($prevTurn, $gameStor->turnterm);

        $maxActionTime = Util::toInt(ini_get('max_execution_time'));
        if ($maxActionTime == 0) {
            $maxActionTime = 60;
        } else {
            $maxActionTime = max($maxActionTime * 2 / 3, $maxActionTime - 10);
        }

        $limitActionTime = (new \DateTimeImmutable())->add(TimeUtil::secondsToDateInterval($maxActionTime));

        // 현재 턴 이전 월턴까지 모두처리.
        //최종 처리 이후 다음 월턴이 현재 시간보다 전이라면
        while ($nextTurn <= $date) {

            [$executionOver, $currentTurn] = static::executeGeneralCommandUntil(
                $nextTurn,
                $limitActionTime,
                $gameStor->year,
                $gameStor->month
            );

            // 트래픽 업데이트
            updateTraffic();

            if ($executionOver) {
                if ($currentTurn !== null) {
                    $executed = true;
                    $gameStor->turntime = $currentTurn;
                }
                unlock();
                if($lastExecuted !== $currentTurn){
                    $executed = true;
                }
                return $gameStor->turntime;
            }

            $monthlyRng = new RandUtil(new LiteHashDRBG(Util::simpleSerialize(
                UniqueConst::$hiddenSeed,
                'monthly',
                $gameStor->year,
                $gameStor->month
            )));

            // 1달마다 처리하는 것들, 벌점 감소 및 건국,전턴,합병 -1, 군량 소모
            static::runEventHandler($db, $gameStor, EventTarget::PreMonth);
            if (!preUpdateMonthly()) {
                unlock();
                throw new \RuntimeException('preUpdateMonthly() 처리 에러');
            }
            turnDate($nextTurn);

            // 분기계산. 장수들 턴보다 먼저 있다면 먼저처리
            if ($gameStor->month == 1) {
                checkStatistic();
            }
            static::runEventHandler($db, $gameStor, EventTarget::Month);
            postUpdateMonthly($monthlyRng);

            // 다음달로 넘김
            $prevTurn = $nextTurn;
            $nextTurn = addTurn($prevTurn, $gameStor->turnterm);
            $gameStor->turntime = $prevTurn;
        }

        // 그 시각 년도,월 저장
        turnDate($prevTurn);
        // 현재시간의 월턴시간 이후 분단위 장수 처리

        [$executionOver, $currentTurn] = static::executeGeneralCommandUntil(
            $date,
            $limitActionTime,
            $gameStor->year,
            $gameStor->month
        );

        if ($currentTurn !== null) {
            $executed = true;
            $gameStor->turntime = $currentTurn;
        }

        //토너먼트 처리
        processTournament();
        //거래 처리
        processAuction();
        // 잠금 해제

        $turntime = $gameStor->turntime;

        $gameStor->resetCache();
        unlock();

        if($lastExecuted !== $currentTurn){
            $executed = true;
        }

        return $turntime;
    }
}
