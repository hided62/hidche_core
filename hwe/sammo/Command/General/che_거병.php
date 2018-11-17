<?php
namespace sammo\GeneralCommand;

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
use function sammo\getNationTypeClass;
use function sammo\refreshNationStaticInfo;
use function sammo\GetNationColors;
use function sammo\getAllNationStaticInfo;



class che_거병 extends Command\GeneralCommand{
    static protected $actionName = '거병';

    protected function argTest():bool{        
        $this->arg = [];

        return true;
    }

    protected function init(){

        $general = $this->generalObj;
        $env = $this->env;

        $this->setCity();
        $this->setNation();

        if(!key_exists($colorType, GetNationColors())){
            return false;
        }

        $relYear = $env['year'] - $env['startyear'];
        
        $this->runnableConstraints=[
            ConstraintHelper::BeNeutral(),
            ConstraintHelper::BeOpeningPart($relYear),
            ConstraintHelper::AllowJoinAction(),
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

        $nationName = $generalName;
        $cityName = $this->city['name'];

        $nationNameExistsCnt = $db->queryFirstField('SELECT count(*) FROM nation WHERE name = %s', $nationName);
        if($nationNameExistsCnt){
            $nationName = mb_strimwidth('㉥'.$nationName, 0, 18);
        }
        $nationNameExistsCnt = $db->queryFirstField('SELECT count(*) FROM nation WHERE name = %s', $nationName);
        if($nationNameExistsCnt){
            //여전히 중복된다면, 그냥 글자 길이 넘어가든 말든 신경쓰지 말고 넘기기.
            $nationName = '㉥'.$nationName;
        }


        $diplomacyInit = [];
        foreach(getAllNationStaticInfo() as $destNation){
            $destNationID = $destNation['nation'];
            $diplomacyInit[] = [
                'me'=>$destNationID,
                'you'=>$nationID,
                'state'=>2,
                'term'=>0,
            ];

            $diplomacyInit[] = [
                'me'=>$nationID,
                'you'=>$destNationID,
                'state'=>2,
                'term'=>0,
            ];
        }
        $db->insert('diplomacy', $diplomacyInit);

        DB::db()->insert('nation', [
            'name'=>$nationName,
            'color'=>'#330000', 
            'gold'=>0, 
            'rice'=>GameConst::$baserice, 
            'rate'=>20, 
            'bill'=>100, 
            'strategic_cmd_limit'=>12, 
            'surlimit'=>72, 
            'type'=>0, 
            'gennum'=>1
        ]);
        $nationID = DB::db()->insertId();

        refreshNationStaticInfo();

        $logger->pushGeneralActionLog("거병에 성공하였습니다. <1>$date</>");
        $logger->pushGlobalActionLog("<Y>{$generalName}</>{$josaYi} <G><b>{$cityName}</b></>에 거병하였습니다.");

        $logger->pushGlobalHistoryLog("<Y><b>【거병】</b></><D><b>{$generalName}</b></>{$josaYi} 세력을 결성하였습니다.");
        $logger->pushGeneralHistoryLog("<G><b>{$cityName}</b></>에서 거병");
        $logger->pushNationalHistoryLog("<Y>{$generalName}</>{$josaYi} <G><b>{$cityName}</b></>에서 거병");

        $exp = 100;
        $ded = 100;
        
        $exp = $general->onPreGeneralStatUpdate($general, 'experience', $exp);
        $ded = $general->onPreGeneralStatUpdate($general, 'dedication', $ded);

        $general->increaseVar('experience', $exp);
        $general->increaseVar('dedication', $ded);
        $general->setVar('belong', 1);
        $general->setVar('level', 12);
        $general->setVar('nation', $nationID);

        $general->setResultTurn(new LastTurn(static::getName(), $this->arg));
        $general->checkStatChange();
        $general->applyDB($db);

        return true;
    }

    
}