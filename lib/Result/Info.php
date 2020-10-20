<?php
class Info {
     private int $width;
     private int $height;
     private array $strategies;

     function getHeight() {
         return $this->height;
     }

     function getWidth() {
         return $this->width;
     }

     function getStrategies() {
         return $this->strategies;
     }

     function __construct($response) {
         $this->width = $response->width;
         $this->height = $response->height;
         $this->strategies = $response->strategies;
     }
 }
