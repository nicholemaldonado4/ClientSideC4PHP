<?php
// Nichole Maldonado
// Extra Credit - Result.php
// Oct 20, 2020
// Dr. Cheon, CS3360

/*
 * A message that contains the result from a valid operation or stores the error.
 * Can either store the error or message of a successful operation.
 */
class Result {
    private $value;
    private $error;

    /*
     * Creates a Result with $value and $error set.
     * @param: The $value and $error.
     * @return: None.
     */
    function __construct($value, $error) {
        $this->error = $error;
        $this->value = $value;
    }

    /*
     * Creates a result with an $error only.
     * @param: The $error.
     * @return: A Result with an $error.
     */
    static function error($error) {
        return new Result(null, $error);
    }

    /*
     * Creates a result with a $value only.
     * @param: The $value.
     * @return: A result with a $value.
     */
    static function value($value) {
        return new Result($value, null);
    }

    /*
     * Getter for the field $value.
     * @param: None.
     * @return: The $value.
     */
    function getValue() {
        return $this->value;
    }

    /*
     * Getter for the field $error.
     * @param: None.
     * @return: The error that occurred.
     */
    function getError() {
        return $this->error;
    }

    /*
     * Determines if $error exists.
     * @param: None.
     * @return: True if error exists, false otherwise.
     */
    function isError() {
        return $this->error !== null;
    }
}