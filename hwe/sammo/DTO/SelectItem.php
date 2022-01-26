<?php

namespace Sammo\DTO;

use Spatie\DataTransferObject\Attributes\Strict;
use Spatie\DataTransferObject\DataTransferObject;

#[Strict]
class SelectItem extends DataTransferObject
{
	public string $title;
	public ?string $info;
	public ?bool $isHtml;
	public ?array $aux;
}
