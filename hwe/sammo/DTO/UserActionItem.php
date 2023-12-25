<?php

namespace sammo\DTO;

class UserActionItem extends \LDTO\DTO {
    public function __construct(
        public string $command,
        public string $brief,
        #[\LDTO\Attr\NullIsUndefined]
        public ?int $untilYearMonth,
    ) {
    }
}