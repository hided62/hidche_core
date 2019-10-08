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
    tryUniqueItemLottery,
    getAllNationStaticInfo
};

use \sammo\Constraint\Constraint;
use \sammo\Constraint\ConstraintHelper;
use sammo\CityConst;
use function sammo\buildNationTypeClass;
use function sammo\refreshNationStaticInfo;
use function sammo\GetNationColors;
use function sammo\newColor;


class che_건국 extends Command\GeneralCommand{
    static protected $actionName = '건국';
    static public $reqArg = true;

    protected function argTest():bool{
        if($this->arg === null){
            return false;
        }
        $nationName = $this->arg['nationName']??null;
        $nationType = $this->arg['nationType']??null;
        $colorType = $this->arg['colorType']??null;

        if($nationName === null || $nationType === null || $colorType === null){
            return false;
        }

        if(!is_string($nationName) || !is_string($nationType) || !is_int($colorType)){
            return false;
        }

        if(mb_strwidth($nationName) > 18 || $nationName == ''){
            return false;
        }

        if(!key_exists($colorType, GetNationColors())){
            return false;
        }

        try{
            $nationTypeClass = buildNationTypeClass($nationType);
        }
        catch(InvalidArgumentException $e){
            return false;
        }
        
        $this->arg = [
            'nationName'=>$nationName,
            'nationType'=>$nationType,
            'colorType'=>$colorType
        ];

        return true;
    }

    protected function init(){

        $general = $this->generalObj;
        $env = $this->env;

        $nationName = $this->arg['nationName'];
        $nationType = $this->arg['nationType'];
        $colorType = $this->arg['colorType'];

        $this->setCity();
        $this->setNation(['gennum']);

        $relYear = $env['year'] - $env['startyear'];
        
        $this->runnableConstraints=[
            ConstraintHelper::BeLord(),
            ConstraintHelper::WanderingNation(),
            ConstraintHelper::ReqNationValue('gennum', '수하 장수', '>=', 2),
            ConstraintHelper::BeOpeningPart($relYear),
            ConstraintHelper::CheckNationNameDuplicate($nationName),
            ConstraintHelper::AllowJoinAction(),
            ConstraintHelper::ConstructableCity(),
        ];
    }

    public function getBrief(): string
    {
        $nationName = $this->arg['nationName'];
        $josaUl = JosaUtil::pick($nationName, '을');
        return "【{$nationName}】{$josaUl} 건국";
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
        $date = $general->getTurnTime($general::TURNTIME_HM);
        $generalName = $general->getName();
        $josaYi = JosaUtil::pick($generalName, '이');

        $nationName = $this->arg['nationName'];
        $nationType = $this->arg['nationType'];
        $colorType = GetNationColors()[$this->arg['colorType']];

        $cityName = $this->city['name'];

        $josaUl = JosaUtil::pick($nationName, '을');

        $logger = $general->getLogger();

        $nationTypeClass = buildNationTypeClass($nationType);
        $nationTypeName = $nationTypeClass->getName();


        $logger->pushGeneralActionLog("<D><b>{$nationName}</></>{$josaUl} 건국하였습니다. <1>$date</>");
        $logger->pushGlobalActionLog("<Y>{$generalName}</>{$josaYi} <G><b>{$cityName}</b></>에 국가를 건설하였습니다.");

        $josaNationYi = JosaUtil::pick($nationName, '이');
        $logger->pushGlobalHistoryLog("<Y><b>【건국】</b></>{$nationTypeName} <D><b>{$nationName}</b></>{$josaNationYi} 새로이 등장하였습니다.");
        $logger->pushGeneralHistoryLog("<D><b>{$nationName}</b></>{$josaUl} 건국");
        $logger->pushNationalHistoryLog("<Y>{$generalName}</>{$josaYi} <D><b>{$nationName}</b></>{$josaUl} 건국");

        $exp = 1000;
        $ded = 1000;
        
        $exp = $general->onCalcStat($general, 'experience', $exp);
        $ded = $general->onCalcStat($general, 'dedication', $ded);

        $general->increaseVar('experience', $exp);
        $general->increaseVar('dedication', $ded);

        $db->update('city', [
            'nation'=>$general->getNationID(),
            'conflict'=>'{}'
        ], 'city=%i', $general->getCityID());

        $db->update('nation', [
            'name'=>$nationName,
            'color'=>$colorType,
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

    public function getForm(): string
    {

        if(count(getAllNationStaticInfo()) >= $this->env['maxnation']){
            return '더 이상 건국은 불가능합니다.';
        }


        //NOTE: 새로운 방법이 생기기 전까진 아무색이나 선택 가능하도록 하자.
        /*
        foreach(GetNationColors() as $color){
            $colorUsed[$color] = 0;
        }
    
        foreach(getAllNationStaticInfo() as $nation){
            if($nation['level'] <= 0){
                continue;
            }
            $colorUsed[$nation['color']]++;
        }
    
        $colorUsedCnt = 0;
        foreach($colorUsed as $color=>$used){
            if($used){
                continue;
            }
            $colorUsedCnt += 1;
        }
    
        //색깔이 다 쓰였으면 그냥 모두 허용
        if($colorUsedCnt === count($colorUsed)){
            foreach(array_keys($colorUsed) as $color){
                $colorUsed[$color] = 0;
            }
        }
        */
        

        ob_start();
?>
현재 도시에서 나라를 세웁니다. 중, 소도시에서만 가능합니다.<br>

<?php foreach(GameConst::$availableNationType as $nationType):
    $nationClass = buildNationTypeClass($nationType);

    [$name, $pros, $cons] = [$nationClass->getName(), $nationClass::$pros, $nationClass::$cons]; 
?>
            
    - <?=$name?> : <span style='color:cyan;'><?=$pros?></span> <span style='color:magenta;'><?=$cons?></span><br>
<?php endforeach; ?>
<br>
국명 : <input type='text' class='formInput' name="nationName" id="nationName" size='18' maxlength='18' style='color:white;background-color:black;'>
색깔 : <select class='formInput' name='colorType' id='colorType' size='1'>

<?php foreach(GetNationColors() as $idx=>$color): 
            /*
            if($colorUsed[$color] > 0){
                continue;
            }
            */
?>
    <option value="<?=$idx?>" style='background-color:<?=$color?>;color:<?=newColor($color)?>';>국가명</option>
<?php endforeach; ?>
</select>
성향 : <select class='formInput' name='nationType' id='nationType' size='1'>

<?php foreach(GameConst::$availableNationType as $nationType):
        $nationTypeName = buildNationTypeClass($nationType)->getName(); 
?>
    <option value='<?=$nationType?>' style=background-color:black;color:white;><?=$nationTypeName?></option>
<?php endforeach; ?>
</select>
<input type=button id="commonSubmit" value="<?=$this->getName()?>">
<?php
        return ob_get_clean();
    }
}