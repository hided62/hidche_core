<?php

namespace sammo;

class VersionGitDynamic
{
    static function getVersion()
    {
        $versionTokens = [];

        $command = 'git describe --long --tags';
        exec($command, $output);
        if (is_array($output)) {
            $versionTokens[] = trim(join('', $output));
        }

        $command = 'git branch --contains HEAD';
        exec($command, $output);
        if (is_array($output)) {
            if (count($output)) {
                $output = $output[1];
                $versionTokens[] = trim($output, " \t\n\r\0\x0b*");
            } else {
                $versionTokens[] = 'unknown';
            }
        }

        return join('-', $versionTokens);
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
