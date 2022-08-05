<?php

namespace sammo\DTO;

class VoteInfo extends \LDTO\DTO
{
  public function __construct(
    public int $id,
    public string $title,
    public int $multipleOptions,

    public string $startDate,
    public ?string $endDate,

    /** @var string[] */
    public array $options,
  ) {
  }
}
