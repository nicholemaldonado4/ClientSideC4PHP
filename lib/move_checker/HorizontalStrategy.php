<?php
// Nichole Maldonado
// Extra Credit - HorizontalStrategy.php
// Oct 20, 2020
// Dr. Cheon, CS3360

require_once dirname(__DIR__) . "/game/Player.php";

/*
 * HorizontalStrategy has two points and moves the points horizontally.
 */
class HorizontalStrategy {
    protected int $leftBoundary;
    protected int $rightBoundary;
    protected array $pt1;
    protected array $pt2;

    /*
     * Sets points back to their original position.
     * @param: the $col and $row.
     * @return: None.
     */
    public function reset($col, $row){
        $this->setPoints($col, $row);
    }

    /*
     * Sets points.
     * @param: the $col and $row.
     * @return: None.
     */
    private function setPoints($col, $row) {
        $this->pt1 = array("x" => $col - 1, "y" => $row);
        $this->pt2 = array("x" => $col + 1, "y" => $row);
    }

    /*
     * Set the left and right boundary of the board. Set pt1 to the left of the column
     * and pt2 to the right of the col.
     * @param: The column and row of the piece to insert. The width of the board.
     * @return: None
     */
    function __construct($col, $row, $width) {
        $this->leftBoundary = max($col - 3, 0);
        $this->rightBoundary = min($col + 3, $width - 1);
        $this->setPoints($col, $row);
    }

    /*
     * Verify that both the point's x values are within the boundaries.
     * @param: None.
     * @return: True if in the range, false otherwise.
     */
    public function compareBoth() {
        return $this->comparePt1() && $this->comparePt2();
    }

    /*
     * Verify that the pt1's x values is within the boundary.
     * @param: None.
     * @return: True if in the range, false otherwise.
     */
    public function comparePt1() {
        return $this->pt1["x"] >= $this->leftBoundary;
    }

    /*
     * Verify that the pt2's x values is within the boundary.
     * @param: None.
     * @return: True if in the range, false otherwise.
     */
    public function comparePt2() {
        return $this->pt2["x"] <= $this->rightBoundary;
    }

    /*
     * Move pt1 and pt2.
     * @param: None.
     * @return: None.
     */
    public function updateBoth() {
        $this->updatePt1();
        $this->updatePt2();
    }

    /*
     * Move pt1 to the left.
     * @param: None.
     * @return: None.
     */
    public function updatePt1() {
        $this->pt1["x"]--;
    }

    /*
     * Move pt2 to the right.
     * @param: None.
     * @return: None.
     */
    public function updatePt2() {
        $this->pt2["x"]++;
    }

    /*
     * Get the game's token at pt1.
     * @param: None.
     * @return: None.
     */
    public function getFromPt1(array $board) {
        return $board[$this->pt1["y"]][$this->pt1["x"]]->getToken();
    }

    /*
     * Get the game's token at pt2.
     * @param: None.
     * @return: None.
     */
    public function getFromPt2(array $board) {
        return $board[$this->pt2["y"]][$this->pt2["x"]]->getToken();
    }

    /*
     * Checks to see if a non-empty token exists below $pt2.
     * @param: the $board.
     * @return: True if a non-empty token exists below $pt2, false otherwise.
     */
    function checkBelowPt2(array $board) {
        return $this->pt2["y"] + 1 >= sizeOf($board) ||
            $board[$this->pt2["y"] + 1][$this->pt2["x"]]->getToken() != Player::EMPTY;
    }

    /*
     * Checks to see if a non-empty token exists below $pt1.
     * @param: the $board.
     * @return: True if a non-empty token exists below $pt1, false otherwise.
     */
    function checkBelowPt1(array $board) {
        return $this->pt1["y"] + 1 >= sizeOf($board) ||
            $board[$this->pt1["y"] + 1][$this->pt1["x"]]->getToken() != Player::EMPTY;
    }

    /*
     * Checks to see if a non-empty token exists below $pt1 and $pt2.
     * @param: the $board.
     * @return: True if a non-empty token exists below $pt1 and $pt2, false otherwise.
     */
    function checkBelowBoth(array $board) {
        return $this->checkBelowPt1($board) && $this->checkBelowPt2($board);
    }
}