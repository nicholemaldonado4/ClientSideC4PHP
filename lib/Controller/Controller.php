<?php
// Nichole Maldonado
// Extra Credit - Controller.php
// Oct 20, 2020
// Dr. Cheon, CS3360

require_once dirname(__DIR__)."/Exceptions/InformationMismatchException.php";
require_once dirname(__DIR__)."/Exceptions/InputException.php";
require_once dirname(__DIR__)."/Game/Board.php";
require_once dirname(__DIR__)."/Model/WebClient.php";
require_once dirname(__DIR__)."/MoveStrategies/MoveStrategy.php";
require_once dirname(__DIR__)."/View/ConsoleUI.php";
require_once dirname(__DIR__)."/View/ResolutionSelection.php";

/*
 * The controller that manages the entire application for Connect 4.
 * Stores a WebClient that represents the model and retrieves Web URL request.
 * Stores a ConsoleUI to get interact with the user. Also stores the
 * game's board which acts as a sub model.
 */
class Controller implements GameStatus, ResolutionSelection {
    private ConsoleUI $view;
    private WebClient $model;
    private Board $board;
    /*
     * Default constructor that sets the view to a new ConsoleUI.
     * @param: None.
     * @return: None.
     */
    function __construct() {
        $this->view = new ConsoleUI();
    }

    /*
     * Gets the server URL and gets the information for the game.
     * @param: None.
     * @return: the Info of the game.
     */
    private function getServer() {
        $this->model = new WebClient($this->view->promptServer());
        $info = null;
        while ($info === null || $info->isError()) {
            $info = $this->model->checkServer();
            if ($info->isError()) {
                $this->model->setServerURL($this->view->promptServer($info->getError()));
            }
        }
        return $info->getValue();
    }

    /*
     * Sets up game by retrieving the server URL and creating the board.
     * @param: None.
     * @return: If the setup was successful, null is returned. Otherwise,
     *          if an error occurs and the user wants to quit,
     *          ResolutionSelection's quit is returned.
     */
    private function setupGame() {
        $info = $this->getServer();
        $resolutionSelection = ResolutionSelection::RETRY;
        while ($resolutionSelection === ResolutionSelection::RETRY) {

            // Calls new/index.php
            $newResult = $this->model->newGame($this->view->promptStrategy($info->getStrategies()));
            if (!$newResult->isError()) {
                $this->board = new Board($info->getWidth(), $info->getHeight());
                $resolutionSelection = null;
            }
            else {
                $resolutionSelection = $this->view->restartOrQuit($newResult->getError());
            }
        }
        return $resolutionSelection;
    }

    /*
     * Gets the slot from user. Based on user's slot selection, a
     * RegularStrategy or CheatStrategy will be used to either check the slot
     * or return an option slot.
     * @param: None.
     * @return: a value from [0, ...] if valid. Otherwise throws an
     *          InformationMismatchException.
     */
    private function getSlot() {
        $result = null;
        $slotStr = $this->view->getSlot($this->board->getWidth());
        while ($result === null || $result->isError()) {
            $moveStrat = MoveStrategy::createMoveStrategy($slotStr);
            $result = $moveStrat->evaluateMoveSelection($this->board);

            // An error indicates that the either a cheat mode was selected and a
            // potential value should be displayed to the user or the user provided
            // an invalid move. Error means to get new move.
            if ($result->isError()) {
                $slotStr = $this->view->getSlot($this->board->getWidth(), $result->getError());
            }
        }
        return $result->getValue();
    }

    /*
     * Adds move to the $board and has the $view display won lost, or drawn game.
     * @param: The $play with the game's status and moves.
     * @return: ResolutionSelection's quit, if the game is over. If an
     *          an error occurs, quit or restart is returned. If the game is
     *          not over, null is returned.
     */
    private function delegateGamePlay(Play $play) {
        $this->board->AddMoves($play->getPlayerMove(), $play->getCompMove());

        // Have view handle a win, loss, or draw game.
        if ($play->getGameStatus() != GameStatus::NONE) {

            // If the game was a win or loss, show it one last time.
            if ($play->getGameStatus() != GameStatus::DRAW && !$this->board->changeColors($play->getRow())) {
                return $this->view->restartOrQuit("The row returned contained malformed data");
            }
            $this->view->showBoard($this->board, $play->getGameStatus(), (
                    $play->getCompMove() != -1 ? $play->getCompMove() + 1: $play->getCompMove()));
            return ResolutionSelection::QUIT;
        }
        $this->view->showBoard($this->board, GameStatus::NONE, $play->getCompMove() + 1);
        return null;
    }

    /*
     * Repeatedly plays the game. Gets the slot from the user and then gets the
     * game response from model. Has view show the result and will continue play if
     * game has not ended in a win, draw, or loss.
     * @param: None.
     * @return: ResolutionSelection's quit if game over or error occurred and user
     *          selection to quit. Returns ResolutionSelection's retry otherwise.
     */
    private function playGame() {
        // Gets slot.
        $slot = null;
        try {
            $slot = $this->getSlot();
        }
        catch (InformationMismatchException $e) {
            return $this->view->restartOrQuit($e->getMessage());
        }

        $resolutionSelection = ResolutionSelection::RETRY;
        while ($resolutionSelection === ResolutionSelection::RETRY) {

            /// Gets game response from server URL.
            $play = $this->model->playGame($slot, $this->board->getWidth(), $this->board->getColHeights());
            if ($play->isError()) {
                $resolutionSelection = $this->view->restartOrQuit($play->getError());
            }
            else {

                /// Adds response's move to game.
                $resolutionSelection = $this->delegateGamePlay($play->GetValue());
          }
        }
        return $resolutionSelection;
    }

    /*
     * Setups game and repeatedly plays game, unless user decides to quit.
     * @param: None.
     * @return: If user decides to restart, then true is returned. Otherwise
     *          false is returned.
     */
    function runTasks() {
        $restartSetup = ResolutionSelection::RESTART;

        // Sets game.
        while ($restartSetup === ResolutionSelection::RESTART) {
            $restartSetup = $this->setupGame();
            if ($restartSetup === ResolutionSelection::QUIT) {
              return false;
            }
        }
        $restartSetup = null;

        // Play game.
        $this->view->showBoard($this->board);
        while($restartSetup === null) {
            $restartSetup = $this->playGame();
        }
        return $restartSetup == ResolutionSelection::RESTART;
    }

    /*
     * Runs the main application.
     * @param: None.
     * @return: None.
     */
    function runController() {
        if (!$this->view->setUpUserInput()) {
            return;
        }
        try {
            while($this->runTasks());
        }

        // Thrown when the view cannot get user input from input stream.
        catch (InputException $e) {
            $this->view->printException($e);
        }
        finally {
            $this->view->closeUserInput();
        }
    }
}