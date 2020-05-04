<?php
namespace sammo\Command\General;

use \sammo\{
    DB, Util, JosaUtil,
    General, 
    ActionLogger,
    GameConst, GameUnitConst,
    LastTurn,
    Command
};


use \sammo\Constraint\Constraint;
use \sammo\Constraint\ConstraintHelper;
use sammo\CityConst;
use function sammo\buildNationTypeClass;
use function sammo\refreshNationStaticInfo;
use function sammo\GetNationColors;
use function sammo\getAllNationStaticInfo;
use function sammo\DeleteConflict;
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

    public function run():bool{
        if(!$this->hasFullConditionMet()){
            throw new \RuntimeException('불가능한 커맨드를 강제로 실행 시도');
        }

        $db = DB::db();
        $env = $this->env;

        $general = $this->generalObj;
        $date = $general->getTurnTime($general::TURNTIME_HM);

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

        DeleteConflict($nationID);
        deleteNation($general);

        refreshNationStaticInfo();

        $logger = $general->getLogger();

        $logger->pushGeneralActionLog("세력을 해산했습니다. <1>$date</>");
        $logger->pushGlobalActionLog("<Y>{$generalName}</>{$josaYi} 세력을 해산했습니다.");

        $logger->pushGeneralHistoryLog("<D><b>{$nationName}</b></>{$josaUl} 해산");

        $general->setResultTurn(new LastTurn(static::getName(), $this->arg));
        $general->applyDB($db);

        return true;
    }

    
}