<?php
require_once dirname(__DIR__)."/Result/Result.php";
require_once dirname(__DIR__)."/Result/Info.php";
require_once dirname(__DIR__)."/Result/Play.php";

class ResponseParser {
    function getInfo($path) {
        return $this->getPageInfo($path, function($response){
            if (!property_exists($response, "width") || !is_int($response->width)) {
                return Result::error("Invalid response. Game missing width or width is not an integer");
            }
            if (!property_exists($response, "height") || !is_int($response->height)) {
                return Result::error('Invalid response. Game missing height or height is not an integer');
            }
            if (!property_exists($response, "strategies") || !is_array($response->strategies)) {
                return Result::error('Invalid response. Game missing strategies');
            }
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

    public function getNew($path) {
        return $this->getPageInfo($path, function($response) {
            if (!property_exists($response, "pid") || !is_string($response->pid)) {
                return Result::error("Invalid request. Either the pid was not specified or had the wrong type");
            }
            return Result::value($response->pid);
        }, true);
    }

    private function evaluateJsonDecode($response) {
        if (!property_exists($response, "response")) {
            return Result::error("No response specified");
        }
        if (!is_bool($response->response)) {
            return Result::error("The response did not contain a boolean value");
        }
        if (!$response->response) {
            if (!property_exists($response, "reason") || !is_string($response->reason)) {
                return Result::error("False response found, but a reason was not specified or was not a string.");
            }
            return Result::error($response->reason);
        }
        return null;
    }

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

    private function getPageInfo($path, Callable $responseChecker, $checkResponse = false) {
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
        if ($checkResponse === true) {
            $jsonDecodedResponse = $this->evaluateJsonDecode($response);
            if ($jsonDecodedResponse !== null) {
                return $jsonDecodedResponse;
            }
        }
        return $responseChecker($response);
    }
}