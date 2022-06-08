<?php
namespace sammo\Command\General;

use \sammo\{
    DB, Util, JosaUtil,
    General,
    CityConst,
    ActionLogger,
    LastTurn,
    Command
};

use \sammo\Constraint\Constraint;
use \sammo\Constraint\ConstraintHelper;

class che_NPC능동 extends Command\GeneralCommand{
    static protected $actionName = 'NPC능동';

    protected function argTest():bool{
        if($this->arg === null){
            return false;
        }
        if(!key_exists('optionText', $this->arg)){
            return false;
        }

        if($this->arg['optionText'] == '순간이동'){
            if(!key_exists('destCityID', $this->arg)){
                return false;
            }
            if(CityConst::byID($this->arg['destCityID']) === null){
                return false;
            }
            $this->arg = [
                'optionText'=>$this->arg['optionText'],
                'destCityID'=>$this->arg['destCityID']
            ];
            return true;
        }

        return false;
    }

    protected function init(){

        $general = $this->generalObj;
        $this->setNation();


        $this->permissionConstraints=[
            ConstraintHelper::MustBeNPC()
        ];

        $this->fullConditionConstraints=[

        ];

    }

    public function canDisplay():bool{
        return false;
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

        $general = $this->generalObj;

        $logger = $general->getLogger();

        $date = $general->getTurnTime($general::TURNTIME_HM);

        if($this->arg['optionText'] == '순간이동'){
            $destCityID = $this->arg['destCityID'];
            $city = CityConst::byID($destCityID);
            $cityName = $city->name;
            $josaRo = JosaUtil::pick($cityName, '로');
            $logger->pushGeneralActionLog("NPC 전용 명령을 이용해 {$cityName}{$josaRo} 이동했습니다.");
            $general->setVar('city', $destCityID);

            $this->setResultTurn(new LastTurn(static::getName(), $this->arg));
        }
        $general->applyDB($db);

        return true;
    }


}