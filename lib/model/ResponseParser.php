<?php
// Nichole Maldonado
// Extra Credit - ResponseParser.dart
// Oct 20, 2020
// Dr. Cheon, CS3360

require_once dirname(__DIR__) . "/result/Info.php";
require_once dirname(__DIR__) . "/result/Play.php";
require_once dirname(__DIR__) . "/result/Result.php";

/*
 * A parser that parses responses and returns encapsulated data.
 * Parses a total of 3 pages: info/index.php, new/index.php, and play/index.php.
 * info/index.php contains general information about the game including the
 * board width, height, and strategies for the computer. new/index.php and
 * play/index.php require a response field. If the response is false, then the
 * reason for the page failure should be included in the json data.
 * new/index.php contains the pid of the game. play/index.php contains the
 * move of the user and move of the computer, if the player's move was not
 * a winning move. Each move consists of a slot, whether the move was a
 * winning move, drawing move, and the rows if won.
 */
class ResponseParser {
    private const WIDTH = 'width';
    private const HEIGHT = 'height';
    private const STRATEGIES = 'strategies';
    private const RESPONSE = 'response';
    private const REASON = 'reason';
    private const PID = 'pid';
    private const SLOT = 'slot';
    private const IS_WIN = 'isWin';
    private const IS_DRAW = 'isDraw';
    private const ROW = 'row';
    private const ACK_MOVE = 'ack_move';
    private const MOVE = 'move';

    private $decodedJson;

    /*
     * Parses the Info from the info/index.php page.
     * Requests the page at path and passes in verifications to check the
     * json response. Verifies the width, height, and strategies.
     * @param: None.
     * @return: result with game Info if true. Otherwise returns
     *          a result with a specific error message.
     */
    function parseInfo() {
        if (!property_exists($this->decodedJson, self::WIDTH) || !is_int($this->decodedJson->width)) {
            return Result::error("Invalid response. game missing width or was not an integer");
        }
        if (!property_exists($this->decodedJson, self::HEIGHT) || !is_int($this->decodedJson->height)) {
            return Result::error("Invalid response. game missing height or was not an integer");
        }
        if (!property_exists($this->decodedJson, self::STRATEGIES) || !is_array($this->decodedJson->strategies)) {
            return Result::error("Invalid response. game missing strategies");
        }

        // Make sure strategies are all strings.
        foreach ($this->decodedJson->strategies as $strategy) {
            if (!is_string($strategy)) {
                return Result::error("Invalid response. Strategies can only be strings.");
            }
        }
        if ($this->decodedJson->width < 7) {
            return Result::error("Invalid response. game board must have a width of at least 7");
        }
        if ($this->decodedJson->height < 6) {
            return Result::error("Invalid response. game board must have a width of at least 6");
        }
        if (sizeOf($this->decodedJson->strategies) < 1) {
            return Result::error("Invalid response. game must have at least one strategy");
        }
        return Result::value(new Info($this->decodedJson));
    }

    /*
     * Parses the pid from the new/index.php.
     * @param: None.
     * @return: The result error returned if pid not included.
     *          Otherwise, result with the pid is returned.
     */
    public function parseNew() {
        if (!property_exists($this->decodedJson, self::PID) || !is_string($this->decodedJson->pid)) {
            return Result::error("Invalid request. Either the pid was not specified or had the wrong type");
        }
        return Result::value($this->decodedJson->pid);
    }

    /*
     * Verifies that the json $response contains a response field.
     * @param: The json data.
     * @return: result with error returned if the response field
     *          does not exist or the response is false. Otherwise
     *          null is returned.
     */
    private function evaluateJsonDecode($response) {
        if (!property_exists($response, self::RESPONSE)) {
            return Result::error("No response specified");
        }
        if (!is_bool($response->response)) {
            return Result::error("The response did not contain a boolean value");
        }
        if (!$response->response) {
            if (!property_exists($response, self::REASON) || !is_string($response->reason)) {
                return Result::error("False response found, but a reason was not specified or was not a string");
            }
            return Result::error($response->reason);
        }
        return null;
    }

    /*
     * Verifies that the json $decodedResponse for is a valid move request.
     * @param: The width to ensure that the slot in the decodedResponse
     *         is less than the width. The colHeights to ensure that a piece
     *         can be still be added to the column.
     * @return: result error if response does not fit the expected format.
     *          Otherwise, a result with Play is returned.
     */
    private function checkMove(&$decodedResponse, $width, $colHeights) {
        /// Check slot in range and can fit in board.
        if (!property_exists($decodedResponse, self::SLOT)) {
            return Result::error("Invalid response. A slot was not provided");
        }
        if (!is_int($decodedResponse->slot)) {
            return Result::error("Invalid slot response. The slot was not an integral value");
        }
        if ($decodedResponse->slot < 0 || $decodedResponse->slot >= $width ||
                $colHeights[$decodedResponse->slot] < 1) {
            return Result::error("Invalid slot response. Slot: ".$decodedResponse->slot);
        }

        /// Verify isWin, isDraw, and row fields exist.
        if (!property_exists($decodedResponse, self::IS_WIN) ||
                !is_bool($decodedResponse->isWin)) {
            return Result::error("Either isWin does not exist or is not a boolean");
        }
        if (!property_exists($decodedResponse, self::IS_DRAW) ||
                !is_bool($decodedResponse->isDraw)) {
            return Result::error("Either isDraw does not exist or is not a boolean");
        }
        if (!property_exists($decodedResponse, self::ROW) ||
                !is_array($decodedResponse->row)) {
            return Result::error("Either the row does not exist or was not a list");
        }
        
        $rowSize = sizeOf($decodedResponse->row);
        /// Check that if isWin, then a row is provided. Check that if isDraw, the
        /// a row is not provided.
        if ($decodedResponse->isWin) {
            if ($rowSize < 8 || $rowSize % 2 != 0) {
                return Result::error("The game was specified as a win, but the winning row did not contain eight values");

            }
            else if ($rowSize > 8){
                $list = $this->createTuples($decodedResponse->row, $rowSize);
                if ($list === false) {
                    return Result::error("The row contained non-integral values");
                }
                $decodedResponse->row = $list;
            }
        }

        if ($decodedResponse->isDraw && $rowSize != 0) {
            return Result::error("The game was specified as a draw, but a winning row was included");
        }
        if(!$decodedResponse->isWin && $rowSize!= 0) {
            return Result::error(
                    "Conflicting response. A winning row was specified, but the game was not marked as a win");
        }
        return null;
    }

    /*
     * Converts the $rows of tuples to a 1D array.
     * @param: An array of tuples.
     * @return: The $rows as a 1D array.
     */
    private function toList(array $rows) {
        $newRow = array();
        $numTuples = 0;
        foreach ($rows as $row) {
            $newRow[] = $row[0];
            $newRow[] = $row[1];
            if (++$numTuples == 4) {
                break;
            }
        }
        return $newRow;
    }

    /*
     * Creates an ordered list of points for $rows.
     * @param: The row of points and the $size of the $rows.
     * @return: false if the row contains non integral values,
     *          otherwise a 1D sorted array.
     */
    private function createTuples(array $rows, $size) {
        $newRows = array();
        $currTuple = 0;
        for ($i = 0; $i < $size; $i += 2) {
            if (!is_int($rows[$i]) || !is_int($rows[$i])) {
                return false;
            }
            $newRows[$currTuple++] = array($rows[$i], $rows[$i + 1]);
        }
        usort($newRows, function($tupleA, $tupleB){
            // Sort by col first, then by row.
            $comparison =  $tupleA[0] <=> $tupleB[0];
            if ($comparison == 0) {
                return $tupleA[1] <=> $tupleB[1];
            }
            return $comparison;
        });
        return $this->toList($newRows);
    }

    /*
     * Creates a Play from the json response.
     * @param: The $width and $colHeights to ensure that the slots provided are correct.
     * @return: result with Play for a valid response. Otherwise
     *          a result error is returned.
     */
    function parsePlay($width, array $colHeights) {
        /// Verify user move.
        if (!property_exists($this->decodedJson, self::ACK_MOVE) || !is_object($this->decodedJson->ack_move)) {
            return Result::error(
                    "Expecting ack_move response, but the response was not received or was not the correct data type");
        }
        $moveCheck = $this->checkMove($this->decodedJson->ack_move, $width, $colHeights);
        if ($moveCheck !== null) {
            return $moveCheck;
        }

        if ($this->decodedJson->ack_move->isWin || $this->decodedJson->ack_move->isDraw) {
            if (property_exists($this->decodedJson, self::MOVE)) {
                return Result::error(
                    "Mismatched response. The response was marked as a win or draw, but a computer move was included");
            }
            else {
                return Result::value(new Play($this->decodedJson));
            }
        }
        if (!property_exists($this->decodedJson, self::MOVE) || !is_object($this->decodedJson->move)) {
            return Result::error(
                    "Expecting move response, but the response was not received or was not the correct data type");
        }

        /// Verify computer move.
        $moveCheck = $this->checkMove($this->decodedJson->move, $width, $colHeights);
        if ($moveCheck !== null) {
            return $moveCheck;
        }
        return Result::value(new Play($this->decodedJson));
    }

    /*
     * Decodes the $json response and assigns to $decodedJson.
     * @param: $response that will be decoded and $checkResponse which
     *         will identify that a response field exists.
     * @return: A result with the error if a problem occurs while decoding.
     *          Otherwise null.
     */
    function decodeJson($response, $checkResponse) {
        $decodedJson = json_decode($response);
        if ($response === null) {
            return Result::error("Unable to parse json");
        }
        if (!is_object($decodedJson)) {
            return Result::error("Invalid json data");
        }

        // If checkResponse is true, ensure that page contained response.
        $result = null;
        if ($checkResponse) {
            $result = $this->evaluateJsonDecode($decodedJson);
        }
        if ($result === null) {
            $this->decodedJson = $decodedJson;
        }
        return $result;
    }
}