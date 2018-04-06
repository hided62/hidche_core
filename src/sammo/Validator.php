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
}
