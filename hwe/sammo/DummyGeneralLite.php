<?php

namespace sammo;

use sammo\Enums\InheritanceKey;

class DummyGeneralLite extends GeneralLite
{
    public function __construct(bool $initLogger = true)
    {
        $raw = [
            'no' => 0,
            'name' => 'Dummy',
            'npc' => 3,
            'city' => 0,
            'nation' => 0,
            'officer_level' => 0,
            'crewtype' => -1,
            'turntime' => '2012-03-04 05:06:07.000000',
            'experience' => 0,
            'dedication' => 0,
            'gold' => 0,
            'rice' => 0,
            'leadership' => 10,
            'strength' => 10,
            'intel' => 10,
            'imgsvr' => 0,
            'picture' => 'default.jpg',
        ];

        $this->raw = $raw;

        if ($initLogger) {
            $this->initLogger(1, 1);
        }
    }

    function applyDB($db): bool
    {
        if ($this->logger) {
            $this->initLogger($this->logger->getYear(), $this->logger->getMonth());
        }
        return true;
    }
}
