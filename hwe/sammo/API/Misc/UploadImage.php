<?php

namespace sammo\API\Misc;

use sammo\Session;
use DateTimeInterface;
use sammo\AppConf;
use sammo\KVStorage;
use sammo\RootDB;
use sammo\TimeUtil;
use sammo\UniqueConst;
use sammo\Validator;

class UploadImage extends \sammo\BaseAPI
{
    public function validateArgs(): ?string
    {
        $v = new Validator($this->args);
        $v->rule('required', [
            'imageData',
        ]);

        if (!$v->validate()) {
            return $v->errorStr();
        }
        return null;
    }

    public function getRequiredSessionMode(): int
    {
        return static::REQ_GAME_LOGIN | static::REQ_READ_ONLY;
    }

    public function launch(Session $session, ?DateTimeInterface $modifiedSince, ?string $reqEtag)
    {
        $imageData = base64_decode($this->args['imageData'], true);
        if ($imageData === false) {
            return "올바른 데이터가 아닙니다.";
        }

        if(strlen($imageData) > 1024*1024){
            //NOTE: 가변 길이를 적용해야할까?
            return "이미지 크기가 1MB보다 큽니다.";
        }

        $contentType = (new \finfo(FILEINFO_MIME_TYPE))->buffer($imageData);
        if (substr($contentType, 0, 5) !== 'image') {
            return '이미지 파일이 아닙니다: ' . $contentType;
        }

        $extension = ltrim($contentType, 'image/');
        $validExtensions = ['png', 'jpeg', 'jpg', 'gif', 'webp'];
        if (!in_array(strtolower($extension), $validExtensions)) {
            return '지원하지 않는 이미지 파일입니다: ' . $contentType;
        }

        $oMD = hash_init('md5');
        hash_update($oMD, $imageData);
        $imgName = hash_final($oMD);
        $imgFullName = "{$imgName}.{$extension}";

        $destDir = AppConf::getUserIconPathFS() . '/uploaded_image';
        $destPath = "{$destDir}/{$imgFullName}";

        if (!file_exists($destPath)) {
            if (!file_exists($destDir)) {
                mkdir($destDir);
            }
            if (!is_dir($destDir)) {
                return '버그! 업로드 경로 확인!';
            }
            if (!is_writable($destDir)) {
                return '버그! 업로드 권한 확인!';
            }

            if (!file_put_contents($destPath, $imageData)) {
                return '업로드에 실패했습니다!';
            }
        }

        $db = RootDB::db();
        $imgStor = KVStorage::getStorage($db, 'img_storage');


        $userID = $session->userID;
        $serverID = UniqueConst::$serverID;

        $storedStatus = $imgStor->$imgFullName ?? [];
        $imgKey = "$serverID:$userID";
        if (!key_exists($imgKey, $storedStatus)) {
            $storedStatus[$imgKey] = TimeUtil::now();
        }

        $imgStor->$imgFullName = $storedStatus;

        return [
            'result' => true,
            'path'=>AppConf::getUserIconPathWeb().'/uploaded_image/'.$imgFullName,
        ];
    }
}
