<?php
namespace sammo;

class APICacheResult{
    function __construct(
        public ?\DateTimeInterface $lastModified = null,
        public ?string $etag = null,
    )
    {
    }
}