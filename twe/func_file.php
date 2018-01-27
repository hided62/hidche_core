<?php
function delInDir($dir) {
    $handle = opendir($dir);
    if($handle !== false){
        while(false !== ($FolderOrFile = readdir($handle))) {
            if ($FolderOrFile == "." || $FolderOrFile == "..") {
                continue;
            }

            $filepath = sprintf('%s/%s', $dir, $FolderOrFile);
            if (is_dir($filepath)) {
                delInDir($filepath);
            } // recursive
            else {
                @unlink($filepath);
            }
        }
    }
    closedir($handle);
//    if(rmdir($dir)) {
//        $success = true;
//    }
    return true;
}