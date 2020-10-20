<?php
class Result {
    private $value;
    private $error;

    function __construct($value, $error) {
        $this->error = $error;
        $this->value = $value;
    }

    static function error($error) {
        return new Result(null, $error);
    }

    static function value($value) {
        return new Result($value, null);
    }

    function getValue() {
        return $this->value;
    }

    function getError() {
        return $this->error;
    }

    function isError() {
        return $this->error !== null;
    }
}