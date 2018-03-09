<?php
require(__DIR__.'/conf.php');
//https://devtalk.kakao.com/t/php-rest-api/14602/3
//header('Content-Type: application/json; charset=utf-8');

define('GET', 'GET');
define('POST', 'POST');
define('DELETE', 'DELETE');


///////////////////////////////////////////////////////////////////////////////



///////////////////////////////////////////////////////////////////////////////


class User_Management_Path
{
  public static $TOKEN          = "/oauth/token";
  public static $SIGNUP         = "/v1/user/signup";
  public static $UNLINK         = "/v1/user/unlink";
  public static $LOGOUT         = "/v1/user/logout";
  public static $ME             = "/v1/user/me";
  public static $UPDATE_PROFILE = "/v1/user/update_profile";
  public static $USER_IDS       = "/v1/user/ids";
}

class Story_Path
{
  public static $PROFILE        = "/v1/api/story/profile";
  public static $ISSTORYUSER    = "/v1/api/story/isstoryuser";
  public static $MYSTORIES      = "/v1/api/story/mystories";
  public static $MYSTORY        = "/v1/api/story/mystory";
  public static $DELETE_MYSTORY = "/v1/api/story/delete/mystory";
  public static $POST_NOTE      = "/v1/api/story/post/note";
  public static $UPLOAD_MULTI   = "/v1/api/story/upload/multi";
  public static $POST_PHOTO     = "/v1/api/story/post/photo";
  public static $LINKINFO       = "/v1/api/story/linkinfo";
  public static $POST_LINK      = "/v1/api/story/post/link";
}

class Talk_Path
{
  public static $TALK_PROFILE= "/v1/api/talk/profile";
  public static $TALK_TO_ME  = "/v2/api/talk/memo/send";
}

class Push_Notification_Path
{
  public static $REGISTER   = "/v1/push/register";
  public static $TOKENS     = "/v1/push/tokens";
  public static $DEREGISTER = "/v1/push/deregister";
  public static $SEND       = "/v1/push/send";
}


class Kakao_REST_API_Helper
{
  public static $OAUTH_HOST = "https://kauth.kakao.com";
  public static $API_HOST = "https://kapi.kakao.com";

  private static $admin_apis;

  private $access_token;
  private $admin_key;

  public function __construct($access_token = '') {

    if ($access_token) {
      $this->access_token = $access_token;
    }

    self::$admin_apis = array(
      User_Management_Path::$USER_IDS,
      Push_Notification_Path::$REGISTER,
      Push_Notification_Path::$TOKENS,
      Push_Notification_Path::$DEREGISTER,
      Push_Notification_Path::$SEND
    );
  }

  public function request($api_path, $params = '', $http_method = GET)
  {
    if ($api_path != Story_Path::$UPLOAD_MULTI && is_array($params)) { // except for uploading
      $params = http_build_query($params);
    }

    $requestUrl = ($api_path == '/oauth/token' ? self::$OAUTH_HOST : self::$API_HOST) . $api_path;

    if (($http_method == GET || $http_method == DELETE) && !empty($params)) {
      $requestUrl .= '?'.$params;
    }

    $opts = array(
      CURLOPT_URL => $requestUrl,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_SSL_VERIFYPEER => false,
      CURLOPT_SSLVERSION => 1,
    );

    if ($api_path != '/oauth/token')
    {
      if (in_array($api_path, self::$admin_apis)) {

        if (!$this->admin_key) {
          throw new Exception('admin key should not be null or empty.');
        }
        $headers = array('Authorization: KakaoAK ' . $this->admin_key);

      } else {

        if (!$this->access_token) {
          throw new Exception('access token should not be null or empty.');
        }
        $headers = array('Authorization: Bearer ' . $this->access_token);
      }

      $opts[CURLOPT_HEADER] = false;
      $opts[CURLOPT_HTTPHEADER] = $headers;
    }

    if ($http_method == POST) {
      $opts[CURLOPT_POST] = true;
      if ($params) {
        $opts[CURLOPT_POSTFIELDS] = $params;
      }
    } else if ($http_method == DELETE) {
      $opts[CURLOPT_CUSTOMREQUEST] = DELETE;
    }

    $curl_session = curl_init();
    curl_setopt_array($curl_session, $opts);
    $return_data = curl_exec($curl_session);

    if (curl_errno($curl_session)) {
      throw new Exception(curl_error($curl_session));
    } else {
      // 디버깅 시에 주석을 풀고 응답 내용 확인할 때
      //print_r(curl_getinfo($curl_session));
      curl_close($curl_session);
      return json_decode($return_data, true);
    }
  }

  public function set_access_token($access_token) {
    $this->access_token = $access_token;
  }

  public function set_admin_key($admin_key) {
    $this->admin_key = $admin_key;
  }

  ///////////////////////////////////////////////////////////////
  // User Management
  ///////////////////////////////////////////////////////////////

  private function _create_or_refresh_access_token($params) {
    return $this->request(User_Management_Path::$TOKEN, $params, POST);
  }

  public function create_access_token($authorization_code){
    $this->AUTHORIZATION_CODE = $authorization_code;
    $params = [
      'grant_type'=>'authorization_code',
      'client_id'=>$this->REST_KEY,
      'redirect_uri'=>$this->REDIRECT_URI,
      'code'=>$this->AUTHORIZATION_CODE
    ];
    $result = $this->_create_or_refresh_access_token($params);
    
    return $result;
  }

  public function refresh_access_token($refresh_token){
    $params = [
      'grant_type'=>'refresh_token',
      'client_id'=>$this->REST_KEY,
      'redirect_uri'=>$this->REDIRECT_URI,
      'code'=>$refresh_token
    ];
    $result = $this->_create_or_refresh_access_token($params);
    
    return $result;
  }

  public function signup() {
    return $this->request(User_Management_Path::$SIGNUP);
  }

  public function unlink() {
    return $this->request(User_Management_Path::$UNLINK);
  }

  public function logout() {
    return $this->request(User_Management_Path::$UNLINK);
  }

  public function me() {
    return $this->request(User_Management_Path::$ME);
  }

  public function meWithEmail(){
    $params = [
      'propertyKeys'=>'["id","kaacount_email","kaccount_email_verified"]'
    ];
    return $this->request(User_Management_Path::$ME);
  }

  public function update_profile($params) {
    return $this->request(User_Management_Path::$UPDATE_PROFILE, $params, POST);
  }

  public function user_ids() {
    return $this->request(User_Management_Path::$USER_IDS);
  }

  ///////////////////////////////////////////////////////////////
  // Kakao Story
  ///////////////////////////////////////////////////////////////

  public function isstoryuser() {
    return $this->request(Story_Path::$ISSTORYUSER);
  }

  public function story_profile() {
    return $this->request(Story_Path::$PROFILE);
  }

  ///////////////////////////////////////////////////////////////
  // Kakao Talk
  ///////////////////////////////////////////////////////////////

  public function talk_profile() {
    return $this->request(Talk_Path::$TALK_PROFILE);
  }


  ///////////////////////////////////////////////////////////////
  // API Test
  ///////////////////////////////////////////////////////////////


  private $REST_KEY = KakaoKey::REST_KEY;  // 디벨로퍼스의 앱 설정에서 확인할 수 있습니다.
  private $REDIRECT_URI = KakaoKey::REDIRECT_URI; // 설정에 등록한 사이트 도메인 + redirect uri
  private $AUTHORIZATION_CODE = ''; // 동의를 한 후 발급되는 code
  private $REFRESH_TOKEN = '';

  /*
   * 유저 관리 API 테스트
   */
  public function test_user_management_api()
  {

/*
    // authorization code로 access token 얻기
    $params = array();
    $params['grant_type']    = 'authorization_code';
    $params['client_id']     = $this->REST_KEY;
    $params['redirect_uri']  = $this->REDIRECT_URI;
    $params['code']          = $this->AUTHORIZATION_CODE;
    $this->create_or_refresh_access_token($params);
*/

/*
    // refresh token으로 access token 얻기
    $params = array();
    $params['grant_type']    = 'refresh_token';
    $params['client_id']     = $this->REST_KEY;
    $params['refresh_token'] = $this->REFRESH_TOKEN;
    echo $this->create_or_refresh_access_token($params);
*/

/*
    // 앱 사용자 정보 요청 (signup 후에 사용 가능)
    echo $this->me();
*/

/*
    // 앱 연결
    echo $this->signup();
*/

/*
    // 앱 탈퇴 (unlink를 하면 access/refresh token이 삭제됩니다.)
    //echo $this->unlink();
*/

/*
    // 앱 로그아웃 (로그아웃을 하면 access/refresh token이 삭제됩니다.)
    echo $this->logout();
*/

/*
    // 앱 사용자 정보 업데이트
    $params = array();
    $params['properties'] = '{"nickname":"test11"}';
    echo $this->updateProfile($params);
    echo $this->me();
*/

/*
    // 앱 사용자 리스트 요청 (파라미터)
    // 테스트하시려면 admin key 지정해야 합니다.
    echo $this->user_ids();
*/

  }

  /*
   * 카카오스토리 API 테스트
   */
  public function test_story_api()
  {

/*
    // 스토리 프로파일 요청
    echo $this->story_profile();
*/

/*
    // 스토리 유저인지 확인
    //echo $this->isstoryuser();
*/

/*
    $story_common_params = array();

    // 글 포스팅이면 필수
    $story_common_params['content'] = '더 나은 세상을 꿈꾸고 그것을 현실로 만드는 이를 위하여 카카오에서 앱 개발 플랫폼 서비스를 시작합니다.';

    // 스토리 포스팅 공통 파라미터. 필요한 것만 선택하여 사용.
    //$story_common_params['permission'] = 'A'; // A : 전체공개, F: 친구에게만 공개, M: 나만보기
    //$story_common_params['enable_share'] = 'true'; // 공개 기능 허용 여부
    //$story_common_params['android_exec_param'] = 'cafe_id=1234'; // 앱 이동시 추가 파라미터
    //$story_common_params['ios_exec_param'] = 'cafe_id=1234';
    //$story_common_params['android_market_param'] = 'cafe_id=1234';
    //$story_common_params['ios_market_param'] = 'cafe_id=1234';

    //$res = $helper->post_note($story_common_params);
    //echo $res;
    //$obj = json_decode($res);
    //this->delete_mystory($obj->id); // 포스팅된 스토리 삭제.
*/

/*
    // 스토리 포스팅 공통 파라미터. 필요한 것만 선택하여 사용.
    $story_common_params = array();
    //$story_common_params['content'] = '더 나은 세상을 꿈꾸고 그것을 현실로 만드는 이를 위하여 카카오에서 앱 개발 플랫폼 서비스를 시작합니다.';
    //$story_common_params['permission'] = 'A'; // A : 전체공개, F: 친구에게만 공개, M: 나만보기
    //$story_common_params['enable_share'] = 'true'; // 공개 기능 허용 여부
    //$story_common_params['android_exec_param'] = 'cafe_id=1234'; // 앱 이동시 추가 파라미터
    //$story_common_params['ios_exec_param'] = 'cafe_id=1234';
    //$story_common_params['android_market_param'] = 'cafe_id=1234';
    //$story_common_params['ios_market_param'] = 'cafe_id=1234';

    // 링크 포스팅
    $test_site_url = 'https://developers.kakao.com';
    $res = $this->post_link($test_site_url, $story_common_params);
    echo $res;
    $obj = json_decode($res);
    //$this->delete_mystory($obj->id); // 포스팅된 테스트 스토리 삭제.
*/

/*
    // 스토리 포스팅 공통 파라미터. 필요한 것만 선택하여 사용.
    $story_common_params = array();
    $story_common_params['content'] = '더 나은 세상을 꿈꾸고 그것을 현실로 만드는 이를 위하여 카카오에서 앱 개발 플랫폼 서비스를 시작합니다.';
    //$story_common_params['permission'] = 'A'; // A : 전체공개, F: 친구에게만 공개, M: 나만보기
    //$story_common_params['enable_share'] = 'true'; // 공개 기능 허용 여부
    //$story_common_params['android_exec_param'] = 'cafe_id=1234'; // 앱 이동시 추가 파라미터
    //$story_common_params['ios_exec_param'] = 'cafe_id=1234';
    //$story_common_params['android_market_param'] = 'cafe_id=1234';
    //$story_common_params['ios_market_param'] = 'cafe_id=1234';

    // 사진 포스팅 (최대 10개까지 가능)
    $file_params = array(
      'file[0]'=>"@/Users/tom/sample1.png",
      'file[1]'=>"@/Users/tom/sample2.png"
    );

    // PHP 5 >= 5.5.0
    $file_params = array(
      'file[0]'=>new CurlFile('/Users/tom/sample1.png','image/png','sample1'),
      'file[1]'=>new CurlFile('/Users/tom/sample2.png','image/png','sample2')
    );

    $res = $this->post_photo($file_params, $story_common_params);
    echo $res;
    $obj = json_decode($res);
    $this->delete_mystory($obj->id); // 포스팅된 테스트 스토리 삭제.
*/

/*
    $test_mystory_id = '_cDLHO.GBNzGysmIZ9';

    // 복수개의 내스토리 정보 요청
    echo $this->get_mystories();

    // 복수개의 내스토리 정보 요청 (특정 아이디 부터)
    echo $this->get_mystories($test_mystory_id);

    // 내스토리 정보 요청
    echo $this->get_mystory($test_mystory_id); // 포스팅된 테스트 스토리 삭제.
*/

  }

  /*
   * 카카오톡 API 테스트
   */
  public function test_talk_api()
  {
    // 카카오톡 프로필 요청
    //echo $this->talk_profile();
  }

  /*
   * 푸시 알림 API 테스트
   */
  public function test_push_notification_api()
  {
    // 파라미터 설명
    // @param uuid 사용자의 고유 ID. 1~(2^63 -1), 숫자만 가능
    // @param push_type  gcm or apns
    // @param push_token apns(64자) or GCM으로부터 발급받은 push token
    // @param uuids 기기의 고유한 ID 리스트 (최대 100개까지 가능)

    // 푸시 알림 관련 API를 테스트하시려면 admin key 지정해야 합니다.

/*
    // 푸시 등록
    $params = array(
      "uuid" => "10000",
      "push_type" => "gcm",
      "push_token" => "xxxxxxxxxx",
      "device_id" => ""
    );
    $this->register_push($params);
*/

/*
    // 푸시 토큰 조회
    $param = array("uuid" => "10000");
    $this->get_push_tokens($param);
*/

/*
    // 푸시 해제
    $params = array(
      "uuid" => "10000",
      "push_type" => "gcm",
      "push_token" => "xxxxxxxxxx"
    );
    $this->deregister_push($params);
*/

/*
    // 푸시 보내기
    $param = array("uuids" => "[\"1\",\"2\", \"3\"]");
    $this->sendPush($param);
*/
  }
}