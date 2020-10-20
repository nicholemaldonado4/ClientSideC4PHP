<?php
require_once __DIR__."/Player.php";

class Board {
    private int $height;
    private int $width;
    private array $rows;
    private array $colHeights;

    function getHeight() {
        return $this->height;
    }

    function getWidth() {
        return $this->width;
    }

    function getRows() {
        return $this->rows;
    }

    function getColHeights() {
        return $this->colHeights;
    }

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

    function getHeightForCol($col) {
        return $this->colHeights[$col];
    }

    function addMoves($playerCol, $compCol = -1) {
        $this->rows[--$this->colHeights[$playerCol]][$playerCol]->setPlayerToken();
        if ($compCol !== -1) {
            $this->rows[--$this->colHeights[$compCol]][$compCol]->setCompToken();
        }
    }

    function eraseMove($playerCol) {
        $this->rows[$this->colHeights[$playerCol]++][$playerCol]->setEmptyToken();
    }

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