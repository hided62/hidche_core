<?php
namespace sammo;

if(AppConf::getRoot()->isExists()) {
    require_once(AppConf::getRoot()->getSettingFile());
} else {
    Error('설정 파일이 없습니다. 설정을 먼저 하십시요!');
}
