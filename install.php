<?php
namespace sammo;

set_time_limit(600);

function tryNpmInstall()
{
    $npmResultPath = '../npm_recent.json.log';
    $packageJsonPath = '../package.json';
    $packageJsonLockPath = '../package-lock.json';

    $packageJsonHash = hash_file('sha512', $packageJsonPath);
    $timestamp = time();
    if (file_exists($npmResultPath) && file_exists($packageJsonLockPath)) {
        do {
            $result = json_decode(file_get_contents($npmResultPath));
            $oldJsonHash = $result->packageJsonHash;
            $oldTimestamp = $result->updateTimestamp;

            //1. package.json 파일이 다르면 업데이트.
            if ($packageJsonHash != $oldJsonHash) {
                break;
            }

            //2. package-lock.json 업데이트가 2주를 초과했다면 업데이트.
            if($oldTimestamp + 60*60*24*14 < $timestamp){
                break;
            }

            //그것도 아니라면 업데이트하지 않겠다.
            return false;
        } while (0);
    }

    exec("npm install", $output, $result_code);
    if ($result_code != 0) {
        Json::die([
            'result' => false,
            'reason' => $output
        ]);
    }

    file_put_contents($npmResultPath, json_encode([
        'packageJsonHash'=>$packageJsonHash,
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
if(tryNpmInstall()){
    genJS();
}

header('location:f_install/install.php');