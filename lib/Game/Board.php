<?php
// Nichole Maldonado
// Extra Credit - Board.php
// Oct 20, 2020
// Dr. Cheon, CS3360

require_once __DIR__."/Player.php";

/*
 * A game board to hold pieces. The board holds width x height Player pieces
 * which denote the piece type. The heights of each column is stored in colHeights.
 */
class Board {
    private int $height;
    private int $width;
    private array $rows;
    private array $colHeights;

    /*
     * Getter for the $height of the board.
     * @param: None.
     * @return: The height of the board.
     */
    function getHeight() {
        return $this->height;
    }

    /*
     * Getter for the $width of the board.
     * @param: None.
     * @return: The width of the board.
     */
    function getWidth() {
        return $this->width;
    }

    /*
     * Getter for the field $rows of the board.
     * @param: None.
     * @return: The rows of the board.
     */
    function getRows() {
        return $this->rows;
    }

    /*
     * Getter for the number of slots available in the board.
     * @param: None.
     * @return: The 1D array of column heights.
     */
    function getColHeights() {
        return $this->colHeights;
    }

    /*
     * Creates a board of $width by $height filled with
     * empty Player pieces.
     * @param: The width and height of the board.
     * @return: None.
     */
    function __construct($width, $height) {
        $this->width = $width;
        $this->height = $height;
        $this->rows = array();
        for ($i = 0; $i < $this->height; $i++) {
            $this->rows[] = array();
            for ($j = 0; $j < $this->width; $j++) {
                $this->rows[$i][] = Player::empty();
            }
        }
        $this->colHeights = array_fill(0, $this->width, $this->height);
    }

    /*
     * The height for a specific column.
     * @param: The $col in the board. Assume $col is within
     *         the range of the board.
     * @return: The height at the $col.
     */
    function getHeightForCol($col) {
        return $this->colHeights[$col];
    }

    /*
     * Adds a player piece to the board at $playerCol and a computer piece
     * to compCol if $compCol is not -1.
     * @param: The columns of the pieces.
     * @return: None.
     */
    function addMoves($playerCol, $compCol = -1) {
        $this->rows[--$this->colHeights[$playerCol]][$playerCol]->setPlayerToken();
        if ($compCol !== -1) {
            $this->rows[--$this->colHeights[$compCol]][$compCol]->setCompToken();
        }
    }

    /*
     * Removes a piece from $playerCol.
     * @param: The column where the piece resides.
     * @return: None.
     */
    function eraseMove($playerCol) {
        $this->rows[$this->colHeights[$playerCol]++][$playerCol]->setEmptyToken();
    }

    /*
     * Sets the Player pieces denoted by $row to be colored.
     * $row must be an even length array that contains column,
     * row entries of pieces on the board that are not
     * empty.
     * @param: The array of column,row entries.
     * @return: False if $row does not meet the specifications.
     *          Otherwise true and the pieces are set as
     *          colored.
     */
    function changeColors(array $row) {
        $size = sizeOf($row);
        for ($i = 0; $i < $size; $i += 2) {
            if ($row[$i] < 0 || $row[$i] >= $this->width || $row[$i + 1] < 0 ||
                    $row[$i + 1] >= $this->height || $this->rows[$row[$i + 1]][$row[$i]]->isEmptyToken()) {
                return false;
            }
            $this->rows[$row[$i + 1]][$row[$i]]->setColoredPiece(true);
        }
        return true;
    }
}