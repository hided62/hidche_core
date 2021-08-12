<?php
namespace sammo\GeneralPool;

use MeekroDB;
use sammo\AbsGeneralPool;
use sammo\GameConst;
use sammo\Util;


class RandomNameGeneral extends AbsGeneralPool{
    public static function getPoolName():string{return "임의 장수명";}

    public function occupyGeneralName(): bool
    {
        $generalID = $this->getGeneralBuilder()->getGeneralID();
        if($generalID === null){
            throw new \RuntimeException('build되지 않음');
        }

        $db= $this->db;
        $db->update('select_pool', [
            'general_id'=>$generalID,
            'owner'=>null,
            'reserved_until'=>null,
        ], 'unique_name = %s', $this->uniqueName);
        return $db->affectedRows()!=0;
    }

    public function giveGeneralSpec(array $pickTypeList, array $avgGen, array $env){
        //do Nothing
        $dexTotal = $avgGen['dex_t'];
        
        $pickType = Util::choiceRandomUsingWeight($pickTypeList);

        $totalStat = GameConst::$defaultStatNPCTotal;
        $minStat = GameConst::$defaultStatNPCMin;
        $mainStat = GameConst::$defaultStatNPCMax - Util::randRangeInt(0, GameConst::$defaultStatNPCMin);
        $otherStat = $minStat + Util::randRangeInt(0, Util::toInt(GameConst::$defaultStatNPCMin/2));
        $subStat = $totalStat - $mainStat - $otherStat;
        if ($subStat < $minStat) {
            $subStat = $otherStat;
            $otherStat = $minStat;
            $mainStat = $totalStat - $subStat - $otherStat;
            if ($mainStat) {
                throw new \LogicException('기본 스탯 설정값이 잘못되어 있음');
            }
        }

        if ($pickType == '무') {
            $leadership = $subStat;
            $strength = $mainStat;
            $intel = $otherStat;
            $dexVal = Util::choiceRandom([
                [$dexTotal * 5 / 8, $dexTotal / 8, $dexTotal / 8, $dexTotal / 8],
                [$dexTotal / 8, $dexTotal * 5 / 8, $dexTotal / 8, $dexTotal / 8],
                [$dexTotal / 8, $dexTotal / 8, $dexTotal * 5 / 8, $dexTotal / 8],
            ]);
        } else if ($pickType == '지') {
            $leadership = $subStat;
            $strength = $otherStat;
            $intel = $mainStat;
            $dexVal = [$dexTotal / 8, $dexTotal / 8, $dexTotal / 8, $dexTotal * 5 / 8];
        } else {
            $leadership = $otherStat;
            $strength = $subStat;
            $intel = $mainStat;
            $dexVal = [$dexTotal / 4, $dexTotal / 4, $dexTotal / 4, $dexTotal / 4];
        }

        $leadership = Util::round($leadership);
        $strength = Util::round($strength);
        $intel = Util::round($intel);

        $builder = $this->getGeneralBuilder();
        $builder->setStat($leadership, $strength, $intel);
        $builder->setDex($dexVal[0], $dexVal[1], $dexVal[2], $dexVal[3], $avgGen['dex5']);
        $builder->setCityID(Util::choiceRandom(array_keys(\sammo\CityConst::all())));

        $builder->setExpDed($avgGen['exp'], $avgGen['ded']);
    }

    static protected function pickGeneral1FromPool(\MeekroDB $db, int $owner, ?string $prefix=null):self{

        $loopCnt = 0;
        while(true){

            $firstname = Util::choiceRandom(GameConst::$randGenFirstName);
            $middlename = Util::choiceRandom(GameConst::$randGenMiddleName);
            $lastname = Util::choiceRandom(GameConst::$randGenLastName);

            $generalName = "{$firstname}{$middlename}{$lastname}";
            if($prefix){
                $generalName = $prefix.$generalName;
            }

            $dupCnt = static::checkDuplicatedCnt($db, $generalName);
            if($dupCnt == 0){
                break;
            }
            if($loopCnt >= 99 || $dupCnt < 2){
                $generalName .= $dupCnt+1;
                break;
            }
            $loopCnt += 1;
        }

        $uniqueName = $generalName;

        return new static($db, [
            'uniqueName'=>$uniqueName,
            'generalName'=>$generalName,
            'imgsvr'=>0,
            'picture'=>null
        ], '9999-12-31 12:00:00');
    }

    static public function pickGeneralFromPool(MeekroDB $db, int $owner, int $pickCnt, ?string $prefix = null): array
    {
        /** @var RandomNameGeneral[] */
        $result = [];
        $dbInsert = [];

        $oNow = new \DateTimeImmutable();
        
        
        for($i=0;$i<$pickCnt;$i++){
            $result[] = static::pickGeneral1FromPool($db, $owner, $prefix);
        }

        if($owner){
            $now = $oNow->format('Y-m-d H:i:s');
            $db->delete('select_pool', [
                'reserved_until'=>null,
                'owner'=>null,
            ],'(reserved_until < %s OR reserved_until IS NULL) AND general_id IS null', $now);
            $validUntil = $oNow->add(new \DateInterval(sprintf('PT%dS', 30)));
            foreach($result as $pickedGeneral){
                $dbInsert[] = [
                    'owner'=>$owner,
                    'uniqueName'=>$pickedGeneral->getUniqueName(),
                    'info'=>$pickedGeneral->getInfo(),
                    'reserved_until'=>$validUntil->format(('Y-m-d H:i:s'))
                ];
            }
            $db->insert('select_pool', $dbInsert);
        }
        return $result;
    }


    public static function initPool(\MeekroDB $db){
        //do Nothing
    }
}