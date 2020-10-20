<?php
class Precedence {
    public const FOUR = 9;
    public const THREE = 8;
    public const TWO_BLOCK = 7;
    public const TWO = 6;
    public const ONE = 5;
    public const THREE_FALL_THROUGH = 4;
    public const TWO_FALL_THROUGH = 3;
    public const TWO_BLOCK_FALL_THROUGH = 2;
    public const ONE_FALL_THROUGH = 1;
    public const NONE = 0;

    static function getPrecedenceFromCount(int $count, bool $possible) {
        if ($count <= 0) {
            return self::NONE;
        }
        if ($count >= 4) {
            return self::FOUR;
        }
        $precedence = null;
        switch($count) {
            case 1:
                $precedence = ($possible) ? self::ONE : $count;
                break;
            case 2:
                $precedence = ($possible) ? self::TWO : self::TWO_FALL_THROUGH;
                break;
            default:
                $precedence = ($possible) ? self::THREE : self::THREE_FALL_THROUGH;
        }
        return $precedence;
    }
}