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

    /**
     * @return MessageTarget
     */
    public static function buildFromArray($arr)
    {
        if(!$arr){
            throw new \InvalidArgumentException();
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

    /**
     * DB 부하 감수하고 일일히 찾아줌. 
     * 게임 로직과 크게 연관이 없는 곳에서 메시지를 생성해야 할 경우에만 사용할 것을 권함.
     * @return MessageTarget
     */
    public static function buildQuick(int $generalID){
       $db = DB::db();
       list(
           $generalName, 
           $nationID, 
           $imgsvr, 
           $picture
        ) = $db->queryFirstList('SELECT `name`, nation, imgsvr, picture FROM general WHERE `no`=%i', $generalID); 

        if($generalName === null){
            throw new \RuntimeException('장수가 없음:'.$generalID);
        }

        $icon = GetImageURL($imgsvr, $picture);
        $nation = getNationStaticInfo($nationID);
        return new MessageTarget($generalID, $generalName, $nationID, $nation['name'], $nation['color'], $icon);
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

    public function toArrayLight() : array{
        //TODO: 이걸 꼭 연관 배열로 보낼 이유가 없다.
        //TODO: 아이콘도 축약 가능하다.
        return [
            'name'=>$this->generalName,
            'nation'=>$this->nationName,
            'color'=>$this->color,
            'icon'=>$this->icon
        ];
    }
}