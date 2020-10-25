<?php
// Nichole Maldonado
// Extra Credit - Info.php
// Oct 20, 2020
// Dr. Cheon, CS3360

/*
 * A container for Connect 4 game information. Stores the width and
 * height of the game board, as well as the strategies that the
 * computer uses to move. Gets information from JSON data.
 */
class Info {
    private int $width;
    private int $height;
    private array $strategies;

    /*
     * Getter for the field height.
     * @param: None.
     * @return: The height of the Connect 4 board.
     */
    function getHeight() {
        return $this->height;
    }

    /*
     * Getter for the field width.
     * @param: None.
     * @return: The width of the Connect 4 board.
     */
    function getWidth() {
        return $this->width;
    }

    /*
     * Getter for the field strategies.
     * @param: None.
     * @return: The strategies that the computer can use to move.
     */
    function getStrategies() {
        return $this->strategies;
    }

    /*
     * Creates an Info based on the JSONObject json.
     * @param: The JSONObject with the fields width, height, and strategies.
     * @return: None.
     */
    function __construct($response) {
        $this->width = $response->width;
        $this->height = $response->height;
        $this->strategies = $response->strategies;
    }
 }
