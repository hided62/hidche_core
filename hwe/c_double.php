<?php
namespace sammo;

include "lib.php";
include "func.php";
// $turn, $command, $cost, $name, $nationname, $note, $double, $third, $fourth
$turn = Util::getReq('turn', 'array_int');
$command = Util::getReq('command', 'int', 0);
$cost = Util::getReq('cost', 'int');
$name = Util::getReq('name');
$nationname = Util::getReq('nationname', 'string', '');
$note = Util::getReq('note', 'string', '');
$double = Util::getReq('double', 'int', 0);
$third = Util::getReq('third', 'int', 0);
$fourth = Util::getReq('fourth', 'int', 0);

if(!$turn){
    $turn = [0];
}

'@phan-var int $double';
'@phan-var int $third';
'@phan-var int $fourth';

//로그인 검사
$session = Session::requireGameLogin()->setReadOnly();
$userID = Session::getUserID();

//TODO: 삭제. 새로 짜
throw new \sammo\NotImplementedException();

$db = DB::db();

if($command < 0) { $command = 0; }
if($double < 0) { $double = 0; }
if($third < 0)  { $third = 0; }
if($fourth < 0) { $fourth = 0; }
if($command > 99) { $command = 0; }
if($double > 9999) { $double = 9999; }
if($fourth > 9999) { $fourth = 9999; }

// 건국
if($command == 46) {
    die();

}

//통합제의
if($command == 53) {
    header('location:b_chiefcenter.php');
    die();
}

//불가침
if($command == 61) {
    header('location:b_chiefcenter.php');
    die();
} 

//포상, 몰수, 발령, 항복권고, 원조
//선전포고, 종전, 파기, 초토화, 천도, 증축, 감축
//백성동원, 수몰, 허보, 피장파장, 의병모집, 이호경식, 급습
//국기변경
if($command == 23 || $command == 24 || $command == 27 || $command == 51 || $command == 52 || $command > 60) {
    header('location:b_chiefcenter.php');
    die();  
}

//일반 턴
header('Location:./');

