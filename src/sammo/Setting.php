<?php
namespace sammo;

class Setting {
    private $basepath;
    private $settingFile;
    private $htaccessFile;
    private $exist = false;
    private $running = false;

    private $shortName;
    private $korName;
    private $color;

    public function __construct(string $basepath, string $korName, string $color, string $name=null) {
        $this->basepath = $basepath;
        $this->settingFile = realpath($basepath.'/d_setting/DB.php');
        $this->htaccessFile = realpath($basepath.'/.htaccess');

        $this->korName = $korName;
        $this->color = $color;
        if($name){
            $this->shortName = $name;
        }
        else{
            $this->shortName = basename($this->basepath);
        }

        if(file_exists($this->settingFile)) {
            $this->exist = true;

            if(!file_exists($this->htaccessFile)){
                $this->running = true;
            }
        }
    }

    public function isRunning(){
        return $this->running;
    }

    public function isExists() {
        return $this->exist;
    }

    public function getShortName(){
        return $this->shortName;
    }

    public function getColor(){
        return $this->color;
    }

    public function getKorName(){
        return $this->korName;
    }

    public function getBasePath(){
        return $this->basepath;
    }

    public function getSettingFile() {
        return $this->settingFile;
    }
}


