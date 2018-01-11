<?php
require_once('_common.php');
require_once(ROOT.'/f_config/config.php');

class _Setting {
    private $settingFile;
    private $isExist = 0;
    private $dbHost;
    private $dbId;
    private $dbPw;
    private $dbName;
    private $mailHost;
    private $mailPort;
    private $mailId;
    private $mailPw;
    private $mailAddr;

    public function __construct($filename) {
        $this->settingFile = $filename;

        if(file_exists($filename)) {
            $this->isExist = 1;

            $f = @file($filename);
            $this->dbHost = trim(str_replace("\n", "", $f[1]));
            $this->dbId = trim(str_replace("\n", "", $f[2]));
            $this->dbPw = trim(str_replace("\n", "", $f[3]));
            $this->dbName = trim(str_replace("\n", "", $f[4]));
            $this->mailHost = trim(str_replace("\n", "", $f[5]));
            $this->mailPort = trim(str_replace("\n", "", $f[6]));
            $this->mailId = trim(str_replace("\n", "", $f[7]));
            $this->mailPw = trim(str_replace("\n", "", $f[8]));
            $this->mailAddr = trim(str_replace("\n", "", $f[9]));
        }
    }

    public function IsExist() {
        return $this->isExist;
    }

    public function DBHost() {
        return $this->dbHost;
    }

    public function DBId() {
        return $this->dbId;
    }

    public function DBPw() {
        return $this->dbPw;
    }

    public function DBName() {
        return $this->dbName;
    }

    public function MailHost() {
        return $this->mailHost;
    }

    public function MailPort() {
        return $this->mailPort;
    }

    public function MailId() {
        return $this->mailId;
    }

    public function MailPw() {
        return $this->mailPw;
    }

    public function MailAddr() {
        return $this->mailAddr;
    }
}

?>
