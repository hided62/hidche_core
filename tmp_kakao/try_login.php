<?php

require('_common.php');
require('kakao.php');




// test code

$helper = new Kakao_REST_API_Helper('');
$helper->set_admin_key('');

$helper->test_user_management_api();
//$helper->test_story_api();
//$helper->test_talk_api();
//$helper->test_push_notification_api();
