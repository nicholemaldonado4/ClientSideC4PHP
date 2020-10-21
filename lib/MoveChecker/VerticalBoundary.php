<?php
// Nichole Maldonado
// Extra Credit - VerticalBoundary.php
// Oct 20, 2020
// Dr. Cheon, CS3360

/*
 * Keeps track of the top and bottom boundary that are 3 from the provided $row
 */
class VerticalBoundary {
    private int $topBoundary;
    private int $bottomBoundary;

    /*
     * Calculates the top and bottom boundaries.
     * @param: The row and height that will be used as a basis.
     * @return: None.
     */
    function __construct($row, $height) {
        $this->topBoundary = max($row - 3, 0);
        $this->bottomBoundary = min($row + 3, $height - 1);
    }

    /*
     * Getter for the field $topBoundary.
     * @param: None.
     * @return the field $topBoundary.
     */
    public function getTopBoundary() {
        return $this->topBoundary;
    }

    /*
     * Getter for the field $bottomBoundary.
     * @param: None.
     * @return the field $bottomBoundary.
     */
    public function getBottomBoundary() {
        return $this->bottomBoundary;
    }
}