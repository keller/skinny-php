<?php
require 'skinny.php';

$app = new skinnyApp();

$app->get('/', function() {
  return array();
});

$app->get('/hello/(.*)', function($name) {
  return "Hello $name!";
});