<?php
// Nichole Maldonado
// Extra Credit - ConsoleUI.php
// Oct 20, 2020
// Dr. Cheon, CS3360

require_once __DIR__.'/Colorize.php';
require_once __DIR__.'/ResolutionSelection.php';
require_once dirname(__DIR__).'/Exceptions/InputException.php';
require_once dirname(__DIR__).'/Game/Board.php';
require_once dirname(__DIR__).'/Game/GameStatus.php';

/*
 * A user interface that gets user input and displays the board game play.
 * Gets the server url from the game, the strategy for the game,
 * and the column to put a piece.
 */
class ConsoleUI implements ResolutionSelection, GameStatus {
    private $userInput;

    /*
     * Opens an input stream.
     * @param: None.
     * @return: True if successfully able to open stream, false otherwise.
     */
    function setupUserInput() {
        $this->userInput = fopen('php://stdin', 'r');
        if ($this->userInput === false) {
            echo "Unable to open stream to get user input. Please restart the application.\r\n";
            return false;
        }
        return true;
    }

    /*
     * Closes the input stream.
     * @param: None.
     * @return: None.
     */
    function closeUserInput() {
        fclose($this->userInput);
    }

    /*
     * Prints the $exception's message.
     * @param: An Exception.
     * @return: None.
     */
    function printException($exception) {
        echo "{$exception->getMessage()}. Please restart the application.\r\n";
    }

    /*
     * Gets the input and returns it as a string.  Throws an InputException if unable to get user input.
     * @param: None.
     * @return: The string of the input.
     */
    private function getInput() {
        $serverURL = fgets($this->userInput);
        if ($serverURL === null) {
            throw new InputException("Unable to open stream to get user input");
        }
        return trim($serverURL);
    }

    /*
     * Gets the server url form the user. If the user does not provide a url, then
     * the default one is provided.
     * @param: The scanner for user input.
     * @return: None.
     */
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

    /*
     * Writes the output to the console and gets the user's response.
     * @param: The scanner for user input, lowerBound, upperBound, and
     *         boolean allowEmpty. Assume lowerBound > 0.
     * @return: a value in the range [lowerBound, upperBound] unless allowEmpty is true.
     * If allowEmpty and the response is empty, -1 will be returned.
     */
    private function getResponseInRange($output, $lowerBound, $upperBound, $allowEmpty = false) {
        $selection = $lowerBound - 1;
        while ($selection < $lowerBound || $selection > $upperBound) {
            echo $output;
            $response = $this->getInput();
            echo "\r\n";
            // Returns -1 if no response.
            if ($response === "" && $allowEmpty) {
                return -1;
            }

            // Otherwise, ensure response in range.
            $selection = intval($response);
            if ($selection < $lowerBound || $selection > $upperBound) {
                echo "Invalid selection: {$response}\r\n\r\n";
            }
        }
        return $selection;
    }

    /*
     * Gets the strategy selection from the user based on strategies.
     * @param: The array of strategies.
     * @return: A strategy from the array. If user does not select a
     *          strategy, the first strategy in strategy will be selected.
     */
    function promptStrategy(array $strategies) {
        $output = "Select the server strategy. ";
        $strategyLength = sizeOf($strategies);
        for ($i = 0; $i < $strategyLength; $i++) {
            $output = $output.($i + 1).". {$strategies[$i]} ";
        }
        $output = $output."[default = {$strategies[0]}]: ";
        $selection = $this->getResponseInRange($output, 1, $strategyLength, true);
        if ($selection == -1) {
            $selection = 1;
        }
        echo "Selected strategy: {$strategies[$selection - 1]}\r\n\r\n";
        return $strategies[$selection - 1];
    }

    /*
     * Maps an integer selection to a ResolutionSelection enum value.
     * @param: The selection [1, 3].
     * @return: The mapped selection.
     */
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

    /*
     * Determines if the user wants to restart or quit the application.
     * Prints the error before asking for the option.
     * @param: The potential error.
     * @return: The ResolutionSelection enum value.
     */
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

    /*
     * Prints if the game was a win, loss, or draw based on the status.
     * @param: The game status.
     * @return: None.
     */
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

    /*
     * Displays the $board with all its pieces. Also
     * prints the status and compMove if it does not equal -1.
     * @param the $board, $status, and computer's move.
     * @return: None.
     */
    function showBoard(Board $board, $status = GameStatus::NONE, $compMove = -1) {
        foreach ($board->getRows() as $row) {
            $line = array_reduce($row, function($carryStr, $player) use ($status) {
                // If status was win or loss, color the pieces that are the
                // winning or losing pieces.
                if ($status == GameStatus::WON && $player->getColoredPiece()) {
                    $pieceColor = Colorize::green($player->getToken());
                }
                else if ($status == GameStatus::LOST && $player->getColoredPiece()) {
                    $pieceColor = Colorize::red($player->getToken());
                }
                else {
                    $pieceColor = $player->getToken();
                }
                return $carryStr." ".$pieceColor;
            });
            echo $line."\r\n";
        }

        // Print indices
        echo " ".implode(" ", range(1, $board->getWidth()))."\r\n";

        // Print computer move if it exists.
        if ($compMove != -1) {
            echo "Computer move: {$compMove}\r\n";
        }

        // Prints game status if it exists.
        if ($status != GameStatus::NONE) {
            $this->printGameStatus($status);
        }
    }

    /*
     * Gets the slot that the user wants to add a piece to.
     * A valid slot option is [0, boardWidth]. msg is a special
     * cheat message.
     * @param: The boardWidth and optional error msg.
     * @return The slot selection from the user.
     */
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