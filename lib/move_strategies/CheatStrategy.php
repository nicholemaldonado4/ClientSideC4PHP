<?php
// Nichole Maldonado
// Extra Credit - CheatStrategy.php
// Oct 20, 2020
// Dr. Cheon, CS3360

require_once __DIR__."/MoveStrategy.php";
require_once dirname(__DIR__) . "/exceptions/InformationMismatchException.php";
require_once dirname(__DIR__) . "/game/Board.php";
require_once dirname(__DIR__) . "/game/Player.php";
require_once dirname(__DIR__) . "/move_checker/CheckerSettings.php";
require_once dirname(__DIR__) . "/move_checker/MoveChecker.php";
require_once dirname(__DIR__) . "/move_checker/MoveRecords.php";
require_once dirname(__DIR__) . "/move_checker/Precedence.php";
require_once dirname(__DIR__) . "/result/Result.php";

/*
 * A strategy to determine the best slot for a user to move to. Selects
 * the best move based on [Precedence] and the priority of the records
 * stored in MoveRecords.
 */
class CheatStrategy extends MoveStrategy {

    /*
     * Determines if the user makes $col move on the $board, checks if computer will win
     * due to the move.
     * @param: The $moveChecker, $col to make the move, and $board.
     * @return: Returns false, if computer can win on the next move. Otherwise,
     *          returns true.
     */
    private function imaginaryCheck(MoveChecker $moveChecker, $col, Board $board) {
        $board->addMoves($col);
        $goodMove = true;

        // checkMove returns true, then the computer can win on the next move,
        // This is not a good move. If checkMove returns false, the computer
        // cannot move on the next move, so this is a good move.
        $precedence = $moveChecker->checkMove($col, new CheckerSettings(false, false, Player::computer()), $board);
        if ($precedence === Precedence::FOUR) {
            $goodMove = false;
        }
        $board->eraseMove($col);
        return $goodMove;
    }

    /*
     * Gets recommended move on the $board based on precedence of move.
     * @param: The $board
     * @return: result error with the recommended move as the error message.
     *          If no moves were found, an InputMismatchException is thrown.
     *          This would result in a unexpected behaviour.
     */
    function evaluateMoveSelection(Board $board) {
        // While searching for a winning move, keep track of if a user can be
        // blocked from winning. If unable to find a winning move, then use the
        // block move. If  unable to block, then move to noWin or default move.
        $records = new MoveRecords();
        $combos = range(0, $board->getWidth() - 1);
        $checkerSettings = new CheckerSettings(true, false, Player::user());
        $moveChecker = new MoveChecker();

        // Repeatedly look at columns for a winning move. Randomize the columns.
        for ($i = 0; $i < $board->getWidth(); $i++) {
            $randIndex = rand(0, $board->getWidth() - 1 - $i);
            $newHeight = $board->getHeightForCol($combos[$randIndex]) - 1;

            // Determine if piece can be placed there.
            if ($newHeight >= 0 && $board->getRows()[$newHeight][$combos[$randIndex]]->getToken() == PLAYER::EMPTY) {
                $precedence = $moveChecker->checkMove($combos[$randIndex], $checkerSettings, $board);

                // If a blocking move was requested and the block reply is true, then a
                // blocking move was found, so store it in case no winning moves exists.
                if ($checkerSettings->getBlockRequest() && $checkerSettings->getBlockReply()) {
                    $checkerSettings->setBlockReply(false);
                    if ($precedence == Precedence::THREE) {
                        $records->setBlock($combos[$randIndex], $precedence);
                        $checkerSettings->setBlockRequest(false);
                    }
                    else if ($precedence > $records->getBlock()->getPrecedence()) {
                        $records->setBlock($combos[$randIndex], $precedence);
                    }
                }
                // Otherwise, if the precedence is FOUR, then the move was a winning
                // move. Store the move and return.
                else if ($precedence == Precedence::FOUR) {
                    return Result::error("Winning move: ".($combos[$randIndex] + 1));
                }

                // If we have found a higher precedence non-win/block move, store it.
                else if ($precedence > $records->getNoWin()->getPrecedence()) {

                    // If  a blocking move and winning move have not been found, then store
                    // the move. In the worst case, this move will be recommended.
                    if ($newHeight > 0 && !$this->imaginaryCheck($moveChecker, $combos[$randIndex], $board)) {
                        if ($precedence > $records->getDefault()->getPrecedence()) {
                            $records->setDefault($combos[$randIndex], $precedence);
                        }
                    }
                    else {
                        $records->setNoWin($combos[$randIndex], $precedence);
                    }
                }
            }
            // Replace with last for O(1) deletions.
            $combos[$randIndex] = $combos[$board->getWidth() - 1 - $i];
            array_pop($combos);
        }

        // Block move has priority over no win move and default move.
        $recordNum = $records->getHighestPriorityRecord();
        if ($recordNum != -1) {
            return Result::error($records->recordDescription($recordNum));
        }
        throw new InformationMismatchException("The game server contained malformed data");
    }
}