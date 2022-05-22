<?php

namespace sammo\DTO;

use sammo\DTO\Attr\Convert;
use sammo\DTO\Converter\MapConverter;

class SelectItem extends DTO
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
