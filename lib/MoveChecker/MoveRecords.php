<?php
// Nichole Maldonado
// Extra Credit - MoveRecords.php
// Oct 20, 2020
// Dr. Cheon, CS3360

require_once __DIR__."/Record.php";

/*
 * Keeps records for block move, no win move, and default move,
 * where block move has the highest precedence.
 * and default move has the lowest precedence. If a move with higher precedence is found, the
 * other moves are not saved. In the end, returns the move with the highest precedence.
 * Block move - prevent the user from winning.
 * No win move - this move will not result in a win for the computer but it is
 *               also a safe move since it ensures that the user cannot put a piece ontop and win.
 * Default move - move if all the above are not met.
 */
class MoveRecords {
    private array $records;

    /*
     * Constructor that stores the three records. Block move is at
     * index 0, no win move is at index 1, and default move is at index 2.
     * @param: None.
     * @return: None.
     */
    function __construct() {
        $this->records = array(new Record(), new Record(), new Record);
    }

    /*
     * Given an index, stores the $record at the index.
     * @param: An index from 0 - 2, the $record
     * @return: None.
     */
    private function setRecord($index, $col, $precedence) {
        $this->records[$index]->setCol($col);
        $this->records[$index]->setPrecedence($precedence);
    }

    /*
     * Sets the block move.
     * @param: The $record associated with the move.
     * @return: None.
     */
    function setBlock($col, $precedence){
        $this->setRecord(0, $col, $precedence);
    }

    /*
     * Sets the no win move.
     * @param: The $record associated with the move.
     * @return: None.
     */
    function setNoWin($col, $precedence){
        $this->setRecord(1, $col, $precedence);
    }

    /*
     * Sets the default move.
     * @param: The $record associated with the move.
     * @return: None.
     */
    function setDefault($col, $precedence){
        $this->setRecord(2, $col, $precedence);
    }

    /*
     * Gets the block move.
     * @param: None.
     * @return: the block move record.
     */
    function getBlock() {
        return $this->records[0];
    }

    /*
     * Gets the no win move.
     * @param: None.
     * @return: the no win move record.
     */
    function getNoWin() {
        return $this->records[1];
    }

    /*
     * Gets the default move.
     * @param: None.
     * @return: the default move record.
     */
    function getDefault() {
        return $this->records[2];
    }

    /*
     * Gets the $col for a given record.
     * @param: The $index of the record.
     * @return: None.
     */
    function getRecordCol($index) {
        return $this->records[$index]->getCol();
    }

    /*
     * Gets user-readable representation of the record.
     * Assumes that the record has already been populated with a column.
     * @parm: The index of the record - either 0, 1, or 2.
     * @return: The human-readable description of the record.
     */
    function recordDescription($index) {
        $description = null;
        switch ($index) {
            case 0:
                $description = 'Blocking move: ';
                break;
            case 1:
                $description = 'Recommended move: ';
                break;
            default:
                $description = 'All moves will allow the computer to win on the next move. Available move: ';
        }
        return $description.($this->records[$index]->getCol() + 1);
    }

    /*
     * Returns the index of the record that is populated. The record has the
     * highest priority.
     * @param: None.
     * @return: The index of the highest priority record that exists.
     */
    function getHighestPriorityRecord() {
        $count = sizeOf($this->records);
        for ($i = 0; $i < $count; $i++) {
            if ($this->records[$i]->getCol() != -1) {
                return $i;
            }
        }
        return -1;
    }
}