<?php
// Nichole Maldonado
// Extra Credit - RightDiagonalStrategy.php
// Oct 20, 2020
// Dr. Cheon, CS3360

require_once __DIR__."/HorizontalStrategy.php";
require_once __DIR__."/ResetPoint.php";
require_once __DIR__."/VerticalBoundary.php";

/*
 * RightDiagonalStrategy has a verticalBoundary to keep track of the board's vertical boundary.
 * Inherits from HorizontalStrategy points that it will use to move to the left diagonally.
 */
class RightDiagonalStrategy extends HorizontalStrategy {
    use ResetPoint;
    private VerticalBoundary $verticalBoundary;

    /*
     * Sets the y coordinates initially.
     * @param: None.
     * @return: None.
     */
    private function setInitialY() {
        $this->pt1["y"]--;
        $this->pt2["y"]++;
    }

    /*
     * Set all boundaries of the board and create the points. Since we are
     * moving to the right diagonally, the left point goes up in the board and
     * the right point starts lower in the board.
     * @param: The $col and $row of the piece to insert. The $width and $height of the board.
     * @return: None.
     */
    function __construct($col, $row, $width, $height) {
        parent::__construct($col, $row, $width);
        $this->verticalBoundary = new VerticalBoundary($row, $height);
        $this->setInitialY();
    }

    /*
     * Verify that pt1's x values is within the upper left boundary.
     * @param: None.
     * @return: True if in the range, false otherwise.
     */
    public function comparePt1() {
        return parent::comparePt1() && $this->pt1["y"] >= $this->verticalBoundary->getTopBoundary();
    }

    /*
     * Verify that pt1's x values is within the lower right boundary.
     * @param: None.
     * @return: True if in the range, false otherwise.
     */
    public function comparePt2() {
        return parent::comparePt2() && $this->pt2["y"] <= $this->verticalBoundary->getBottomBoundary();
    }

    /*
     * Move pt1 to the upper left.
     * @param: None.
     * @return: None.
     */
    public function updatePt1() {
        parent::updatePt1();
        $this->pt1["y"]--;
    }

    /*
     * Move pt2 to the lower right.
     * @param: None.
     * @return: None.
     */
    public function updatePt2() {
        parent::updatePt2();
        $this->pt2["y"]++;
    }
}