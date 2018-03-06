<?php
require_once('_common.php');

class _Setting {
    private $basepath;
    private $settingFile;
    private $exist = 0;

    public function __construct($basepath) {
        $this->basepath = realpath($basepath);
        $this->settingFile = realpath($basepath.D_SETTING.W.'conf.php');

        if(file_exists($this->settingFile)) {
            $this->exist = 1;
        }
    }

    public function isExist() {
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


