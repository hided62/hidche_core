<?php

namespace sammo\API\Global;

use sammo\DB;
use sammo\General;
use sammo\Json;
use sammo\KVStorage;
use sammo\RootDB;
use sammo\Session;
use sammo\Util;
use sammo\Validator;

use function sammo\calcLeadershipBonus;
use function sammo\checkLimit;
use function sammo\checkSecretPermission;
use function sammo\getDed;
use function sammo\getDedLevelText;
use function sammo\getExpLevel;
use function sammo\getGenChar;
use function sammo\getGeneralSpecialDomesticName;
use function sammo\getGeneralSpecialWarName;
use function sammo\getHonor;
use function sammo\getNationStaticInfo;
use function sammo\getOfficerLevelText;
use function sammo\increaseRefresh;

class GeneralListWithToken extends GeneralList
{
    static $withToken = true;
}
