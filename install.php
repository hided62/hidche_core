<?php
namespace sammo;

set_time_limit(600);

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
            'reason' => $output
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
            'reason' => $output
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
    $command = "./node_modules/.bin/webpack build";

    exec(($command), $output, $result_code);
    if ($result_code != 0) {
        Json::die([
            'result' => false,
            'reason' => $output
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