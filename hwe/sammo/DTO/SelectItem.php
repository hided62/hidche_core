<?php

namespace sammo\DTO;

use LDTO\Attr\Convert;
use LDTO\Converter\MapConverter;

class SelectItem extends \LDTO\DTO
{
	public function __construct(
		public string $title,
		public ?string $info,
		public ?bool $isHtml,
		#[Convert(MapConverter::class, ['string', 'int', 'float', 'array', 'null', 'bool'])]
		public ?array $aux,
	) {
	}
}
