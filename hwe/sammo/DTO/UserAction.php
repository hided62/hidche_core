<?php

namespace sammo\DTO;

use LDTO\Attr\Convert;
use LDTO\Converter\ArrayConverter;
use LDTO\Converter\MapConverter;
use LDTO\DTO;


class UserAction extends \LDTO\DTO
{
    /**
     * @param UserActionItem[] $active
     * @param Array<int,UserActionItem> $reserved
     * */
	public function __construct(

		#[Convert(MapConverter::class, [UserActionItem::class])]
		public ?array $reserved,
        #[Convert(ArrayConverter::class, [UserActionItem::class])]
        public ?array $active,
	) {
	}
}
