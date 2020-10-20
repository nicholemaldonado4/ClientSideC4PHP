<?php
class Player {
    private string $token;
    private bool $coloredPiece = false;

    function setPlayerToken() {
        $this->token = "X";
    }

    function setCompToken() {
        $this->token = "0";
    }

    function setEmptyToken() {
        $this->token = ".";
    }

    static function empty() {
        $player = new Player();
        $player->setEmptyToken();
        return $player;
    }

    static function computer() {
        $player = new Player();
        $player->setCompToken();
        return $player;
    }

    static function user() {
        $player = new Player();
        $player->setPlayerToken();
        return $player;
    }

    function getToken() {
        return $this->token;
    }

    function getColoredPiece() {
        return $this->coloredPiece;
    }

    function setColoredPiece($coloredPiece) {
        $this->coloredPiece = $coloredPiece;
    }

    function toggleToken() {
        $this->token = ($this->token === 'X') ? '0' : 'X';
    }

    function isEmptyToken() {
        return $this->token === '.';
    }
}