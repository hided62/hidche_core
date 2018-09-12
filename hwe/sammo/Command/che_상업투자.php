<?php
namespace sammo\Command;

use \sammo\{
    Util, JosaUtil,
    General, 
    ActionLogger
};

use \sammo\Constraint\Constraint;


class che_상업투자 extends BaseCommand{
    static $cityKey = 'comm';
    static $actionName = '상업 투자';

    protected function init(){

        $general = $this->generalObj;

        $this->setCity();
        $this->setNation();
        $develCost = $this->env['develcost'];

        $nationTypeObj = $general->getNationTypeObj();
        $generalLevelObj = $general->getGeneralLevelObj();

        $this->constraints=[

        ];
    }

    
}