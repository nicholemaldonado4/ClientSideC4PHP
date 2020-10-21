<?php
// Nichole Maldonado
// Extra Credit - Precedence.php
// Oct 20, 2020
// Dr. Cheon, CS3360

/*
 * Maps counts of consecutive same colored pieces to a precedence number.
 *
 * Each precedence is defined as follows:
 * The following are all precedences for moves that could be made now. For
 * example, if a user was able to put x number of pieces in the board, they
 * could immediately win the game. (not fall through)
 * FOUR - four consecutive pieces in a row.
 * THREE - three consecutive pieces in a row.
 * TWO_BLOCK - two of opposers pieces in a row.
 * TWO - two pieces in a row.
 * ONE - one piece in a row.
 *
 * The following are precedences for moves that can be made, but another move
 * will be considered fall through.
 * THREE_FALL_THROUGH - three in a row, but not currently possible to add a 4th.
 * TWO_FALL_THROUGH - two in a row, but not currently possible to add two more.
 * TWO_BLOCK_FALL_THROUGH - block two from opponent, but if not blocked, the
 *                          opponent cannot make two more moves.
 * ONE_FALL_THROUGH - one in a row, but not currently possible to add three more.
 *
 * NONE - Special case, only when count is 0.
 */
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

    /*
     * Gets the precedence constant based on $count. If possible, then
     * the move will not fall-through, so focus on const values ONE to FOUR.
     * @param: The $count (expected to be 0 - 4) and possible which denotes
     *         the piece won't fall through so higher precedence.
     */
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