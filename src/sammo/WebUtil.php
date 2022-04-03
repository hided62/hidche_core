<?php

namespace sammo;

use phpDocumentor\Reflection\Types\Boolean;

class WebUtil
{
    private function __construct()
    {
    }

    public static function escapeIPv4($ip)
    {
        return str_replace('.', '\\.', $ip);
    }

    public static function resolveRelativePath(string $path, string $basepath): string
    {
        return \phpUri::parse($basepath)->join($path);
    }

    public static function setHeaderNoCache()
    {
        if (!headers_sent()) {
            header('Expires: Wed, 01 Jan 2014 00:00:00 GMT');
            header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
            header('Cache-Control: no-store, no-cache, must-revalidate');
            header('Cache-Control: post-check=0, pre-check=0', false);
            header('Pragma: no-cache');
        }
    }

    public static function isAJAX()
    {
        return strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? null) === 'xmlhttprequest';
    }

    public static function setCacheHeader(?APICacheResult $cache, int $maxAge=60){
        header_remove('expires');
        header("Cache-Control: private, max-age={$maxAge}");
        header("Pragma: cache");
        if($cache->etag !== null){
            header("ETag: \"{$cache->etag}\"");
        }
        if($cache->lastModified !== null){
            $lastModifiedUnixTime = Util::toInt(TimeUtil::DateTimeToSeconds($cache->lastModified, true));
            $lastModified = gmdate("D, d M Y H:i:s", $lastModifiedUnixTime);
            header("Last-Modified: $lastModified GMT");
        }
    }

    public static function dieWithNotModified(): never{
        header("HTTP/1.1 304 Not Modified");
        die();
    }

    public static function parseETag(): ?string{
        $etag = $_SERVER['HTTP_IF_NONE_MATCH']??null;
        if($etag === null){
            return $etag;
        }
        $etag = trim($etag);
        if(str_starts_with($etag, 'W/"') && str_ends_with($etag, '"')){
            return substr($etag, 3, strlen($etag) - 4);
        }
        if(str_starts_with($etag, '"') && str_ends_with($etag, '"')){
            return substr($etag, 1, strlen($etag) - 2);
        }
        return $etag;
    }

    public static function parseLastModified(): ?\DateTimeInterface{
        $modifiedSinceStr = $_SERVER['HTTP_IF_MODIFIED_SINCE']??null;
        if($modifiedSinceStr === null){
            return null;
        }
        return new \DateTimeImmutable($modifiedSinceStr, new \DateTimeZone("UTC"));
    }

    public static function requireAJAX(): void
    {
        if (!static::isAJAX()) {
            Json::die([
                'result' => false,
                'reason' => 'no ajax'
            ]);
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
                    $error .= 'Maximum depth exceeded! : ' . $content;
                    break;
                case JSON_ERROR_STATE_MISMATCH:
                    $error .= 'Underflow or the modes mismatch! : ' . $content;
                    break;
                case JSON_ERROR_CTRL_CHAR:
                    $error .= 'Unexpected control character found : ' . $content;
                    break;
                case JSON_ERROR_SYNTAX:
                    $error .= 'Malformed JSON : ' . $content;
                    break;
                case JSON_ERROR_UTF8:
                    $error .= 'Malformed UTF-8 characters found! : ' . $content;
                    break;
                default:
                    $error .= 'Unknown error! : ' . $content;
                    break;
            }
            throw new \Exception($error);
        }

        return $decoded;
    }

    public static function preloadAsset(string $path, string $type)
    {
        $upath = \phpUri::parse($path);
        $path = $upath->join('');
        if (!$upath->scheme) {
            if (!file_exists($upath->path)) {
                return "<!-- preload:{$type} '{$path}' -->\n";
            }

            $mtime = filemtime($upath->path);
            if ($upath->query) {
                $tail = '&' . $mtime;
            } else {
                $tail = '?' . $mtime;
            }
        } else {
            $tail = '';
        }
        return "<link href='{$path}{$tail}' rel='preload' as='$type'>\n";
    }

    public static function preloadCSS(string $path)
    {
        return static::preloadAsset($path, 'style');
    }

    public static function preloadJS(string $path)
    {
        return static::preloadAsset($path, 'script');
    }

    public static function printDist(string $type, string|array $entryName, bool $isDefer = false)
    {
        if (is_string($entryName)) {
            $entryName = [$entryName];
        }

        $serverBasePath = \phpUri::parse(ServConfig::$serverWebPath)->path;

        if ($type == 'gateway') {
            $serverBasePath .= "/dist_js/{$type}";
            $basePath = dirname(__DIR__, 2) . "/dist_js/{$type}";
        } else {
            if (!class_exists('\\sammo\\VersionGit') || is_subclass_of('\\sammo\\VersionGit', '\\sammo\\VersionGitDynamic')) {
                $version = DB::prefix() . '_dynamic';
            } else {
                $version = VersionGit::getVersion();
            }
            $serverBasePath .= "/dist_js/{$version}/{$type}";
            $basePath = dirname(__DIR__, 2) . "/dist_js/{$version}/{$type}";
        }

        $outputs = ["\n"];

        foreach (["vendors", "common_ts", ...$entryName] as $moduleName) {
            foreach (['js', 'css'] as $ext) {
                $checkPath = $basePath . "/{$moduleName}.{$ext}";
                if (!file_exists($checkPath)) {
                    $outputs[] = "<!-- '{$type}/{$moduleName}.{$ext}' -->\n";
                    continue;
                }
                $mtime = filemtime($checkPath);
                if ($ext == 'css') {
                    $outputs[] = "<link  href='{$serverBasePath}/{$moduleName}.{$ext}?{$mtime}' rel='stylesheet' type='text/css' />\n";
                } else if ($ext == 'js') {
                    $typeText = $isDefer ? 'defer' : '';
                    $outputs[] = "<script src='{$serverBasePath}/{$moduleName}.{$ext}?{$mtime}' {$typeText}></script>\n";
                }
            }
        }
        return join("", $outputs);
    }

    public static function printJS(string $path, bool $isDefer = false)
    {
        //async 옵션 고려?
        $upath = \phpUri::parse($path);
        $path = $upath->join('');
        if (!$upath->scheme) {
            if (!file_exists($upath->path)) {
                return "<!-- JS '{$path}' -->\n";
            }
            $mtime = filemtime($upath->path);
            if ($upath->query) {
                $tail = '&' . $mtime;
            } else {
                $tail = '?' . $mtime;
            }
        } else {
            $tail = '';
        }

        $typeText = $isDefer ? 'defer' : '';
        return "<script {$typeText} src='{$path}{$tail}'></script>\n";
    }

    public static function printCSS(string $path)
    {
        $upath = \phpUri::parse($path);
        $path = $upath->join('');
        if (!$upath->scheme) {
            if (!file_exists($upath->path)) {
                return "<!-- CSS '{$path}' -->\n";
            }
            $mtime = filemtime($upath->path);
            if ($upath->query) {
                $tail = '&' . $mtime;
            } else {
                $tail = '?' . $mtime;
            }
        } else {
            $tail = '';
        }
        return "<link rel='stylesheet' type='text/css' href='{$path}{$tail}' />\n";
    }

    public static function printStaticValues(array $values, bool $pretty = true)
    {
        if (!count($values)) {
            return;
        }
        $lines = ["<script>"];

        foreach ($values as $key => $value) {
            $lines[] = "var {$key} = " . Json::encode($value, Json::EMPTY_ARRAY_IS_DICT | ($pretty ? Json::PRETTY : 0));
        }

        $lines[] = "</script>\n";

        return join("\n", $lines);
    }

    public static function htmlPurify(?string $text): string
    {
        if (!$text) {
            return '';
        }

        $config = \HTMLPurifier_HTML5Config::createDefault();
        $config->set('Filter.Custom', array(new \HTMLPurifier_Filter_YouTube()));
        $config->set('HTML.SafeIframe', true);
        $config->set('URI.SafeIframeRegexp', '%^(https?:)?//(www\.youtube(?:-nocookie)?\.com/embed/|player\.vimeo\.com/video/)%'); //allow YouTube and Vimeo
        $def = $config->getHTMLDefinition();
        $def->info_global_attr['data-flip'] = new \HTMLPurifier_AttrDef_Text;
        $purifier = new \HTMLPurifier($config);
        return $purifier->purify($text);
    }

    public static function errorBackMsg(string $msg, ?string $target = null): string
    {
        $jmsg = Json::encode($msg);
        if (!$target) {
            $moveNext = 'history.go(-1);';
        } else {
            $moveNext = "location.replace('{$target}');";
        }

        return "<html><head><style>html,body{background:black;}</style><script>alert({$jmsg});$moveNext</script></head><body></body></html>";
    }

    public static function drawMenu(string $path): string
    {
        if (!file_exists($path)) {
            return '';
        }
        $json = Json::decode(file_get_contents($path));

        $result = [];
        foreach ($json as $menuItem) {
            if (count($menuItem) == 2) {
                [$url, $title] = $menuItem;
                $targetAttr = '';
            } else {
                [$url, $title, $target] = $menuItem;
                $target = htmlspecialchars($target);
                $targetAttr = "target='$target' ";
            }
            $title = htmlspecialchars($title);
            $url = htmlspecialchars($url);
            $result[] = "<a class='nav-link' href='$url' $targetAttr>$title</a>";
        }

        return join("\n", $result);
    }
}
