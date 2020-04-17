<?php
namespace sammo\Command\Nation;

use \sammo\{
    DB, Util, JosaUtil,
    General, DummyGeneral,
    ActionLogger,
    GameConst,
    LastTurn,
    GameUnitConst,
    Command,
    MessageTarget,
    Message
};

use function \sammo\{
    getDomesticExpLevelBonus,
    CriticalRatioDomestic, 
    CriticalScoreEx,
    GetImageURL
};

use \sammo\Constraint\Constraint;
use \sammo\Constraint\ConstraintHelper;

class che_몰수 extends Command\NationCommand{
    static protected $actionName = '몰수';
    static public $reqArg = true;

    protected function argTest():bool{
        if($this->arg === null){
            return false;
        }
        //NOTE: 사망 직전에 턴을 넣을 수 있으므로, 존재하지 않는 장수여도 argTest에서 바로 탈락시키지 않음
        if(!key_exists('isGold', $this->arg)){
            return false;
        }
        if(!key_exists('amount', $this->arg)){
            return false;
        }
        if(!key_exists('destGeneralID', $this->arg)){
            return false;
        }
        $isGold = $this->arg['isGold'];
        $amount = $this->arg['amount'];
        $destGeneralID = $this->arg['destGeneralID'];
        if(!is_int($amount)){
            return false;
        }
        $amount = Util::valueFit($amount, 100, GameConst::$maxResourceActionAmount);
        if(!is_bool($isGold)){
            return false;
        }
        if(!is_int($destGeneralID)){
            return false;
        }
        if($destGeneralID <= 0){
            return false;
        }
        if($destGeneralID == $this->generalObj->getID()){
            return false;
        }
        $this->arg = [
            'isGold'=>$isGold,
            'amount'=>$amount,
            'destGeneralID'=>$destGeneralID
        ];
        return true;
    }

    protected function init(){
        $general = $this->generalObj;

        $env = $this->env;

        $this->setCity();
        $this->setNation(['gold', 'rice']);

        $destGeneral = General::createGeneralObjFromDB($this->arg['destGeneralID'], ['gold', 'rice', 'npc', 'nation', 'imgsvr', 'picture'], 1);
        $this->setDestGeneral($destGeneral);

        $relYear = $env['year'] - $env['startyear'];
        
        $this->runnableConstraints=[
            ConstraintHelper::NotBeNeutral(), 
            ConstraintHelper::OccupiedCity(),
            ConstraintHelper::BeChief(),
            ConstraintHelper::NotOpeningPart($relYear),
            ConstraintHelper::SuppliedCity(),
            ConstraintHelper::ExistsDestGeneral(),
            ConstraintHelper::FriendlyDestGeneral()
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

    public function getBrief():string{
        $isGold = $this->arg['isGold'];
        $amount = $this->arg['amount'];
        $amountText = number_format($amount, 0);
        $resName = $isGold?'금':'쌀';
        $destGeneral = $this->destGeneralObj;
        $commandName = $this->getName();
        return "【{$destGeneral->getName()}】 {$resName} $amountText {$commandName}";
    }


    public function run():bool{
        if(!$this->isRunnable()){
            throw new \RuntimeException('불가능한 커맨드를 강제로 실행 시도');
        }

        $db = DB::db();

        $general = $this->generalObj;
        $date = $general->getTurnTime($general::TURNTIME_HM);

        $nation = $this->nation;
        $nationID = $nation['nation'];

        $isGold = $this->arg['isGold'];
        $amount = $this->arg['amount'];
        $resKey = $isGold?'gold':'rice';
        $resName = $isGold?'금':'쌀';
        $destGeneral = $this->destGeneralObj;
        
        $amount = Util::valueFit(
            $amount,
            0,
            ($destGeneral->getVar($resKey) - 
                ($isGold?GameConst::$generalMinimumGold:GameConst::$generalMinimumRice))
        );
        $amountText = number_format($amount, 0);

        if($destGeneral->getVar('npc') >= 2 && Util::randBool(0.01)){
            $npcTexts = [
                '몰수를 하다니... 이것이 윗사람이 할 짓이란 말입니까...',
                '사유재산까지 몰수해가면서 이 나라가 잘 될거라 믿습니까? 정말 이해할 수가 없군요...',
                '내 돈 내놔라! 내 돈! 몰수가 왠 말이냐!',
                '몰수해간 내 자금... 언젠가 몰래 다시 빼내올 것이다...',
                '몰수로 인한 사기 저하는 몰수로 얻은 물자보다 더 손해란걸 모른단 말인가!'  
            ];
            $text = Util::choiceRandom($npcTexts);
            $src = new MessageTarget(
                $destGeneral->getID(), 
                $destGeneral->getName(),
                $nationID,
                $nation['name'],
                $nation['color'],
                GetImageURL($destGeneral->getVar('imgsvr'), $destGeneral->getVar('picture'))
            );
            $msg = new Message(
                Message::MSGTYPE_PUBLIC, 
                $src,
                $src,
                $text,
                new \DateTime(),
                new \DateTime('9999-12-31'),
                []
            );
            $msg->send();
        }
        
        $logger = $general->getLogger();

        $destGeneral->increaseVarWithLimit($resKey, -$amount, 0);
        $db->update('nation', [
            $resKey=>$db->sqleval('%b + %i', $resKey, $amount)
        ], 'nation=%i', $nationID);

        $destGeneral->getLogger()->pushGeneralActionLog("{$resName} {$amountText}을 몰수 당했습니다.", ActionLogger::PLAIN);
        $logger->pushGeneralActionLog("{$destGeneral->getName()}에게서 {$resName} <C>$amountText</>을 몰수했습니다. <1>$date</>");

        $this->setResultTurn(new LastTurn(static::getName(), $this->arg));
        $general->applyDB($db);
        $destGeneral->applyDB($db);

        return true;
    }

    public function getForm(): string
    {
        //TODO: 암행부처럼 보여야...
        $db = DB::db();

        $destRawGenerals = $db->query('SELECT no,name,officer_level,npc,gold,rice FROM general WHERE nation = %i AND no != %i ORDER BY npc,binary(name)',$this->generalObj->getNationID(), $this->generalObj->getID());
        $destGeneralList = [];
        foreach($destRawGenerals as $destGeneral){
            $nameColor = \sammo\getNameColor($destGeneral['npc']);
            if($nameColor){
                $nameColor = " style='color:{$nameColor}'";
            }

            $name = $destGeneral['name'];
            if($destGeneral['officer_level'] >= 5){
                $name = "*{$name}*";
            }

            $destGeneralList[] = [
                'no'=>$destGeneral['no'],
                'color'=>$nameColor,
                'name'=>$name,
                'gold'=>$destGeneral['gold'],
                'rice'=>$destGeneral['rice']
            ];
        }
        ob_start();
?>
장수의 자금이나 군량을 몰수합니다.<br>
몰수한것은 국가재산으로 귀속됩니다.<br>
<select class='formInput' name="destGeneralID" id="destGeneralID" size='1' style='color:white;background-color:black;'>
<?php foreach($destGeneralList as $destGeneral): ?>
<option value='<?=$destGeneral['no']?>' <?=$destGeneral['color']?>><?=$destGeneral['name']?>(금:<?=$destGeneral['gold']?>, 쌀:<?=$destGeneral['rice']?>)</option>
<?php endforeach; ?>
</select>
<select class='formInput' name="isGold" id="isGold" size='1' style='color:white;background-color:black;'>
    <option value="true">금</option>
    <option value="false">쌀</option>
</select>
</select>
<select class='formInput' name="amount" id="amount" size='1' style='color:white;background-color:black;'>
<?php foreach(GameConst::$resourceActionAmountGuide as $amount): ?>
    <option value='<?=$amount?>'><?=$amount?></option>
<?php endforeach; ?>
</select> <input type=button id="commonSubmit" value="<?=$this->getName()?>"><br>
<br>
<?php
        return ob_get_clean();
    }
}