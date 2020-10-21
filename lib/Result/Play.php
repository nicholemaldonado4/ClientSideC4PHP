<?php
// Nichole Maldonado
// Extra Credit - Play.php
// Oct 20, 2020
// Dr. Cheon, CS3360

require_once dirname(__DIR__)."/Game/GameStatus.php";

/*
 * A container for Connect 4 game move information. Stores the player
 * move, computer move, status of the game, and row of a winning game.
 * Populates field based on Json data which is checked first by the
 * ResponseParser.
 */
class Play implements GameStatus {
    private int $playerMove;
    private int $compMove = -1;
    private int $gameStatus = GameStatus::NONE;
    private array $row;

    /*
     * Getter for the field $playerMove.
     * @param: None.
     * @return: The column of the board to put a user piece.
     */
    function getPlayerMove() {
        return $this->playerMove;
    }

    /*
     * Getter for the field $compMove.
     * @param: None.
     * @return: The column of the board to put a computer piece.
     */
    function getCompMove() {
        return $this->compMove;
    }

    /*
     * Getter for the field gameStatus.
     * @param: None.
     * @return: The status of the game.
     */
    function getGameStatus() {
        return $this->gameStatus;
    }

    /*
     * Getter for the field $row.
     * @param: None.
     * @return: The row of a winning set of 4 pieces.
     */
    function getRow() {
        return $this->row;
    }

    /*
     * Sets $gameStatus to draw if $decodedJsonMoveType's isDraw is true.
     * @param: The json data with field isDraw.
     * @return: True if isDraw, false otherwise.
     */
    function isDraw($decodedJsonMoveType) {
        if ($decodedJsonMoveType->isDraw) {
            $this->gameStatus = GameStatus::DRAW;
            return true;
        }
        return false;
    }

    /*
     * Sets $gameStatus to win if $decodedJsonMoveType's isWin is true.
     * @param: The json data with field isWin.
     * @return: True if isWin, false otherwise.
     */
    function isWin($decodedJsonMoveType) {
        if ($decodedJsonMoveType->isWin) {
            $this->row = $decodedJsonMoveType->row;
          return true;
        }
        return false;
    }

    /*
     * Constructor that creates a Play object with game move information.
     * Assume $decodedJson has the mentioned fields.
     * @param: $decodedJson which contains the JSON data.
     * @return: None.
     */
    function __construct($decodedJson) {
        $this->playerMove = $decodedJson->ack_move->slot;
        if ($this->isDraw($decodedJson->ack_move)) {
            return;
        }
        if ($this->isWin($decodedJson->ack_move)) {
            $this->gameStatus = GameStatus::WON;
            return;
        }
        $this->compMove = $decodedJson->move->slot;
        if ($this->isDraw($decodedJson->move)) {
            return;
        }
        if ($this->isWin($decodedJson->move)) {
            $this->gameStatus = GameStatus::LOST;
        }
    }
}