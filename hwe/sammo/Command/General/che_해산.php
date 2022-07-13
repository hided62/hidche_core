<?php
namespace sammo\Command\General;

use \sammo\{
    DB, Util, JosaUtil,
    General,
    ActionLogger,
    GameConst, GameUnitConst,
    LastTurn,
    Command,
    Json,
    KVStorage
};


use \sammo\Constraint\Constraint;
use \sammo\Constraint\ConstraintHelper;
use sammo\CityConst;
use sammo\Event\EventHandler;

use function sammo\refreshNationStaticInfo;
use function sammo\deleteNation;

class che_해산 extends Command\GeneralCommand{
    static protected $actionName = '해산';

    protected function argTest():bool{
        $this->arg = [];

        return true;
    }

    protected function init(){

        $general = $this->generalObj;
        $env = $this->env;

        $this->setCity();
        $this->setNation(['gennum']);

        $this->fullConditionConstraints=[
            ConstraintHelper::BeLord(),
            ConstraintHelper::WanderingNation(),
        ];
    }

    public function getCost():array{
        return [0, 0];
    }

    public function getPreReqTurn():int{
        return 0;
    }

    public function getPostReqTurn():int{
        return 0;
    }

    public function run(\Sammo\RandUtil $rng):bool{
        if(!$this->hasFullConditionMet()){
            throw new \RuntimeException('불가능한 커맨드를 강제로 실행 시도');
        }

        $db = DB::db();
        $env = $this->env;

        $general = $this->generalObj;
        $logger = $general->getLogger();
        $date = $general->getTurnTime($general::TURNTIME_HM);

        $initYearMonth = Util::joinYearMonth($env['init_year'], $env['init_month']);
        $yearMonth = Util::joinYearMonth($env['year'], $env['month']);
        if($yearMonth <= $initYearMonth){
            $logger->pushGeneralActionLog("다음 턴부터 해산할 수 있습니다. <1>$date</>");
            $this->alternative = new che_인재탐색($general, $this->env, null);
            return false;
        }

        $generalName = $general->getName();
        $josaYi = JosaUtil::pick($generalName, '이');

        $nation = $this->nation;
        $nationID = $nation['nation'];
        $nationName = $nation['name'];
        $josaUl = JosaUtil::pick($nationName, '을');

        $db->update('general', [
            'gold'=>GameConst::$defaultGold
        ], 'nation=%i AND gold>%i', $nationID, GameConst::$defaultGold);
        $db->update('general', [
            'rice'=>GameConst::$defaultRice
        ], 'nation=%i AND gold>%i', $nationID, GameConst::$defaultRice);

        $general->increaseVarWithLimit('gold', 0, 0, GameConst::$defaultGold);
        $general->increaseVarWithLimit('rice', 0, 0, GameConst::$defaultRice);

        refreshNationStaticInfo();


        $logger->pushGeneralActionLog("세력을 해산했습니다. <1>$date</>");
        $logger->pushGlobalActionLog("<Y>{$generalName}</>{$josaYi} 세력을 해산했습니다.");
        $logger->pushGeneralHistoryLog("<D><b>{$nationName}</b></>{$josaUl} 해산");
        $this->setResultTurn(new LastTurn(static::getName(), $this->arg));

        $nationGenerals = deleteNation($general, false);
        foreach($nationGenerals as $oldGeneral){
            $oldGeneral->setVar('makelimit', 12);
            $oldGeneral->applyDB($db);
        }
        $general->applyDB($db);

        // 이벤트 핸들러 동작
        $gameStor = KVStorage::getStorage($db, 'game_env');
        $e_env = null;
        foreach (DB::db()->query('SELECT * FROM event WHERE target = "DESTROY_NATION" ORDER BY `priority` DESC, `id` ASC') as $rawEvent) {
            if ($e_env === null) {
                $e_env = $gameStor->getAll(false);
            }
            $eventID = $rawEvent['id'];
            $cond = Json::decode($rawEvent['condition']);
            $action = Json::decode($rawEvent['action']);
            $event = new EventHandler($cond, $action);
            $e_env['currentEventID'] = $eventID;

            $event->tryRunEvent($e_env);
        }

        if ($e_env !== null) {
            $gameStor->resetCache();
        }

        return true;
    }


}