<?php
require_once __DIR__."/ResponseParser.php";

class WebClient {
    private string $serverURL;
    private string $pid;
    private ResponseParser $responseParser;

    function __construct($serverURL) {
        $this->serverURL = ($serverURL[strlen($serverURL) - 1] === "/") ? $serverURL : $serverURL."/";
        $this->responseParser = new ResponseParser();
    }

    function setServerURL($serverURL) {
        $this->serverURL = $serverURL;
    }

    function checkServer() {
        return $this->responseParser->getInfo($this->serverURL."info");
    }

    function newGame($strategy) {
        $result = $this->responseParser->getNew(
                $this->serverURL."new/?strategy=".$strategy);
        if (!$result->isError()) {
            $this->pid = $result->getValue();
        }
        return $result;
    }

    function playGame($slot, $width, $colHeights) {
        $result = $this->responseParser->getPlay($this->serverURL."play?pid=".$this->pid."&move=".$slot,
                $width, $colHeights);
        if (!$result->isError() && $result->getValue()->getPlayerMove() !== $slot) {
            return Result::error("Invalid response. Expected slot ".$slot." did not match the received slot ".$result->getValue()->getPlayerMove());
        }
        return $result;
    }
}