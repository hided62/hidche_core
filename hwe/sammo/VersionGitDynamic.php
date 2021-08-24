<?php

namespace sammo;

class VersionGitDynamic
{
    public static $version = '_tK_verionGit_';

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

    static function __get($name){
        if($name === 'version'){
            return static::getVersion();
        }
        if($name === 'hash'){
            return static::getHash();
        }
        trigger_error("Undefined property $name");
    }

    private function __construct()
    {
    }
}
//{"version":"_tK_verionGit_"}
