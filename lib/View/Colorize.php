<?php
// Nichole Maldonado
// Extra Credit - Colorize.php
// Oct 20, 2020
// Dr. Cheon, CS3360

/*
 * Class that returns colorized strings.
 */
class Colorize {

    /*
     * Static method that returns the $token in red.
     * @param: None
     * @return: None
     */
    static function red($token) {
        return self::getColoredStr($token, "0;31");
    }

    /*
     * Static method that returns the $token in green.
     * @param: None
     * @return: None
     */
    static function green($token) {
        return self::getColoredStr($token, "0;32");
    }

    /*
     * Static method that returns the $token in blue.
     * @param: None
     * @return: None
     */
    static function blue($token) {
        return self::getColoredStr($token, "0;34");
    }

    /*
     * Static method that returns the $token in the provided $color.
     * @param: None
     * @return: None
     */
    private static function getColoredStr($token, $color) {
        return "\033[".$color."m".$token."\033[0m";
    }
}