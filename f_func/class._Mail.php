<?php
require_once('_common.php');
require_once(ROOT.W.E_LIB.W.'phpmailer5/class.phpmailer.php');
require_once(ROOT.W.E_LIB.W.'phpmailer5/class.smtp.php');

class _Mail {
    private $objMail;

    public function __construct($host, $port, $id, $pw, $addr) {
        $this->objMail = new PHPMailer();
        $this->objMail->IsSMTP();
        $this->objMail->SMTPAuth = false; 
        //$this->objMail->SMTPSecure = 'ssl';//TODO 제대로된 Mailer 옵션 설정
        $this->objMail->Host = $host;
        $this->objMail->Port = $port;
        $this->objMail->Username = $id;
        $this->objMail->Password = $pw;
        $this->objMail->ContentType = 'text/plain';
        $this->objMail->CharSet = 'utf-8';
        $this->objMail->Encoding = 'base64';
        $this->objMail->SetFrom($addr);
        $this->objMail->SMTPOptions = array (
            'ssl' => array(
                'verify_peer'  => false,
                'allow_self_signed' => true
            )
        );
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


