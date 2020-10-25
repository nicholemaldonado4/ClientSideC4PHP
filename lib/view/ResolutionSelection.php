<?php
// Nichole Maldonado
// Extra Credit - ResolutionSelection.php
// Oct 20, 2020
// Dr. Cheon, CS3360

/*
 * An enum that denotes the next step for an operation or an entire program.
 */
interface ResolutionSelection {
    // Restart the program.
    const RESTART = 0;
    // Retry the task.
    const RETRY = 1;
    // Quit the program.
    const QUIT = 2;
}