<?php
require_once dirname(__DIR__).'/Exceptions/InputException.php';
require_once __DIR__.'/ResolutionSelection.php';
require_once __DIR__.'/Colorize.php';
require_once dirname(__DIR__).'/Game/Board.php';
require_once dirname(__DIR__).'/Game/GameStatus.php';

class ConsoleUI {
    private $userInput;

    function setUpUserInput() {
        $this->userInput = fopen('php://stdin', 'r');
        if ($this->userInput === false) {
            echo "Unable to open stream to get user input. Please restart the application.\r\n";
            return false;
        }
        return true;
    }

    function closeUserInput() {
        fclose($this->userInput);
    }

    function printException($error) {
        echo "{$error->getMessage()}. Please restart the application.\r\n";
    }

    private function getInput() {
        $serverURL = fgets($this->userInput);
        if ($serverURL === null) {
            throw new InputException("Unable to open stream to get user input");
        }
        return trim($serverURL);
    }

    function promptServer(string $error = null) {
        if ($error != null) {
            echo "Error: $error. Please re-enter the server url.\r\n\r\n";
        }
        $foundURL = false;
        $defaultURL = "https://cssrvlab01.utep.edu/Classes/cs3360/nmaldonado2/";
        while (!$foundURL) {
            echo "Enter the server url [default=$defaultURL]: ";
            $serverURL = $this->getInput();
            if ($serverURL === "") {
                echo "No server url was provided. The default will be used.\r\n";
                $serverURL = $defaultURL;
                $foundURL = true;
            }
            else {
                if (filter_var($serverURL, FILTER_VALIDATE_URL) === false) {
                    echo "Invalid URL. Please try again.\r\n\r\n";
                }
                else {
                    $foundURL = true;
                }
            }
        }
        echo "Obtaining server information....\r\n\r\n";
        return $serverURL;
    }

    // Assume $lowerBound is > 0
    private function getResponseInRange($output, $lowerBound, $upperBound, $allowEmpty = false) {
        $selection = $lowerBound - 1;
        while ($selection < $lowerBound || $selection > $upperBound) {
            echo "$output";
            $response = $this->getInput();
            if ($response === "" && $allowEmpty) {
                return -1;
            }

            $selection = intval($response);
            if ($selection < $lowerBound || $selection > $upperBound) {
                echo "Invalid selection: {$response}\r\n\r\n";
            }
        }
        return $selection;
    }

    function promptStrategy(array $strategies) {
        $output = "Select the server strategy. ";
        $strategyLength = sizeOf($strategies);
        for ($i = 0; $i < $strategyLength; $i++) {
            $output = $output.($i + 1).". {$strategies[$i]} ";
        }
        $output = $output."[default = {$strategies[0]}]: ";
        $selection = $this->getResponseInRange($output, 1, $strategyLength, true);
        if ($selection === -1) {
            $selection = 1;
        }
        echo "Selected strategy: {$strategies[$selection - 1]}\r\n\r\n";
        return $strategies[$selection - 1];
    }

    private function mapResponseToSelection($selection) {
        $resolutionSelection = null;
        switch ($selection) {
            case 1:
                $resolutionSelection = ResolutionSelection::RETRY;
                break;
            case 2:
                $resolutionSelection = ResolutionSelection::RESTART;
                break;
            default:
                $resolutionSelection = ResolutionSelection::QUIT;
        }
        return $resolutionSelection;
    }

    function restartOrQuit($error) {
        echo "Error: {$error}\r\n\r\n";
        $output = <<< Selection
        Select an option\r\n
        1. Restart (Enter server)\r\n
        2. Quit\r\n
        Select 1 or 2: 
        Selection;
        return $this->mapResponseToSelection($this->getResponseInRange($output, 1, 2) + 1);
    }

    private function printGameStatus($status) {
        $result = "";
        switch($status) {
            case GameStatus::WON:
                $result = Colorize::green('You won!');
                break;
            case GameStatus::LOST:
                $result = Colorize::red('You Lost :(');
                break;
            case GameStatus::DRAW:
                $result = Colorize::blue('The game ended in a draw.');
                break;
        }
        echo $result."\r\n";
    }

    function showBoard(Board $board, $status = GameStatus::NONE, $compMove = -1) {
        foreach ($board->getRows() as $row) {
            $line = array_reduce($row, function($carryStr, $player) use ($status) {
                if ($status === GameStatus::WON && $player->getColoredPiece()) {
                    $pieceColor = Colorize::green($player->getToken());
                }
                else if ($status === GameStatus::LOST && $player->getColoredPiece()) {
                    $pieceColor = Colorize::red($player->getToken());
                }
                else {
                    $pieceColor = $player->getToken();
                }
                return $carryStr." ".$pieceColor;
            });
            echo $line."\r\n";
        }
        echo " ".implode(" ", range(1, $board->getWidth()))."\r\n";
        if ($compMove !== -1) {
            echo "Computer move: {$compMove}\r\n";
        }
        if ($status !== GameStatus::NONE) {
            $this->printGameStatus($status);
        }
    }

    function getSlot($boardWidth, $msg = null) {
        if ($msg !== null) {
            echo $msg."\r\n";
        }
        echo "Enter a slot [1 - {$boardWidth}] or 'cheat' for cheat mode: ";
        $input = $this->getInput();
        echo "\r\n";
        return $input;
    }
}