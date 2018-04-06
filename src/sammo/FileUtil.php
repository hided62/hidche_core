<?php
namespace sammo;

class FileUtil
{
    public static function delInDir(string $dir)
    {
        $handle = opendir($dir);
        if ($handle !== false) {
            while (false !== ($FolderOrFile = readdir($handle))) {
                if ($FolderOrFile == "." || $FolderOrFile == "..") {
                    continue;
                }
                if ($FolderOrFile[0] == '.') {
                    continue;
                }
    
                $filepath = sprintf('%s/%s', $dir, $FolderOrFile);
                if (is_dir($filepath)) {
                    FileUtil::delInDir($filepath);
                } // recursive
                else {
                    @unlink($filepath);
                }
            }
            closedir($handle);
        }
        
        return true;
    }

    public function delExpiredInDir($dir, $t)
    {
        $handle = opendir($dir);
        if ($handle !== false) {
            while (false !== ($FolderOrFile = readdir($handle))) {
                if ($FolderOrFile == "." || $FolderOrFile == "..") {
                    continue;
                }

                $filepath = sprintf('%s/%s', $dir, $FolderOrFile);
                if (is_dir($filepath)) {
                    static::delExpiredInDir($filepath, $t);
                } // recursive
                else {
                    $mt = filemtime($filepath);
                    if ($mt < $t) {
                        @unlink($filepath);
                    }
                }
            }
            closedir($handle);
        }

    }
}
