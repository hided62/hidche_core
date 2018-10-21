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
}
