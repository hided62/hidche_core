<?php
namespace sammo\GeneralPool;

use MeekroDB;
use sammo\AbsGeneralPool;
use sammo\GameConst;
use sammo\RandUtil;
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

    static protected function pickGeneral1FromPool(\MeekroDB $db, RandUtil $rng, int $owner, ?string $prefix=null):self{

        $loopCnt = 0;
        while(true){

            $firstname = $rng->choice(GameConst::$randGenFirstName);
            $middlename = $rng->choice(GameConst::$randGenMiddleName);
            $lastname = $rng->choice(GameConst::$randGenLastName);

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

        return new static($db, $rng, [
            'uniqueName'=>$uniqueName,
            'generalName'=>$generalName,
            'imgsvr'=>0,
            'picture'=>null
        ], '9999-12-31 12:00:00');
    }

    static public function pickGeneralFromPool(MeekroDB $db, RandUtil $rng, int $owner, int $pickCnt, ?string $prefix = null): array
    {
        /** @var RandomNameGeneral[] */
        $result = [];
        $dbInsert = [];

        $oNow = new \DateTimeImmutable();


        for($i=0;$i<$pickCnt;$i++){
            $result[] = static::pickGeneral1FromPool($db, $rng, $owner, $prefix);
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