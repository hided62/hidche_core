<?php

namespace sammo;

abstract class BaseAPI
{
    const NO_SESSION = 0;
    const REQ_LOGIN = 1;
    const REQ_GAME_LOGIN = 2;
    const REQ_READ_ONLY = 4;

    protected array $args;
    protected string $rootPath;
    public function __construct(string $rootPath, array $args)
    {
        $this->rootPath = $rootPath;
        $this->args = $args;
    }
    abstract public function getRequiredSessionMode(): int;
    abstract function validateArgs(): ?string;

    /** @return null|string|array */
    abstract function launch(?Session $session, ?\DateTimeInterface $modifiedSince, ?string $reqEtag);

    public function tryCache():?string{
        return null;
    }
}
