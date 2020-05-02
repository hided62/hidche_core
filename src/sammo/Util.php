<?php
namespace sammo;

class Util extends \utilphp\util
{

    /**
     * int 값 반환을 강제하는 부동소수점 반올림
     * @param int|float $value
     * @param int       $pos    반올림 자리수, 0 이하의 '음수만'
     */
    public static function round($value, $pos=0) : int{
        assert($pos <= 0, 'Util::round는 음수만 입력 가능');
        return intval(round($value, $pos));
    }

    /**
     * int 값으로 강제로 설정하는 부동소수점 반올림
     * @param int|float $value
     * @param int       $pos    반올림 자리수, 0 이하의 '음수만'
     */
    public static function setRound(&$value, $pos=0) : void{
        $value = static::round($value, $pos);
    }

    private static function _parseReq($value, string $type)
    {
        if (is_array($value)) {
            if ($type === 'array_int') {
                return array_map('intval', $value);
            }

            if ($type === 'array_string') {
                return array_map(function ($item) {
                    return (string)$item;
                }, $value);
            }

            if ($type === 'array') {
                return $value;
            }

            throw new \InvalidArgumentException('지원할 수 없는 type 지정. array 가 붙은 type이어야 합니다');
        }

        if ($type === 'bool') {
            $value = strtolower($value);
            if ($value === null || $value === '' || $value === 'off' || $value === 'false' || $value === 'no' || $value === 'n' || $value === 'x' || $value === 'null') {
                return false;
            }
            return !!$value;
        }
        if ($type === 'int') {
            return (int)$value;
        }
        if ($type === 'float') {
            return (float)$value;
        }
        if ($type === 'string') {
            return (string)$value;
        }

        throw new \InvalidArgumentException('올바르지 않은 type 지정');
    }

    public static function zip(\Generator  ...$iterators){
        while(true){
            $hasValue = false;
            $values = [];
            foreach($iterators as $iter){
                if($iter->valid()){
                    $values[] = $iter->send(NULL);
                    $hasValue = true;
                }
            }
            yield $values;

            if(!$hasValue){
                break;
            }
        }
    }

    /**
     * $_POST, $_GET에서 값을 가져오는 함수. Util::array_get($_POST[$name])을 축약 가능.
     * 타입이 복잡해질 경우 이 함수를 통하지 않고 json으로 요청할 것을 권장.
     *
     * @param string $name 가져오고자 하는 key 이름.
     * @param string $type 가져오고자 하는 type. [string, int, float, bool, array, array_string, array_int]
     * @param mixed $ifNotExists 만약 $_POST와 $_GET에 값이 없을 경우 반환하는 변수. 이 값은 $type을 검사하지 않음.
     * @return int|float|string|array|null
     * @throws \InvalidArgumentException
     */
    public static function getReq(string $name, string $type = 'string', $ifNotExists = null)
    {
        if (isset($_POST[$name])) {
            $value = $_POST[$name];
        } elseif (isset($_GET[$name])) {
            $value = $_GET[$name];
        } else {
            return $ifNotExists;
        }

        return static::_parseReq($value, $type);
    }

    /**
     * $_POST에서 값을 가져오는 함수. Util::array_get($_POST[$name])을 축약 가능. $_GET에서도 가져올 수 있다면 getReq 사용.
     * 타입이 복잡해질 경우 이 함수를 통하지 않고 json으로 요청할 것을 권장.
     *
     * @param string $name 가져오고자 하는 key 이름.
     * @param string $type 가져오고자 하는 type. [string, int, float, bool, array, array_string, array_int]
     * @param mixed $ifNotExists 만약 $_GET과 $_POST에 값이 없을 경우 반환하는 변수. 이 값은 $type을 검사하지 않음.
     * @return int|float|string|array|null
     * @throws \InvalidArgumentException
     */
    public static function getPost(string $name, string $type = 'string', $ifNotExists = null)
    {
        if (isset($_POST[$name])) {
            $value = $_POST[$name];
        } else {
            return $ifNotExists;
        }

        return static::_parseReq($value, $type);
    }

    public static function hashPassword($salt, $password)
    {
        return hash('sha512', $salt.$password.$salt);
    }

    /**
     * 변환할 내용이 _tK_$key_ 형태로 작성된 단순한 템플릿 파일을 이용하여 결과물을 생성해주는 함수.
     */
    public static function generateFileUsingSimpleTemplate(string $srcFilePath, string $destFilePath, array $params, bool $canOverwrite=false)
    {
        if ($destFilePath === $srcFilePath) {
            return 'invalid destFilePath';
        }
        if (!file_exists($srcFilePath)) {
            return 'srcFilePath is not exists';
        }
        if (file_exists($destFilePath) && !$canOverwrite) {
            return 'destFilePath is already exists';
        }
        if (!is_writable(dirname($destFilePath))) {
            return 'destFilePath is not writable';
        }

        $text = file_get_contents($srcFilePath);
        foreach ($params as $key => $value) {
            $text = str_replace("_tK_{$key}_", $value, $text);
        }
        file_put_contents($destFilePath, $text);

        return true;
    }

    /**
     * params에 맞도록 class를 생성해주는 함수
     */
    public static function generatePHPClassFile(string $destFilePath, array $params, ?string $srcClassName=null, string $namespace='sammo'){
        if (!is_writable(dirname($destFilePath))) {
            return 'destFilePath is not writable';
        }

        $newClassName = basename($destFilePath, '.php');
        $newClassName = basename($newClassName, '.orig');
        $head = [];
        $head[] = '<?php';
        $head[] = "namespace $namespace;";
        if($srcClassName === null){
            $head[] = "class $newClassName";
        }
        else{
            $head[] = "class $newClassName extends $srcClassName";
        }
        
        $head[] = '{';
        $head[] = '';
        $head = join("\n", $head);

        $body = [];
        foreach($params as $key=>$value){

            if(!preg_match('/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/',$key)){
                return "$key is not valid variable name";
            }

            $body[] = '    public static $'.$key.' = '.var_export($value, true).';';
        }
        $tail = "\n}";

        if(file_exists($destFilePath)){
            unlink($destFilePath);
        }
        $result = file_put_contents($destFilePath, $head.join("\n", $body).$tail, LOCK_EX);
        assert($result);
        
        return true;
    }

    /**
     * '비교적' 안전한 int 변환
     * null -> null
     * int -> int
     * float -> int
     * numeric(int, float) 포함 -> int
     * 기타 -> 예외처리
     *
     * @return int|null
     */
    public static function toInt($val, $silent=false)
    {
        if (!isset($val)) {
            return null;
        }
        if ($val === null) {
            return null;
        }
        if (is_int($val)) {
            return $val;
        }
        if (is_numeric($val)) {
            return intval($val);//
        }
        if (strtolower($val) === 'null') {
            return null;
        }

        if ($silent) {
            if ($val == null) {
                return null;
            }
            if ($val == ''){
                return null;
            }
            return intval($val);
        }
        throw new \InvalidArgumentException('올바르지 않은 타입형 :'.$val);
    }

    /**
     * Generate a random string, using a cryptographically secure
     * pseudorandom number generator (random_int)
     *
     * For PHP 7, random_int is a PHP core function
     * For PHP 5.x, depends on https://github.com/paragonie/random_compat
     *
     * @param int $length      How many characters do we want?
     * @param string $keyspace A string of all possible characters
     *                         to select from
     * @return string
     */
    public static function randomStr($length, $keyspace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ')
    {
        $str = '';
        $max = mb_strlen($keyspace, '8bit') - 1;
        for ($i = 0; $i < $length; ++$i) {
            $str .= $keyspace[random_int(0, $max)];
        }
        return $str;
    }

    public static function mapWithKey($callback, $dict)
    {
        $result = [];
        foreach ($dict as $key=>$value) {
            $result[$key] = ($callback)($key, $value);
        }
        return $result;
    }

    public static function convertArrayToDict($arr, $keyName)
    {
        $result = [];
    
        foreach ($arr as $obj) {
            $key = $obj[$keyName];
            $result[$key] = $obj;
        }
    
        return $result;
    }

    public static function convertArrayToSetLike($arr){
        $result = [];
        foreach($arr as $datum){
            $result[$datum] = $datum;
        }
        return $result;
    }

    public static function convertPairArrayToDict($arr){
        $result = [];
        foreach($arr as [$key, $val]){
            $result[$key] = $val;
        }
        return $result;
    }

    public static function convertDictToArray($dict, bool $withKey=true)
    {
        $result = [];
    
        foreach($dict as $key=>$value){
            if($withKey){
                $result[] = [$key, $value];
            }
            else{
                $result[] = $value;
            }
        }
        
        return $result;
    }

    public static function squeezeFromArray(array $dict, $key){
        $result = [];
        foreach($dict as $dictKey=>$value){
            $result[$dictKey] = $value[$key];
        }
        
        return $result;
    }

    public static function isDict($array)
    {
        if($array === null){
            return false;
        }
        if (!is_array($array)) {
            //배열이 아니면 dictionary 조차 아님.
            return false;
        }
        if(count($array) === 0){
            return true;
        }

        $idx = 0;
        foreach (array_keys($array) as $key) {
            if (is_string($key)) {
                return true;
            }
            if($idx !== $key){
                return true;
            }
            $idx = $key + 1;
        }
    
        return false;
    }

    /**
     * @param null|mixed|mixed[] $dict
     * @return null|mixed|mixed[]
     */
    public static function eraseNullValue($dict, int $depth=512)
    {
        //TODO:Test 추가
        if ($dict === null) {
            return null;
        }
    
        if (is_array($dict) && empty($dict)) {
            return null;
        }
    
        if ($depth <= 0) {
            return $dict;
        }
    
        foreach ($dict as $key=>$value) {
            if ($value === null) {
                unset($dict[$key]);
                continue;
            } 
            if (!Util::isDict($value)) {
                continue;
            }

            $newValue = Util::eraseNullValue($value, $depth - 1);
            if ($newValue === null) {
                unset($dict[$key]);
            } else {
                $dict[$key] = $newValue;
            }
        }
    
        return $dict;
    }

    /**
     * key=>value pair를 보존한 섞기
     */
    public static function shuffle_assoc(&$array) {
        $keys = array_keys($array);

        shuffle($keys);

        $new = [];

        foreach($keys as $key) {
            $new[$key] = $array[$key];
        }

        $array = $new;

        return true;
    }


    /**
     * [0.0, 1.0] 사이의 선형 랜덤 float
     * @return float
     */
    public static function randF()
    {
        return mt_rand() / mt_getrandmax();
    }

    /**
     * [min, max] 사이의 선형 랜덤 float
     * @return float
     */
    public static function randRange(float $min, float $max)
    {
        return static::randF()*($max - $min) + $min;
    }

    /**
     * [min, max] 사이의 선형 랜덤 int
     * 현재는 rand(min, max)와 동일
     * @return int
     */
    public static function randRangeInt(int $min, int $max){
        return mt_rand($min, $max);
    }

    /**
     * $prob의 확률로 true를 반환
     * @return boolean
     */
    public static function randBool($prob = 0.5)
    {
        return self::randF() < $prob;
    }

    /**
     * aaa(.bbb)?% 의 텍스트를 float으로 변환. 100% = 1.0
     * @return float|null
     */
    public static function convPercentStrToFloat(string $text):?float{
        preg_match('/^(\d+(\.\d+)?)\%$/', $text, $matches);
        if($matches === null){
            return null;
        }

        return (float)$matches[1] / 100;
    }
    

    /**
     * $min과 $max 사이의 값으로 교정
     */
    public static function valueFit($value, $min = null, $max = null)
    {
        if($max !== null && $min !== null && $max < $min){
            return $min;
        }
        if ($min !== null && $value < $min) {
            return $min;
        }
        if ($max !== null && $value > $max) {
            return $max;
        }
        return $value;
    }

    /**
     * 각 값의 비중에 따라 랜덤한 값을 선택
     *
     * @param array $items 각 수치의 비중
     *
     * @return int|string 선택된 랜덤 값의 key값. 단순 배열인 경우에는 index
     */
    public static function choiceRandomUsingWeight(array $items)
    {
        $sum = 0;
        foreach ($items as $value) {
            if($value <= 0){
                continue;
            }
            $sum += $value;
        }

        $rd = self::randF()*$sum;
        foreach ($items as $key=>$value) {
            if($value <= 0){
                $value = 0;
            }
            if ($rd <= $value) {
                return $key;
            }
            $rd -= $value;
        }

        //fallback. 이곳으로 빠지지 않음
        end($items);
        return key($items);
    }

    /**
     * 각 값의 비중에 따라 랜덤한 값을 선택.
     *
     * @param array $items 각 수치와 비중. [값, weight] 으로 보관
     *
     * @return object 선택된 랜덤 값의 첫번째 값
     */
    public static function choiceRandomUsingWeightPair(array $items)
    {
        $sum = 0;
        foreach ($items as [$item, $value]) {
            if($value <= 0){
                continue;
            }
            $sum += $value;
        }

        $rd = self::randF()*$sum;
        foreach ($items as [$item, $value]) {
            if($value <= 0){
                $value = 0;
            }
            if ($rd <= $value) {
                return $item;
            }
            $rd -= $value;
        }

        //fallback. 이곳으로 빠지지 않음
        end($items);
        return $items[key($items)][0];
    }

    /**
     * 2중 배열에서 특정 키의 합
     * 
     * @param array $array 배열. 1차원 배열 또는 2차원 배열
     * @param int|string|null $key 2차원 배열에서 참조할 키.
     * @return int|float 합계
     */
    public static function arraySum(array $array, $key = null){
        if($key === null){
            return array_sum($array);
        }
        $sum = 0;
        foreach($array as $val){
            $sum += $val[$key];
        }
        return $sum;
    }

    /**
     * 특정 키를 가진 값으로 묶음
     * 
     * @param array $array 배열. 1차원 배열 또는 2차원 배열
     * @param int|string|null $key 2차원 배열에서 참조할 키.
     * @return array
     */
    public static function arrayGroupBy(array $array, $key, bool $preserveRowKey=false) {
        $result = array();
    
        if($preserveRowKey){
            foreach($array as $rowKey=>$val) {
                if(key_exists($key, $val)){
                    $result[$val[$key]][$rowKey] = $val;
                }else{
                    $result[""][$rowKey] = $val;
                }
            }
        }
        else{
            foreach($array as $val) {
                if(key_exists($key, $val)){
                    $result[$val[$key]][] = $val;
                }else{
                    $result[""][] = $val;
                }
            }
        }
        
        return $result;
    }

    public static function getKeyOfMaxValue(array $array){
        $max = null;
        $result = null;
        foreach ($array as $key => $value) {
            if ($max === null || $value > $max) {
                $result = $key;
                $max = $value;
            }
        }

        return $result;
    }

    /**
     * 배열의 아무거나 고름. Python의 random.choice()
     *
     * @param array $items 선택하고자 하는 배열
     *
     * @return int|float|string|object 선택된 value값.
     */
    public static function choiceRandom(array $items)
    {
        return $items[array_rand($items)];
    }

    /**
     * fqn 클래스 경로에서 클래스 이름을 받아옴
     */
    public static function getClassName(string $classpath)
    {
        if ($pos = strrpos($classpath, '\\')) return substr($classpath, $pos + 1);
        return $pos;
    }

    public static function getClassNameFromObj($object){
        $reflect = new \ReflectionClass($object);
        return $reflect->getShortName();
    }

    /**
     * 배열의 원소에 대해서 테스트를 수행하고 모두 true인지 확인
     */
    public static function testArrayValues(array $array, ?callable $callback):bool{
        if($callback === null){
            foreach($array as $value){
                if(!$value){
                    return false;
                }
            }
            return true;
        }

        foreach($array as $value){
            if(!($callback)($value)){
                return false;
            }
        }
        return true;
    }

    /**
     * MeekroDB에서 %lb의 처리가 이상하여 따로 만든 코드
     */
    public static function formatListOfBackticks(array $array):string{
        if(!$array){
            throw new MustNotBeReachedException('backtick 목록에 없음');
        }
        //.이 들어간 경우에는 분리해서 묶어야함.
        return join(',', array_map(function($value){
            $value = preg_replace('/\s/', '', $value);
            if(strpos($value, '.') !== false){
                $value = explode('.', $value);
                $value = join('`.`', $value);
            }
            return "`{$value}`";
        }, $array));
    }

    public static function joinYearMonth(int $year, int $month):int{
        return $year * 12 + $month - 1;
    }

    public static function parseYearMonth(int $yearMonth):array{
        return [intdiv($yearMonth, 12), $yearMonth%12 + 1];
    }

    /**
     * 변수의 값을 첫번째 값부터 비교해서 대소를 반환
     * 길이가 다른 경우, 앞의 결과를 먼저 비교한 뒤, 짧은쪽의 값을 null으로 가정하여 길이 처리
     */
    public static function arrayCompare(iterable $lhs, iterable $rhs, ?callable $comp=null){
        if($lhs instanceof \Traversable){
            $lhsIter = new \IteratorIterator($lhs);
        }
        else if(is_array($lhs)){
            $lhsIter = new \ArrayIterator($lhs);
        }
        else{
            throw new \InvalidArgumentException('$lhs is not Traversable');
        }
        
        if($rhs instanceof \Traversable){
            $rhsIter = new \IteratorIterator($rhs);
        }
        else if(is_array($rhs)){
            $rhsIter = new \ArrayIterator($rhs);
        }
        else{
            throw new \InvalidArgumentException('$rhs is not Traversable');
        }

        while($lhsIter->valid() && $rhsIter->valid()){
            $lhsVal = $lhsIter->current();
            $rhsVal = $rhsIter->current();

            if($comp !== null){
                $compResult = $comp($lhsVal, $rhsVal);
            }
            else{
                $compResult = $lhsVal <=> $rhsVal;
            }

            if($compResult !== 0){
                return $compResult;
            }

            $lhsIter->next();
            $rhsIter->next();
        }

        $rhsVal = null;
        while($lhsIter->valid()){
            $lhsVal = $lhsIter->current();

            if($comp !== null){
                $compResult = $comp($lhsVal, $rhsVal);
            }
            else{
                $compResult = $lhsVal <=> $rhsVal;
            }

            if($compResult !== 0){
                return $compResult;
            }

            $lhsIter->next();
        }

        $lhsVal = null;
        while($rhsIter->valid()){
            $rhsVal = $rhsIter->current();

            if($comp !== null){
                $compResult = $comp($lhsVal, $rhsVal);
            }
            else{
                $compResult = $lhsVal <=> $rhsVal;
            }

            if($compResult !== 0){
                return $compResult;
            }

            $rhsIter->next();
        }

        return 0;
    }

    public static function isPowerOfTwo(int $number):bool{
        if($number <= 0){
            return false;
        }
        return ($number & ($number - 1)) == 0;
    }

    /**
     * Python 3의 range와 동일
     * @param int $from 
     * @param null|int $to 
     * @param null|int $step 
     * @return \Traversable
     * @throws \InvalidArgumentException 
     */
    public static function range(int $from, ?int $to=null, ?int $step=null):\Traversable{
        if($to === null){
            $to = $from;
            $from = 0;
        }
        if($step === null){
            $step = 1;
        }
        else if($step === 0){
            throw new \InvalidArgumentException('xrange() arg 3 must not be zero');
        }

        if($step > 0){
            while($from < $to){
                yield $from;
                $from += $step;
            }
        }
        else{
            while($from > $to){
                yield $from;
                $from += $step;
            }
        }
    }
    
};
