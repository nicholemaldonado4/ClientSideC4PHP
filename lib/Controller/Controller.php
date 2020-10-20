<?php
require_once dirname(__DIR__)."/View/ResolutionSelection.php";
require_once dirname(__DIR__)."/View/ConsoleUI.php";
require_once dirname(__DIR__)."/Exceptions/InputException.php";
require_once dirname(__DIR__)."/Exceptions/InformationMismatchException.php";
require_once dirname(__DIR__)."/Model/WebClient.php";
require_once dirname(__DIR__)."/Game/Board.php";
require_once dirname(__DIR__)."/MoveStrategies/MoveStrategy.php";

class Controller {
    private ConsoleUI $view;
    private WebClient $model;
    private Board $board;

    function __construct() {
        $this->view = new ConsoleUI();
    }

    private function getServer(){
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

    private function setupGame() {
        $info = $this->getServer();
        $resolutionSelection = ResolutionSelection::RETRY;
        while ($resolutionSelection == ResolutionSelection::RETRY) {
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

    private function getSlot() {
        $result = null;
        $slotStr = $this->view->getSlot($this->board->getWidth());
        while ($result === null || $result->isError()) {
            $moveStrat = MoveStrategy::createMoveStrategy($slotStr);
            $result = $moveStrat->evaluateMoveSelection($this->board);

            if ($result->isError()) {
                $slotStr = $this->view->getSlot($this->board->getWidth(), $result->getError());
            }
        }
        return $result->getValue();
    }

    private function delegateGamePlay(Play $play) {
        $this->board->AddMoves($play->getPlayerMove(), $play->getCompMove());

        if ($play->getGameStatus() !== GameStatus::NONE) {
            if ($play->getGameStatus() != GameStatus::DRAW && !$this->board->changeColors($play->getRow())) {
                return $this->view->restartOrQuit("The row returned contained malformed data");
            }
            $this->view->showBoard($this->board, $play->getGameStatus(), (
                    $play->getCompMove() !== -1 ? $play->getCompMove() + 1: $play->getCompMove()));
            return ResolutionSelection::QUIT;
        }
        $this->view->showBoard($this->board, GameStatus::NONE, $play->getCompMove() + 1);
        return null;
    }

    private function playGame() {
        $slot = null;
        try {
            $slot = $this->getSlot();
        }
        catch (InformationMismatchException $e) {
            return $this->view->restartOrQuit($e->getMessage());
        }

        $resolutionSelection = ResolutionSelection::RETRY;
        while ($resolutionSelection == ResolutionSelection::RETRY) {

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

    function runTasks() {
        $restartSetup = ResolutionSelection::RESTART;

        // Sets game.
        while ($restartSetup === ResolutionSelection::RESTART) {
            $restartSetup = $this->setupGame();
            if ($restartSetup == ResolutionSelection::QUIT) {
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
    function runController() {
        if (!$this->view->setUpUserInput()) {
            return;
        }
        try {
            while($this->runTasks());
        }
        catch (InputException $e) {
            $this->view->printException($e);
        }
        finally {
            $this->view->closeUserInput();
        }
    }
}