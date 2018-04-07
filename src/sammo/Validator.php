<?php
namespace sammo;

/**
 * @method \sammo\Validator rule(string, array<string>|string, null|int|string|array<int|string> $option=null)
 */
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
