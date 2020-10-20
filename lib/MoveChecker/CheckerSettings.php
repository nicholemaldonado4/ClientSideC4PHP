<?php
require_once dirname(__DIR__)."/Game/Player.php";

/*
 * Stores a blockRequest and reply and the piece color
 * to place in the board.
 */
class CheckerSettings {
    private Player $player;
    private bool $blockRequest;
    private bool $blockReply;

    /*
     * Constructor that stores the $blockRequest, $blockReply, and $pieceColor.
     * @param: Whether or not we are requesting to see if a move will be blocking,
     *         the reply of this request, and piece color of the piece.
     * @return: None.
     */
    public function __construct($blockRequest, $blockReply, $player) {
        $this->player = $player;
        $this->blockRequest = $blockRequest;
        $this->blockReply = $blockReply;
    }

    /*
     * Setter for the field $blockRequest
     * @param: The $blockRequest to assign to the field.
     * @return: None.
     */
    public function setBlockRequest($blockRequest) {
        $this->blockRequest = $blockRequest;
    }

    /*
     * Setter for the field $blockReply
     * @param: The $blockReply to assign to the field.
     * @return: None.
     */
    public function setBlockReply($blockReply) {
        $this->blockReply = $blockReply;
    }

    /*
     * Getter for the field $blockRequest.
     * @param: None
     * @return: The boolean value of $blockRequest.
     */
    public function getBlockRequest() {
        return $this->blockRequest;
    }

    /*
     * Getter for the field $blockRequest.
     * @param: None
     * @return: The boolean value of $blockReply.
     */
    public function getBlockReply() {
        return $this->blockReply;
    }

    /*
     * Getter for the field $pieceColor.
     * @param: None
     * @return: The PieceColor assigned to $pieceColor.
     */
    public function getPlayer() {
        return $this->player;
    }
}