<?php
// Nichole Maldonado
// Lab 1 - Record
// September 18, 2020
// Dr. Cheon, CS3360
// ResetPoint used to eliminate code duplication among the two derived classes
// LeftDiagonalStrategy and RightDiagonalStrategy. The parent, refers to
// HorizontalStrategy.php.

trait ResetPoint{

    /*
     * Set initial y values.
     * @param: None.
     * @return: None.
     */
    abstract function setInitialY();

    /*
     * Sets points back to their original position.
     * @param: the $col and $row.
     * @return: None.
     */
    function reset($row, $col) {
        parent::reset($row, $col);
        $this->setInitialY();
    }
}