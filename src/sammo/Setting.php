<?php
namespace sammo;

class Setting
{
    private $basepath;
    private $settingFile;
    private $htaccessFile;
    private $versionFile;
    private $exist = false;
    private $running = false;

    private $shortName;
    private $korName;
    private $color;
    private $version = null;

    public function __construct(string $basepath, string $korName, string $color, string $name=null)
    {
        $this->basepath = $basepath;
        $this->settingFile = realpath($basepath.'/d_setting/DB.php');
        $this->htaccessFile = realpath($basepath.'/.htaccess');
        $this->versionFile = realpath($basepath.'/d_setting/VersionGit.php');

        $this->korName = $korName;
        $this->color = $color;
        if ($name) {
            $this->shortName = $name;
        } else {
            $this->shortName = basename($this->basepath);
        }

        if (file_exists($this->settingFile)) {
            $this->exist = true;

            if (!file_exists($this->htaccessFile)) {
                $this->running = true;
            }
        }
    }

    public function isRunning()
    {
        return $this->running;
    }

    public function isExists()
    {
        return $this->exist;
    }

    public function getShortName()
    {
        return $this->shortName;
    }

    public function getColor()
    {
        return $this->color;
    }

    public function getKorName()
    {
        return $this->korName;
    }

    public function getBasePath()
    {
        return $this->basepath;
    }

    public function getSettingFile()
    {
        return $this->settingFile;
    }

    public function getVersion()
    {
        if ($this->version !== null) {
            return $this->version;
        }

        if (!file_exists($this->versionFile)) {
            $this->version = 'noVersionFile';
            return $this->version;
        }

        $tail = new FileTail($this->versionFile);
        $version = 'noVersionJson';
        foreach ($tail->smart(5, 100, true) as $line) {
            if (Util::starts_with($line, '//{')) {
                $version = Json::decode(substr($line, 2));
                $version = Util::array_get($version['version'], 'noVersionValue');
                break;
            }
        }
        $this->version = $version;
        return $version;
    }

    public function closeServer()
    {
        if (!file_exists($this->basepath) || !is_dir($this->basepath)) {
            return false;
        }
        $templates = new \League\Plates\Engine(__DIR__.'/templates');
        //TODO: .htaccess가 서버 오픈에도 사용될 수 있으니 별도의 방법이 필요함
        $allow_ip = Util::get_client_ip(false);
        if (Util::starts_with($allow_ip, '192.168.') ||
            Util::starts_with($allow_ip, '10.')) {
            //172.16~172.31은 코딩하기 귀찮으니까 안할거다
            $allow_ip = Util::get_client_ip(true);
        }

        $xforward_allow_ip = WebUtil::escapeIPv4($allow_ip);

        $htaccess = $templates->render(
            'block_htaccess',
            ['allow_ip' => $allow_ip, 'xforward_allow_ip' => $xforward_allow_ip]
        );
        file_put_contents($this->basepath.'/.htaccess', $htaccess);
        return true;
    }

    public function openServer()
    {
        if (!file_exists($this->basepath) || !is_dir($this->basepath)) {
            return false;
        }
        if (file_exists($this->basepath.'/.htaccess')) {
            @unlink($this->basepath.'/.htaccess');
        }
        return true;
    }
}
