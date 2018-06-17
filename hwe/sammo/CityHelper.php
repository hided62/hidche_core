<?php
namespace sammo;

class CityHelper{
    //Just Helper
    private static $list = null;
    private static $listInv = null;
    private static $listByNation  = null;

    private function __construct(){

    }

    public static function flushCache(){
        self::$list = null;
        self::$listInv = null;
        self::$listByNation = null;
    }

    public static function generateCache(){
        $list = [];
        $listInv = [];
        $listByNation = [];

        '@phan-var array<int,mixed>|null $list';
        '@phan-var array<string,mixed>|null $listInv';
        '@phan-var array<int,mixed>|null $listByNation';
        
        foreach (DB::db()->query('SELECT `city` as `id`, `name`, `level`, `nation` from city') as $city) {
            $id = $city['id'];
            $name = $city['name'];
            $nationID = $city['nation'];
            $list[$id] = $city;
            $listInv[$city['name']] = $city;

            if(!key_exists($nationID, $listByNation)){
                $listByNation[$nationID] = [];
            }
            $listByNation[$nationID][] = $city;
        }

        self::$list = $list;
        self::$listInv = $listInv;
        self::$listByNation = $listByNation;
    }

    /**
     * @return array[]
     */
    public static function getAllCities(){
        if(self::$list === null){
            self::generateCache();
        }

        return self::$list;
    }

    public static function getAllNationCities(int $nationID){
        if(self::$listByNation === null){
            self::generateCache();
        }

        return Util::array_get(self::$listByNation[$nationID], []);
    }

    public static function getCity(int $cityID){
        if(self::$list === null){
            self::generateCache();
        }

        return self::$list[$cityID];
    }

    public static function getCityByName(string $cityName){
        if(self::$listInv === null){
            self::generateCache();
        }

        $city = self::$listInv[$cityName]??null;
        if($city === null){
            trigger_error("$cityName 에 해당하는 도시가 없습니다.");
        }
        return $city;
    }

    
}