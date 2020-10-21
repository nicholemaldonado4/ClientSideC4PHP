<?php
// Nichole Maldonado
// Extra Credit - ResetPoint.php
// Oct 20, 2020
// Dr. Cheon, CS3360

/*
 * A mechanism to make the two points border the focal point.
 */
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