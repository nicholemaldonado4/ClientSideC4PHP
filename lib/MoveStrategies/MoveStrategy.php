<?php
// Nichole Maldonado
// Extra Credit - MoveStrategy.php
// Oct 20, 2020
// Dr. Cheon, CS3360

require_once __DIR__."/CheatStrategy.php";
require_once __DIR__."/RegularStrategy.php";
require_once dirname(__DIR__)."/Game/Board.php";

/*
 * A basis for the RandomStrategy and CheatStrategy to allow computer to select move.
 */
abstract class MoveStrategy {

    /*
     * Creates a CheatStrategy or RegularStrategy based on slotStr.
     * @param: The string representation of the slot or cheat.
     * @return: A CheatStrategy if slotStr is cheat, otherwise
     *          a RandomStrategy..
     */
    static function createMoveStrategy($slotStr) {
        return (strtolower($slotStr) === "cheat") ? new CheatStrategy() :
                new RegularStrategy(trim($slotStr));
    }

    /*
     * Evaluates a move if provided, otherwise finds a cheat move.
     * @param: The board to make the move on.
     * @return: A Result. If isError, then need a new move.
     *          Otherwise, good move stored as the value.
     *          Throws an InformationMismatchException if board contains
     *          malformed data.
     */
    abstract function evaluateMoveSelection(Board $board);
}