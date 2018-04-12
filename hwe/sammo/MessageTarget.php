<?php
namespace sammo;

class MessageTarget {
    /** @var int */
    public $generalID;
    /** @var int */
    public $nationID;

    /** @var string */
    public $nationName;
    /** @var string */
    public $color;
    
    public function __construct(int $generalID, int $nationID, string $nationName, string $color){
        

        if($mailbox > Message::MAILBOX_NATIONAL){
            $this->isGeneral = false;
        }
        else{
            $this->isGeneral = true;
        }
        
        $this->generalID = $generalID;
        $this->nationID = $nationID;
        $this->nationName = $nationName;
        $this->color = $color;
    }

    public static function buildFromArray(array $arr) : MessageTarget
    {
        if(!Util::array_get($arr['nation'])){
            $arr['nation'] = '재야';
            $arr['color'] = '#ffffff';
            $arr['nation_id'] = 0;
        }

        return new MessageTarget($arr['id'], $arr['nation_id'], $arr['nation'], $arr['color']);
    }

    public function toArray() : array{
        return [
            'id'=>$this->generalID,
            'nation_id'=>$this->nationID,
            'nation'=>$this->nationName,
            'color'=>$this->color
        ];
    }
}