<?php
require_once __DIR__."/MoveStrategy.php";
require_once dirname(__DIR__)."/Game/Board.php";
require_once dirname(__DIR__)."/Result/Result.php";

class RegularStrategy extends MoveStrategy {
    private string $slot;

    function __construct($slot) {
        $this->slot = $slot;
    }

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