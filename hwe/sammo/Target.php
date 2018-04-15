<?php
namespace sammo;

class Target {
    /** @var int */
    public $generalID;
    /** @var int */
    public $nationID;
    
    public function __construct(int $generalID, int $nationID){

        $this->generalID = $generalID;
        $this->nationID = $nationID;
    }

    /**
     * @return Target
     */
    public static function buildFromArray($arr)
    {
        return new Target($arr['id'], $arr['nation_id']??0);
    }

    public function toArray() : array{
        return [
            'id'=>$this->generalID,
            'nation_id'=>$this->nationID
        ];
    }
}