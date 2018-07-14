<?php

namespace sammo;

class ActionLogger{
    protected $generalId;
    protected $nationId;
    protected $autoFlush;
    
    protected $generalHistoryLog = [];
    protected $generalActionLog = [];
    protected $generalBattleResultLog = [];
    protected $generalBattleDetailLog = [];
    protected $nationalHistoryLog = [];
    protected $globalHistoryLog = [];
    protected $globalActionLog = [];

    public function __construct(int $generalId, int $nationId, bool $autoFlush = true){
        $this->generalId = $generalId;
        $this->nationId = $nationId;
        $this->autoFlush = $autoFlush;
    }

    public function __destruct(){
        if($this->autoFlush){
            $this->flush();
        }
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
            pushGenLog(['no'=>$this->generalId], $this->generalBattleResultLog);
            $this->generalBattleResultLog = [];
        }

        if($this->generalBattleDetailLog){
            pushGenLog(['no'=>$this->generalId], $this->generalBattleDetailLog);
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
            pushWorldHistory($this->globalHistoryLog);
            $this->globalHistoryLog = [];
        }

        if($this->globalActionLog){
            pushGeneralPublicRecord($this->globalActionLog);
            $this->globalActionLog = [];
        }
    }


    public function pushGeneralHistoryLog($text){
        if(!$text){
            return;
        }
        if(is_array($text)){
            foreach($text as $textItem){
                $this->pushGeneralHistoryLog($textItem);
            }
            return;
        }
        $this->generalHistoryLog[] = $text;
    }

    public function pushGeneralActionLog($text){
        if(!$text){
            return;
        }
        if(is_array($text)){
            foreach($text as $textItem){
                $this->pushGeneralActionLog($textItem);
            }
            return;
        }
        $this->generalActionLog[] = $text;
    }

    public function pushGeneralBattleResultLog($text){
        if(!$text){
            return;
        }
        if(is_array($text)){
            foreach($text as $textItem){
                $this->pushGeneralBattleResultLog($textItem);
            }
            return;
        }
        $this->generalBattleResultLog[] = $text;
    }

    public function pushGeneralBattleDetailLog($text){
        if(!$text){
            return;
        }
        if(is_array($text)){
            foreach($text as $textItem){
                $this->pushGeneralBattleDetailLog($textItem);
            }
            return;
        }
        $this->generalBattleDetailLog[] = $text;
    }

    public function pushNationalHistoryLog($text){
        if(!$text){
            return;
        }
        if(is_array($text)){
            foreach($text as $textItem){
                $this->pushNationalHistoryLog($textItem);
            }
            return;
        }
        $this->nationalHistoryLog[] = $text;
    }

    public function pushGlobalActionLog($text){
        if(!$text){
            return;
        }
        if(is_array($text)){
            foreach($text as $textItem){
                $this->pushGlobalActionLog($textItem);
            }
            return;
        }
        $this->globalActionLog[] = $text;
    }

    public function pushGlobalHistoryLog($text){
        if(!$text){
            return;
        }
        if(is_array($text)){
            foreach($text as $textItem){
                $this->pushGlobalHistoryLog($textItem);
            }
            return;
        }
        $this->globalHistoryLog[] = $text;
    }



}