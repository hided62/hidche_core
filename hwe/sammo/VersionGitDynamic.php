<?php

namespace sammo;

class VersionGitDynamic
{
    static function getVersion()
    {
        $command = 'git describe --long --tags';
        exec($command, $output);
        if (is_array($output)) {
            $output = join('', $output);
        }
        return trim($output);
    }

    static function getHash()
    {
        $command = 'git rev-parse HEAD';
        exec($command, $output);
        if (is_array($output)) {
            $output = join('', $output);
        }
        return trim($output);
    }

    private function __construct()
    {
    }
}
