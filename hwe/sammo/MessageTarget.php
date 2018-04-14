<?php
namespace sammo;

class MessageTarget extends Target {
    /** @var string */
    public $generalName;
    /** @var string */
    public $nationName;
    /** @var string */
    public $color;
    
    public function __construct(int $generalID, string $generalName, int $nationID, string $nationName, string $color){
        
        parent::__construct($generalID, $nationID);

        if($mailbox > Message::MAILBOX_NATIONAL){
            $this->isGeneral = false;
        }
        else{
            $this->isGeneral = true;
        }
        
        $this->generalName = $generalName;
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

        return new MessageTarget($arr['id'], $arr['name'], $arr['nation_id'], $arr['nation'], $arr['color']);
    }

    public function toArray() : array{
        return [
            'id'=>$this->generalID,
            'name'=>$this->generalName,
            'nation_id'=>$this->nationID,
            'nation'=>$this->nationName,
            'color'=>$this->color
        ];
    }
}