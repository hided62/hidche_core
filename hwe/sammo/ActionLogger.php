<?php

namespace sammo;

class ActionLogger{
    protected $generalId;
    protected $nationId;
    protected $autoFlush;

    protected $year = null;
    protected $month = null;
    
    protected $generalHistoryLog = [];
    protected $generalActionLog = [];
    protected $generalBattleResultLog = [];
    protected $generalBattleDetailLog = [];
    protected $nationalHistoryLog = [];
    protected $globalHistoryLog = [];
    protected $globalActionLog = [];

    const RAWTEXT = 0;
    const PLAIN = 1;
    const YEAR_MONTH = 2;
    const YEAR = 3;
    const MONTH = 4;
    const EVENT_PLAIN = 5;
    const EVENT_YEAR_MONTH = 6;
    const NOTICE = 7;
    const NOTICE_YEAR_MONTH = 8;

    public function __construct(int $generalId, int $nationId, int $year, int $month, bool $autoFlush = true){
        $this->generalId = $generalId;
        $this->nationId = $nationId;
        $this->year = $year;
        $this->month = $month;
        $this->autoFlush = $autoFlush;
    }

    public function __destruct(){
        if($this->autoFlush){
            $this->flush();
        }
    }

    public function rollback(){
        $this->generalHistoryLog = [];
        $this->generalActionLog = [];
        $this->generalBattleResultLog = [];
        $this->generalBattleDetailLog = [];
        $this->nationalHistoryLog = [];
        $this->globalHistoryLog = [];
        $this->globalActionLog = [];
    }

    public function flush(){
        $db = DB::db();

        if($this->generalHistoryLog){
            $rawText = join('<br>', $this->generalHistoryLog).'<br>';
            $db->update('general', [
                'history'=>$db->sqleval('concat(%s, history)', $rawText)
            ], 'no=%i', $this->generalId);
            $this->generalHistoryLog = [];
        }

        if($this->generalActionLog){
            pushGenLog(['no'=>$this->generalId], $this->generalActionLog);
            $this->generalActionLog = [];
        }

        if($this->generalBattleResultLog){
            pushBatRes(['no'=>$this->generalId], $this->generalBattleResultLog);
            $this->generalBattleResultLog = [];
        }

        if($this->generalBattleDetailLog){
            pushBatLog(['no'=>$this->generalId], $this->generalBattleDetailLog);
            $this->generalBattleDetailLog = [];
        }

        if($this->nationId && $this->nationalHistoryLog){
            $rawText = join('<br>', $this->nationalHistoryLog).'<br>';
            $db->update('nation', [
                'history'=>$db->sqleval('concat(%s, history)', $rawText)
            ], 'nation=%i', $this->nationId);
            $this->nationalHistoryLog = [];
        }

        if($this->globalHistoryLog){
            pushWorldHistory($this->globalHistoryLog, $this->year, $this->month);
            $this->globalHistoryLog = [];
        }

        if($this->globalActionLog){
            pushGeneralPublicRecord($this->globalActionLog, $this->year, $this->month);
            $this->globalActionLog = [];
        }
    }


    public function pushGeneralHistoryLog($text, int $formatType = self::YEAR_MONTH){
        if(!$text){
            return;
        }
        if(is_array($text)){
            foreach($text as $textItem){
                $this->pushGeneralHistoryLog($textItem);
            }
            return;
        }

        $text = $this->formatText($text, $formatType);
        $this->generalHistoryLog[] = $text;
    }

    public function pushGeneralActionLog($text, int $formatType = self::MONTH){
        if(!$text){
            return;
        }
        if(is_array($text)){
            foreach($text as $textItem){
                $this->pushGeneralActionLog($textItem);
            }
            return;
        }

        $text = $this->formatText($text, $formatType);
        $this->generalActionLog[] = $text;
    }

    public function pushGeneralBattleResultLog($text, int $formatType = self::RAWTEXT){
        if(!$text){
            return;
        }
        if(is_array($text)){
            foreach($text as $textItem){
                $this->pushGeneralBattleResultLog($textItem);
            }
            return;
        }

        $text = $this->formatText($text, $formatType);
        $this->generalBattleResultLog[] = $text;
    }

    public function pushGeneralBattleDetailLog($text, int $formatType = self::PLAIN){
        if(!$text){
            return;
        }
        if(is_array($text)){
            foreach($text as $textItem){
                $this->pushGeneralBattleDetailLog($textItem);
            }
            return;
        }

        $text = $this->formatText($text, $formatType);
        $this->generalBattleDetailLog[] = $text;
    }

    public function pushNationalHistoryLog($text, int $formatType = self::RAWTEXT){
        if(!$text){
            return;
        }
        if(is_array($text)){
            foreach($text as $textItem){
                $this->pushNationalHistoryLog($textItem);
            }
            return;
        }

        $text = $this->formatText($text, $formatType);
        $this->nationalHistoryLog[] = $text;
    }

    public function pushGlobalActionLog($text, int $formatType = self::MONTH){
        if(!$text){
            return;
        }
        if(is_array($text)){
            foreach($text as $textItem){
                $this->pushGlobalActionLog($textItem);
            }
            return;
        }

        $text = $this->formatText($text, $formatType);
        $this->globalActionLog[] = $text;
    }

    public function pushGlobalHistoryLog($text, int $formatType = self::YEAR_MONTH){
        if(!$text){
            return;
        }
        if(is_array($text)){
            foreach($text as $textItem){
                $this->pushGlobalHistoryLog($textItem);
            }
            return;
        }

        $text = $this->formatText($text, $formatType);
        $this->globalHistoryLog[] = $text;
    }

    public function formatText(string $text, int $formatType):string{
        if($formatType === self::RAWTEXT){
            return $text;
        }

        if($formatType === self::PLAIN){
            return "<C>●</>{$text}";
        }

        if($formatType === self::YEAR_MONTH){
            return "<C>●</>{$this->year}년 {$this->month}월:{$text}";
        }

        if($formatType === self::YEAR){
            return "<C>●</>{$this->year}년:{$text}";
        }

        if($formatType === self::MONTH){
            return "<C>●</>{$this->month}월:{$text}";
        }

        if($formatType === self::EVENT_PLAIN){
            return "<S>◆</>{$text}";
        }

        if($formatType === self::EVENT_YEAR_MONTH){
            return "<S>◆</>{$this->year}년 {$this->month}월:{$text}";
        }

        if($formatType === self::NOTICE){
            return "<R>★</>{$text}";
        }

        if($formatType === self::NOTICE_YEAR_MONTH){
            return "<R>★</>{$this->year}년 {$this->month}월:{$text}";
        }
        
        return $text;
    }

    public function pushBattleResultTemplate(
        WarUnit $me,
        WarUnit $oppose
    ){
        if($me instanceof WarUnitCity){
            return;
        }

        $templates = new \League\Plates\Engine(__dir__.'/../templates');

        $render_me = [
            'crewtype' => $me->getCrewTypeShortName(),
            'name' => $me->getName(),
            'remain_crew' => $me->getHP(),
            'killed_crew' => -$me->getDeadCurrentBattle()
        ];

        $render_oppose = [
            'crewtype' => $oppose->getCrewTypeShortName(),
            'name' => $oppose->getName(),
            'remain_crew' => $oppose->getHP(),
            'killed_crew' => -$oppose->getDeadCurrentBattle()
        ];

        if(!$me->isAttacker()){
            $warType = 'defense';
            $warTypeStr = '←';
        }
        else if($oppose instanceof WarUnitCity){
            $warType = 'siege';
            $warTypeStr = '→';
        }
        else{
            $warType = 'attack';
            $warTypeStr = '→';
        }

        $res = str_replace(["\r\n", "\r", "\n"], '', $templates->render('small_war_log',[
            'year'=>$this->year,
            'month'=>$this->month,
            'war_type'=>$warType,
            'war_type_str'=>$warTypeStr,
            'me' => $render_me,
            'you' => $render_oppose,
        ]));

        $this->pushGeneralBattleResultLog($res, self::EVENT_YEAR_MONTH);
        $this->pushGeneralBattleDetailLog($res, self::EVENT_YEAR_MONTH);
        $this->pushGeneralActionLog($res, self::EVENT_YEAR_MONTH);
    }

}