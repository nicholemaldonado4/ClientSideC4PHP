<?php
// Nichole Maldonado
// Extra Credit - MoveChecker.php
// Oct 20, 2020
// Dr. Cheon, CS3360

require_once __DIR__."/CheckerSettings.php";
require_once __DIR__."/HorizontalStrategy.php";
require_once __DIR__."/LeftDiagonalStrategy.php";
require_once __DIR__."/Precedence.php";
require_once __DIR__."/Record.php";
require_once __DIR__."/RightDiagonalStrategy.php";
require_once dirname(__DIR__)."/Game/Board.php";
require_once dirname(__DIR__)."/Game/Player.php";

/*
 * A checker that determines the precedence of a move.
 * Checks to see if a winning move could occur with a move. If a winning
 * move is not found, then returns the highest Precedence of the move.
 */
class MoveChecker {

    /*
     * Precedence of token piece from the top of the $col, downwards.
     * Continues to count until at least three consecutive tokens are found
     * or a different token is found.
     * @param: The $col to evaluate, the $token, and $board.
     * @return: The precedence based on the number of same token,
     *          consecutive pieces.
     */
    private function verticalMoveHelper($col, $token, Board $board) {
        $row = $board->getColHeights()[$col];
        $count = 1;

        // Count the number of same colored pieces until we find 4 or a different piece color.
        while ($row < $board->getHeight() && $count < 4 && $board->getRows()[$row][$col]->getToken() == $token) {
            $row++;
            $count++;
        }
        return Precedence::getPrecedenceFromCount($count,
                $this->roomAbove($board->getColHeights()[$col] - 1, $count));
    }

    /*
     * Verifies if there is a space to put a piece above the row.
     * @param: $count which is the number of same colored pieces and
     *         $row is the top of these consecutive pieces.
     * @return: True if there is room to pieces above the row.
     *          false otherwise.
     */
    private function roomAbove($row, $count) {
        return $row - (4 - $count) >= 0;
    }

    /*
     * Gets the Precedence of the token piece based on a vertical move.
     * Precedence is based on the number of tokens in the $col of the board.
     * If a block is requested and the four consecutive, same token
     * pieces were not found, recalls to see if four consecutive, user tokens
     * exist.
     * @param: The $col to put the piece, the $checkerSettings with the
     *        token color and block request/reply, and the $board.
     * @return: The precedence of placing the token at $col.
     */
    private function verticalMove($col, CheckerSettings $checkerSettings, Board $board) {
        $precedence = $this->verticalMoveHelper($col, $checkerSettings->getPlayer()->getToken(), $board);

        // If the $precedence was 0, then we know that the first piece down did
        // not match the token. If a block was requested, check to see a column of
        // three computer color pieces exist.
        if ($precedence <= Precedence::ONE && $checkerSettings->getBlockRequest()) {
            $checkerSettings->getPlayer()->toggleToken();
            $blockPrecedence = $this->verticalMoveHelper($col, $checkerSettings->getPlayer()->getToken(), $board);
            $checkerSettings->getPlayer()->toggleToken();

            // If four consecutive, user color pieces were found, set BlockReply to
            // true.
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
     * Counts the number of same token pieces that are the same as $token in
     * the $board. $count is the starting count and if $fallThrough is true,
     * we check to see if pieces exist under the current point. Useful if
     * token is the empty piece.
     * @param: The token representation, the move strategy, board, and whether we check
     *         to see if a piece exists below the current piece (Piece can be any type except empty).
     * @return: the count in range 1 to 4.
     */
    private function rippleCounter($token, HorizontalStrategy $moveStrat, Board $board,
            $count, $fallThrough) {

        // Radiates outwards looking for same $token pieces.
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

    /*
     * Gets the precedence of the placing the $token on the $board.
     * The placement is evaluated based on the type of $moveStrat.
     * @param: The $token representation, move strategy, and $board.
     * @return: The precedence of the move.
     */
    private function rippleMoveHelper($token, HorizontalStrategy $moveStrat, Board $board) {
        $count = $this->rippleCounter($token, $moveStrat, $board, 1, false);
        if ($count == 4) {
            return Precedence::FOUR;
        }
        $newCount = $this->rippleCounter(Player::EMPTY, $moveStrat, $board, $count, true);
        return Precedence::getPrecedenceFromCount($count, $newCount == 4);
    }

    /*
     * Gets the Precedence of token pieces based on the $moveStrat.
     * If a block is requested and four consecutive, same $token pieces were not
     * found in the $board, recalls to see if four consecutive, user pieces
     * exist. If a block is requested in $checkerSettings and a block is
     * found the block reply is set to true.
     * @param: $checkerSettings which stores the token, the move strategy, $col
     *         to put the piece and the $board.
     * @return: The precedence.
     */
    private function rippleMove(CheckerSettings $checkerSettings, HorizontalStrategy $moveStrat,
            $col, Board $board) {
        $precedence = $this->rippleMoveHelper($checkerSettings->getPlayer()->getToken(), $moveStrat, $board);

        // If the $precedence was 0, then we know that the first piece down did
        // not match the token. If a block was requested, check to see a
        // column of four computer color pieces exist.
        if ($precedence < Precedence::FOUR && $checkerSettings->getBlockRequest()) {
            $checkerSettings->getPlayer()->toggleToken();
            $moveStrat->reset($col, $board->getColHeights()[$col] - 1);
            $blockPrecedence = $this->rippleMoveHelper($checkerSettings->getPlayer()->getToken(), $moveStrat, $board);
            $checkerSettings->getPlayer()->toggleToken();

            // If four consecutive, computer tokens were found, set block reply to true.
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
     * Checks the $checkerSettingsCopy and sets the $maxPrecedence and $maxBlockPrecedence based on the
     * $precedence and whether a block was round.
     * @param: The $checkerSettingsCopy, $maxBlockPrecedence, $maxPrecedence, and $precedence.
     * @return: Precedence.FOUR if the precedence was greater than or equal to the value. Null otherwise.
     */
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
     * Determines the precedence of a piece added at $col in the $board.
     * If a block is requested and found, then will reply that the move will
     * result in a block, if the move will not result in a win.
     * @param: $checkerSettings which maintains the token color, block
     *         request, and block repl, the $col to put the piece, and
     *         $board.
     * @return: The max precedence of a move at $col.
     */
    function checkMove($col, CheckerSettings $checkerSettings, Board $board) {
        $maxBlockPrecedence = Precedence::NONE;
        $maxPrecedence = Precedence::NONE;
        $checkerSettingsCopy = clone $checkerSettings;

        // Check vertical. If the move is a winning move, then precedence is four
        // so return it.
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