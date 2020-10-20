<?php
// Nichole Maldonado
// Lab 1 - MoveValidator
// September 7, 2020
// Dr. Cheon, CS3360
// Verifies if a move could result in a winning move, and if requested a blocking move.
// Also provides the functionality based to create a Move based on the move. Used to
// process moves.

require_once __DIR__."/HorizontalStrategy.php";
require_once __DIR__."/LeftDiagonalStrategy.php";
require_once __DIR__."/Precedence.php";
require_once __DIR__."/Record.php";
require_once __DIR__."/RightDiagonalStrategy.php";
require_once __DIR__."/CheckerSettings.php";
require_once dirname(__DIR__)."/Game/Board.php";

/*
 * Verifies if a move could result in a winning move, and if requested a blocking move.
 * Also provides the functionality based to create a Move based on the move.
 */
class MoveChecker {

    /*
     * Counts the number of $pieceColor pieces from the top of the $col down until at least three consecutive same
     * colored pieces are found or a different piece color is found.
     * @param: The number of same colored, consecutive pieces.
     * @return: None.
     */
    private function verticalMoveHelper($col, $token, Board $board) {
        $row = $board->getColHeights()[$col];
        $count = 1;

        // Count the number of same colored pieces until we find 3 or a different piece color.
        while ($row < $board->getHeight() && $count < 4 && $board->getRows()[$row][$col]->getToken() == $token) {
            $row++;
            $count++;
        }

        // Map to precedence.
        return Precedence::getPrecedenceFromCount($count,
                $this->roomAbove($board->getColHeights()[$col] - 1, $count));
    }

    private function roomAbove($row, $count) {
        return $row - (4 - $count) >= 0;
    }

    /*
     * Get the number of consecutive, same colored pieces that exist vertically down the column.
     * If a block is requested and the four consecutive, same colored pieces were not found,
     * recalls to see if four consecutive, user color pieces exist.
     * @param: The $board, $col, and $validatorSettings with the piece color.
     * @return: Returns the $count which maps to the precedence.
     */
    private function verticalMove($col, CheckerSettings $checkerSettings, Board $board) {
        $precedence = $this->verticalMoveHelper($col, $checkerSettings->getPlayer()->getToken(), $board);

        // If the precedence was 1, then we know that the first piece down did not match the pieceColor. If a block
        // was requested, check to see a column of three user color pieces exist.
        if ($precedence <= Precedence::ONE && $checkerSettings->getBlockRequest()) {
            $checkerSettings->getPlayer()->toggleToken();
            $blockPrecedence = $this->verticalMoveHelper($col, $checkerSettings->getPlayer()->getToken(), $board);
            $checkerSettings->getPlayer()->toggleToken();

            // If three consecutive, user color pieces were found, set BlockReply to true.
            if ($blockPrecedence == Precedence::FOUR) {
                $checkerSettings->setBlockReply(true);
            }
            else if ($blockPrecedence == Precedence::THREE && $precedence < $blockPrecedence) {
               $precedence = Precedence::TWO_BLOCK;
            }
        }
        return $precedence;
    }

    /*
     * Radiates outward in a direction specified by $moveStrat.
     * @param: The board, piece color, and the move strategy that will be applied.
     * @return: The count of consecutive same colored pieces.
     */
    private function rippleCounter($token, HorizontalStrategy $moveStrat, Board $board,
            $count, $fallThrough) {

        // Radiate outwards looking for same colored pieces.
        while ($moveStrat->compareBoth() &&
                $moveStrat->getFromPt1($board->getRows()) == $moveStrat->getFromPt2($board->getRows()) &&
                $moveStrat->getFromPt1($board->getRows()) == $token && $count < 4 &&
                ($fallThrough ? $moveStrat->checkBelowBoth($board->getRows()) : true)) {
            $moveStrat->updateBoth();
            $count += 2;
        }
        if ($count >= 4) {
            return 4;
        }

        // If we can evaluate the pieces more to the left or more to the right, then do so.
        while ($moveStrat->comparePt1() && $moveStrat->getFromPt1($board->getRows()) == $token &&
                $count < 4 && ($fallThrough ? $moveStrat->checkBelowPt1($board->getRows()) : true)) {
            $moveStrat->updatePt1();
            $count++;
        }
        while ($moveStrat->comparePt2() && $moveStrat->getFromPt2($board->getRows()) == $token &&
             $count < 4 && ($fallThrough ? $moveStrat->checkBelowPt2($board->getRows()) : true)) {
            $moveStrat->updatePt2();
            $count++;
        }
        return min($count, 4);
    }

    private function rippleMoveHelper($token, HorizontalStrategy $moveStrat, Board $board) {
        $count = $this->rippleCounter($token, $moveStrat, $board, 1, false);
        if ($count == 4) {
            return Precedence::FOUR;
        }
        $newCount = $this->rippleCounter(".", $moveStrat, $board, $count, true);
        return Precedence::getPrecedenceFromCount($count, $newCount === 4);
    }

    /*
     * Get the number of consecutive, same colored pieces that exist based on the move strategy.
     * If a block is requested and four consecutive, same colored pieces were not found,
     * recalls to see if four consecutive, user color pieces exist.
     * @param: The $board, $col, $validatorSettings with the piece color, and $moveStrat.
     * @return: Returns the $count which maps to the precedence.
     */
    private function rippleMove(CheckerSettings $checkerSettings, HorizontalStrategy $moveStrat,
                                 $col, Board $board) {
        $precedence = $this->rippleMoveHelper($checkerSettings->getPlayer()->getToken(), $moveStrat, $board);

        //  If a block was requested and we could not find a regular win move,
        // check to see a block move could occur.
        if ($precedence < Precedence::FOUR && $checkerSettings->getBlockRequest()) {
            $checkerSettings->getPlayer()->toggleToken();
            $moveStrat->reset($col, $board->getColHeights()[$col] - 1);
            $blockPrecedence = $this->rippleMoveHelper($checkerSettings->getPlayer()->getToken(), $moveStrat, $board);
            $checkerSettings->getPlayer()->toggleToken();

            // If found a move, set block reply to true.
            if ($blockPrecedence == Precedence::FOUR) {
                $checkerSettings->setBlockReply(true);
            }

            // If the count is three, then we know that the user is trying to build a set of three.
            // So set the precedence based on whether the next move would be a fall through.
            else if ($blockPrecedence == Precedence::THREE && $precedence < $blockPrecedence) {
                $precedence = Precedence::TWO_BLOCK;
            }
        }
        return $precedence;
    }

    private function evaluateMove($checkerSettingsCopy, &$maxBlockPrecedence, &$maxPrecedence, $precedence) {
        if ($checkerSettingsCopy->getBlockRequest() && $checkerSettingsCopy->getBlockReply()) {
            if ($precedence == Precedence::THREE) {
                $checkerSettingsCopy->setBlockRequest(false);
            }
            $maxBlockPrecedence = max($maxBlockPrecedence, $precedence);
            $checkerSettingsCopy->setBlockReply(false);
        }
        else if ($precedence >= Precedence::FOUR) {
            return Precedence::FOUR;
        }
        else {
            $maxPrecedence = max($maxPrecedence, $precedence);
        }
        return null;
    }

    /*
     * Validates if a piece added at $col will lead to win. If a block is requested and found, then
     * will reply that the move will result in a block, if the move will not result in a win.
     * @param: None.
     * @return: [direction, start] if direction != NONE. Means that the move is a winning move.
     *          start denotes where we should populate the row of winning col,row pairs.
     *          Otherwise returns [direction, record]. If the direction == NONE and $vaidatorSettings'
     *          blockReply is true, then the move will result in a block. If $validatorSettings'
     *          blockReply is false and direction == NONE, then the move will not result in a move or a
     *          block. In all instances, if direction != NONE, then the move will result in a win, in the
     *           returned direction. start denotes where we should populate the row of winning col,row pairs.
     */
    function checkMove($col, CheckerSettings $checkerSettings, Board $board) {
        $maxBlockPrecedence = Precedence::NONE;
        $maxPrecedence = Precedence::NONE;
        $checkerSettingsCopy = clone $checkerSettings;

        // Check vertical.
        // If block found, and precedence is three, don't look for anymore blocks. Else just store if
        // largest block precedence.
        $precedence = $this->verticalMove($col, $checkerSettingsCopy, $board);
        $precedence = $this->evaluateMove($checkerSettingsCopy, $maxBlockPrecedence, $maxPrecedence, $precedence);
        if ($precedence !== null) {
            return $precedence;
        }

        $moves = array("HorizontalStrategy",
            "LeftDiagonalStrategy",
            "RightDiagonalStrategy");

        // For remaining directions, we do a ripple effect. Start at the piece and radiate out.
        foreach ($moves as $strategy) {
            // If the move is a winning move, return the direction.
            $moveStrat = new $strategy($col, $board->getColHeights()[$col] - 1, $board->getWidth(), $board->getHeight());
            $precedence = $this->rippleMove($checkerSettingsCopy, $moveStrat, $col, $board);
            $precedence = $this->evaluateMove($checkerSettingsCopy, $maxBlockPrecedence, $maxPrecedence, $precedence);
            if ($precedence !== null) {
                return $precedence;
            }
        }

        // If a block was found and a winning move was not found, then set reply.
        if ($maxBlockPrecedence > Precedence::NONE) {
            $checkerSettings->setBlockReply(true);
            $maxPrecedence = $maxBlockPrecedence;
        }

        // Winning move not found. A block move was found however, if blockReply is true.
        return $maxPrecedence;
    }
}