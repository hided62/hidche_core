<?php

namespace sammo;

use sammo\Enums\APIRecoveryType;

abstract class BaseAPI
{
    const NO_SESSION = -1;
    const NO_LOGIN = 0;
    const REQ_LOGIN = 1;
    const REQ_GAME_LOGIN = 2;
    const REQ_READ_ONLY = 4;

    static array $sensitiveArgs = [];
    static bool $allowExternalAPI = true;

    public function getFilteredArgs(): array
    {
        $filteredArgs = $this->args;
        foreach (static::$sensitiveArgs as $argName) {
            if (isset($filteredArgs[$argName])) {
                $filteredArgs[$argName] = '***';
            }
        }
        return $filteredArgs;
    }

    protected array $args;
    protected string $rootPath;
    public function __construct(string $rootPath, array $args)
    {
        $this->rootPath = $rootPath;
        $this->args = $args;
    }
    abstract public function getRequiredSessionMode(): int;
    abstract function validateArgs(): ?string;

    abstract function launch(Session $session, ?\DateTimeInterface $modifiedSince, ?string $reqEtag): null | string | array | APIRecoveryType;

    public function tryCache(): ?APICacheResult
    {
        return null;
    }
}
