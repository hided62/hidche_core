<?php
require_once('_common.php');

class _Setting {
    private $basepath;
    private $settingFile;
    private $exist = false;

    public function __construct($basepath) {
        $this->basepath = $basepath;
        $this->settingFile = realpath($basepath.'/d_setting/conf.php');

        if(file_exists($this->settingFile)) {
            $this->exist = true;
        }
    }

    public function isExists() {
        return $this->exist;
    }

    public function getShortName(){
        return basename($this->basepath);
    }

    public function getBasePath(){
        return $this->basepath;
    }

    public function getSettingFile() {
        return $this->settingFile;
    }
}


