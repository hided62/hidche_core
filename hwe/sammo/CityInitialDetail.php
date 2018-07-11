<?php
namespace sammo;

class CityInitialDetail{
    public $id;
    public $name;
    public $level;
    public $population;
    public $agriculture;
    public $commerce;
    public $security;
    public $defence;
    public $wall;
    public $region;
    public $posX;
    public $posY;
    public $path;

    public function __construct(
        int $id,
        string $name,
        int $level,
        int $population,
        int $agriculture,
        int $commerce,
        int $security,
        int $defence,
        int $wall,
        int $region,
        int $posX,
        int $posY,
        array $path
    ){
        $this->id = $id;
        $this->name = $name;
        $this->level = $level;
        $this->population = $population;
        $this->agriculture = $agriculture;
        $this->commerce = $commerce;
        $this->security = $security;
        $this->defence = $defence;
        $this->wall = $wall;
        $this->region = $region;
        $this->posX = $posX;
        $this->posY = $posY;
        $this->path = $path;
    }

}