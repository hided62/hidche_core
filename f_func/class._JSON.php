<?php

class _JSON {
    public static function Encode($arr) {
        foreach($arr as $key => $val) {
            $key = _JSON::AddSlashes($key);
            if(is_array($val) == true) {
                $val = json_encode($val);
            } else {
                $val = _JSON::AddSlashes($val);
            }
            $item[] = "\"{$key}\":\"{$val}\"";
        }
        $encoded = '{' . implode(',', $item) . '}';
        return $encoded;
    }

    public static function Decode($encoded) {
        $decoded = substr($encoded, 2, strlen($encoded)-4);
        $len = strlen($decoded);
        $s = 0; $e = 0;
        while($s < $len) {
            $e = strpos($decoded, '":"', $s);
            $key = substr($decoded, $s, $e-$s);
            $s = $e + 3;
            $e = strpos($decoded, '","', $s);
            if($e == false) $e = $len;
            $val = substr($decoded, $s, $e-$s);

            if(substr($val, 0, 2) != '{"') {
                $s = $e + 3;
            } else {
                $e = strpos($decoded, '"}"', $s) + 2;
                $val = substr($decoded, $s, $e-$s);
                $s = $e + 3;

                $val = json_decode($val);
            }
            $result[$key] = $val;
        }
        return $result;
    }

    public static function AddSlashes($str) {
        $str = str_replace("\\", "\\\\", $str);
        $str = str_replace("\"", "\\\"", $str);
        $str = str_replace("'", "\\'", $str);
        $str = str_replace("\r\n", "\\n", $str);
        $str = str_replace("\n", "\\n", $str);

        return $str;
    }
}

?>
