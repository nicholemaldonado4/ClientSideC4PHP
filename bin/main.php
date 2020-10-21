#!/usr/bin/php
<?php
// Nichole Maldonado
// Extra Credit - main.php
// Oct 20, 2020
// Dr. Cheon, CS3360

require_once dirname(__DIR__)."/lib/Controller/Controller.php";

// Runs the main client side connect 4 program.
$controller = new Controller();
$controller->runController();
