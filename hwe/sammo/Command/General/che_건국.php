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


use function \sammo\{
    tryUniqueItemLottery
};

use \sammo\Constraint\Constraint;
use \sammo\Constraint\ConstraintHelper;
use sammo\CityConst;
use function sammo\getNationTypeClass;
use function sammo\refreshNationStaticInfo;
use function sammo\GetNationColors;



class che_건국 extends Command\GeneralCommand{
    static protected $actionName = '건국';
    static public $reqArg = true;

    protected function argTest():bool{
        if($this->arg === null){
            return false;
        }
        $nationName = $this->arg['nationName']??null;
        $nationType = $this->arg['nationType']??null;
        $nationColor = $this->arg['nationColor']??null;

        if($nationName === null || $nationType === null || $nationColor === null){
            return false;
        }

        if(!is_string($nationName) || !is_string($nationType) || !is_int($nationColor)){
            return false;
        }

        if(mb_strwidth($nationName) > 18 || $nationName == ''){
            return false;
        }

        if(!key_exists($nationColor, GetNationColors())){
            return false;
        }

        try{
            $nationTypeClass = getNationTypeClass($nationType);
        }
        catch(InvalidArgumentException $e){
            return false;
        }
        
        $this->arg = [
            'nationName'=>$nationName,
            'nationType'=>$nationType,
            'nationColor'=>$nationColor
        ];

        return true;
    }

    protected function init(){

        $general = $this->generalObj;
        $env = $this->env;

        $nationName = $this->arg['nationName'];
        $nationType = $this->arg['nationType'];
        $nationColor = $this->arg['nationColor'];

        $this->setCity();
        $this->setNation(['gennum']);

        $relYear = $env['year'] - $env['startyear'];
        
        $this->runnableConstraints=[
            ConstraintHelper::ReqNationValue('gennum', '수하 장수', '>=', 2),
            ConstraintHelper::BeOpeningPart($relYear),
            ConstraintHelper::WanderingNation(),
            ConstraintHelper::CheckNationNameDuplicate($nationName),
            ConstraintHelper::BeLord(),
            ConstraintHelper::AllowJoinAction(),
            ConstraintHelper::ConstructableCity(),
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
        if(!$this->isRunnable()){
            throw new \RuntimeException('불가능한 커맨드를 강제로 실행 시도');
        }

        $db = DB::db();
        $env = $this->env;

        $general = $this->generalObj;
        $date = substr($general->getVar('turntime'),11,5);
        $generalName = $general->getName();
        $josaYi = JosaUtil::pick($generalName, '이');

        $nationName = $this->arg['nationName'];
        $nationType = $this->arg['nationType'];
        $nationColor = GetNationColors()[$this->arg['nationColor']];

        $cityName = $this->city['name'];

        $josaUl = JosaUtil::pick($nationName, '을');

        $logger = $general->getLogger();

        $nationTypeClass = getNationTypeClass($nationType);
        $nationTypeName = $nationTypeClass::$name;


        $logger->pushGeneralActionLog("<D><b>{$nationName}</></>{$josaUl} 건국하였습니다. <1>$date</>");
        $logger->pushGlobalActionLog("<Y>{$generalName}</>{$josaYi} <G><b>{$cityName}</b></>에 국가를 건설하였습니다.");

        $josaNationYi = JosaUtil::pick($nationName, '이');
        $logger->pushGlobalHistoryLog("<Y><b>【건국】</b></>{$nationTypeName} <D><b>{$nationName}</b></>{$josaNationYi} 새로이 등장하였습니다.");
        $logger->pushGeneralHistoryLog("<D><b>{$nationName}</b></>{$josaUl} 건국");
        $logger->pushNationalHistoryLog("<Y>{$generalName}</>{$josaYi} <D><b>{$nationName}</b></>{$josaUl} 건국");

        $exp = 1000;
        $ded = 1000;
        
        $exp = $general->onPreGeneralStatUpdate($general, 'experience', $exp);
        $ded = $general->onPreGeneralStatUpdate($general, 'dedication', $ded);

        $general->increaseVar('experience', $exp);
        $general->increaseVar('dedication', $ded);

        $db->update('city', [
            'nation'=>$general->getNationID(),
            'conflict'=>'{}'
        ], 'city=%i', $general->getCityID());

        $db->update('nation', [
            'name'=>$nationName,
            'color'=>$nationColor,
            'level'=>1,
            'type'=>$nationType,
            'capital'=>$general->getCityID()
        ], 'nation=%i', $general->getNationID());

        refreshNationStaticInfo();

        $general->setResultTurn(new LastTurn(static::getName(), $this->arg));
        $general->checkStatChange();
        tryUniqueItemLottery($general, '건국');
        $general->applyDB($db);

        return true;
    }

    
}