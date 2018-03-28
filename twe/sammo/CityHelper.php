<?php
namespace sammo\Scenario;
use \sammo\DB;

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
        
        foreach (DB::db()->query('SELECT `city` as `id`, `name`, `level`, `nation` from city') as $city) {
            $list[$city['id']] = $city;
            $listInv[$city['name']] = $city;

            if(!key_exists($id, $listByNation)){
                $listByNation[$id] = [];
            }
            $listByNation[$id][] = $city;
        }

        self::$list = $list;
        self::$listInv = $listInv;
        self::$listByNation = $listByNation;
    }

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

        return self::$listByNation[$nationID];
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

        return self::$listInv[$cityName];
    }

    
}