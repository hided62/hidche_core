<?php
namespace sammo\GeneralTrigger;
use sammo\BaseGeneralTrigger;
use sammo\General;
use sammo\ActionLogger;
use sammo\DB;
use sammo\Util;
use sammo\JosaUtil;

class che_부상경감 extends BaseGeneralTrigger{
    protected $priority = 30010;

    public function action(?array $env=null, $arg=null):?array{

        /** @var \sammo\General $general */
        $general = $this->object;

        if($general->getVar('injury') && !$general->hasActivatedSkill('pre.부상경감')){
            $general->increaseVarWithLimit('injury', -10, 0);
            $general->activateSkill('pre.부상경감');
        }

        return $env;
    }
}