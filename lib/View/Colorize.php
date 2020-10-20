<?php
class Colorize {
    static function red($token) {
        return self::getColoredStr($token, "0;31");
    }
    static function green($token) {
        return self::getColoredStr($token, "0;32");
    }
    static function blue($token) {
        return self::getColoredStr($token, "0;34");
    }

    private static function getColoredStr($token, $color) {
        return "\033[".$color."m".$token."\033[0m";
    }
}