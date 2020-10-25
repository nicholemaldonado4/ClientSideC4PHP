<?php
// Nichole Maldonado
// Extra Credit - RegularStrategy.php
// Oct 20, 2020
// Dr. Cheon, CS3360

require_once __DIR__."/MoveStrategy.php";
require_once dirname(__DIR__) . "/game/Board.php";
require_once dirname(__DIR__) . "/result/Result.php";

/*
 * A strategy to determine if a piece can be placed in the provided slot.
 * Expects the provided slot to be a string that is mapped as a 1 - based
 * value. Since the board starts at index (0, 0), will check the slot a
 */
class RegularStrategy extends MoveStrategy {
    private string $slot;

    /*
     * Creates a RegularStrategy with the slot set.
     * @param: The $slot to evaluate.
     * @return: None.
     */
    function __construct($slot) {
        $this->slot = $slot;
    }

    /*
     * Determines if slot exists in the board.
     * @param: The $board to see if the slot exists.
     * @return: The result with the integral representation of the slot
     *          field. Otherwise, the result with the error message
     *          is returned.
     */
    function evaluateMoveSelection(Board $board) {
        $errorMsg = <<< ErrorMsg
Invalid slot: {$this->slot}. Slot must be in range [1,{$board->getWidth()}].
Please try again
ErrorMsg;
        if (!is_numeric($this->slot)) {
            return Result::error($errorMsg);
        }
        $slotNum = intval($this->slot) - 1;
        if ($slotNum < 0 || $slotNum >= $board->getWidth()) {
            return Result::error($errorMsg);
        }
        if ($board->getHeightForCol($slotNum) < 1) {
            return Result::error("This column is already full.\r\nPlease try again.\r\n");
        }
        return Result::value($slotNum);
    }
}