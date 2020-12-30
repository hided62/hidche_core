<?php
namespace sammo;

use sammo\AbsGeneralPool;
use sammo\GameConst;
use sammo\Json;
use sammo\Util;

abstract class AbsFromUserPool extends AbsGeneralPool{
    static $valid_seconds = 3600*24;

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
        ], 'unique_name = %s AND owner IS NOT NULL', $this->uniqueName);
        return $db->affectedRows()!=0;
    }

    static public function pickGeneralFromPool(\MeekroDB $db, int $owner, int $pickCnt, ?string $prefix=null):array{
        $oNow = new \DateTimeImmutable();
        $now = $oNow->format('Y-m-d H:i:s');

        $db->update('select_pool', [
            'reserved_until'=>null,
            'owner'=>null,
        ],'reserved_until < %s AND general_id IS NULL', $now);

        $pool = [];
        foreach($db->query('SELECT id, unique_name, info FROM select_pool WHERE reserved_until IS NULL AND general_id IS NULL', $pickCnt) as $cand){
            $cand['info'] = Json::decode($cand['info']);
            $dexTotal = array_sum($cand['info']['dex']);
            $pool[] = [$cand, $dexTotal];
        }

        if(count($pool) < $pickCnt){
            throw new \RuntimeException('pool 부족');
        }

        $result = [];
        $validUntil = $oNow->add(new \DateInterval(sprintf('PT%dS', static::$valid_seconds)));
        $validUntilText = $validUntil->format('Y-m-d H:i:s');
        while(count($result) < $pickCnt){
            $cand = Util::choiceRandomUsingWeightPair($pool);
            $poolID = $cand['id'];
            if(key_exists($poolID, $result)){
                continue;
            }
            $candInfo = $cand['info'];
            $candInfo['uniqueName'] = $cand['unique_name'];

            //하나씩 한다.
            $db->update('select_pool', [
                'owner'=>$owner,
                'reserved_until'=>$validUntilText,
            ], 'id = %i AND reserved_until IS NULL AND owner IS NULL and general_id IS NULL', $poolID);
            if($db->affectedRows()==0){
                continue;
            }
            $result[$poolID] = new static($db, $candInfo, $validUntilText);
        }

        return array_values($result);
    }
}