<?php
// Nichole Maldonado
// Lab 1 - SmartStrategy
// September 7, 2020
// Dr. Cheon, CS3360
// Evaluates putting a piece at the top of each column. Tries to make a winning move first. If one does not exist,
// then makes a blocking move. If neither exists, then just makes a regular move.

require_once __DIR__."/MoveStrategy.php";
require_once dirname(__DIR__)."/Game/Board.php";
require_once dirname(__DIR__)."/MoveChecker/MoveChecker.php";
require_once dirname(__DIR__)."/MoveChecker/CheckerSettings.php";
require_once dirname(__DIR__)."/Game/Player.php";
require_once dirname(__DIR__)."/MoveChecker/MoveRecords.php";
require_once dirname(__DIR__)."/MoveChecker/Precedence.php";
require_once dirname(__DIR__)."/Result/Result.php";
require_once dirname(__DIR__)."/Exceptions/InformationMismatchException.php";

/*
 * Evaluates putting a piece at the top of each column. If the computer can win, makes this move. If
 * the computer cannot win but can block the user from winning (user currently has 3 pieces in a row), then the
 * computer makes a blocking move. If the computer cannot make a winning or blocking move, then the computer
 * just moves to a random location.
 */
class SmartStrategy extends MoveStrategy {

    /*
     * Add-on of the Smart strategy. Determines if we put the piece in $col, will the user
     * be able to win on the next game.
     * @param: The $moveValidator, $origHeight to put the piece, the $col, and $game.
     * @return: False if placing a piece in column will allow the user to win next time.
     *          True otherwise.
     */
    private function imaginaryCheck(MoveChecker $moveChecker, $col, Board $board) {
        $board->addMoves($col);
        $goodMove = true;

        // See that if the computer does put a piece in col, if the user will be
        // able to make a winning move above it.
        $precedenece = $moveChecker->checkMove($col, new CheckerSettings(false, false, Player::computer()), $board);
        if ($precedenece === Precedence::FOUR) {
            $goodMove = false;
        }
        $board->eraseMove($col);
        return $goodMove;
    }

    /*
     * Randomly selects columns to see if they can result in a winning move. While doing so saves
     * block move, no win move, or default move. See MoveRecords for more details.
     * @param: The Game, which contains the board, the current MoveValidator to validate the move,
     *          and the Move $compMove which will store the slot, whether the move was a winning move, a
     *          drawing move, and if the move was a winning move the row for the connected 4 pieces.
     * @return: True if a Move was able to be populated. May return false in the event that a file was tampered
     *          with. For example, the game is missing one piece by the time it is the computer's turn. However,
     *          in between calls, someone tampered the game file and added the last piece. In which case the computer
     *          would realize that all the spots were filled even though at least one empty spot was expected.
     */
    function evaluateMoveSelection(Board $board) {

        // While searching for a winning move, we also keep track of if we can block a user from winning. If we
        // unable to find a winning move, then we use the block move. If we are unable to block, then we just
        // move to any location that we found.
        $records = new MoveRecords();
        $combos = range(0, $board->getWidth() - 1);
        $checkerSettings = new CheckerSettings(true, false, Player::user());
        $moveChecker = new MoveChecker();

        // Repeatedly look at columns for a winning move. Randomize the columns we search for, but unlike random,
        // we will look at all the columns unless we find a winning move.
        for ($i = 0; $i < $board->getWidth(); $i++) {
            $randIndex = rand(0, $board->getWidth() - 1 - $i);
            $newHeight = $board->getHeightForCol($combos[$randIndex]) - 1;

            // Evaluate the position if we can put a piece there.
            if ($newHeight >= 0 && $board->getRows()[$newHeight][$combos[$randIndex]]->getToken() == ".") {
                $precedence = $moveChecker->checkMove($combos[$randIndex], $checkerSettings, $board);

                // Found blocking move.
                if ($checkerSettings->getBlockRequest() && $checkerSettings->getBlockReply()) {
                    $checkerSettings->setBlockReply(false);

                    // Found block move of the highest precedence so stop all other move requests.
                    if ($precedence == Precedence::THREE) {
                        $records->setBlock($combos[$randIndex], $precedence);
                        $checkerSettings->setBlockRequest(false);
                    }
                    // Otherwise, store higher precedence block move.
                    else if ($precedence > $records->getBlock()->getPrecedence()) {
                        $records->setBlock($combos[$randIndex], $precedence);
                    }
                }
                // If the direction is not NONE, then the move was a winning move. Make the move and return.
                else if ($precedence == Precedence::FOUR) {
                    return Result::error("Winning move: ".($combos[$randIndex] + 1));
                }

                // If we have found a higher precedence non-win/block move, store it.
                else if ($precedence > $records->getNoWin()->getPrecedence()) {

                    // If user can't win by placing the piece here, then store in noWin, otherwise, set as
                    // default.
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