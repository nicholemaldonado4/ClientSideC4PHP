<?php
require_once __DIR__."/Precedence.php";

/*
 * Record stores a $precedence of the move and $col of the move.
 */
class Record{
    private int $col;
    private int $precedence;

    /*
     * Default constructor that set $col to -1.
     * @param: None
     * @return: None.
     */
    function __construct() {
        $this->col = -1;
        $this->precedence = Precedence::NONE;
    }

    /*
     * Populates a record with the $col and $precedence.
     * @param: The $col and $precedence of the move.
     * @return: The populated record.
     */
    static function populateRecord($col, $precedence = Precedence::FOUR) {
        $record = new Record();
        $record->setCol($col);
        $record->setPrecedence($precedence);
        return $record;
    }

    /*
     * Getter for field $col.
     * @param: None.
     * @return: The $col.
     */
    function getCol() {
        return $this->col;
    }

    /*
     * Getter for field $precedence.
     * @param: None.
     * @return: The $col.
     */
    function getPrecedence() {
        return $this->precedence;
    }

    /*
     * Setter for field $col.
     * @param: The $col to set the field to.
     * @return: None.
     */
    function setCol($col) {
        $this->col = $col;
    }

    /*
     * Setter for field $precedence.
     * @param: The $precedence to set the field to.
     * @return: None.
     */
    function setPrecedence($precedence) {
        $this->precedence = $precedence;
    }
}