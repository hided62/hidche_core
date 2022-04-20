<?php
namespace sammo;

class APICacheResult{
    function __construct(
        public ?\DateTimeInterface $lastModified = null,
        public ?string $etag = null,
        public int $validSeconds = 60,
        public bool $isPublic = false,
    )
    {
    }
}