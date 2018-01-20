<?php
//임시 땜빵 리팩터링.
require_once "lib.php";

class General{
    protected
        $no, $user_id, $name, $level, $level_exp,
        $leadership, $leadership_exp,
        $power, $power_exp,
        $intel, $intel_exp;
}

class NPCGeneral extends General{
    protected
        $npc_name, $npcmsg;
}