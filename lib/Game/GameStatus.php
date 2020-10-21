<?php
// Nichole Maldonado
// Extra Credit - GameStatus.php
// Oct 20, 2020
// Dr. Cheon, CS3360

/*
 * A status indicator of the game.
 */
interface GameStatus {
    // Not a win, loss, or draw.
    public const NONE = -1;
    // Game was won.
    public const WON = 0;
    // Game was a lost.
    public const LOST = 1;
    // Game was a draw.
    public const DRAW = 2;
}