<?php
namespace sammo;

class APICacheResult{
    function __construct(
        public ?\DateTimeInterface $lastModified,
        public ?string $etag,
    )
    {

    }
}