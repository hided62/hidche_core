<?php
namespace sammo\Command\General;

class che_성벽보수 extends che_상업투자{
    static protected $cityKey = 'wall';
    static protected $statKey = 'strength';
    static protected $actionKey = '성벽';
    static protected $actionName = '성벽 보수';
    static protected $debuffFront = 0.25;
}