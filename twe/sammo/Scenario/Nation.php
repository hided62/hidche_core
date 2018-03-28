<?php
namespace sammo\Scenario;

class Nation{
    private $id;
    private $name;
    private $color;
    private $gold;
    private $rice;
    private $infoText;
    private $tech;
    private $type;
    private $nationLevel;

    private $cities = [];
    private $generals = [];

    public function __construct(
        int $id = null, 
        string $name = '국가', 
        string $color = '#ffffff', 
        int $gold = 0, 
        int $rice = 2000, 
        string $infoText = '국가 설명', 
        int $tech = 0, 
        string $type = '유가', 
        int $nationLevel = 0, 
        array $cities = []
    ){

    }

    public function setID(int $id){
        $this->id = $id;
    }

    public function build($env=[]){

    }
}