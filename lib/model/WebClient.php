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
     * @return: Returns a result error if an error occurred when
     *          parsing, or result Info if successful.
     */
    function getInfo() {
        return $this->getPageInfo($this->serverURL."info", function() {
            return $this->responseParser->parseInfo();
        });
    }

    /*
     * Has responseParser get the new game from new/index.php.
     * @param: The selected strategy for the game.
     * @return: a result with error if an error occurs during parsing.
     *          Otherwise, returns a successful result.
     */
    function getNew($strategy) {
        $result = $this->getPageInfo(
                $this->serverURL."new/?strategy=".$strategy, function() {
            return $this->responseParser->parseNew();
        });
        if (!$result->isError()) {
            $this->pid = $result->getValue();
        }
        return $result;
    }

    /*
     * Has responsePaser make a play at the provided slot. Signals
     * call to play/index.php.
     * @param: The $width of the board and the $colHeights to be used during parsing.
     *         The $slot that the user selected.
     * @return: The result with error if one occured. Otherwise, returns successful
     *          Play data.
     */
    function getPlay($slot, $width, $colHeights) {
        $result = $this->getPageInfo($this->serverURL."play?pid=".$this->pid."&move=".$slot,
            function() use ($width, $colHeights){
                return $this->responseParser->parsePlay($width, $colHeights);
            });
        if (!$result->isError() && $result->getValue()->getPlayerMove() != $slot) {
            return Result::error( <<<ErrorMsg
Invalid response. Expected slot $slot did not match the received slot $result->getValue()->getPlayerMove()
ErrorMsg
            );
        }
        return $result;
    }

    /*
     * Calls path and returns the parsed information from the page.
     * @param: The $path, $parseResponse that is called to process the
     *         page response, and $checkResponse, which if true, will have the
     *         $responseParser verify that a response field exists.
     */
    private function getPageInfo($path, Callable $parseResponse, $checkResponse = false) {
        $pageInfo = file_get_contents($path);
        if ($pageInfo === false) {
            return Result::error("Unable to access page contents");
        }
        $response = $this->responseParser->decodeJson($pageInfo, $checkResponse);
        if ($response !== null) {
            return $response;
        }
        return $parseResponse();
    }
}