<?php
namespace sammo;

use MeekroDB;
use sammo\GameConst;
use sammo\Scenario\GeneralBuilder;
use sammo\Util;

abstract class AbsGeneralPool{
    protected $builder;
    protected $info;
    protected $db=null;
    protected $uniqueName;
    protected $generalName;
    protected $validUntil;

    /*
     * info = [
     * uniqueName
     * generalName
     * imgsvr
     * picture
     * 
     * leadership
     * strength
     * intel
     * 
     * experience
     * dedication
     * 
     * dex[5]
     * 
     * specialDomestic
     * specialWar
     */

    public function __construct(\MeekroDB $db, array $info, string $validUntil)
    {
        $this->db = $db;
        $this->info = $info;
        $this->uniqueName = $info['uniqueName'];
        $this->generalName = $info['generalName'];
        $this->builder = new GeneralBuilder(
            $info['generalName'],
            $info['imgsvr'],
            $info['picture'],
            0
        );
        $this->validUntil = $validUntil;

        $builder = $this->builder;
        if(key_exists('leadership', $info)){
            $builder->setStat($info['leadership'], $info['strength'], $info['intel']);
        }

        if(key_exists('experience', $info)){
            $builder->setExpDed($info['experience'], $info['dedication']);
        }

        if(key_exists('dex', $info)){
            $builder->setDex($info['dex'][0], $info['dex'][1], $info['dex'][2], $info['dex'][3], $info['dex'][4]);
        }

        if(key_exists('specialDomestic', $info) || key_exists('specialWar', $info)){
            $builder->setSpecial($info['specialDomestic']??GameConst::$defaultSpecialDomestic, $info['specialWar']??GameConst::$defaultSpecialWar);
        }

    }

    public function getUniqueName():string{
        return $this->uniqueName;
    }

    public function getInfo():array{
        return $this->info;
    }

    static protected function checkDuplicatedCnt(\MeekroDB $db, string $name):int{
        $duplicateCnt = 0;
        foreach(GeneralBuilder::$prefixList as $npcPrefix){
            $testName = "{$npcPrefix}{$name}";
            $duplicateCnt += $db->queryFirstField('SELECT count(no) FROM general WHERE name LIKE %s', $testName.'%');
        }
        return $duplicateCnt;
    }

    public function getGeneralBuilder():GeneralBuilder{
        if(!$this->generalName){
            throw new \RuntimeException('generalName not picked');
        }
        return $this->builder;
    }

    public function getValidUntil():string{
        return $this->validUntil;
    }

    /**
     * @param \MeekroDB $db
     * @param int $owner 
     * @param int $pickCnt 
     * @param null|string $prefix
     * @return AbsGeneralPool[]
     */
    static abstract public function pickGeneralFromPool(\MeekroDB $db, int $owner, int $pickCnt, ?string $prefix=null):array;
    abstract public function occupyGeneralName():bool;
    

    abstract public static function getPoolName():string;
    abstract public static function initPool(\MeekroDB $db);
}