<?php
namespace sammo\TextDecoration;

use \sammo\General;
use \sammo\Util;
use \sammo\JosaUtil;

class DyingMessage{
    private $name;
    private $npc;

    static protected $defaultMessage = '<Y>:name:</>:이: <R>사망</>했습니다.';
    static protected $messages = null;
    static protected $scenarioNPCMessages = null;
    static protected $utilNPCMessages = null;

    protected static function getMessageList():array{
        return [
            '<Y>:name:</>:이: 역병에 걸려 <R>죽고</> 말았습니다.',
            '<Y>:name:</>:이: <R>요절</>하고 말았습니다.',
            '<Y>:name:</>:이: 거리에서 갑자기 <R>객사</>하고 말았습니다.',
            '<Y>:name:</>:이: 안타깝게도 번개에 맞아 <R>죽고</> 말았습니다.',
            '<Y>:name:</>:이: 고리대금에 시달리다가 <R>자살</>하고 말았습니다.',
            '<Y>:name:</>:이: 일확천금에 놀라 심장마비로 <R>죽고</> 말았습니다.',
            '<Y>:name:</>:이: 산속에서 호랑이에게 물려 <R>죽고</> 말았습니다.',
            '<Y>:name:</>:이: 산책중 곰에게 할퀴어 <R>죽고</> 말았습니다.',
            '<Y>:name:</>:이: 수영을 하다 <R>익사</>하고 말았습니다.',
            '<Y>:name:</>:이: 황제를 모독하다가 <R>처형</>당하고 말았습니다.',
            '<Y>:name:</>:이: 이튿날 침실에서 <R>죽은채로</>발견되었습니다.',
            '<Y>:name:</>:이: 색에 빠져 기력이 쇠진해 <R>죽고</>말았습니다.',
            '<Y>:name:</>:이: 미녀를 보고 심장마비로 <R>죽고</>말았습니다.',
            '<Y>:name:</>:이: 우울증에 걸려 <R>자살</>하고 말았습니다.',
            '<Y>:name:</>:이: 천하 정세를 비관하며 <R>분신</>하고 말았습니다.',
            '<Y>:name:</>:이: 어떤 관심도 못받고 쓸쓸히 <R>죽고</>말았습니다.',
            '<Y>:name:</>:이: 유산 상속 문제로 다투다가 <R>살해</>당했습니다.',
            '<Y>:name:</>:이: 누군가의 사주로 자객에게 <R>암살</>당했습니다.',
            '<Y>:name:</>:이: 바람난 배우자에게 <R>독살</>당하고 말았습니다.',
            '<Y>:name:</>:이: 농약을 술인줄 알고 마셔 <R>죽고</>말았습니다.',
            '<Y>:name:</>:이: 아무 이유 없이 <R>죽고</>말았습니다.',
            '<Y>:name:</>:이: 전재산을 잃고 화병으로 <R>죽고</>말았습니다.',
            '<Y>:name:</>:이: 단식운동을 하다가 굶어 <R>죽고</>말았습니다.',
            '<Y>:name:</>:이: 귀신에게 홀려 시름 앓다가 <R>죽고</>말았습니다.',
            '<Y>:name:</>:이: 사람들에게 집단으로 맞아서 <R>죽고</>말았습니다.',
            '<Y>:name:</>:이: 갑자기 성벽에서 뛰어내려 <R>죽고</>말았습니다.',
            '<Y>:name:</>:이: 농사중 호미에 머리를 맞아 <R>죽고</>말았습니다.',
            '<Y>:name:</>:이: 저세상이 궁금하다며 <R>자살</>하고 말았습니다.',
            '운좋기로 소문난 <Y>:name:</>:이: 불운하게도 <R>죽고</>말았습니다.',
            '<Y>:name:</>:이: 무리하게 단련을 하다가 <R>죽고</>말았습니다.',
            '<Y>:name:</>:이: 생활고를 비관하며 <R>자살</>하고 말았습니다.',
            '<Y>:name:</>:이: 평생 결혼도 못해보고 <R>죽고</> 말았습니다.',
            '<Y>:name:</>:이: 과식하다 배가 터져 <R>죽고</> 말았습니다.',
            '<Y>:name:</>:이: 웃다가 숨이 넘어가 <R>죽고</> 말았습니다.',
            '<Y>:name:</>:이: 추녀를 보고 놀라서 <R>죽고</> 말았습니다.',
            '<Y>:name:</>:이: 물에 빠진 사람을 구하려다 같이 <R>죽고</> 말았습니다.',
            '<Y>:name:</>:이: 독살을 준비하다 독에 걸려 <R>죽고</> 말았습니다.',
            '<Y>:name:</>:이: 뒷간에서 너무 힘을 주다가 <R>죽고</> 말았습니다.',
            '<Y>:name:</>:이: 돌팔이 의사에게 치료받다가 <R>죽고</> 말았습니다.',
            '<Y>:name:</>:이: 남의 보약을 훔쳐먹다 부작용으로 <R>죽고</> 말았습니다.',
            '희대의 사기꾼 <Y>:name:</>:이: <R>사망</>했습니다.',
            '희대의 호색한 <Y>:name:</>:이: <R>사망</>했습니다.',
        ];
    }

    protected static function getScenarioNPCMessageList():array{
        return [
            static::$defaultMessage,
        ];
    }

    protected static function getUtilNPCMessageList():array{
        return [
            '<Y>:name:</>:이: 푸대접에 실망하여 떠났습니다.',
            '<Y>:name:</>:이: 갑자기 화를 내며 떠났습니다.',
            '<Y>:name:</>:이: 의견차이를 좁히지 못하고 떠났습니다.',
            '<Y>:name:</>:이: 판단 착오였다며 떠났습니다.',
            '<Y>:name:</>:이: 생활고가 나아지지 않는다며 떠났습니다.',
            '<Y>:name:</>:이: 기대가 너무 컸다며 떠났습니다.',
            '<Y>:name:</>:이: 아무 이유 없이 떠났습니다.',
            '<Y>:name:</>:이: 자기 목적은 달성했다며 떠났습니다.',
            '<Y>:name:</>:이: 자기가 없어도 될것 같다며 떠났습니다.',
            '<Y>:name:</>:이: 처자식이 그립다며 떠났습니다.',
        ];
    }

    protected static function initMessageList():void{
        static::$messages = static::getMessageList();
        static::$scenarioNPCMessages = static::getScenarioNPCMessageList();
        static::$utilNPCMessages = static::getUtilNPCMessageList();
    }

    protected function _constructWithObj(General $general){
        $this->name = $general->getName();
        $this->npc = $general->getVar('npc');
    }

    protected function _constructWithRawValue(string $name, int $npc){
        $this->name = $name;
        $this->npc = $npc;
    }

    function __construct($name, ?int $npc = null)
    {
        if(static::$messages === null){
            static::initMessageList();
        }
        if($name instanceof General){
            $this->_constructWithObj($name);
        }
        else{
            $this->_constructWithRawValue($name, $npc);
        }
    }

    public function getText():string{
        $name = $this->name ;
        $npc = $this->npc;

        if($npc == 0){
            $text = Util::choiceRandom(static::$messages??[]);
        }
        else if($npc == 2 || $npc == 6){
            $text = Util::choiceRandom(static::$scenarioNPCMessages??[]);
        }
        else if($npc == 3 || $npc == 4){
            $text = Util::choiceRandom(static::$utilNPCMessages??[]);
        }
        else{
            $text = static::$defaultMessage;
        }

        $text = str_replace(':name:', $name, $text);

        JosaUtil::batch($text, $name);
        return $text;
    }
}