<?php
// Nichole Maldonado
// Extra Credit - ResponseParser.dart
// Oct 20, 2020
// Dr. Cheon, CS3360

require_once dirname(__DIR__)."/Result/Info.php";
require_once dirname(__DIR__)."/Result/Play.php";
require_once dirname(__DIR__)."/Result/Result.php";

/*
 * A parser that calls php page, parses response, and returns encapsulated data.
 * Calls a total of 3 pages: info/index.php, new/index.php, and play/index.php.
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
    /*
     * Retrieves Info from the info/index.php page.
     * Requests the page at path and passes in verifications to check the
     * json response. Verifies the width, height, and strategies.
     * @param: The path for info/index.php
     * @return: Result with game Info if true. Otherwise returns
     *          a Result with a specific error message.
     */
    function getInfo($path) {
        return $this->getPageInfo($path, function($response){
            if (!property_exists($response, "width") || !is_int($response->width)) {
                return Result::error("Invalid response. Game missing width or was not an integer");
            }
            if (!property_exists($response, "height") || !is_int($response->height)) {
                return Result::error('Invalid response. Game missing height or was not an integer');
            }
            if (!property_exists($response, "strategies") || !is_array($response->strategies)) {
                return Result::error('Invalid response. Game missing strategies');
            }

            // Make sure strategies are all strings.
            foreach ($response->strategies as $strategy) {
                if (!is_string($strategy)) {
                    return Result::error("Invalid response. Strategies can only be strings.");
                }
            }
            if ($response->width < 7) {
                return Result::error(
                    'Invalid response. Game board must have a width of at least 7');
            }
            if ($response->height < 6) {
                return Result::error(
                    'Invalid response. Game board must have a width of at least 6');
            }
            if (sizeOf($response->strategies) < 1) {
                return Result::error(
                    'Invalid response. Game must have at least one strategy');
            }
            return Result::value(new Info($response));
        });
    }

    /*
     * Retrieves the pid from the new/index.php.
     * @param: The path for the requested page.
     * @return: The Result error returned if pid not included.
     *          Otherwise, Result with the pid is returned.
     */
    public function getNew($path) {
        return $this->getPageInfo($path, function($response) {
            if (!property_exists($response, "pid") || !is_string($response->pid)) {
                return Result::error("Invalid request. Either the pid was not specified or had the wrong type");
            }
            return Result::value($response->pid);
        }, true);
    }

    /*
     * Verifies that the json response contains a response field.
     * @param: The json data.
     * @return: Result with error returned if the response field
     *          does not exist or the response is false. Otherwise
     *          null is returned.
     */
    private function evaluateJsonDecode($response) {
        if (!property_exists($response, "response")) {
            return Result::error("No response specified");
        }
        if (!is_bool($response->response)) {
            return Result::error("The response did not contain a boolean value");
        }
        if (!$response->response) {
            if (!property_exists($response, "reason") || !is_string($response->reason)) {
                return Result::error("False response found, but a reason was not specified or was not a string");
            }
            return Result::error($response->reason);
        }
        return null;
    }

    /*
     * Verifies that the json decodedResponse for is a valid move request.
     * @param: The width to ensure that the slot in the decodedResponse
     *         is less than the width. The colHeights to ensure that a piece
     *         can be still be added to the column.
     * @return: Result error if response does not fit the expected format.
     *          Otherwise, a Result with Play is returned.
     */
    private function checkMove($decodedResponse, $width, $colHeights) {
        /// Check slot in range and can fit in board.
        if (!property_exists($decodedResponse, "slot")) {
            return Result::error('Invalid response. A slot was not provided');
        }
        if (!is_int($decodedResponse->slot)) {
            return Result::error('Invalid slot response. The slot was not an integral value');
        }
        if ($decodedResponse->slot < 0 || $decodedResponse->slot >= $width ||
                $colHeights[$decodedResponse->slot] < 1) {
            return Result::error('Invalid slot response. Slot: '.$decodedResponse->slot);
        }

        /// Verify isWin, isDraw, and row fields exist.
        if (!property_exists($decodedResponse, "isWin") ||
                !is_bool($decodedResponse->isWin)) {
            return Result::error('Either isWin does not exist or is not a boolean');
        }
        if (!property_exists($decodedResponse, "isDraw") ||
                !is_bool($decodedResponse->isDraw)) {
            return Result::error('Either isDraw does not exist or is not a boolean');
        }
        if (!property_exists($decodedResponse, "row") ||
                !is_array($decodedResponse->row)) {
            return Result::error('Either the row does not exist or was not a list');
        }

        // Make sure row only consists of integers.
        $rowSize = 0;
        foreach ($decodedResponse->row as $value) {
            if (!is_int($value)) {
                return Result::error("Invalid response. The row can only contain integral values.");
            }
            $rowSize++;
        }

        /// Check that if isWin, then a row is provided. Check that if isDraw, the
        /// a row is not provided.
        if ($decodedResponse->isWin && $rowSize != 8) {
            return Result::error('The game was specified as a win, but the winning row did not contain eight values');
        }
        if ($decodedResponse->isDraw && $rowSize != 0) {
            return Result::error('The game was specified as a draw, but a winning row was included');
        }
        if(!$decodedResponse->isWin && $rowSize!= 0) {
            return Result::error('Conflicting response. A winning row was specified, but the game was not marked as a win');
        }
        return null;
    }

    /*
     * Creates a Play from the json response.
     * @param: The $path of the requested page. The $width and $colHeights
     *         to ensure that the slots provided are correct.
     * @return: Result with Play for a valid response. Otherwise
     *          a Result error is returned.
     */
    function getPlay($path, $width, array $colHeights) {
        return $this->getPageInfo($path, function($response) use ($width, $colHeights) {
            /// Verify user move.
            if (!property_exists($response, "ack_move") || !is_object($response->ack_move)) {
                return Result::error('Expecting ack_move response, but the response was not received or was not the correct data type');
            }
            $moveCheck = $this->checkMove($response->ack_move, $width, $colHeights);
            if ($moveCheck !== null) {
                return $moveCheck;
            }

            if ($response->ack_move->isWin || $response->ack_move->isDraw) {
                if (property_exists($response, "move")) {
                    return Result::error("Mismatched response. The response was marked as a win or draw, but a computer move was included");
                }
                else {
                    return Result::value(new Play($response));
                }
            }
            if (!property_exists($response, "move") || !is_object($response->move)) {
                return Result::error("Expecting move response, but the response was not received or was not the correct data type");
            }

            /// Verify computer move.
            $moveCheck = $this->checkMove($response->move, $width, $colHeights);
            if ($moveCheck !== null) {
                return $moveCheck;
            }
            return Result::value(new Play($response));
        });
    }

    /*
     * Calls the paths and returns the parsed information from the page.
     * @param: parseResponse which will perform checks on the response
     *         to ensure that it is valid. If checkResponse is true,
     *         checks that the response part of the JSON is valid.
     * @return: A Result with the data filled. If checks fail
     *          or unable to call the path, a Result error is returned.
     */
    private function getPageInfo($path, Callable $parseResponse, $checkResponse = false) {
        $pageInfo = file_get_contents($path);
        if ($pageInfo === false) {
            return Result::error("Unable to access page contents");
        }
        $response = json_decode($pageInfo);
        if ($response === null) {
            return Result::error("Unable to parse json at $path");
        }
        if (!is_object($response)) {
            return Result::error("Invalid json data");
        }
        if ($checkResponse == true) {
            $jsonDecodedResponse = $this->evaluateJsonDecode($response);
            if ($jsonDecodedResponse !== null) {
                return $jsonDecodedResponse;
            }
        }
        return $parseResponse($response);
    }
}