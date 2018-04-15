<?php
namespace sammo;

class MessageTarget extends Target {
    /** @var string */
    public $generalName;
    /** @var string */
    public $nationName;
    /** @var string */
    public $color;
    /** @var string */
    public $icon;
    
    public function __construct(
        int $generalID,
        string $generalName,
        int $nationID,
        string $nationName,
        string $color,
        string $icon=''
    ){
        
        parent::__construct($generalID, $nationID);

        if(!$icon){
            $icon = ServConfig::getSharedIconPath().'/default.jpg';
        }
        
        $this->generalName = $generalName;
        $this->nationName = $nationName;
        $this->color = $color;
        $this->icon = $icon;
    }

    public static function buildFromArray(array $arr) : MessageTarget
    {
        if(!$arr){
            return null;
        }
        if(!Util::array_get($arr['nation_id'])){
            $arr['nation'] = '재야';
            $arr['color'] = '#000000';
            $arr['nation_id'] = 0;
        }

        return new MessageTarget(
            $arr['id'], 
            $arr['name'], 
            $arr['nation_id'], 
            $arr['nation'], 
            $arr['color'], 
            $arr['icon']??''
        );
    }

    public function toArray() : array{
        return [
            'id'=>$this->generalID,
            'name'=>$this->generalName,
            'nation_id'=>$this->nationID,
            'nation'=>$this->nationName,
            'color'=>$this->color,
            'icon'=>$this->icon
        ];
    }
}