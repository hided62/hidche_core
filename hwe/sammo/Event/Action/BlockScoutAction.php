<?php
namespace sammo\Event\Action;
use sammo\Util;
use sammo\DB;
use sammo\KVStorage;

class BlockScoutAction extends \sammo\Event\Action{
    public function __construct(private ?bool $blockChangeScout = null){

    }

    public function run(array $env){
        $db = DB::db();
        $db->update('nation', [
            'scout'=>1
        ], true);
        if($this->blockChangeScout !== null){
            $gameStor = KVStorage::getStorage($db, 'game_env');
            $gameStor->setValue('block_change_scout', $this->blockChangeScout);
        }

        return [__CLASS__, $db->affectedRows()];
    }

}