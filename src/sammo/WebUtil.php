<?php
namespace sammo;

class WebUtil
{
    private function __construct()
    {
    }

    public static function escapeIPv4($ip)
    {
        return str_replace('.', '\\.', $ip);
    }

    public static function resolveRelativePath(string $path, string $basepath) : string
    {
        return \phpUri::parse($basepath)->join($path);
    }

    public static function setHeaderNoCache()
    {
        if (!headers_sent()) {
            header('Expires: Wed, 01 Jan 2014 00:00:00 GMT');
            header('Cache-Control: no-store, no-cache, must-revalidate');
            header('Cache-Control: post-check=0, pre-check=0', false);
            header('Pragma: no-cache');
        }
    }

    /**
     * @return mixed|mixed[]
     */
    public static function parseJsonPost()
    {
        // http://thisinterestsme.com/receiving-json-post-data-via-php/
        // http://thisinterestsme.com/php-json-error-handling/
        if (strcasecmp($_SERVER['REQUEST_METHOD'], 'POST') != 0) {
            throw new \Exception('Request method must be POST!');
        }
        
        //Make sure that the content type of the POST request has been set to application/json
        $contentType = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '';
        if (strcasecmp($contentType, 'application/json') != 0) {
            throw new \Exception('Content type must be: application/json');
        }
        
        //Receive the RAW post data.
        $content = trim(file_get_contents("php://input"));
        
        //Attempt to decode the incoming RAW post data from JSON.
        $decoded = Json::decode($content);
        
        
        $jsonError = json_last_error();
        
        //In some cases, this will happen.
        if (is_null($decoded) && $jsonError == JSON_ERROR_NONE) {
            throw new \Exception('Could not decode JSON!');
        }
        
        //If an error exists.
        if ($jsonError != JSON_ERROR_NONE) {
            $error = 'Could not decode JSON! ';
            
            //Use a switch statement to figure out the exact error.
            switch ($jsonError) {
                case JSON_ERROR_DEPTH:
                    $error .= 'Maximum depth exceeded! : '.$content;
                break;
                case JSON_ERROR_STATE_MISMATCH:
                    $error .= 'Underflow or the modes mismatch! : '.$content;
                break;
                case JSON_ERROR_CTRL_CHAR:
                    $error .= 'Unexpected control character found : '.$content;
                break;
                case JSON_ERROR_SYNTAX:
                    $error .= 'Malformed JSON : '.$content;
                break;
                case JSON_ERROR_UTF8:
                     $error .= 'Malformed UTF-8 characters found! : '.$content;
                break;
                default:
                    $error .= 'Unknown error! : '.$content;
                break;
            }
            throw new \Exception($error);
        }
    
        return $decoded;
    }

    public static function printJS(string $path){
        //async, defer 옵션 고려
        $upath = \phpUri::parse($path);
        $path = $upath->join('');
        if(!$upath->scheme && file_exists($upath->path)){
            $mtime = filemtime($upath->path);
            if($upath->query){
                $tail = '&'.$mtime;
            }
            else{
                $tail = '?'.$mtime;
            }
        }
        else{
            $tail = '';
        }
        return "<script src='{$path}{$tail}'></script>\n";
    }

    public static function printCSS(string $path){
        $upath = \phpUri::parse($path);
        $path = $upath->join('');
        if(!$upath->scheme && file_exists($upath->path)){
            $mtime = filemtime($upath->path);
            if($upath->query){
                $tail = '&'.$mtime;
            }
            else{
                $tail = '?'.$mtime;
            }
        }
        else{
            $tail = '';
        }
        return "<link rel='stylesheet' type='text/css' href='{$path}{$tail}' />\n";
    }

    public static function htmlPurify(?string $text): string{
        if(!$text){
            return '';
        }

        $config = \HTMLPurifier_HTML5Config::createDefault();
        $purifier = new \HTMLPurifier($config);
        return $purifier->purify($text);
    }
}
