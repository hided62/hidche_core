<?php
namespace sammo\VO;
class InheritancePointType{
  public function __construct(
    public bool|array $storeType,
    public int|float $pointCoeff,
    public string $info,
    public ?float $rebirthStoreCoeff,
  )
  {

  }

}