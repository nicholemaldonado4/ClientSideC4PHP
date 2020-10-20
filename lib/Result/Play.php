<?php
require_once dirname(__DIR__)."/Game/GameStatus.php";

class Play {
    private int $playerMove;
    private int $compMove = -1;
    private int $gameStatus = GameStatus::NONE;
    private array $row;

    function getPlayerMove() {
        return $this->playerMove;
    }

    function getCompMove() {
        return $this->compMove;
    }

    function getGameStatus() {
        return $this->gameStatus;
    }

    function getRow() {
        return $this->row;
    }

    /// Sets [_gameStatus] to draw if [decodedJsonMoveType]'s isDraw is true.
    ///
    /// [decodedJsonMoveType] is decoded Json data with a isDraw field. If isDraw
    /// returns true, otherwise false.
    function isDraw($decodedJsonMoveType) {
        if ($decodedJsonMoveType->isDraw) {
            $this->gameStatus = GameStatus::DRAW;
            return true;
        }
        return false;
    }

/// Sets [_gameStatus] to win if [decodedJsonMoveType]'s isWin is true.
///
/// [decodedJsonMoveType] is decoded Json data with a isWin and row field. If
/// isWin, then sets [_row] to the row. If isWin, returns true, otherwise
/// false.
    function isWin($decodedJsonMoveType) {
        if ($decodedJsonMoveType->isWin) {
            $this->row = $decodedJsonMoveType->row;
          return true;
        }
        return false;
    }

  /// Constructor that creates a Play object with game move information.
  ///
  /// Stores [decodedJson] which is decoded JSON data. Assume [decodedJson]
  /// has the mentioned fields.
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