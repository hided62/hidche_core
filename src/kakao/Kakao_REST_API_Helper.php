<?php
namespace kakao;

if (class_exists('\\kakao\\KakaoKey') === false) {
    /** @suppress PhanRedefineClass */
    class KakaoKey
    {
        const REST_KEY = '';
        const ADMIN_KEY = '';
        const REDIRECT_URI = '';
    }
}
//https://devtalk.kakao.com/t/php-rest-api/14602/3
//header('Content-Type: application/json; charset=utf-8');

///////////////////////////////////////////////////////////////////////////////


///////////////////////////////////////////////////////////////////////////////


class User_Management_Path
{
    public static $TOKEN          = "/oauth/token";
    public static $SIGNUP         = "/v1/user/signup";
    public static $UNLINK         = "/v1/user/unlink";
    public static $LOGOUT         = "/v1/user/logout";
    public static $ME             = "/v2/user/me";
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
    public static $TALK_TO_ME_DEFAULT  = "/v2/api/talk/memo/default/send";
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

    public function __construct($access_token = '')
    {
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

    public function request($api_path, $params = '', $http_method = 'GET')
    {
        if ($api_path != Story_Path::$UPLOAD_MULTI && is_array($params)) { // except for uploading
            $params = http_build_query($params);
        }

        $requestUrl = ($api_path == '/oauth/token' ? self::$OAUTH_HOST : self::$API_HOST) . $api_path;

        if (($http_method == 'GET' || $http_method == 'DELETE') && !empty($params)) {
            $requestUrl .= '?'.$params;
        }

        $opts = [
            CURLOPT_URL => $requestUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSLVERSION => CURL_SSLVERSION_TLSv1,
        ];

        if ($api_path != '/oauth/token') {
            if (in_array($api_path, self::$admin_apis)) {
                if (!$this->admin_key) {
                    throw new \Exception('admin key should not be null or empty.');
                }
                $headers = array('Authorization: KakaoAK ' . $this->admin_key);
            } else {
                if (!$this->access_token) {
                    throw new \Exception('access token should not be null or empty.');
                }
                $headers = array('Authorization: Bearer ' . $this->access_token);
            }

            $opts[CURLOPT_HEADER] = false;
            $opts[CURLOPT_HTTPHEADER] = $headers;
        }

        if ($http_method == 'POST') {
            $opts[CURLOPT_POST] = true;
            if ($params) {
                $opts[CURLOPT_POSTFIELDS] = $params;
            }
        } elseif ($http_method == 'DELETE') {
            $opts[CURLOPT_CUSTOMREQUEST] = 'DELETE';
        }

        $curl_session = curl_init();
        curl_setopt_array($curl_session, $opts);
        $return_data = curl_exec($curl_session);

        if (curl_errno($curl_session)) {
            throw new \Exception(curl_error($curl_session));
        } else {
            // 디버깅 시에 주석을 풀고 응답 내용 확인할 때
            //print_r(curl_getinfo($curl_session));
            curl_close($curl_session);
            return json_decode($return_data, true);
        }
    }

    public function set_access_token($access_token)
    {
        $this->access_token = $access_token;
    }

    public function set_admin_key($admin_key)
    {
        $this->admin_key = $admin_key;
    }

    ///////////////////////////////////////////////////////////////
    // User Management
    ///////////////////////////////////////////////////////////////

    private function _create_or_refresh_access_token($params)
    {
        return $this->request(User_Management_Path::$TOKEN, $params, 'POST');
    }

    public function create_access_token($authorization_code)
    {
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

    public function refresh_access_token($refresh_token)
    {
        $params = [
      'grant_type'=>'refresh_token',
      'client_id'=>$this->REST_KEY,
      'redirect_uri'=>$this->REDIRECT_URI,
      'code'=>$refresh_token
    ];
        $result = $this->_create_or_refresh_access_token($params);
    
        return $result;
    }

    public function signup()
    {
        return $this->request(User_Management_Path::$SIGNUP);
    }

    public function unlink()
    {
        return $this->request(User_Management_Path::$UNLINK);
    }

    public function logout()
    {
        return $this->request(User_Management_Path::$UNLINK);
    }

    public function me()
    {
        return $this->request(User_Management_Path::$ME);
    }

    public function meWithEmail()
    {
        $params = [
            'property_keys'=>'['.
                '"id",'.
                '"kakao_account.has_email","kakao_account.email",'.
                '"kakao_account.is_email_valid","kakao_account.is_email_verified"'.
            ']'
        ];
        return $this->request(User_Management_Path::$ME);
    }

    public function update_profile($params)
    {
        return $this->request(User_Management_Path::$UPDATE_PROFILE, $params, 'POST');
    }

    public function user_ids()
    {
        return $this->request(User_Management_Path::$USER_IDS);
    }

    ///////////////////////////////////////////////////////////////
    // Kakao Story
    ///////////////////////////////////////////////////////////////

    public function isstoryuser()
    {
        return $this->request(Story_Path::$ISSTORYUSER);
    }

    public function story_profile()
    {
        return $this->request(Story_Path::$PROFILE);
    }

    ///////////////////////////////////////////////////////////////
    // Kakao Talk
    ///////////////////////////////////////////////////////////////

    public function talk_profile()
    {
        return $this->request(Talk_Path::$TALK_PROFILE);
    }

    public function talk_to_me_default($req)
    {
        $params = [
      'template_object' => json_encode($req)
    ];
        return $this->request(Talk_Path::$TALK_TO_ME_DEFAULT, $params, 'POST');
    }


    ///////////////////////////////////////////////////////////////
    // API Test
    ///////////////////////////////////////////////////////////////


    private $REST_KEY = KakaoKey::REST_KEY;  // 디벨로퍼스의 앱 설정에서 확인할 수 있습니다.
  private $REDIRECT_URI = KakaoKey::REDIRECT_URI; // 설정에 등록한 사이트 도메인 + redirect uri
  private $AUTHORIZATION_CODE = ''; // 동의를 한 후 발급되는 code
  private $REFRESH_TOKEN = '';
}
