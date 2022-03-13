<?php
namespace sammo;

set_time_limit(600);


class Json
{
    /** @return never */
    public static function die($value)
    {
        if (!headers_sent()) {
            header('Expires: Wed, 01 Jan 2014 00:00:00 GMT');
            header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
            header('Cache-Control: no-store, no-cache, must-revalidate');
            header('Cache-Control: post-check=0, pre-check=0', false);
            header('Pragma: no-cache');
        }

        header('Content-Type: application/json');
        die(json_encode($value));
    }
}

function tryComposerInstall()
{
    $resultPath = 'composer_result.json.log';
    $lockPath = 'composer.lock';
    $runCode = "php composer.phar install";

    $timestamp = time();
    if (file_exists($resultPath) && file_exists($lockPath)) {
        do {
            $result = json_decode(file_get_contents($resultPath));
            $oldJsonHash = $result->lockHash;
            $lockHash = hash_file('sha512', $lockPath);

            if ($lockHash != $oldJsonHash) {
                break;
            }

            //그것도 아니라면 업데이트하지 않겠다.
            return false;
        } while (0);
    }

    exec($runCode, $output, $result_code);
    if ($result_code != 0) {
        Json::die([
            'result' => false,
            'reason' => $output,
            'state' => 'composer',
        ]);
    }

    if (!file_exists($lockPath)){
        Json::die([
            'result' => false,
            'reason' => "no lockfile: {$lockPath}"
        ]);
    }
    $lockHash = hash_file('sha512', $lockPath);


    file_put_contents($resultPath, json_encode([
        'lockHash'=>$lockHash,
        'updateTimestamp'=>$timestamp,
    ]));
    return true;
}


function tryNpmInstall()
{
    $resultPath = 'npm_recent.json.log';
    $lockPath = 'package-lock.json';
    $runCode = "npm ci";
    $runAltCode = "npm install";

    $timestamp = time();
    if (file_exists($resultPath) && file_exists($lockPath)) {
        do {
            $result = json_decode(file_get_contents($resultPath));
            $oldJsonHash = $result->lockHash;
            $lockHash = hash_file('sha512', $lockPath);

            if ($lockHash != $oldJsonHash) {
                break;
            }

            //그것도 아니라면 업데이트하지 않겠다.
            return false;
        } while (0);
    }

    if(file_exists($lockPath)){
        exec($runCode, $output, $result_code);
    }
    else{
        exec($runAltCode, $output, $result_code);
    }

    if ($result_code != 0) {
        Json::die([
            'result' => false,
            'reason' => $output,
            'state' => 'npm ci',
        ]);
    }

    if (!file_exists($lockPath)){
        Json::die([
            'result' => false,
            'reason' => "no lockfile: {$lockPath}"
        ]);
    }
    $lockHash = hash_file('sha512', $lockPath);


    file_put_contents($resultPath, json_encode([
        'lockHash'=>$lockHash,
        'updateTimestamp'=>$timestamp,
    ]));
    return true;
}

function genJS()
{
    $command = sprintf("./node_modules/.bin/webpack build --env target=gateway");

    exec(($command), $output, $result_code);
    if ($result_code != 0) {
        Json::die([
            'result' => false,
            'reason' => $output,
            'state' => 'webpack build',
        ]);
    }
}

if(tryComposerInstall()){
    header('location:install.php');
    die();
}

if(tryNpmInstall()){
    genJS();
}

header('location:f_install/install.php');