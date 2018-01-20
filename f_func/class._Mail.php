<?php
require_once('_common.php');
require_once(__dir__.'/../d_setting/conf.php');

class _Mail {
    private $objMail;

    public function __construct() {
        $this->objMail = newMailObj();
        $this->objMail->ContentType = 'text/plain';
        $this->objMail->CharSet = 'utf-8';
        $this->objMail->Encoding = 'base64';
    }

    public function Send($to, $subject, $content) {
        $this->objMail->AddAddress($to);
        $this->objMail->Subject = '=?utf-8?b?'.base64_encode($subject).'?=';
        $this->objMail->Body = $content;

        if(!$this->objMail->Send()) {
            $result['msg'] = $this->objMail->ErrorInfo;
            $result['result'] = 1;
        } else {
            $result['msg'] = 'Successfully sent.';
            $result['result'] = 0;
        }

        return $result;
    }
}


