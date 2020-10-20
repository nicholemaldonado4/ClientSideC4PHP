<?php
require_once __DIR__."/SmartStrategy.php";
require_once __DIR__."/RegularStrategy.php";

abstract class MoveStrategy {
    static function createMoveStrategy($slotStr) {
        return (strtolower($slotStr) === "cheat") ? new SmartStrategy() :
                new RegularStrategy(trim($slotStr));
    }
}