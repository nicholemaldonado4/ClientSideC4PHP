<?php
// Nichole Maldonado
// Extra Credit - WebClient.php
// Oct 20, 2020
// Dr. Cheon, CS3360

require_once __DIR__."/ResponseParser.php";

/*
 * A web client that initiates calls to the game php files.
 * Calls a total of 3 pages: info/index.php, new/index.php, and play/index.php
 * further defined in ResponseParser. For new/index.php, ensures that the
 * URL request has a query based on the strategy that the user chose. For
 * play/index.php, ensures that the URL request contains the move that the
 * user selected and pid of the current game.
 */
class WebClient {
    private string $serverURL;
    private string $pid;
    private ResponseParser $responseParser;

    /*
     * Creates a WebClient and sets the serverURL.
     * @param: The serverURL to set the serverURL field.
     * @return: None.
     */
    function __construct($serverURL) {
        $this->serverURL = ($serverURL[strlen($serverURL) - 1] === "/") ? $serverURL : $serverURL."/";
        $this->responseParser = new ResponseParser();
    }

    /*
     * Setter for the field serverURL.
     * @param: The serverURL to set the field serverURL.
     * @return: None.
     */
    function setServerURL($serverURL) {
        $this->serverURL = $serverURL;
    }

    /*
     * Has responseParser get the game Info from info/index.php.
     * @param: None.
     * @return: Returns a Result error if an error occurred when
     *          parsing, or Result Info if successful.
     */
    function checkServer() {
        return $this->responseParser->getInfo($this->serverURL."info");
    }

    /*
     * Has responseParser get the new game from new/index.php.
     * @param: The selected strategy for the game.
     * @return: a Result with error if an error occurs during parsing.
     *          Otherwise, returns a successful Result.
     */
    function newGame($strategy) {
        $result = $this->responseParser->getNew(
                $this->serverURL."new/?strategy=".$strategy);
        if (!$result->isError()) {
            $this->pid = $result->getValue();
        }
        return $result;
    }

    /*
     * Has responsePraser make a play at the provided slot. Signals
     * call to play/index.php.
     * @param: The $width of the board and the $colHeights to be used during parsing.
     *         The $slot that the user selected.
     * @return: The Result with error if one occured. Otherwise, returns successful
     *          Play data.
     */
    function playGame($slot, $width, $colHeights) {
        $result = $this->responseParser->getPlay($this->serverURL."play?pid=".$this->pid."&move=".$slot,
                $width, $colHeights);
        if (!$result->isError() && $result->getValue()->getPlayerMove() != $slot) {
            return Result::error( <<<ErrorMsg
Invalid response. Expected slot ".$slot." did not match the received slot ".$result->getValue()->getPlayerMove()
ErrorMsg
            );
        }
        return $result;
    }
}