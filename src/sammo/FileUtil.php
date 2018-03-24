<?php
namespace sammo;

class FileUtil{
    public static function delInDir(string $dir) {
        $handle = opendir($dir);
        if($handle !== false){
            while(false !== ($FolderOrFile = readdir($handle))) {
                if ($FolderOrFile == "." || $FolderOrFile == "..") {
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

    function delExpiredInDir($dir, $t) {
        $handle = opendir($dir);
        if ($handle !== false) {
            while (false !== ($FolderOrFile = readdir($handle))) {
                if ($FolderOrFile == "." || $FolderOrFile == "..") {
                    continue;
                }

                $filepath = sprintf('%s/%s', $dir, $FolderOrFile);
                if (is_dir($filepath)) {
                    delExpiredInDir($filepath, $t);
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
        
        return $success;
    }    
}