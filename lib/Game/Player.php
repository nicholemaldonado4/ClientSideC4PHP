<?php
// Nichole Maldonado
// Extra Credit - Player.php
// Oct 20, 2020
// Dr. Cheon, CS3360

/*
 * A player piece to store in the board. The player piece has a token
 * which can represent an empty piece, user piece or computer piece.
 * A piece is considered colored or highlighted if it will be represented
 * in a different color by the view.
 */
class Player {
    private string $token;
    private bool $coloredPiece = false;
    public const EMPTY = ".";
    public const COMPUTER = "0";
    public const PLAYER = "X";

    /*
     * Sets the token to "X".
     * @param: None.
     * @return:
     */
    function setPlayerToken() {
        $this->token = self::PLAYER;
    }

    /*
     * Sets token to "0".
     * @param: None.
     * @return: None.
     */
    function setCompToken() {
        $this->token = self::COMPUTER;
    }

    /*
     * Sets the token to ".".
     * @param: None.
     * @return: None.
     */
    function setEmptyToken() {
        $this->token = self::EMPTY;
    }

    /*
     * Creates an empty Player.
     * @param: None.
     * @return: A Player whose token is ".".
     */
    static function empty() {
        $player = new Player();
        $player->setEmptyToken();
        return $player;
    }

    /*
     * Creates a computer Player.
     * @param: None.
     * @return: A Player whose token is "0".
     */
    static function computer() {
        $player = new Player();
        $player->setCompToken();
        return $player;
    }

    /*
     * Creates a user Player.
     * @param: None.
     * @return: A Player whose token is "X".
     */
    static function user() {
        $player = new Player();
        $player->setPlayerToken();
        return $player;
    }

    /*
     * Getter for the field token.
     * @param: None.
     * @return: The token of the player piece.
     */
    function getToken() {
        return $this->token;
    }

    /*
     * Getter for the field coloredPiece.
     * @param: None.
     * @return: A boolean of whether the piece is colored or not.
     */
    function getColoredPiece() {
        return $this->coloredPiece;
    }

    /*
     * Setter for the field coloredPiece.
     * @param: The boolean of whether the piece should be colored or not.
     * @return: None.
     */
    function setColoredPiece($coloredPiece) {
        $this->coloredPiece = $coloredPiece;
    }

    /*
     * Toggles the token types between the user and computer.
     * @param: None.
     * @return: None.
     */
    function toggleToken() {
        $this->token = ($this->token === self::PLAYER) ? self::COMPUTER : self::PLAYER;
    }

    /*
     * Determines if the token is empty.
     * @param: None.
     * @return: True if the token is empty, false otherwise.
     */
    function isEmptyToken() {
        return $this->token === self::EMPTY;
    }
}