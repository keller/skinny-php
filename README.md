Skinny PHP
==========

"Come on skinny love"

Skinny PHP is a simple framework taken from a simple api I created. It handles urls routing and returns the data in JSON. That's about all it does. I wanted something super tiny that I could modify the framework if I wanted more functionality. It's skinny (not fat).


A simple app might look something like:

    <?php
    require 'skinny.php';

    $app = new skinnyApp();

    $app->get('/', function() {
      return array();
    });

    $app->get('/hello/(.*)', function($name) {
      return "Hello $name!";
    });