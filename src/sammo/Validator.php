<?php
namespace sammo;

class Validator extends \Valitron\Validator
{
    protected static $_lang = 'ko';

    public function errorStr()
    {
        $errors = array_values((array)$this->errors());
        $errors = array_map(function ($value) {
            return join(', ', $value);
        }, $errors);
        $errors = join(', ', $errors);
        return $errors;
    }

    /**
     * Convenience method to add a single validation rule
     * Phan의 오동작으로 PhanUndeclaredFunctionInCallable가 발생하는것을 억제하기 위해서 별도 제작.
     * $rule에 함수를 넣는 대신 상속한 클래스에 추가하는 방식을 사용할 것.
     *
     * @suppress PhanUndeclaredFunctionInCallable
     *
     * @param  string                    $rule
     * @param  array|string              $fields
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function rule($rule, $fields){
        $params = func_get_args();
        return parent::rule(...$params);


    }

    /**
     * Validate that a field is an integer array
     *
     * @param  string $field
     * @param  mixed  $value
     * @return bool
     */
    protected function validateIntegerArray($field, $value)
    {
        if(!is_array($value)){
            return false;
        }
        foreach($value as $subItem){
            if(!is_int($subItem)){
                return false;
            }
        }
        return true;
    }

    /**
     * Validate that a field is an string array
     *
     * @param  string $field
     * @param  mixed  $value
     * @return bool
     */
    protected function validateStringArray($field, $value)
    {
        if(!is_array($value)){
            return false;
        }
        foreach($value as $subItem){
            if(!is_string($subItem)){
                return false;
            }
        }
        return true;
    }

    /**
     * Validate that a field is an integer value
     *
     * @param  string $field
     * @param  mixed  $value
     * @return bool
     */
    protected function validateInt($field, $value)
    {
        if(!is_int($value)){
            return false;
        }
        return true;
    }

    /**
     * Validate that a field is an float value
     *
     * @param  string $field
     * @param  mixed  $value
     * @return bool
     */
    protected function validateFloat($field, $value)
    {
        if(!is_float($value)){
            return false;
        }
        return true;
    }

    /**
     * 문자열의 '최대 너비'를 확인함. mb_strwidth 기반
     *
     * @param  string $field
     * @param  mixed  $value
     * @return bool
     */
    protected function validateStringWidthMax($field, $value, $params){
        if(!is_string($value)){
            return false;
        }
        $width = mb_strwidth($value);
        return $width <= $params[0];
    }

    /**
     * 문자열의 '최소 너비'를 확인함. mb_strwidth 기반
     *
     * @param  string $field
     * @param  mixed  $value
     * @return bool
     */
    protected function validateStringWidthMin($field, $value, $params){
        if(!is_string($value)){
            return false;
        }
        $width = mb_strwidth($value);
        return $width >= $params[0];
    }

    /**
     * 문자열의 '너비'를 확인함. mb_strwidth 기반
     *
     * @param  string $field
     * @param  mixed  $value
     * @return bool
     */
    protected function validateStringWidthBetween($field, $value, $params){
        if(!is_string($value)){
            return false;
        }
        $width = mb_strwidth($value);
        return $params[0] <= $width && $width <= $params[1];
    }

    protected function validateKeyExists($field, $value, $params){
        if(!is_string($value) && !is_numeric($value)){
            return false;
        }
        return key_exists($value, $params[0]);
    }
}
